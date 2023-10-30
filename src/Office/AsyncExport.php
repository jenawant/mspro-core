<?php

namespace MsPro\Office;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\HttpMessage\Stream\SwooleStream;
use MsPro\Exception\NormalStatusException;
use MsPro\MsProResponse;
use MsPro\Office\Excel\PhpOffice;
use MsPro\Office\Excel\XlsWriter;

/**
 * 异步队列导出，自动切割数据，输出.zip压缩文件
 */
class AsyncExport
{
    /**
     * @var string
     */
    private string $folder = BASE_PATH . '/runtime/export/';

    /**
     * 创建导出文件
     * @description 因“异步队列消息注解“采用序列化形式处理，故本方法不支持传递匿名函数处理数据，可结合DTO的属性注释dictName, path等实现关联数据取值
     * @param string $service Service类
     * @param string $dto DTO类
     * @param array $params 数据筛选参数
     * @param string $filename 要存储的文件名，最终输出.zip
     * @param bool $column_adapter DTO定义列自适应数据集列
     * @param int $row_size 单文件行数
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[AsyncQueueMessage]
    public function create(string $service, string $dto, array $params, string $filename, bool $column_adapter = false, int $row_size = 150000): void
    {
        $folder = $this->folder;
        if (!is_dir($folder)) {
            mkdir($folder, '0774', true);
        }

        $files      = [];
        $index      = 0;
        $excelDrive = \Hyperf\Config\config('msproadmin.excel_drive');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }

        container()->get($service)->mapper->listQuerySetting($params, false)
            ->chunk($row_size, function ($data) use (&$index, $filename, &$files, $excel, $folder, $column_adapter) {
                $index++;
                $files[] = $excel->create($filename . '_' . $index, $data->toArray(), null, $column_adapter, $folder);
                unset($data, $datum);
            });

        $zip     = new \ZipArchive();
        $zipFile = $folder . $filename . '.zip';
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($files as $file) {
            $zip->addFile($file, pathinfo($file, PATHINFO_BASENAME));
        }
        $zip->close();
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * 下载导出文件
     * @param string $filename
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function download(string $filename): \Psr\Http\Message\ResponseInterface
    {
        $file   = $this->folder . $filename . '.zip';

        if (!is_file($file)) {
            throw new NormalStatusException('处理中，请稍候', 10002);
        }

        $response = container()->get(MsProResponse::class);

        ob_start();
        if (copy($file, 'php://output') === false) {
            throw new NormalStatusException('导出数据失败', 10003);
        }

        $res = $response->getResponse()
            ->withHeader('Server', 'MsProAdmin')
            ->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'application/zip')
            ->withHeader('content-disposition', "attachment; filename={$filename}.zip; filename*=UTF-8''" . rawurlencode($filename . '.zip'))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream(ob_get_contents()));

        ob_end_clean();
        @unlink($file);

        return $res;
    }
}
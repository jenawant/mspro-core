<?php
declare(strict_types=1);

/**
 * MsProAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MsProAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/jenawant/msproadmin
 * @Link https://gitee.com/xmo/MineAdmin
 */

namespace MsPro\Office\Excel;

use Closure;
use Exception;
use MsPro\Exception\MsProException;
use MsPro\MsProModel;
use MsPro\MsProRequest;
use MsPro\Office\ExcelPropertyInterface;
use MsPro\Office\MsProExcel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vtiful\Kernel\Format;

class XlsWriter extends MsProExcel implements ExcelPropertyInterface
{
    /**
     * 导入数据
     * @param MsProModel $model
     * @param Closure|null $closure
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function import(MsProModel $model, ?Closure $closure = null): bool
    {
        $request = container()->get(MsProRequest::class);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $tempFileName = 'import_'.time().'.'.$file->getExtension();
            $tempFilePath = BASE_PATH.'/runtime/'.$tempFileName;
            file_put_contents($tempFilePath, $file->getStream()->getContents());
            $xlsxObject = new \Vtiful\Kernel\Excel(['path' => BASE_PATH . '/runtime/']);
            $data = $xlsxObject->openFile($tempFileName)->openSheet()->getSheetData();
            unset($data[0]);

            $importData = [];
            foreach ($data as $item) {
                $tmp = [];
                foreach ($item as $key => $value) {
                    $tmp[$this->property[$key]['name']] = (string) $value;
                }
                $importData[] = $tmp;
            }

            if ($closure instanceof Closure) {
                return $closure($model, $importData);
            }

            try {
                foreach ($importData as $item) {
                    $model::create($item);
                }
                @unlink($tempFilePath);
            } catch (Exception $e) {
                @unlink($tempFilePath);
                throw new Exception($e->getMessage());
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 导出excel
     * EDIT.JENA.20230616
     * @param string $filename
     * @param array|Closure $closure
     * @param Closure|null $callbackData
     * @param bool $column_adapter
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function export(string $filename, array|Closure $closure, Closure $callbackData = null, bool $column_adapter = false): ResponseInterface
    {
        $file = $this->create($filename, $closure, $callbackData, $column_adapter);

        ob_start();
        if (copy($file, 'php://output') === false) {
            throw new MsProException('文件读取失败');
        }
        $res = $this->downloadExcel($filename, ob_get_contents());
        ob_end_clean();
        @unlink($file);

        return $res;
    }


    /**
     * 创建文件
     * @param string $filename
     * @param array|Closure $closure
     * @param Closure|null $callbackData
     * @param bool $column_adapter
     * @param string $folder
     * @return string
     */
    public function create(string $filename, array|Closure $closure, Closure $callbackData = null, bool $column_adapter = false, string $folder = BASE_PATH . '/runtime/export'): string
    {
        $filename .= '.xlsx';
        is_array($closure) ? $data = &$closure : $data = $closure();

        if (!is_dir($folder)) {
            mkdir($folder, 0774, true);
        }

        $aligns = [
            'left' => Format::FORMAT_ALIGN_LEFT,
            'center' => Format::FORMAT_ALIGN_CENTER,
            'right' => Format::FORMAT_ALIGN_RIGHT,
        ];

        $columnName = [];
        $columnField = [];
        foreach ($this->property as $index => $item) {
            //根据请求列设置表头及数据.FIXED.JENA.20230503
            //自定义是否适配数据列宽.FIXED.JENA.20231025
            if ($column_adapter && isset($data[0]) && $data[0] && !in_array($item['name'], array_keys($data[0]))) {
                unset($this->property[$index]);
                continue;
            }

            $columnName[] = $item['value'];
            $columnField[] = $item['name'];
        }

        $xlsxObject = new \Vtiful\Kernel\Excel(['path' => $folder]);
        $fileObject = $xlsxObject->fileName($filename)->header($columnName);
        $columnFormat = new Format($fileObject->getHandle());
        $rowFormat = new Format($fileObject->getHandle());

        $index = 0;
        for ($i = 65; $i < (65 + count($columnField)); $i++) {
            if (!isset($this->property[$index])) continue;
            $columnNumber = chr($i) . '1';
            $fileObject->setColumn(
                sprintf('%s:%s', $columnNumber, $columnNumber),
                $this->property[$index]['width'] ?? mb_strlen($columnName[$index]) * 5,
                $columnFormat->align($this->property[$index]['align'] ? $aligns[$this->property[$index]['align']] : $aligns['left'])
                    ->background($this->property[$index]['bgColor'] ?? Format::COLOR_WHITE)
                    ->border(Format::BORDER_THIN)
                    ->fontColor($this->property[$index]['color'] ?? Format::COLOR_BLACK)
                    ->toResource()
            );
            $index++;
        }

        // 表头加样式
        $fileObject->setRow(
            sprintf('A1:%s1', chr(65 + count($columnField))), 20,
            $rowFormat->bold()->align(Format::FORMAT_ALIGN_CENTER, Format::FORMAT_ALIGN_VERTICAL_CENTER)
                ->background(0x4ac1ff)->fontColor(Format::COLOR_BLACK)
                ->border(Format::BORDER_THIN)
                ->toResource()
        );

        $exportData = [];
        foreach ($data as $item) {
            $yield = [];
            if ($callbackData) {
                $item = $callbackData($item);
            }
            foreach ($this->property as $property) {
                foreach ($item as $name => $value) {
                    if ($property['name'] == $name) {
                        if (!empty($property['dictName'])) {
                            $yield[] = $property['dictName'][$value];
                        } else if (!empty($property['dictData'])) {
                            $yield[] = $property['dictData'][$value];
                        }else if (!empty($property['path'])){
                            $yield[] = \Hyperf\Collection\data_get($item, $property['path']);
                        }else if(!empty($this->dictData[$name])){
                            $yield[] = $this->dictData[$name][$value] ?? '';
                        } else {
                            $yield[] = $value;
                        }
                        break;
                    }
                }
            }
            $exportData[] = $yield;
            unset($yield, $item);
        }

        $file = $fileObject->data($exportData)->output();
        unset($data, $exportData);
        return $file;
    }
}
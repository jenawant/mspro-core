<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

namespace MsPro\Traits;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Database\Model\Collection;
use Hyperf\HttpMessage\Stream\SwooleStream;
use MsPro\Exception\NormalStatusException;
use MsPro\Office\Excel\PhpOffice;
use MsPro\Office\Excel\XlsWriter;
use MsPro\Abstracts\AbstractMapper;
use MsPro\Annotation\Transaction;
use MsPro\MsProCollection;
use MsPro\MsProModel;
use MsPro\MsProResponse;
use Psr\Http\Message\ResponseInterface;

trait ServiceTrait
{
    /**
     * @var AbstractMapper
     */
    public $mapper;

    /**
     * 获取列表数据
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->mapper->getList($params, $isScope);
    }

    /**
     * 从回收站过去列表数据
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getList($params, $isScope);
    }

    /**
     * 获取列表数据（带分页）
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getPageList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        return $this->mapper->getPageList($params, $isScope);
    }

    /**
     * 从回收站获取列表数据（带分页）
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getPageListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getPageList($params, $isScope);
    }

    /**
     * 获取树列表
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getTreeList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->mapper->getTreeList($params, $isScope);
    }

    /**
     * 从回收站获取树列表
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getTreeListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getTreeList($params, $isScope);
    }

    /**
     * 新增数据
     * @param array $data
     * @return int
     */
    public function save(array $data): int
    {
        return $this->mapper->save($data);
    }

    /**
     * 批量新增
     * @param array $collects
     * @return bool
     */
    #[Transaction]
    public function batchSave(array $collects): bool
    {
        foreach ($collects as $collect) {
            $this->mapper->save($collect);
        }
        return true;
    }

    /**
     * 读取一条数据
     * @param int $id
     * @param array $column
     * @return MsProModel|null
     */
    public function read(int $id, array $column = ['*']): ?MsProModel
    {
        return $this->mapper->read($id, $column);
    }

    /**
     * Description:获取单个值
     * User:mike
     * @param array $condition
     * @param string $columns
     * @return \Hyperf\Utils\HigherOrderTapProxy|mixed|void|null
     */
    public function value(array $condition, string $columns = 'id')
    {
        return $this->mapper->value($condition, $columns);
    }

    /**
     * Description:获取单列值
     * User:mike
     * @param array $condition
     * @param string $columns
     * @return array|null
     */
    public function pluck(array $condition, string $columns = 'id'): array
    {
        return $this->mapper->pluck($condition, $columns);
    }

    /**
     * 从回收站读取一条数据
     * @param int $id
     * @return MsProModel
     * @noinspection PhpUnused
     */
    public function readByRecycle(int $id): MsProModel
    {
        return $this->mapper->readByRecycle($id);
    }

    /**
     * 单个或批量软删除数据
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        return !empty($ids) && $this->mapper->delete($ids);
    }

    /**
     * 更新一条数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->mapper->update($id, $data);
    }

    /**
     * 按条件更新数据
     * @param array $condition
     * @param array $data
     * @return bool
     */
    public function updateByCondition(array $condition, array $data): bool
    {
        return $this->mapper->updateByCondition($condition, $data);
    }

    /**
     * 单个或批量真实删除数据
     * @param array $ids
     * @return bool
     */
    public function realDelete(array $ids): bool
    {
        return !empty($ids) && $this->mapper->realDelete($ids);
    }

    /**
     * 单个或批量从回收站恢复数据
     * @param array $ids
     * @return bool
     */
    public function recovery(array $ids): bool
    {
        return !empty($ids) && $this->mapper->recovery($ids);
    }

    /**
     * 单个或批量禁用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function disable(array $ids, string $field = 'status'): bool
    {
        return !empty($ids) && $this->mapper->disable($ids, $field);
    }

    /**
     * 单个或批量启用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function enable(array $ids, string $field = 'status'): bool
    {
        return !empty($ids) && $this->mapper->enable($ids, $field);
    }

    /**
     * 修改数据状态
     * @param int $id
     * @param string $value
     * @param string $filed
     * @return bool
     */
    public function changeStatus(int $id, string $value, string $filed = 'status'): bool
    {
        return $value == MsProModel::ENABLE ? $this->mapper->enable([$id], $filed) : $this->mapper->disable([$id], $filed);
    }

    /**
     * 数字更新操作
     * @param int $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function numberOperation(int $id, string $field, int $value): bool
    {
        return $this->mapper->numberOperation($id, $field, $value);
    }

    /**
     * 导出数据
     * @param array $params
     * @param string|null $dto
     * @param string|null $filename
     * @param \Closure|null $callbackData
     * @return ResponseInterface
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function export(array $params, ?string $dto, string $filename = null, \Closure $callbackData = null): ResponseInterface
    {
        if (empty($dto)) {
            return container()->get(MsProResponse::class)->error('导出未指定DTO');
        }

        if (empty($filename)) {
            $filename = $this->mapper->getModel()->getTable();
        }

        return (new MsProCollection())->export($dto, $filename, $this->mapper->getList($params), $callbackData);
    }

    /**
     * 异步导出
     * @param array $params
     * @param string $dto
     * @param string $filename
     * @param \Closure|null $closure
     * @param bool $column_adapter
     * @param int $row_size
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[AsyncQueueMessage]
    public function asyncExport(array $params, string $dto, string $filename, \Closure $closure = null, bool $column_adapter = false, int $row_size = 150000): void
    {
        $folder = BASE_PATH . '/runtime/export/';
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

        $this->mapper->listQuerySetting($params, false)
            ->chunk($row_size, function ($data) use (&$index, $filename, &$files, $excel, $closure, $column_adapter, $folder) {
                $index++;
                $files[] = $excel->create($filename . '_' . $index, $data->toArray(), $closure, $column_adapter, $folder);
                unset($data);
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
     * @param string $filename
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function asyncDownload(string $filename): \Psr\Http\Message\ResponseInterface
    {
        $folder = BASE_PATH . '/runtime/export/';
        $file   = $folder . $filename . '.zip';

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

    /**
     * 数据导入
     * @param string $dto
     * @param \Closure|null $closure
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[Transaction]
    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return $this->mapper->import($dto, $closure);
    }

    /**
     * 数组数据转分页数据显示
     * @param array|null $params
     * @param string $pageName
     * @return array
     */
    public function getArrayToPageList(?array $params = [], string $pageName = 'page'): array
    {
        $collect = $this->handleArraySearch(collect($this->getArrayData($params)), $params);

        $pageSize = MsProModel::PAGE_SIZE;
        $page     = 1;

        if ($params[$pageName] ?? false) {
            $page = (int)$params[$pageName];
        }

        if ($params['pageSize'] ?? false) {
            $pageSize = (int)$params['pageSize'];
        }

        $data = $collect->forPage($page, $pageSize)->toArray();

        return [
            'items'    => $this->getCurrentArrayPageBefore($data, $params),
            'pageInfo' => [
                'total'       => $collect->count(),
                'currentPage' => $page,
                'totalPage'   => ceil($collect->count() / $pageSize)
            ]
        ];
    }

    /**
     * 数组数据搜索器
     * @param \Hyperf\Collection\Collection $collect
     * @param array $params
     * @return Collection
     */
    protected function handleArraySearch(\Hyperf\Collection\Collection $collect, array $params): \Hyperf\Collection\Collection
    {
        return $collect;
    }

    /**
     * 数组当前页数据返回之前处理器，默认对key重置
     * @param array $data
     * @param array $params
     * @return array
     */
    protected function getCurrentArrayPageBefore(array &$data, array $params = []): array
    {
        sort($data);
        return $data;
    }

    /**
     * 设置需要分页的数组数据
     * @param array $params
     * @return array
     */
    protected function getArrayData(array $params = []): array
    {
        return [];
    }

    /**
     * 远程通用列表查询
     */
    public function getRemoteList(array $params = []): array
    {
        $remoteOption = $params['remoteOption'] ?? [];
        unset($params['remoteOption']);
        return $this->mapper->getRemoteList(array_merge($params, $remoteOption));
    }
}

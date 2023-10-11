<?php


declare(strict_types=1);

namespace Mine\Generator;

use App\Setting\Model\SettingGenerateColumns;
use App\Setting\Model\SettingGenerateTables;
use App\System\Model\SystemMenu;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Filesystem\Filesystem;
use Mine\Exception\NormalStatusException;
use Mine\Helper\Str;
use Hyperf\Database\Model\Collection;

/**
 * React index文件生成
 * Class VueIndexGenerator
 * @package Mine\Generator
 */
class ReactIndexGenerator extends MineGenerator implements CodeGenerator
{
    /**
     * @var SettingGenerateTables
     */
    protected SettingGenerateTables $model;
    /**
     * @var SystemMenu
     */
    #[Inject]
    protected SystemMenu $menuModel;

    /**
     * @var string
     */
    protected string $codeContent;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var Collection
     */
    protected Collection $columns;

    /**
     * @var array
     */
    protected array $menus;

    /**
     * 设置生成信息
     * @param SettingGenerateTables $model
     * @return VueIndexGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setGenInfo(SettingGenerateTables $model): ReactIndexGenerator
    {
        $this->model = $model;
        $this->filesystem = make(Filesystem::class);
        if (empty($model->module_name) || empty($model->menu_name)) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $menus = [];
        if ($model->belong_menu_id > 0) {
            $parentMenu = $this->menuModel::find($model->belong_menu_id);
            $rootMenu = $this->menuModel::whereIn('id', explode(',', $parentMenu->level))->pluck('name');
            $menus = $rootMenu->toArray();
            array_push($menus, $parentMenu->name, $model->menu_name);
        }
        $this->menus = $menus;
        $this->columns = SettingGenerateColumns::query()
            ->where('table_id', $model->id)->orderByDesc('sort')
            ->get([
                'column_name', 'column_type', 'column_comment', 'allow_roles', 'options', 'is_required', 'is_insert',
                'is_edit', 'is_query', 'is_sort', 'is_pk', 'is_list', 'view_type', 'dict_type',
            ]);

        return $this->placeholderReplace();
    }

    /**
     * 生成代码
     */
    public function generator(): void
    {
        $module = ucfirst(Str::lower($this->model->module_name));
        $businessName = ucfirst($this->getShortBusinessName());
        $path = BASE_PATH . "/runtime/generate/react/src/pages/{$module}/{$businessName}/index.tsx";
        $this->filesystem->makeDirectory(
            BASE_PATH . "/runtime/generate/react/src/pages/{$module}/{$businessName}",
            0755, true, true
        );
        $this->filesystem->put($path, $this->replace()->getCodeContent());
    }

    /**
     * 预览代码
     */
    public function preview(): string
    {
        return $this->replace()->getCodeContent();
    }

    /**
     * 获取模板地址
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . '/React/index.stub';
    }

    /**
     * 读取模板内容
     * @return string
     */
    protected function readTemplate(): string
    {
        return $this->filesystem->sharedGet($this->getTemplatePath());
    }

    /**
     * 占位符替换
     */
    protected function placeholderReplace(): ReactIndexGenerator
    {
        $this->setCodeContent(str_replace(
            $this->getPlaceHolderContent(),
            $this->getReplaceContent(),
            $this->readTemplate()
        ));

        // 扩展：替换函数
        $this->setCodeContent($this->replaceFunctions('{CONVERT_VALUE}', 'convertValue'));

        return $this;
    }

    /**
     * 获取要替换的占位符
     */
    protected function getPlaceHolderContent(): array
    {
        return [
            '{CODE}',
            '{TYPE_COLUMNS}',
            '{{CRUD}}',
            '{COLUMNS}',
            '{OPTION_COLUMN}',
            '{BUSINESS_EN_NAME}',
            '{INPUT_NUMBER}',
            '{SWITCH_STATUS}',
            '{MODULE_NAME}',
            '{MENU_BREADCRUMB}',
            '{PK}',
            '{FORM}',
            '{FORM_TYPE}'
        ];
    }

    /**
     * 获取要替换占位符的内容
     * @return string[]
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->getCode(),
            $this->getTypeColumns(),
            $this->getCrud(),
            $this->getColumns(),
            $this->getOptionColumn(),
            $this->getBusinessEnName(),
            $this->getInputNumber(),
            $this->getSwitchStatus(),
            $this->getModuleName(),
            $this->getMenuBreadCrumb(),
            $this->getPk(),
            $this->getForm(),
            $this->getFormType()
        ];
    }

    /**
     * 获取标识代码
     * @return string
     */
    protected function getCode(): string
    {
        return Str::lower($this->model->module_name) . ':' . $this->getShortBusinessName();
    }

    /**
     * 字段类型定义
     * @return string
     */
    protected function getTypeColumns(): string
    {
        // 字段配置项
        $options = [];
        foreach ($this->columns as $column) {
            $options[$column->column_name] = str_contains($column->column_type, 'int') ? 'number' : 'string';
        }

        return 'type ColumnItem = ' . $this->jsonFormat($options, true, true) . ';';
    }

    /**
     * 获取CRUD配置代码
     * @return string
     */
    protected function getCrud(): string
    {
        // 配置项
        $options = [];
        $options['rowSelection'] = ['showCheckedAll' => true];
        $options['searchLabelWidth'] = "'auto'";
        $options['pk'] = "'" . $this->getPk() . "'";
        $options['operationWidth'] = 160;
        $options['formSetting'] = [
            'cols' => 1,
            'width' => 600,
        ];
        $options['api'] = $this->getBusinessEnName() . '.getList';
        if (Str::contains($this->model->generate_menus, 'recycle')) {
            $options['recycleApi'] = $this->getBusinessEnName() . '.getRecycleList';
        }
        $options['add']['show'] = false;
        if (Str::contains($this->model->generate_menus, 'save')) {
            $options['add'] = [
                'show' => true,
                'api' => $this->getBusinessEnName() . '.save',
                'auth' => "'" . $this->getCode() . ":save'"
            ];
        }
        $options['edit']['show'] = false;
        if (Str::contains($this->model->generate_menus, 'update')) {
            $options['edit'] = [
                'show' => true,
                'api' => $this->getBusinessEnName() . '.update',
                'auth' => "'" . $this->getCode() . ":update'"
            ];
        }
        $options['delete']['show'] = false;
        if (Str::contains($this->model->generate_menus, 'delete')) {
            $options['delete'] = [
                'show' => true,
                'api' => $this->getBusinessEnName() . '.deletes',
                'auth' => "'" . $this->getCode() . ":delete'"
            ];
            if (Str::contains($this->model->generate_menus, 'recycle')) {
                $options['delete']['realApi'] = $this->getBusinessEnName() . '.realDeletes';
                $options['delete']['realAuth'] = "'" . $this->getCode() . ":realDeletes'";
                $options['recovery'] = [
                    'show' => true,
                    'api' => $this->getBusinessEnName() . '.recoverys',
                    'auth' => "'" . $this->getCode() . ":recovery'"
                ];
            }
        }
        $requestRoute = Str::lower($this->model->module_name) . '/' . $this->getShortBusinessName();
        // 导入
        $options['import']['show'] = false;
        if (Str::contains($this->model->generate_menus, 'import')) {
            $options['import'] = [
                'show' => true,
                'url' => "'" . $requestRoute . '/import' . "'",
                'templateUrl' => "'" . $requestRoute . '/downloadTemplate' . "'",
                'auth' => "'" . $this->getCode() . ":import'"
            ];
        }
        // 导出
        $options['export']['show'] = false;
        if (Str::contains($this->model->generate_menus, 'export')) {
            $options['export'] = [
                'show' => true,
                'url' => "'" . $requestRoute . '/export' . "'",
                'auth' => "'" . $this->getCode() . ":export'"
            ];
        }
        return 'const CRUD = ' . $this->jsonFormat($options, true) . ';';
    }

    /**
     * 获取列配置代码
     * @return string
     */
    protected function getColumns(): string
    {
        // 字段配置项
        $options = [];
        foreach ($this->columns as $column) {
            $tmp = [
                'title' => $column->column_comment,
                'dataIndex' => $column->column_name,
                'valueType' => $this->getViewType($column->view_type),
            ];
            // 基础
            if ($column->is_query == self::NO) {
                $tmp['hideInSearch'] = true;
            }
            if ($column->is_insert == self::NO) {
                $tmp['hideInForm'] = true;
            }
            if ($column->is_edit == self::NO) {
                $tmp['hideInForm'] = true;
            }
            if ($column->is_list == self::NO) {
                $tmp['hideInTable'] = true;
            }
            if ($column->is_required == self::YES) {
                $tmp['formItemProps']['rules'][] = [
                    'required' => true,
                    'message' => t('validation.required', ['attribute' => $column->column_comment])
                ];
            }
            if ($column->is_sort == self::YES) {
                $tmp['sorter'] = true;
                $tmp['defaultSortOrder'] = 'descend';
            }
            if ($column->view_type === 'inputNumber') {
                $tmp['fieldProps']['style'] = ['width' => '100%'];
            }
            // 扩展项
            if (!empty($column->options)) {
                $tmp['fieldProps'] = $column->options;
                // 选择器多选
                if ($column->view_type === 'select' && $column->options['multiple'] === true) {
                    $tmp['fieldProps']['mode'] = 'multiple';
                }
                // 对日期处理
                if ($column->view_type == 'date') {
                    if (isset($column->options['type']) && $column->options['type'] !== 'Date') {
                        $tmp['valueType'] .= $column->options['type'];
                    }
                    if (isset($column->options['showTime']) && $column->options['showTime'] === true){
                        $tmp['valueType'] .= 'Time';
                    }
                    if (isset($column->options['range']) && $column->options['range']) {
                        $tmp['valueType'] .= 'Range';
                    }
                    unset($tmp['fieldProps']['type'], $tmp['fieldProps']['range'], $tmp['fieldProps']['showTime']);
                }
                // 对时间处理
                if ($column->view_type == 'time') {
                    if (isset($column->options['range']) && $column->options['range']) {
                        $tmp['valueType'] .= 'Range';
                    }
                    unset($tmp['fieldProps']['range']);
                }
                if ($column->view_type == 'upload') {
                    $tmp['hideInSearch'] = true;
                    $tmp['convertValue'] = '{CONVERT_VALUE}';

                }
            }
            // 字典
            if (!empty($column->dict_type)) {
                $tmp['fieldProps']['type'] = $tmp['valueType'];
                $tmp['valueType'] = 'dict';
                $tmp['fieldProps']['dict'] = $column->dict_type;
                $tmp['fieldProps']['fieldNames'] = ['label' => 'title', 'value' => 'key'];
            }
            // 密码处理
            if ($column->view_type == 'password') {
                $tmp['type'] = 'password';
            }
            // 允许查看字段的角色（前端还待支持）
            // todo...
            $options[] = $tmp;
        }

        return "const columns: (ProColumns<ColumnItem, 'dict' | 'country' | 'upload'> & ProFormColumnsType<ColumnItem, 'dict' | 'country' | 'upload'>)[] = " . $this->jsonFormat($options) . ';';
    }

    /**
     * @return string
     */
    protected function getShowRecycle(): string
    {
        return (strpos($this->model->generate_menus, 'recycle') > 0) ? 'true' : 'false';
    }

    /**
     * 获取业务英文名
     * @return string
     */
    protected function getBusinessEnName(): string
    {
        return Str::camel(str_replace(env('DB_PREFIX'), '', $this->model->table_name));
    }

    /**
     * @return string
     */
    protected function getModuleName(): string
    {
        return Str::lower($this->model->module_name);
    }

    /**
     * 返回主键
     * @return string
     */
    protected function getPk(): string
    {
        foreach ($this->columns as $column) {
            if ($column->is_pk == self::YES) {
                return $column->column_name;
            }
        }
        return '';
    }

    /**
     * 替换函数占位符
     * @param string $fun_name
     * @param string $tmp_name
     * @return string
     */
    protected function replaceFunctions(string $fun_name, string $tmp_name): string
    {
        return str_ireplace('"' . $fun_name . '"', $this->getOtherTemplate($tmp_name), $this->getCodeContent());
    }

    /**
     * 计数器组件方法
     * @return string
     * @noinspection BadExpressionStatementJS
     */
    protected function getInputNumber(): string
    {
        if (in_array('numberOperation', explode(',', $this->model->generate_menus))) {
            return str_replace('{BUSINESS_EN_NAME}', $this->getBusinessEnName(), $this->getOtherTemplate('numberOperation'));
        }
        return '';
    }

    /**
     * 计数器组件方法
     * @return string
     * @noinspection BadExpressionStatementJS
     */
    protected function getSwitchStatus(): string
    {
        if (in_array('changeStatus', explode(',', $this->model->generate_menus))) {
            return str_replace('{BUSINESS_EN_NAME}', $this->getBusinessEnName(), $this->getOtherTemplate('switchStatus'));
        }
        return '';
    }

    /**
     * 列操作方法
     * @return string
     * @noinspection BadExpressionStatementJS
     */
    protected function getOptionColumn(): string
    {
        if (in_array('update', explode(',', $this->model->generate_menus)) || in_array('delete', explode(',', $this->model->generate_menus))) {
            return $this->getOtherTemplate('optionColumn');
        }
        return '';
    }

    /**
     * 获取表单
     * @return string
     */
    protected function getForm(): string
    {
        if (in_array('update', explode(',', $this->model->generate_menus)) || in_array('add', explode(',', $this->model->generate_menus))) {
            return $this->getOtherTemplate($this->model->component_type == 1 ? 'ModalForm' : 'DrawerForm');
        }
        return '';
    }

    /**
     * 获取表单类型
     * @return string
     */
    protected function getFormType(): string
    {
        if (in_array('update', explode(',', $this->model->generate_menus)) || in_array('add', explode(',', $this->model->generate_menus))) {
            return $this->model->component_type == 1 ? 'ModalForm' : 'DrawerForm';
        }
        return '';
    }

    /**
     * 获取菜单面包屑
     * @return string
     */
    protected function getMenuBreadCrumb(): string
    {
        $items = [];
        foreach ($this->menus as $menu) {
            $items[] = ['path' => '', 'title' => $menu];
        }
        return $this->jsonFormat($items);
    }

    /**
     * @param string $tpl
     * @return string
     */
    protected function getOtherTemplate(string $tpl): string
    {
        return $this->filesystem->sharedGet($this->getStubDir() . "/React/{$tpl}.stub");
    }

    /**
     * 获取短业务名称
     * @return string
     */
    public function getShortBusinessName(): string
    {
        return Str::camel(str_replace(
            Str::lower($this->model->module_name),
            '',
            str_replace(env('DB_PREFIX'), '', $this->model->table_name)
        ));
    }

    /**
     * 视图组件
     * @param string $viewType
     * @return string
     */
    protected function getViewType(string $viewType): string
    {
        $viewTypes = [
            'text' => 'text',
            'password' => 'password',
            'textarea' => 'textarea',
            'inputNumber' => 'digit',
            'inputTag' => 'input-tag',
            'mention' => 'mention',
            'switch' => 'switch',
            'slider' => 'slider',
            'select' => 'select',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'treeSelect' => 'treeSelect',
            'date' => 'date',
            'time' => 'time',
            'rate' => 'rate',
            'cascader' => 'cascader',
            'transfer' => 'transfer',
            'selectUser' => 'select-user',
            'userInfo' => 'user-info',
            'country' => 'country',
            'cityLinkage' => 'city-linkage',
            'icon' => 'icon',
            'formGroup' => 'form-group',
            'upload' => 'upload',
            'selectResource' => 'select-resource',
            'editor' => 'editor',
            'codeEditor' => 'code-editor',
        ];

        return $viewTypes[$viewType] ?? 'text';
    }

    /**
     * array 到 json 数据格式化
     * @param array $data
     * @param bool $removeValueQuotes
     * @param bool $convertTosemicolon
     * @return string
     */
    protected function jsonFormat(array $data, bool $removeValueQuotes = false, bool $convertTosemicolon = false): string
    {
        $data = str_replace('    ', '  ', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $data = str_replace(['"true"', '"false"', "\\"], [true, false, ''], $data);
        $data = preg_replace('/(\s+)\"(.+)\":/', "\\1\\2:", $data);
        if ($removeValueQuotes) {
            $data = preg_replace('/(:\s)\"(.+)\"/', "\\1\\2", $data);
        }
        if ($convertTosemicolon) {
            $data = str_replace(',', ';', $data);
        }
        return $data;
    }

    /**
     * 设置代码内容
     * @param string $content
     */
    public function setCodeContent(string $content)
    {
        $this->codeContent = $content;
    }

    /**
     * 获取代码内容
     * @return string
     */
    public function getCodeContent(): string
    {
        return $this->codeContent;
    }

}
<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro;

use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use MsPro\Traits\ModelMacroTrait;

/**
 * Class MsProModel
 * @package MsPro
 */
class MsProModel extends Model
{
    use Cacheable, ModelMacroTrait;

    /**
     * 隐藏的字段列表
     * @var string[]
     */
    protected array $hidden = ['deleted_at'];

    /**
     * 数据权限字段，表中需要有此字段
     */
    protected string $dataScopeField = 'created_by';

    /**
     * 状态
     */
    public const ENABLE = 1;
    public const DISABLE = 2;

    /**
     * 默认每页记录数
     */
    public const PAGE_SIZE = 15;

    /**
     * MsProModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        //注册常用方法
        $this->registerBase();
        //注册用户数据权限方法
        $this->registerUserDataScope();
    }

    /**
     * 设置主键的值
     * @param string | int $value
     */
    public function setPrimaryKeyValue($value): void
    {
        $this->{$this->primaryKey} = $value;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        return parent::save($options);
    }

    /**
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        return parent::update($attributes, $options);
    }

    /**
     * @param array $models
     * @return MsProCollection
     */
    public function newCollection(array $models = []): MsProCollection
    {
        return new MsProCollection($models);
    }

    /**
     * @return string
     */
    public function getDataScopeField(): string
    {
        return $this->dataScopeField;
    }

    /**
     * @param string $name
     * @return MsProModel
     */
    public function setDataScopeField(string $name): self
    {
        $this->dataScopeField = $name;
        return $this;
    }
}

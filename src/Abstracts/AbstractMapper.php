<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare (strict_types = 1);
namespace MsPro\Abstracts;

use Hyperf\Context\Context;
use MsPro\MsProModel;
use MsPro\Traits\MapperTrait;

/**
 * Class AbstractMapper
 * @package MsPro\Abstracts
 */
abstract class AbstractMapper
{
    use MapperTrait;

    /**
     * @var MsProModel
     */
    public $model;
    
    abstract public function assignModel();

    public function __construct()
    {
        $this->assignModel();
    }

    /**
     * 把数据设置为类属性
     * @param array $data
     */
    public static function setAttributes(array $data)
    {
        Context::set('attributes', $data);
    }

    /**
     * 魔术方法，从类属性里获取数据
     * @param string $name
     * @return mixed|string
     */
    public function __get(string $name)
    {
        return $this->getAttributes()[$name] ?? '';
    }

    /**
     * 获取数据
     * @return array
     */
    public function getAttributes(): array
    {
        return Context::get('attributes', []);
    }
}

<?php
/**

declare(strict_types = 1);
namespace MsPro\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 删除缓存。
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DeleteCache extends AbstractAnnotation
{

    /**
     * @param string|null $keys 缓存key，多个以逗号分开
     */
    public function __construct(public ?string $keys = null) {}
}
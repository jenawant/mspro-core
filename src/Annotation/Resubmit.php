<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types = 1);
namespace MsPro\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 禁止重复提交
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Resubmit extends AbstractAnnotation
{
    /**
     * @var int $second 限制时间（秒）
     * @var string|null $message 提示信息
     */
    public function __construct(public int $second = 3, public ?string $message = null) {}
}
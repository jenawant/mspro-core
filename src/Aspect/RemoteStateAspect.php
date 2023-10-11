<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use MsPro\Annotation\RemoteState;
use MsPro\Exception\MsProException;

/**
 * Class RemoteStateAspect
 * @package MsPro\Aspect
 */
#[Aspect]
class RemoteStateAspect extends AbstractAspect
{

    public array $annotations = [
        RemoteState::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws MsProException
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $remote = $proceedingJoinPoint->getAnnotationMetadata()->method[RemoteState::class];
        if (! $remote->state) {
            throw new MsProException('当前功能服务已禁止使用远程通用接口', 500);
        }

        return $proceedingJoinPoint->process();
    }
}
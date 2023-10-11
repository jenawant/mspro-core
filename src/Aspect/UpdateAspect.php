<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Aspect;

use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use MsPro\MsProModel;
use MsPro\MsProRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UpdateAspect
 * @package MsPro\Aspect
 */
#[Aspect]
class UpdateAspect extends AbstractAspect
{
    public array $classes = [
        'MsPro\MsProModel::update'
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $instance = $proceedingJoinPoint->getInstance();
        // 更新更改人
        if ($instance instanceof MsProModel &&
            in_array('updated_by', $instance->getFillable()) &&
            config('msproadmin.data_scope_enabled') &&
            Context::has(ServerRequestInterface::class) &&
            container()->get(MsProRequest::class)->getHeaderLine('authorization')
        ) {
            try {
                $instance->updated_by = user()->getId();
            } catch (\Throwable $e) {}
        }
        return $proceedingJoinPoint->process();
    }
}
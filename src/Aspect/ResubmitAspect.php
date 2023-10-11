<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use MsPro\Annotation\Resubmit;
use MsPro\Exception\MsProException;
use MsPro\Exception\NormalStatusException;
use MsPro\MsProRequest;
use MsPro\Redis\MsProLockRedis;

/**
 * Class ResubmitAspect
 * @package MsPro\Aspect
 */
#[Aspect]
class ResubmitAspect extends AbstractAspect
{

    public array $annotations = [
        Resubmit::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        try {
            $result = $proceedingJoinPoint->process();

            /** @var $resubmit Resubmit */
            if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Resubmit::class])) {
                $resubmit = $proceedingJoinPoint->getAnnotationMetadata()->method[Resubmit::class];
            }

            $request = container()->get(MsProRequest::class);

            $key = md5(sprintf('%s-%s-%s', $request->ip(), $request->getPathInfo(), $request->getMethod()));

            $lockRedis = new MsProLockRedis();
            $lockRedis->setTypeName('resubmit');

            if ($lockRedis->check($key)) {
                $lockRedis = null;
                throw new NormalStatusException($resubmit->message ?: t('msproadmin.resubmit'), 500);
            }

            $lockRedis->lock($key, $resubmit->second);
            $lockRedis = null;

            return $result;
        } catch (\Throwable $e) {
            throw new MsProException($e->getMessage(), $e->getCode());
        }
    }
}
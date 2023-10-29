<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace MsPro\Log\Processor;

use MsPro\Log\RequestIdHolder;
use Hyperf\Coroutine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class UuidRequestIdProcessor implements ProcessorInterface
{
    public function __invoke(array|LogRecord $record)
    {
        RequestIdHolder::setType('uuid');
        $record['extra']['request_id'] = RequestIdHolder::getId();
        $record['extra']['coroutine_id'] = Coroutine::id();
        return $record;
    }
}

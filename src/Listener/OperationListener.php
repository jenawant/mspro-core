<?php

/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */
declare (strict_types=1);
namespace MsPro\Listener;

use MsPro\Interfaces\ServiceInterface\OperLogServiceInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MsPro\Event\Operation;
use Psr\Container\ContainerInterface;
/**
 * Class OperationListener
 * @package MsPro\Listener
 */
#[Listener]
class OperationListener implements ListenerInterface
{
    
    protected $container;
    
    protected $ignoreRouter = ['/login', '/getInfo', '/system/captcha'];
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen() : array
    {
        return [Operation::class];
    }
    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     * @param object $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(object $event): void
    {
        $requestInfo = $event->getRequestInfo();
        if (!in_array($requestInfo['router'], $this->ignoreRouter)) {
            $service = $this->container->get(OperLogServiceInterface::class);
            $requestInfo['request_data'] = json_encode($requestInfo['request_data'], JSON_UNESCAPED_UNICODE);
            //            $requestInfo['response_data'] = $requestInfo['response_data'];
            $service->save($requestInfo);
        }
    }
}
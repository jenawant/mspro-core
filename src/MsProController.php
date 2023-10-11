<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro;

use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use MsPro\Traits\ControllerTrait;

/**
 * 后台控制器基类
 * Class MsProController
 * @package MsPro
 */
abstract class MsProController
{
    use ControllerTrait;

    /**
     * @var MsPro
     */
    #[Inject]
    protected MsPro $mine;
}

<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro;

use MsPro\Traits\ControllerTrait;

/**
 * API接口控制器基类
 * Class MsProApi
 * @package MsPro
 */
abstract class MsProApi
{
    use ControllerTrait;
}

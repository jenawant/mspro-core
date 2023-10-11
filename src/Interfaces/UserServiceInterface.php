<?php
declare(strict_types=1);

/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */
namespace MsPro\Interfaces;

use MsPro\Vo\UserServiceVo;

/**
 * 用户服务抽象
 */
interface UserServiceInterface
{
    public function login(UserServiceVo $userServiceVo);

    public function logout();
}
<?php

declare(strict_types=1);

/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

namespace MsPro\Interfaces;

/**
 * key/value 枚举接口
 */
interface KeyValueEnum
{
    public function key();

    public function value();
}
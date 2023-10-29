<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

namespace MsPro\Interfaces;

interface MsProRedisInterface
{
    /**
     * 设置 key 类型名
     * @param string $typeName
     */
    public function setTypeName(string $typeName): void;

    /**
     * 获取key 类型名
     * @return string
     */
    public function getTypeName(): string;
}
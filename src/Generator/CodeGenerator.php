<?php
/**
 * MsProAdmin is forked from Min-eAdmin, with the aim of building the system more customizable and faster
 */

declare(strict_types=1);
namespace MsPro\Generator;

interface CodeGenerator
{
    public function generator();

    public function preview();
}
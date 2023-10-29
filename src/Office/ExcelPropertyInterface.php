<?php
declare(strict_types=1);

namespace MsPro\Office;

interface ExcelPropertyInterface
{
    public function import(\MsPro\MsProModel $model, ?\Closure $closure = null): bool;

    public function export(string $filename, array|\Closure $closure): \Psr\Http\Message\ResponseInterface;
}
<?php
declare(strict_types=1);

/**
 * MsProAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MsProAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/jenawant/msproadmin
 * @Link https://gitee.com/xmo/MineAdmin
 */

namespace MsPro\Office\Excel;

use Closure;
use Generator;
use MsPro\Exception\MsProException;
use MsPro\MsProModel;
use MsPro\MsProRequest;
use MsPro\Office\ExcelPropertyInterface;
use MsPro\Office\MsProExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class PhpOffice extends MsProExcel implements ExcelPropertyInterface
{

    /**
     * 导入
     * @param MsProModel $model
     * @param Closure|null $closure
     * @return bool
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function import(MsProModel $model, ?Closure $closure = null): bool
    {
        $request = container()->get(MsProRequest::class);
        $data = [];
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $tempFileName = 'import_'.time().'.'.$file->getExtension();
            $tempFilePath = BASE_PATH . '/runtime/'. $tempFileName;
            file_put_contents($tempFilePath, $file->getStream()->getContents());
            $reader = IOFactory::createReader(IOFactory::identify($tempFilePath));
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($tempFilePath);
            $endCell = isset($this->property) ? $this->getColumnIndex(count($this->property)) : null;
            try {
                foreach ($sheet->getActiveSheet()->getRowIterator(2) as $row) {
                    $temp = [];
                    foreach ($row->getCellIterator('A', $endCell) as $index => $item) {
                        $propertyIndex = ord($index) - 65;
                        if (isset($this->property[$propertyIndex])) {
                            $temp[$this->property[$propertyIndex]['name']] = $item->getFormattedValue();
                        }
                    }
                    if (! empty($temp)) {
                        $data[] = $temp;
                    }
                }
                unlink($tempFilePath);
            } catch (Throwable $e) {
                unlink($tempFilePath);
                throw new MsProException($e->getMessage());
            }
        } else {
            return false;
        }
        if ($closure instanceof Closure) {
            return $closure($model, $data);
        }

        foreach ($data as $datum) {
            $model::create($datum);
        }
        return true;
    }


    /**
     * 导出
     * @param string $filename
     * @param array|Closure $closure
     * @param Closure|null $callbackData
     * @param bool $column_adapter
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export(string $filename, array|Closure $closure, Closure $callbackData = null, bool $column_adapter = false): ResponseInterface
    {
        $file = $this->create($filename, $closure, $callbackData, $column_adapter);

        ob_start();
        if (copy($file, 'php://output') === false) {
            throw new MsProException('文件读取失败');
        }
        $res = $this->downloadExcel($filename, ob_get_contents());
        ob_end_clean();
        @unlink($file);

        return $res;
    }


    /**
     * 生成文件
     * @param string $filename
     * @param array|Closure $closure
     * @param Closure|null $callbackData
     * @param bool $column_adapter
     * @param string $folder
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function create(string $filename, array|Closure $closure, Closure $callbackData = null, bool $column_adapter = false, string $folder = BASE_PATH . '/runtime/export'): string
    {
        $spread = new Spreadsheet();
        $sheet = $spread->getActiveSheet();
        $filename .= '.xlsx';

        if (!is_dir($folder)){
            mkdir($folder, 0774, true);
        }

        is_array($closure) ? $data = &$closure : $data = $closure();

        // 表头
        $titleStart = 0;
        foreach ($this->property as $item) {
            $headerColumn = $this->getColumnIndex($titleStart) . '1';
            $sheet->setCellValue($headerColumn, $item['value']);
            $style = $sheet->getStyle($headerColumn)->getFont()->setBold(true);
            $columnDimension = $sheet->getColumnDimension($headerColumn[0]);

            empty($item['width']) ? $columnDimension->setAutoSize(true) : $columnDimension->setWidth((float) $item['width']);

            empty($item['align']) || $sheet->getStyle($headerColumn)->getAlignment()->setHorizontal($item['align']);

            empty($item['headColor']) || $style->setColor(new Color(str_replace('#', '', $item['headColor'])));

            if (!empty($item['headBgColor'])) {
                $sheet->getStyle($headerColumn)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(str_replace('#', '', $item['headBgColor']));
            }
            $titleStart++;
        }

        $generate = $this->yieldExcelData($data, $callbackData);

        // 表体
        try {
            $row = 2;
            while ($generate->valid()) {
                $column = 0;
                $items = $generate->current();
                foreach ($items as $name => $value) {
                    $columnRow = $this->getColumnIndex($column) . $row;
                    $annotation = '';
                    foreach ($this->property as $item) {
                        if ($item['name'] == $name) {
                            $annotation = $item;
                            break;
                        }
                    }

                    if (!empty($annotation['dictName'])) {
                        $sheet->setCellValue($columnRow, $annotation['dictName'][$value]);
                    } else if (!empty($annotation['path'])){
                        $sheet->setCellValue($columnRow, \Hyperf\Collection\data_get($items, $annotation['path']));
                    } else if (!empty($annotation['dictData'])) {
                        $sheet->setCellValue($columnRow, $annotation['dictData'][$value]);
                    } else if(!empty($this->dictData[$name])){
                        $sheet->setCellValue($columnRow, $this->dictData[$name][$value] ?? '');
                    } else {
                        $sheet->setCellValue($columnRow, $value . "\t");
                    }

                    if (! empty($item['color'])) {
                        $sheet->getStyle($columnRow)->getFont()
                            ->setColor(new Color(str_replace('#', '', $annotation['color'])));
                    }

                    if (! empty($item['bgColor'])) {
                        $sheet->getStyle($columnRow)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB(str_replace('#', '', $annotation['bgColor']));
                    }
                    $column++;
                }
                $generate->next();
                $row++;
            }
        } catch (RuntimeException $e) {}

        $writer = IOFactory::createWriter($spread, 'Xlsx');
        $file = $folder . $filename;
        $writer->save($file);
        $spread->disconnectWorksheets();

        unset($data);

        return $file;
    }

    /**
     * @param array $data
     * @param Closure|null $callbackData
     * @return Generator
     */
    protected function yieldExcelData(array $data, Closure $callbackData = null): Generator
    {
        foreach ($data as $dat) {
            $yield = [];
            if ($callbackData) {
                $dat = $callbackData($dat);
            }
            foreach ($this->property as $item) {
                $yield[ $item['name'] ] = $dat[$item['name']] ?? '';
            }
            yield $yield;
        }
    }
}
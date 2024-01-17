<?php

namespace SPHERE\Application\Api\Reporting\Standard;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\Storage;

class ExcelBuilder
{
    /**
     * @param string $fileName
     * @param array $headerNameList
     * @param array $dataList
     * @param array|null $preTextList
     * @param array|null $headerWidthList
     *
     * @return false|string
     */
    public static function getDownloadFile(string $fileName, array $headerNameList, array $dataList, ?array $preTextList = null, ?array $headerWidthList = null)
    {
        if (!empty($dataList)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;
            $countData = count($dataList);

            if ($preTextList) {
                foreach ($preTextList as $text) {
                    $export->setValue($export->getCell($column, $row++), str_replace('&nbsp;', ' ', strip_tags($text)));
                }

                $row++;
            }

            foreach ($headerNameList as $key => $header) {
                $export->setValue($export->getCell($column, $row), str_replace('&nbsp;', ' ', strip_tags($header)));
                if (isset($headerWidthList[$key])) {
                    $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row + $countData))->setColumnWidth($headerWidthList[$key]);
                }
                $column++;
            }
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();

            $export->setStyle($export->getCell(0, $row), $export->getCell($column - 1, $row + $countData))->setBorderAll();
            foreach ($dataList as $item) {
                $row++;
                $column = 0;
                foreach ($headerNameList as $key => $header) {
                    if (isset($item[$key])) {
                        $export->setValue($export->getCell($column, $row), strip_tags($item[$key]));
                    }
                    $column++;
                }
            }
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return FileSystem::getDownload($fileLocation->getRealPath(), $fileName . ".xlsx")->__toString();
        }

        return false;
    }
}
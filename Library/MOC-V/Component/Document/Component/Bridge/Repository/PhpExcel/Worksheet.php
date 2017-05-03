<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;

/**
 * Class Worksheet
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel
 */
abstract class Worksheet extends File
{
    /**
     * @param string $Name
     * @return PhpExcel
     */
    public function createWorksheet($Name)
    {
        $this->Source->createSheet();
        $SheetCount = $this->Source->getSheetCount();
        $this->Source->getSheet($SheetCount - 1)->setTitle($Name);
        $this->selectWorksheetByName($Name);
        return $this;
    }

    /**
     * @return PhpExcel
     */
    public function destroyWorksheet()
    {
        $Index = $this->Source->getIndex($this->Source->getActiveSheet());
        $this->Source->removeSheetByIndex($Index);
        $this->selectWorksheetByIndex($Index - 1);
        return $this;
    }

    /**
     * @param string $Name
     * @return PhpExcel
     */
    public function renameWorksheet($Name)
    {
        $this->Source->getActiveSheet()->setTitle($Name);
        return $this;
    }

    /**
     * @param string $Name
     * @return PhpExcel
     */
    public function selectWorksheetByName($Name)
    {
        $this->Source->setActiveSheetIndex(
            $this->Source->getIndex(
                $this->Source->getSheetByName($Name)
            )
        );
        return $this;
    }

    /**
     * @param int $Index
     * @return PhpExcel
     */
    public function selectWorksheetByIndex($Index = 0)
    {
        $this->Source->setActiveSheetIndex($Index);
        return $this;
    }
}
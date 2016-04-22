<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;

use MOC\V\Component\Document\Component\Exception\ComponentException;

/**
 * Class Style
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel
 */
class Style
{

    /** @var null|\PHPExcel_Worksheet $Worksheet */
    private $Worksheet = null;
    /** @var null|Cell $CellTL Cell Top-Left */
    private $CellTL = null;
    /** @var null|Cell $CellTL Cell Bottom-Right */
    private $CellBR = null;

    /**
     * Style constructor.
     *
     * @param \PHPExcel_Worksheet $Worksheet
     * @param Cell                $CellTopLeft     Cell Single or Top-Left
     * @param Cell|null           $CellBottomRight Cell Bottom-Right
     */
    public function __construct(\PHPExcel_Worksheet $Worksheet, Cell $CellTopLeft, Cell $CellBottomRight = null)
    {

        $this->Worksheet = $Worksheet;
        $this->CellTL = $CellTopLeft;
        $this->CellBR = $CellBottomRight;
    }

    /**
     * @param float|int $Value [-1 = Auto]
     *
     * @return $this
     */
    public function setColumnWidth($Value = -1)
    {

        $ColumnRange = $this->getRangeColumnNameList();
        foreach ($ColumnRange as $ColumnName) {
            if (-1 == $Value) {
                $this->Worksheet->getColumnDimension($ColumnName)->setAutoSize(true);
            } else {
                $this->Worksheet->getColumnDimension($ColumnName)->setAutoSize(false);
                $this->Worksheet->getColumnDimension($ColumnName)->setWidth((float)$Value);
            }
        }
        return $this;
    }

    /**
     * @return array Cell & Range: array( 'A', ... )
     */
    private function getRangeColumnNameList()
    {

        if (null === $this->CellBR) {
            return array($this->CellTL->getColumnName());
        } else {
            return range($this->CellTL->getColumnName(), $this->CellBR->getColumnName());
        }
    }

    /**
     * @return float|int|array Cell: float | -1, Range: array( 'A' => float, 'B' => -1, ... ) [-1 = Auto]
     */
    public function getColumnWidth()
    {

        $Result = array();
        $ColumnRange = $this->getRangeColumnNameList();
        foreach ($ColumnRange as $ColumnName) {
            $Result[$ColumnName] = $this->Worksheet->getColumnDimension($ColumnName)->getWidth();
        }
        if (count($Result) == 1) {
            return current($Result);
        } else {
            return $Result;
        }
    }

    /**
     * @param bool $Toggle
     *
     * @return $this
     */
    public function setFontBold($Toggle = true)
    {

        $this->Worksheet->getStyle($this->getRangeName())->getFont()->setBold($Toggle);
        return $this;
    }

    /**
     * @return string 'A1:C2'
     */
    private function getRangeName()
    {

        if (null === $this->CellBR) {
            return (string)$this->CellTL->getCellName();
        } else {
            return (string)$this->CellTL->getCellName().':'.$this->CellBR->getCellName();
        }
    }

    /**
     * @param float|int $Size
     *
     * @return $this
     */
    public function setFontSize($Size = 11)
    {

        $this->Worksheet->getStyle($this->getRangeName())->getFont()->setSize($Size);
        return $this;
    }

    /**
     * @return bool|array Cell: bool, Range: array( 'A1' => bool, 'B2' => bool, ... )
     */
    public function getFontBold()
    {

        $Result = array();
        $CellRange = $this->getRangeCellList();
        foreach ($CellRange as $CellName) {
            $Result[$CellName] = $this->Worksheet->getStyle($CellName)->getFont()->getBold();
        }
        if (count($Result) == 1) {
            return current($Result);
        } else {
            return $Result;
        }
    }

    /**
     * @return array Cell & Range: array( A1, A2, B1, ... )
     */
    private function getRangeCellList()
    {

        $Result = array();
        if (null === $this->CellBR) {
            $Result = array($this->CellTL->getCellName());
        } else {
            $ColumnRange = $this->getRangeColumnNameList();
            $RowList = $this->getRangeRowList();
            foreach ($ColumnRange as $ColumnName) {
                foreach ($RowList as $Index) {
                    array_push($Result, $ColumnName.$Index);
                }
            }
        }
        return $Result;
    }

    /**
     * @return array Cell & Range: array( 1, ... )
     */
    private function getRangeRowList()
    {

        if (null === $this->CellBR) {
            return array($this->CellTL->getRow());
        } else {
            return range($this->CellTL->getRow(), $this->CellBR->getRow());
        }
    }

    /**
     * @return float|array Cell: float, Range: array( 'A1' => float, 'B2' => float, ... )
     */
    public function getFontSize()
    {

        $Result = array();
        $CellRange = $this->getRangeCellList();
        foreach ($CellRange as $CellName) {
            $Result[$CellName] = (float)$this->Worksheet->getStyle($CellName)->getFont()->getSize();
        }
        if (count($Result) == 1) {
            return current($Result);
        } else {
            return $Result;
        }
    }

    /**
     * @return $this
     * @throws ComponentException
     */
    public function mergeCells()
    {

        if (null !== $this->CellBR) {
            try {
                $this->Worksheet->mergeCells($this->getRangeName());
            } catch (\Exception $Exception) {
                throw new ComponentException($Exception->getMessage(), $Exception->getCode(), $Exception);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function setAlignmentLeft()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        return $this;
    }

    /**
     * @return $this
     */
    public function setAlignmentCenter()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        return $this;
    }

    /**
     * @return $this
     */
    public function setAlignmentRight()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        return $this;
    }

    /**
     * @return $this
     */
    public function setAlignmentTop()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        return $this;
    }

    /**
     * @return $this
     */
    public function setAlignmentMiddle()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        return $this;
    }

    /**
     * @return $this
     */
    public function setAlignmentBottom()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderAll()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getAllBorders()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderVertical()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getVertical()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getVertical()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderHorizontal()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getHorizontal()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getHorizontal()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderOutline()
    {

        $this->setBorderTop();
        $this->setBorderRight();
        $this->setBorderBottom();
        $this->setBorderLeft();
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderTop()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getTop()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderRight()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getRight()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderBottom()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getBottom()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }

    /**
     * @return $this
     */
    public function setBorderLeft()
    {

        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        $this->Worksheet->getStyle($this->getRangeName())->getBorders()->getLeft()->setColor(new \PHPExcel_Style_Color());
        return $this;
    }
}

<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;

/**
 * Class Cell
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel
 */
class Cell
{

    /** @var int $Column */
    private $Column;
    /** @var int $Row */
    private $Row;

    /**
     * @param int $Column
     * @param int $Row
     */
    public function __construct($Column, $Row)
    {

        $this->Column = $Column;
        $this->Row = $Row;
    }

    /**
     * @return string
     */
    public function getCellName()
    {

        return $this->getColumnName().$this->getRow();
    }

    /**
     * @return string
     */
    public function getColumnName()
    {

        return \PHPExcel_Cell::stringFromColumnIndex($this->getColumn());
    }

    /**
     * @return int
     */
    public function getColumn()
    {

        return $this->Column;
    }

    /**
     * @return int
     */
    public function getRow()
    {

        return $this->Row;
    }
}

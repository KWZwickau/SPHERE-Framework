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
    function __construct( $Column, $Row )
    {

        $this->Column = $Column;
        $this->Row = $Row;
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

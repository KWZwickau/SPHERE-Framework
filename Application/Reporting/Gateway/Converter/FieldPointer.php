<?php
namespace SPHERE\Application\Reporting\Gateway\Converter;

/**
 * Class FieldPointer
 *
 * @package SPHERE\Application\Reporting\Gateway\Converter
 */
class FieldPointer
{

    private $Field = '';
    private $Column = '';

    /**
     * FieldPointer constructor.
     *
     * @param string $Column
     * @param string $Field
     */
    public function __construct($Column, $Field)
    {

        $this->Field = $Field;
        $this->Column = $Column;
    }

    /**
     * @return string
     */
    public function getColumn()
    {

        return $this->Column;
    }

    /**
     * @return string
     */
    public function getField()
    {

        return $this->Field;
    }
}

<?php
namespace SPHERE\Application\Reporting\Gateway\Converter;

/**
 * Class FieldSanitizer
 *
 * @package SPHERE\Application\Reporting\Gateway\Converter
 */
class FieldSanitizer
{

    private $Field = '';
    private $Column = '';
    private $Callback = null;

    /**
     * FieldSanitizer constructor.
     *
     * @param string $Column
     * @param string $Field
     * @param array $Callback
     */
    public function __construct($Column, $Field, $Callback)
    {

        $this->Field = $Field;
        $this->Column = $Column;
        $this->Callback = $Callback;
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

    /**
     * @return null|callable
     */
    public function getCallback()
    {

        return $this->Callback;
    }
}

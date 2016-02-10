<?php
namespace SPHERE\Application\Transfer\Gateway\Converter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;

/**
 * Class AbstractConverter
 *
 * @package SPHERE\Application\Transfer\Gateway\Converter
 */
abstract class AbstractConverter
{

    /** @var PhpExcel $Document */
    private $Document = null;

    /** @var int $SizeWidth */
    private $SizeWidth = 0;
    /** @var int $SizeHeight */
    private $SizeHeight = 0;

    /** @var array $FieldPointer */
    private $FieldPointer = array();
    /** @var array $SanitizePointer */
    private $SanitizePointer = array();
    /** @var array $SanitizeChain */
    private $SanitizeChain = array();

    /**
     *
     * @param FieldPointer $FieldPointer
     *
     * @return self
     */
    final public function setPointer(FieldPointer $FieldPointer)
    {

        $this->FieldPointer[$FieldPointer->getColumn()][] = $FieldPointer;
        return $this;
    }

    /**
     *
     * @param FieldSanitizer $FieldSanitizer
     *
     * @return self
     * @throws \Exception
     */
    final public function setSanitizer(FieldSanitizer $FieldSanitizer)
    {

        if (is_callable($FieldSanitizer->getCallback())) {
            $this->SanitizePointer[$FieldSanitizer->getColumn()][$FieldSanitizer->getField()] = $FieldSanitizer->getCallback();
        } else {
            throw new \Exception(end($Callback).' nicht verfügbar');
        }
        return $this;
    }

    /**
     * @param int      $Offset
     * @param null|int $Length
     */
    final public function scanFile($Offset, $Length = null)
    {

        for ($RunHeight = ( 1 + $Offset ); $RunHeight <= ( $Length ? ( $Offset + $Length ) : $this->SizeHeight ); $RunHeight++) {

            $Payload = array();
            for ($RunWidth = 0; $RunWidth < $this->SizeWidth; $RunWidth++) {
                $Column = \PHPExcel_Cell::stringFromColumnIndex($RunWidth);
                $Value = $this->Document->getValue($this->Document->getCell($Column.$RunHeight));
                if (isset( $this->FieldPointer[$Column] )) {
                    $PointerList = $this->FieldPointer[$Column];
                    /** @var FieldPointer $Pointer */
                    foreach ((array)$PointerList as $Pointer) {
                        $Field = $Pointer->getField();
                        if (!isset( $this->SanitizePointer[$Column][$Field] ) && !isset( $this->SanitizeChain[$Field] ) ) {
                            // Chain Sanitizer
                            $SanitizedValue = $Value;
                            // Always-Default
                            foreach ((array)$this->SanitizeChain['#'] as $Sanitizer) {
                                $SanitizedValue = $Sanitizer($SanitizedValue);
                            }
                        } else {
                            // Chain Sanitizer
                            $SanitizedValue = $Value;
                            // Always-Default
                            foreach ((array)$this->SanitizeChain['#'] as $Sanitizer) {
                                $SanitizedValue = $Sanitizer($SanitizedValue);
                            }
                            // Field-Bound
                            if( isset( $this->SanitizeChain[$Field] ) ) {
                                foreach ((array)$this->SanitizeChain[$Field] as $Sanitizer) {
                                    $SanitizedValue = $Sanitizer($SanitizedValue);
                                }
                            }
                            // Current Sanitizer
                            if( isset( $this->SanitizePointer[$Column][$Field] ) ) {
                                $Sanitize = $this->SanitizePointer[$Column][$Field];
                                $SanitizedValue = $Sanitize($SanitizedValue);
                            }
                        }
                        $Payload[$Pointer->getColumn()][$Pointer->getField()] = $SanitizedValue;
                    }
                }
            }
            $this->runConvert($Payload);
        }
    }

    /**
     * @param array $Row
     *
     * @return mixed|void
     */
    abstract public function runConvert($Row);

    /**
     * @param string $File
     *
     * @return self
     * @throws DocumentTypeException
     */
    final protected function loadFile($File)
    {

        $this->Document = Document::getDocument($File);
        $this->SizeHeight = $this->Document->getSheetRowCount();
        $this->SizeWidth = $this->Document->getSheetColumnCount();
        return $this;
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeFullTrim($Value)
    {

        return trim($Value);
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeAddressCityCode($Value)
    {

        return str_pad($Value, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param array $Callback
     * @param string $Chain
     *
     * @return self
     */
    final protected function addSanitizer( $Callback, $Chain = '#' )
    {
        if (is_callable($Callback)) {
            $this->SanitizeChain[$Chain][] = $Callback;
        }
        return $this;
    }
}

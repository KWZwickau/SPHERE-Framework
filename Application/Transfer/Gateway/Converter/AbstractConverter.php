<?php
namespace SPHERE\Application\Transfer\Gateway\Converter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use SPHERE\Application\Transfer\Gateway\Structure\AbstractStructure;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class AbstractConverter
 *
 * @package SPHERE\Application\Transfer\Gateway\Converter
 */
abstract class AbstractConverter extends Sanitizer
{

    /** @var AbstractStructure $Structure */
    private $Structure = null;

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
            /** @var array $Callback */
            $Callback = $FieldSanitizer->getCallback();
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
            for ($RunWidth = 1; $RunWidth < $this->SizeWidth; $RunWidth++) {
                $Column = Coordinate::stringFromColumnIndex($RunWidth);
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
                            if( isset( $this->SanitizeChain['#'] ) ) {
                                foreach ((array)$this->SanitizeChain['#'] as $Sanitizer) {
                                    $SanitizedValue = $Sanitizer($SanitizedValue);
                                }
                            }
                        } else {
                            // Chain Sanitizer
                            $SanitizedValue = $Value;
                            // Always-Default
                            if( isset( $this->SanitizeChain['#'] ) ) {
                                foreach ((array)$this->SanitizeChain['#'] as $Sanitizer) {
                                    $SanitizedValue = $Sanitizer($SanitizedValue);
                                }
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
     * @return AbstractStructure
     */
    public function getStructure()
    {

        return $this->Structure;
    }

    /**
     * @param string $File
     * @param null|int|string $Worksheet
     *
     * @return AbstractConverter
     * @throws DocumentTypeException
     */
    final protected function loadFile($File, $Worksheet = null)
    {

        $this->Document = Document::getDocument($File);

        if( is_integer( $Worksheet ) ) {
            $this->Document->selectWorksheetByIndex( $Worksheet );
        }
        if( is_string( $Worksheet ) ) {
            $this->Document->selectWorksheetByName( $Worksheet );
        }

        $this->SizeHeight = $this->Document->getSheetRowCount();
        $this->SizeWidth = $this->Document->getSheetColumnCount();
        return $this;
    }

    /**
     * @param array  $Callback
     * @param string $Chain
     *
     * @return self
     * @throws \Exception
     */
    final protected function addSanitizer( $Callback, $Chain = '#' )
    {
        if (is_callable($Callback)) {
            $this->SanitizeChain[$Chain][] = $Callback;
        } else {
            throw new \Exception( 'Sanitizer not available: '.end($Callback) );
        }
        return $this;
    }

    /**
     * @param AbstractStructure $Structure
     *
     * @return $this
     */
    final protected function setStructure(AbstractStructure $Structure)
    {

        $this->Structure = $Structure;
        return $this;
    }
}

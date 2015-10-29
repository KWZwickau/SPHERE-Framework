<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;

/**
 * Class Config
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel
 */
abstract class Config extends Bridge implements IBridgeInterface
{

    /** @var null|\PHPExcel $Source */
    protected $Source = null;

    /**
     * @return \PHPExcel_Cell_AdvancedValueBinder
     */
    public function createAdvancedValueBinder()
    {

        return new \PHPExcel_Cell_AdvancedValueBinder();
    }

    /**
     * @param PaperOrientationParameter $PaperOrientation
     *
     * @return PhpExcel
     */
    public function setPaperOrientationParameter(PaperOrientationParameter $PaperOrientation)
    {

        parent::setPaperOrientationParameter($PaperOrientation);
        $this->Source->getActiveSheet()->getPageSetup()
            ->setOrientation(
                constant('\PHPExcel_Worksheet_PageSetup::ORIENTATION_'.$this->getPaperOrientationParameter())
            );
        return $this;
    }

    /**
     * @param PaperSizeParameter $PaperSize
     *
     * @return PhpExcel
     */
    public function setPaperSizeParameter(PaperSizeParameter $PaperSize)
    {

        parent::setPaperSizeParameter($PaperSize);
        $this->Source->getActiveSheet()->getPageSetup()
            ->setPaperSize(
                constant('\PHPExcel_Worksheet_PageSetup::PAPERSIZE_'.$this->getPaperSizeParameter())
            );
        return $this;
    }

    /**
     * @param \PHPExcel_Cell_IValueBinder $ValueBinder
     */
    protected function setConfiguration(\PHPExcel_Cell_IValueBinder $ValueBinder = null)
    {

        \PHPExcel_Settings::setCacheStorageMethod(
            \PHPExcel_CachedObjectStorageFactory::cache_in_memory, array('cacheTime' => 3600)
        );
        if (null !== $ValueBinder) {
            \PHPExcel_Cell::setValueBinder($ValueBinder);
        }
    }
}

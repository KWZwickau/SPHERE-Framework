<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Config
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel
 */
abstract class Config extends Bridge implements IBridgeInterface
{

    /** @var null|Spreadsheet $Source */
    protected $Source = null;

    /**
     * @return AdvancedValueBinder
     */
    public function createAdvancedValueBinder()
    {

        return new AdvancedValueBinder();
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
                strtolower($this->getPaperOrientationParameter()->getOrientation())
//                constant()
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
                $PaperSize->getSizeConstant()
//                constant('\PHPExcel_Worksheet_PageSetup::PAPERSIZE_'.$this->getPaperSizeParameter())
            );
        return $this;
    }

    /**
     * @return void
     */
    protected function setConfiguration()
    {

        Settings::setCache(Settings::getCache());
//        \PHPExcel_Settings::setCacheStorageMethod(
//            \PHPExcel_CachedObjectStorageFactory::cache_in_memory, array('cacheTime' => 3600)
//        );
//        if (null !== $ValueBinder) {
//            \PHPExcel_Cell::setValueBinder($ValueBinder);
//        }
    }
}

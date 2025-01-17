<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Style;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as WorksheetPhpOffice;

/**
 * Class PhpExcel
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository
 */
class PhpExcel extends Worksheet
{

    protected $SpreadSheet = null;

    /**
     * PhpExcel constructor.
     */
    public function __construct()
    {

//        require_once(__DIR__.'/../../../Vendor/PhpExcel/1.8.0/Classes/PHPExcel.php');
//        require_once(__DIR__.DIRECTORY_SEPARATOR.'../../../../../Php8Combined/vendor/autoload.php');
        require_once(__DIR__.DIRECTORY_SEPARATOR.'../../../Vendor/PhpSpreadSheet/1.29.0/vendor/autoload.php');
        $this->SpreadSheet = new Spreadsheet();
    }

    /**
     * @param string|int $Column Name or Index
     * @param null|int   $Row    Index
     *
     * @return Cell
     */
    public function getCell($Column, $Row = null)
    {

        if (preg_match('![a-z]!is', $Column)) {
            $Coordinate = Coordinate::coordinateFromString($Column);
            $Column = Coordinate::columnIndexFromString($Coordinate[0]);
            $Row = $Coordinate[1];
        } else {
            $Column++;
            $Row++;
        }
        return new Cell($Column, $Row);
    }

    /**
     * @param Cell   $Cell
     * @param mixed  $Value
     * @param string $TypeString
     *
     * @return PhpExcel
     */
    public function setValue(Cell $Cell, mixed $Value, string $TypeString = DataType::TYPE_STRING):PhpExcel
    {

        $this->Source->getActiveSheet()->setCellValueExplicitByColumnAndRow($Cell->getColumn(), $Cell->getRow(),
            $Value, $TypeString);
        return $this;
    }

    /**
     * @param Cell $Cell
     *
     * @return string
     */
    public function getValue(Cell $Cell)
    {

        $Value = $this->Source->getActiveSheet()->getCell(new CellAddress($Cell->getCellName()))->getValue();
        // NULL durch leeren string ersetzen, da es ein unerwarteter Typ ist
        return $Value ?? '';
    }

    /**
     * @param Cell      $Cell  Single Cell or Top-Left
     * @param Cell|null $Range Bottom-Right
     *
     * @return PhpExcel\Style
     */
    public function setStyle(Cell $Cell, Cell $Range = null): PhpExcel\Style
    {

        return new Style($this->Source->getActiveSheet(), $Cell, $Range);
    }

    /**
     * @return int
     */
    public function getSheetColumnCount()
    {

        return Coordinate::columnIndexFromString($this->Source->getActiveSheet()->getHighestColumn()) + 1;
    }

    /**
     * @return int
     */
    public function getSheetRowCount()
    {

        return $this->Source->getActiveSheet()->getHighestRow();
    }

    /**
     * @return WorksheetPhpOffice
     */
    public function getActiveSheet():WorksheetPhpOffice
    {

        return $this->SpreadSheet->getActiveSheet();
    }
}

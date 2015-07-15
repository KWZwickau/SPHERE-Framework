<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;

/**
 * Class PhpExcel
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository
 */
class PhpExcel extends Bridge implements IBridgeInterface
{

    /** @var null|\PHPExcel $Source */
    private $Source = null;

    /**
     *
     */
    function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/PhpExcel/1.8.0/Classes/PHPExcel.php' );
    }

    /**
     * @param string|int $Column Name or Index
     * @param null|int   $Row    Index
     *
     * @return Cell
     */
    public function getCell( $Column, $Row = null )
    {

        if (preg_match( '![a-z]!is', $Column )) {
            $Coordinate = \PHPExcel_Cell::coordinateFromString( $Column );
            $Column = \PHPExcel_Cell::columnIndexFromString( $Coordinate[0] ) - 1;
            $Row = $Coordinate[1];
        } else {
            $Row += 1;
        }
        return new Cell( $Column, $Row );
    }

    /**
     * @param Cell  $Cell
     * @param mixed $Value
     *
     * @return $this
     */
    public function setValue( Cell $Cell, $Value )
    {

        $this->Source->getActiveSheet()->setCellValueExplicitByColumnAndRow( $Cell->getColumn(), $Cell->getRow(),
            $Value );
        return $this;
    }

    /**
     * @param Cell $Cell
     *
     * @return mixed
     */
    public function getValue( Cell $Cell )
    {

        return $this->Source->getActiveSheet()->getCellByColumnAndRow( $Cell->getColumn(),
            $Cell->getRow() )->getValue();
    }

    /**
     * @return int
     */
    public function getSheetColumnCount()
    {

        return \PHPExcel_Cell::columnIndexFromString(
            $this->Source->getActiveSheet()->getHighestColumn()
        );
    }

    /**
     * @return int
     */
    public function getSheetRowCount()
    {

        return $this->Source->getActiveSheet()->getHighestRow();
    }

    /**
     * @param FileParameter $Location
     *
     * @return PhpExcel
     */
    public function newFile( FileParameter $Location )
    {

        $this->setFileParameter( $Location );
        $this->setConfiguration();
        $this->Source = new \PHPExcel();
        return $this;
    }

    /**
     * @throws \PHPExcel_Exception
     */
    private function setConfiguration()
    {

        \PHPExcel_Settings::setCacheStorageMethod(
//            \PHPExcel_CachedObjectStorageFactory::cache_to_apc, array( 'cacheTime' => 3600 )
            \PHPExcel_CachedObjectStorageFactory::cache_in_memory, array( 'cacheTime' => 3600 )
        );
        //\PHPExcel_Cell::setValueBinder( new \PHPExcel_Cell_AdvancedValueBinder() );
    }

    /**
     * @param FileParameter $Location
     *
     * @return PhpExcel
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function loadFile( FileParameter $Location )
    {

        $this->setFileParameter( $Location );
        $this->setConfiguration();

        $Info = $Location->getFileInfo();
        switch ($Info->getExtension()) {
            case 'xlsx':
            case 'xlsm':
            case 'xltx':
            case 'xltm':
                $ReaderType = 'Excel2007';
                break;
            case 'xls':
            case 'xlt':
                $ReaderType = 'Excel5';
                break;
            // @codeCoverageIgnoreStart
            case 'ods':
            case 'ots':
                $ReaderType = 'OOCalc';
                break;
            case 'slk':
                $ReaderType = 'SYLK';
                break;
            case 'xml':
                $ReaderType = 'Excel2003XML';
                break;
            case 'gnumeric':
                $ReaderType = 'Gnumeric';
                break;
            // @codeCoverageIgnoreEnd
            case 'htm':
            case 'html':
                $ReaderType = 'HTML';
                break;
            case 'txt':
            case 'csv':
                $ReaderType = 'CSV';
                break;
            default:
                $ReaderType = null;
                break;
        }
        if ($ReaderType) {
            /** @var \PHPExcel_Reader_IReader|\PHPExcel_Reader_CSV $Reader */
            $Reader = \PHPExcel_IOFactory::createReader( $ReaderType );
            /**
             * Find CSV Delimiter
             */
            if ('CSV' == $ReaderType) {
                $Delimiter = array(
                    ',',
                    ';',
                    "\t"
                );
                $Result = array();
                $Content = file( $Location );
                for ($Line = 0; $Line < 5; $Line++) {
                    if (isset( $Content[$Line] )) {
                        foreach ($Delimiter as $Char) {
                            $Result[$Char][$Line] = substr_count( $Content[$Line], $Char );
                        }
                    }
                }
                foreach ($Result as $Delimiter => $Count) {
                    if (0 == array_sum( $Count )) {
                        $Result[$Delimiter] = false;
                    } else {
                        $Count = array_unique( $Count );
                        if (1 == count( $Count )) {
                            $Result[$Delimiter] = true;
                        } else {
                            $Result[$Delimiter] = false;
                        }
                    }
                }
                $Result = array_filter( $Result );
                if (1 == count( $Result )) {
                    $Reader->setDelimiter( key( $Result ) );
                }
            }
            $this->Source = $Reader->load( $Location->getFile() );
        } else {
            throw new TypeFileException( 'No Reader for '.$Info->getExtension().' available!' );
        }
        return $this;
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return PhpExcel
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function saveFile( FileParameter $Location = null )
    {

        if (null === $Location) {
            $Info = $this->getFileParameter()->getFileInfo();
        } else {
            $Info = $Location->getFileInfo();
        }
        $WriterType = null;
        switch ($Info->getExtension()) {
            case 'xlsx':
            case 'xlsm':
            case 'xltx':
            case 'xltm':
                $WriterType = 'Excel2007';
                break;
            case 'xls':
            case 'xlt':
                $WriterType = 'Excel5';
                break;
            case 'htm':
            case 'html':
                $WriterType = 'HTML';
                break;
            case 'csv':
                $WriterType = 'CSV';
                break;
            // @codeCoverageIgnoreStart
            default:
                break;
            // @codeCoverageIgnoreEnd
        }
        if (null === $Location) {
            if ($WriterType) {
                $Writer = \PHPExcel_IOFactory::createWriter( $this->Source, $WriterType );
                $Writer->save( $this->getFileParameter() );
            } else {
                // @codeCoverageIgnoreStart
                throw new TypeFileException( 'No Writer for '.$Info->getExtension().' available!' );
                // @codeCoverageIgnoreEnd
            }
        } else {
            if ($WriterType) {
                $Writer = \PHPExcel_IOFactory::createWriter( $this->Source, $WriterType );
                $Writer->save( $Location->getFile() );
            } else {
                // @codeCoverageIgnoreStart
                throw new TypeFileException( 'No Writer for '.$Info->getExtension().' available!' );
                // @codeCoverageIgnoreEnd
            }
        }
        return $this;
    }

    /**
     * @param PaperOrientationParameter $PaperOrientation
     *
     * @return PhpExcel
     */
    public function setPaperOrientationParameter( PaperOrientationParameter $PaperOrientation )
    {

        parent::setPaperOrientationParameter( $PaperOrientation );
        $this->Source->getActiveSheet()->getPageSetup()
            ->setOrientation( constant( '\PHPExcel_Worksheet_PageSetup::ORIENTATION_'.$this->getPaperOrientationParameter() ) );
        return $this;
    }

    /**
     * @param PaperSizeParameter $PaperSize
     *
     * @return PhpExcel
     */
    public function setPaperSizeParameter( PaperSizeParameter $PaperSize )
    {

        parent::setPaperSizeParameter( $PaperSize );
        $this->Source->getActiveSheet()->getPageSetup()
            ->setPaperSize( constant( '\PHPExcel_Worksheet_PageSetup::PAPERSIZE_'.$this->getPaperSizeParameter() ) );
        return $this;
    }
}

<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;

/**
 * Class File
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel
 */
abstract class File extends Config
{
    /** @var $string $delimiter */
    private $delimiter = null;

    /** @var $string $delimiter */
    private $headColumnLimitCsv = null;

    /**
     * @param $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $HeaderMax (ColumnName in Excel like 'AE' or 'C')
     * only first line will change his length
     * necessary for CSV DateV export
     */
    public function setHeadColumnLimitCsv($HeaderMax = '')
    {
        $this->headColumnLimitCsv = $HeaderMax;
    }

    /**
     * @param FileParameter               $Location
     * @param \PHPExcel_Cell_IValueBinder $ValueBinder
     *
     * @return PhpExcel
     */
    public function newFile(FileParameter $Location, \PHPExcel_Cell_IValueBinder $ValueBinder = null)
    {

        $this->setFileParameter($Location);
        $this->setConfiguration($ValueBinder);
        $this->Source = new \PHPExcel();
        return $this;
    }

    /**
     * @param FileParameter               $Location
     * @param \PHPExcel_Cell_IValueBinder $ValueBinder
     *
     * @return PhpExcel
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function loadFile(FileParameter $Location, \PHPExcel_Cell_IValueBinder $ValueBinder = null)
    {

        $this->setFileParameter($Location);
        $this->setConfiguration($ValueBinder);

        $Info = $Location->getFileInfo();
        $ReaderType = $this->getReaderType($Info);

        if ($ReaderType) {
            /** @var \PHPExcel_Reader_IReader|\PHPExcel_Reader_CSV $Reader */
            $Reader = \PHPExcel_IOFactory::createReader($ReaderType);
            /**
             * Find CSV Delimiter
             */
            if ('CSV' == $ReaderType) {
                if( $this->delimiter === null ) {
                    $Result = $this->getDelimiterType();
                } else {
                    $Result = $this->delimiter;
                }
                if ($Result) {
                    $Reader->setDelimiter($Result);
                }
            }
            $this->Source = $Reader->load($Location->getFile());
        } else {
            throw new TypeFileException('No Reader for '.$Info->getExtension().' available!');
        }
        return $this;
    }

    /**
     * @param \SplFileInfo $Info
     *
     * @return string
     */
    private function getReaderType(\SplFileInfo $Info)
    {

        $ReaderList = array(
            'Excel2007'    => array(
                'xlsx',
                'xlsm',
                'xltx',
                'xltm'
            ),
            'Excel5'       => array(
                'xls',
                'xlt'
            ),
            'OOCalc'       => array(
                'ods',
                'ots'
            ),
            'SYLK'         => array(
                'slk'
            ),
            'Excel2003XML' => array(
                'xml'
            ),
            'Gnumeric'     => array(
                'gnumeric'
            ),
            'HTML'         => array(
                'htm',
                'html'
            ),
            'CSV'          => array(
                'txt',
                'TXT',
                'csv',
                'CSV'
            )
        );

        $ReaderType = null;
        $Extension = $Info->getExtension();
        array_walk($ReaderList, function ($TypeList, $Reader) use (&$ReaderType, $Extension) {

            if (in_array($Extension, $TypeList)) {
                $ReaderType = $Reader;
            }
        });
        return $ReaderType;
    }

    /**
     * @return bool|string
     */
    private function getDelimiterType()
    {

        $Delimiter = array(
            ',',
            ';',
            "\t"
        );
        $Result = array();
        $Content = file($this->getFileParameter());
        for ($Line = 0; $Line < 5; $Line++) {
            if (isset( $Content[$Line] )) {
                foreach ($Delimiter as $Char) {
                    $Result[$Char][$Line] = substr_count($Content[$Line], $Char);
                }
            }
        }
        array_walk($Result, function ($Count, $Delimiter) use (&$Result) {

            if (0 == array_sum($Count)) {
                $Result[$Delimiter] = false;
            } else {
                $Count = array_unique($Count);
                if (1 == count($Count)) {
                    $Result[$Delimiter] = true;
                } else {
                    $Result[$Delimiter] = false;
                }
            }
        });
        $Result = array_filter($Result);
        if (1 == count($Result)) {
            return key($Result);
        } else {
            return false;
        }
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return PhpExcel
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function saveFile(FileParameter $Location = null)
    {

        if (null === $Location) {
            $Info = $this->getFileParameter()->getFileInfo();
        } else {
            $Info = $Location->getFileInfo();
        }

        $WriterType = $this->getWriterType($Info);

        if (null === $Location) {
            $Location = $this->getFileParameter();
        } else {
            $Location = $Location->getFile();
        }

        if ($WriterType) {
            $Writer = \PHPExcel_IOFactory::createWriter($this->Source, $WriterType);

            /**
             * Find CSV Delimiter
             */
            if ('CSV' == $WriterType) {
                if( $this->delimiter !== null ) {
                    $Writer->setDelimiter($this->delimiter);
                }
            }

            if($this->headColumnLimitCsv && 'CSV' == $WriterType){
                // updated save function for CSV Writer to manipulate first row
                $Writer->save($Location, $this->headColumnLimitCsv);
            } else {
                $Writer->save($Location);
            }

        } else {
            // @codeCoverageIgnoreStart
            throw new TypeFileException('No Writer for '.$Info->getExtension().' available!');
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    /**
     * @param \SplFileInfo $Info
     *
     * @return null|string
     */
    private function getWriterType(\SplFileInfo $Info)
    {

        $WriterList = array(
            'Excel2007' => array(
                'xlsx',
                'xlsm',
                'xltx',
                'xltm'
            ),
            'Excel5'    => array(
                'xls',
                'xlt'
            ),
            'HTML'      => array(
                'htm',
                'html'
            ),
            'CSV'       => array(
                'csv'
            )
        );

        $WriterType = null;
        $Extension = $Info->getExtension();
        array_walk($WriterList, function ($TypeList, $Writer) use (&$WriterType, $Extension) {

            if (in_array($Extension, $TypeList)) {
                $WriterType = $Writer;
            }
        });
        return $WriterType;
    }
}

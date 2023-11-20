<?php

namespace SPHERE\Application\Platform\System\BasicData;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use SPHERE\Application\Platform\System\BasicData\Service\Data;
use SPHERE\Application\Platform\System\BasicData\Service\Entity\TblHoliday;
use SPHERE\Application\Platform\System\BasicData\Service\Entity\TblHolidayType;
use SPHERE\Application\Platform\System\BasicData\Service\Entity\TblState;
use SPHERE\Application\Platform\System\BasicData\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Database\Binding\AbstractService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Platform\System\BasicData
 */
class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById($Id)
    {
        return (new Data($this->getBinding()))->getStateById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblState
     */
    public function getStateByName($Name)
    {
        return (new Data($this->getBinding()))->getStateByName($Name);
    }

    /**
     * @param $Id
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeById($Id)
    {
        return (new Data($this->getBinding()))->getHolidayTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getHolidayTypeByIdentifier($Identifier);
    }

    /**
     * @param $Name
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByName($Name)
    {
        return (new Data($this->getBinding()))->getHolidayTypeByName($Name);
    }

    /**
     * @return false|TblHolidayType[]
     */
    public function getHolidayTypeAll()
    {
        return (new Data($this->getBinding()))->getHolidayTypeAll();
    }

    /**
     * @return false|TblHoliday[]
     */
    public function getHolidayAll() {
        return (new Data($this->getBinding()))->getHolidayAll();
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllBy(\DateTime $startDate, \DateTime $endDate) {
        $list = array();
        if (($tblHolidayAll = $this->getHolidayAll())) {
            foreach ($tblHolidayAll as $tblHoliday) {
                if (($fromDateTime = $tblHoliday->getFromDateTime())) {
                    if ($fromDateTime >= $startDate && $fromDateTime <= $endDate) {
                        $list[$tblHoliday->getId()] = $tblHoliday;
                    } elseif (($toDateTime = $tblHoliday->getToDateTime())
                        && $toDateTime >= $startDate && $toDateTime <= $endDate
                    ) {
                        $list[$tblHoliday->getId()] = $tblHoliday;
                    }
                }
            }
        }

        return empty($list) ? false : $list;
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|String
     */
    public function createHolidaysFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {

        $errorList = array();
        $countNewHolidays = 0;
        $countExistsHoliday = 0;

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {
                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Typ' => null,
                    'Datum von' => null,
                    'Datum bis' => null,
                    'Name' => null,
                    'Optional Bundesland' => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        $rowCount = $RunY +1;

                        $type = trim($Document->getValue($Document->getCell($Location['Typ'], $RunY)));
                        if (($tblHolidayType = BasicData::useService()->getHolidayTypeByName($type))) {
                            if (($fromDate = (trim($Document->getValue($Document->getCell($Location['Datum von'], $RunY))))) != '') {
                                $fromDate = date('Y-m-d', Date::excelToTimestamp($fromDate));
                                if (($toDate = (trim($Document->getValue($Document->getCell($Location['Datum bis'], $RunY))))) != '') {
                                    $toDate = date('Y-m-d', Date::excelToTimestamp($toDate));
                                }
                                $tblState = null;
                                if (($state = (trim($Document->getValue($Document->getCell($Location['Optional Bundesland'], $RunY))))) != '') {
                                    if (!($tblState = BasicData::useService()->getStateByName($state))) {
                                        $errorList[] = array(
                                            'RowCount' => $rowCount,
                                            'Message' => 'Das Optionale Bundesland: ' . $state . ' existiert nicht.',
                                            'Result' => 'Der Unterrichtsfreie Tag wurde nicht angelegt.'
                                        );
                                    }
                                }

                                if ($tblState !== false) {
                                    if ((new Data($this->getBinding()))->getHolidayBy(
                                        $tblHolidayType,
                                        new \DateTime($fromDate),
                                        $toDate ? new \DateTime($toDate) : null,
                                        $tblState
                                    )) {
                                        $countExistsHoliday++;
                                    } else {
                                        if (($tblHoliday = (new Data($this->getBinding()))->createHoliday(
                                            $tblHolidayType,
                                            new \DateTime($fromDate),
                                            $toDate ? new \DateTime($toDate) : null,
                                            trim($Document->getValue($Document->getCell($Location['Name'], $RunY))),
                                            $tblState
                                        ))) {
                                            $countNewHolidays++;
                                        }
                                    }
                                }
                            } else {
                                $errorList[] = array(
                                    'RowCount' => $rowCount,
                                    'Message' => 'Das Datum von: ' . $fromDate . ' ist leer.',
                                    'Result' => 'Der Unterrichtsfreie Tag wurde nicht angelegt.'
                                );
                            }
                        } else {
                            $errorList[] = array(
                                'RowCount' => $rowCount,
                                'Message' => 'Der Typ: ' . $type . ' existiert nicht.',
                                'Result' => 'Der Unterrichtsfreie Tag wurde nicht angelegt.'
                            );
                        }
                    }
                }
            }
        }

        return new Success('Es wurden ' .  $countNewHolidays . ' Unterrichtsfreie Tage importiert.')
            . ($countExistsHoliday > 0 ? new Warning($countExistsHoliday . ' Unterrichtsfreie Tage existieren bereits') : '')
            . (empty($errorList) ? '' : new Danger(new TableData(
                $errorList,
                new Title('Fehlermeldungen'),
                array(
                    'RowCount' => 'Zeile',
                    'Message' => 'Fehlermeldung',
                    'Result' => 'Ergebnis'
                ))));
    }
}
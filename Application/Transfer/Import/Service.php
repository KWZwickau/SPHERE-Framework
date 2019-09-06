<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2019
 * Time: 08:55
 */

namespace SPHERE\Application\Transfer\Import;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import
 */
class Service
{
    /**
     * @var array
     */
    private $Location;

    /** @var PhpExcel $Document */
    private $Document;

    public function __construct($Location, $Document)
    {
        $this->Location = $Location;
        $this->Document = $Document;
    }

    /**
     * @param $year
     *
     * @return TblYear
     */
    public function insertSchoolYear($year)
    {
        $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            if (!$tblPeriodList) {
                // firstTerm
                $tblPeriod = Term::useService()->insertPeriod(
                    '1. Halbjahr',
                    '01.08.20' . $year,
                    '31.01.20' . ($year + 1)
                );
                if ($tblPeriod) {
                    Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                }

                // secondTerm
                $tblPeriod = Term::useService()->insertPeriod(
                    '2. Halbjahr',
                    '01.02.20' . ($year + 1),
                    '31.07.20' . ($year + 1)
                );
                if ($tblPeriod) {
                    Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                }
            }
        }

        return $tblYear;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     * @param string $Remark
     */
    public function insertPrivatePhone($tblPerson, $columnName, $RunY, $Remark = '')
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            $tblType = Phone::useService()->getTypeById(1);
            if (0 === strpos($phoneNumber, '01')) {
                $tblType = Phone::useService()->getTypeById(2);
            }

            Phone::useService()->insertPhoneToPerson($tblPerson, $phoneNumber, $tblType, $Remark);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     */
    public function insertBusinessPhone($tblPerson, $columnName, $RunY)
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            $tblType = Phone::useService()->getTypeById(3);
            if (0 === strpos($phoneNumber, '01')) {
                $tblType = Phone::useService()->getTypeById(4);
            }

            Phone::useService()->insertPhoneToPerson($tblPerson, $phoneNumber, $tblType, '');
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     */
    public function insertPrivateMail($tblPerson, $columnName, $RunY)
    {
        $mailAddress = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($mailAddress != '') {
            Mail::useService()->insertMailToPerson(
                $tblPerson,
                $mailAddress,
                Mail::useService()->getTypeById(1),
                ''
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     */
    public function insertBusinessMail($tblPerson, $columnName, $RunY)
    {
        $mailAddress = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($mailAddress != '') {
            Mail::useService()->insertMailToPerson(
                $tblPerson,
                $mailAddress,
                Mail::useService()->getTypeById(2),
                ''
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param $columnName
     * @param $RunY
     * @param string $separator
     */
    public function insertGroupsByName(TblPerson $tblPerson, $columnName, $RunY, $separator = ';')
    {
        $groupNames = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($groupNames != '') {
            $list = preg_split('/' . $separator . '/', $groupNames);
            foreach ($list as $name) {
                if ($tblGroup = Group::useService()->insertGroup(trim($name))) {
                    Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                }
            }
        }
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return array
     */
    public function splitStreet($columnName, $RunY)
    {
        $streetName = '';
        $streetNumber = '';
        $street = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($street != '') {
            if (preg_match_all('!\d+!', $street, $matches)) {
                $pos = strpos($street, $matches[0][0]);
                if ($pos !== null) {
                    $streetName = trim(substr($street, 0, $pos));
                    $streetNumber = trim(substr($street, $pos));
                }
            }
        }

        return array($streetName, $streetNumber);
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return string
     */
    public function formatZipCode($columnName, $RunY)
    {
        $code = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($code) {
            return str_pad(
                $code,
                5,
                "0",
                STR_PAD_LEFT
            );
        }

        return '';
    }
}
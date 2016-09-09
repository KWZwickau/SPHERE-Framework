<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:54
 */

namespace SPHERE\Application\Api\Document;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class AbstractDocument
 *
 * @package SPHERE\Application\Api\Document
 */
abstract class AbstractDocument
{

    /** @var null|Frame $Document */
    private $Document = null;

    /**
     * @var TblPerson|null
     */
    private $tblPerson = null;

    /**
     * @return false|TblPerson
     */
    public function getTblPerson()
    {
        if (null === $this->tblPerson) {
            return false;
        } else {
            return $this->tblPerson;
        }
    }

    /**
     * @param false|TblPerson $tblPerson
     */
    public function setTblPerson(TblPerson $tblPerson = null)
    {

        $this->tblPerson = $tblPerson;
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return Frame
     */
    abstract public function buildDocument();

    /**
     * @param array $Data
     *
     * @return IBridgeInterface
     */
    public function createDocument($Data = array())
    {

        if (isset($Data['Person']['Id'])) {
            if (($person = Person::useService()->getPersonById($Data['Person']['Id']))) {
                $this->setTblPerson($person);
                $this->allocatePersonData($Data);
                $this->allocatePersonAddress($Data);
                $this->allocatePersonCommon($Data);
                $this->allocateStudent($Data);
            } else {
                $this->setTblPerson(null);
            }
        }

        $this->Document = $this->buildDocument();

        if (!empty($Data)) {
            $this->Document->setData($Data);
        }

        return $this->Document->getTemplate();
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonData(&$Data)
    {

        if ($this->getTblPerson()) {
            $Data['Person']['Data']['Name']['Salutation'] = $this->getTblPerson()->getSalutation();
            $Data['Person']['Data']['Name']['First'] = $this->getTblPerson()->getFirstSecondName();
            $Data['Person']['Data']['Name']['Last'] = $this->getTblPerson()->getLastName();
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonAddress(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblAddress = $this->getTblPerson()->fetchMainAddress())) {
                $Data['Person']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Data['Person']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Data['Person']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Data['Person']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonCommon(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblCommon = Common::useService()->getCommonByPerson($this->getTblPerson()))
                && $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()
            ) {
                $Data['Person']['Common']['BirthDates']['Gender'] = $tblCommonBirthDates->getGender();
                $Data['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
                $Data['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace()
                    ? $tblCommonBirthDates->getBirthplace() : '&nbsp;';
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocateStudent(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblDivisionList = Student::useService()->getCurrentDivisionListByPerson($this->getTblPerson()))) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (!$tblDivision->getTblLevel()->getIsChecked()) {
                        $Data['Student']['Division']['Name'] = $tblDivision->getDisplayName();
                        break;
                    }
                }
            }

            if (($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))) {
                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))) {
                    if (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType))
                    ) {
                        if (($tblCompany = $tblTransfer->getServiceTblCompany())){
                            if (($tblAddress = $tblCompany->fetchMainAddress())){
                                $Data['Document']['PlaceDate'] = $tblAddress->getTblCity()->getName() . ', '
                                    . date('d.m.Y');
                            }
                        }
                    }
                }

                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))) {
                    if (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType))
                    ) {
                        $Data['Student']['LeaveDate'] = $tblTransfer->getTransferDate();
                    }
                }
            }
        }

        return $Data;
    }
}
<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentIntegration;

/**
 * Class Integration
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Integration extends Subject
{

    /**
     * @param int $Id
     *
     * @return bool|TblStudentIntegration
     */
    public function getStudentIntegrationById($Id)
    {

        return (new Data($this->getBinding()))->getStudentIntegrationById($Id);
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentFocus[]
     */
    public function getStudentFocusAllByStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->getStudentFocusAllByStudent($tblStudent);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentFocusType
     */
    public function getStudentFocusTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentFocusTypeById($Id);
    }

    /**
     * @param $Name
     * @return bool|TblStudentFocusType
     */
    public function getStudentFocusTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getStudentFocusTypeByName($Name);
    }

    /**
     * @return bool|TblStudentFocusType[]
     */
    public function getStudentFocusTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentFocusTypeAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentFocus
     */
    public function getStudentFocusById($Id)
    {

        return (new Data($this->getBinding()))->getStudentFocusById($Id);
    }

    /**
     * @return bool|TblStudentFocus[]
     */
    public function getStudentFocusAll()
    {

        return (new Data($this->getBinding()))->getStudentFocusAll();
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentDisorder[]
     */
    public function getStudentDisorderAllByStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->getStudentDisorderAllByStudent($tblStudent);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentDisorderType
     */
    public function getStudentDisorderTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentDisorderTypeById($Id);
    }

    /**
     * @param $Name
     * @return bool|TblStudentDisorderType
     */
    public function getStudentDisorderTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getStudentDisorderTypeByName($Name);
    }

    /**
     * @return bool|TblStudentDisorderType[]
     */
    public function getStudentDisorderTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentDisorderTypeAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentDisorder
     */
    public function getStudentDisorderById($Id)
    {

        return (new Data($this->getBinding()))->getStudentDisorderById($Id);
    }

    /**
     * @return bool|TblStudentDisorder[]
     */
    public function getStudentDisorderAll()
    {

        return (new Data($this->getBinding()))->getStudentDisorderAll();
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentDisorderType $tblStudentDisorderType
     *
     * @return TblStudentDisorder
     */
    public function addStudentDisorder(
        TblStudent $tblStudent,
        TblStudentDisorderType $tblStudentDisorderType
    ) {

        return (new Data($this->getBinding()))->addStudentDisorder($tblStudent, $tblStudentDisorderType);
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentFocusType $tblStudentFocusType
     *
     * @return TblStudentFocus
     */
    public function addStudentFocus(
        TblStudent $tblStudent,
        TblStudentFocusType $tblStudentFocusType
    ) {

        return (new Data($this->getBinding()))->addStudentFocus($tblStudent, $tblStudentFocusType);
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return false|TblStudentFocus
     */
    public function getStudentFocusPrimary(TblStudent $tblStudent) {

        return (new Data($this->getBinding()))->getStudentFocusPrimary($tblStudent);
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentDisorderType $tblStudentDisorderType
     *
     * @return false|TblStudentDisorder
     */
    public function getStudentDisorder(TblStudent $tblStudent, TblStudentDisorderType $tblStudentDisorderType)
    {

        return (new Data($this->getBinding()))->getStudentDisorder($tblStudent, $tblStudentDisorderType);
    }
}

<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecial;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportType;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Support
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Support extends Integration
{

    /**
     * @param int   $PersonId
     * @param array $Data
     *
     * @return IFormInterface|Redirect|string
     */
    public function createSupport($PersonId, $Data = array())
    {


        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblAccount = Account::useService()->getAccountBySession();
        $tblPersonEditor = null;
        if($tblAccount){
            $tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount);
            if($tblPersonList){
                $tblPersonEditor = $tblPersonList[0];
            }
        }

        $Date = new \DateTime($Data['Date']);
        $tblSupportType = $this->getSupportTypeById($Data['SupportType']);
        $serviceTblCompany = Company::useService()->getCompanyById($Data['Company']);
        if(!$serviceTblCompany){
            $serviceTblCompany = null;
        }
        $PersonSupport = $Data['PersonSupport'];
        $SupportTime = $Data['SupportTime'];
        $Remark = $Data['Remark'];

        if($tblPerson && $tblSupportType && $Date){
            $tblSupport = (new Data($this->getBinding()))->createSupport($tblPerson, $tblSupportType, $Date, $tblPersonEditor, $serviceTblCompany, $PersonSupport, $SupportTime, $Remark);

            if(isset($Data['PrimaryFocus']) && !empty($Data['PrimaryFocus'])){
                $tblStudentFocusType = $this->getStudentFocusTypeById($Data['PrimaryFocus']);
                if($tblStudentFocusType){
                    $IsPrimary = true;
                    $this->createSupportFocus($tblSupport, $tblStudentFocusType, $IsPrimary);
                }
            }

            if(isset($Data['CheckboxList'])) {
                $CheckboxList = $Data['CheckboxList'];
                if(!empty($CheckboxList)){
                    $IsPrimary = false;
                    foreach($CheckboxList as $Checkbox){
                        $tblStudentFocusType = $this->getStudentFocusTypeById($Checkbox);
                        $this->createSupportFocus($tblSupport, $tblStudentFocusType, $IsPrimary);
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert')
            .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param TblSupport          $tblSupport
     * @param TblStudentFocusType $tblStudentFocusType
     * @param bool                $IsPrimary
     *
     * @return TblSupportFocus
     */
    public function createSupportFocus(TblSupport $tblSupport, TblStudentFocusType $tblStudentFocusType, $IsPrimary = false)
    {

        return (new Data($this->getBinding()))->createSupportFocus($tblSupport, $tblStudentFocusType, $IsPrimary);
    }

    /**
     * @param int   $PersonId
     * @param array $Data
     *
     * @return IFormInterface|Redirect|string
     */
    public function createSpecial($PersonId, $Data = array())
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblAccount = Account::useService()->getAccountBySession();
        $PersonEditor = '';
        if($tblAccount){
            $tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount);
            if($tblPersonList){
                $tblPersonEditor = $tblPersonList[0];
                if($tblPersonEditor){
                    $PersonEditor = $tblPersonEditor->getLastFirstName();
                    if(($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonEditor))){
                        $PersonEditor .= '('.$tblTeacher->getAcronym().')';
                    }
                }
            }
        }

        $Date = new \DateTime($Data['Date']);
        $Remark = $Data['Remark'];

        if($tblPerson && $Date){
            $tblSpecial = (new Data($this->getBinding()))->createSpecial($tblPerson, $Date, $PersonEditor, $Remark);

            if(isset($Data['CheckboxList'])) {
                $CheckboxList = $Data['CheckboxList'];
                if(!empty($CheckboxList)){
                    foreach($CheckboxList as $Checkbox){
                        $tblStudentDisorderType = $this->getStudentDisorderTypeById($Checkbox);
                        $this->createSpecialDisorder($tblSpecial, $tblStudentDisorderType);
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert')
            .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param TblSpecial             $tblSpecial
     * @param TblStudentDisorderType $tblStudentDisorderType
     *
     * @return TblSpecialDisorder
     */
    public function createSpecialDisorder(TblSpecial $tblSpecial, TblStudentDisorderType $tblStudentDisorderType)
    {

        return (new Data($this->getBinding()))->createSpecialDisorder($tblSpecial, $tblStudentDisorderType);
    }

    /**
     * @param $Id
     *
     * @return false|TblSupportType
     */
    public function getSupportTypeById($Id)
    {

        return ( new Data($this->getBinding()) )->getSupportTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSupport
     */
    public function getSupportById($Id)
    {

        return ( new Data($this->getBinding()) )->getSupportById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSpecial
     */
    public function getSpecialById($Id)
    {

        return ( new Data($this->getBinding()) )->getSpecialById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblSupport[]
     */
    public function getSupportByPerson(TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->getSupportByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblSupport[]
     */
    public function getSpecialByPerson(TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->getSpecialByPerson($tblPerson);
    }

    /**
     * @return false|TblSupportType[]
     */
    public function getSupportTypeAll()
    {

        return ( new Data($this->getBinding()) )->getSupportTypeAll();
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return TblStudentFocusType|bool
     */
    public function getPrimaryFocusBySupport(TblSupport $tblSupport)
    {

        $FocusList = false;
        $tblSupportFocusList = (new Data($this->getBinding()))->getSupportFocusBySupport($tblSupport);
        if($tblSupportFocusList){
            foreach($tblSupportFocusList as $tblSupportFocus){
                if($tblSupportFocus->getIsPrimary()){
                    $FocusList = $tblSupportFocus->getTblStudentFocusType();
                }
            }
        }

        return $FocusList;
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return TblStudentFocusType[]|bool
     */
    public function getFocusListBySupport(TblSupport $tblSupport)
    {

        $FocusList = array();
        $tblSupportFocusList = (new Data($this->getBinding()))->getSupportFocusBySupport($tblSupport);
        if($tblSupportFocusList){
            foreach($tblSupportFocusList as $tblSupportFocus){
                if(!$tblSupportFocus->getIsPrimary()){
                    $FocusList[] = $tblSupportFocus->getTblStudentFocusType();
                }
            }
        }

        return (!empty($FocusList) ? $FocusList : false);
    }

    /**
     * @param TblSpecial $tblSpecial
     *
     * @return bool|TblStudentDisorderType[]
     */
    public function getStudentDisorderTypeAllBySpecial(TblSpecial $tblSpecial)
    {

        $tblStudentDisorderTypeList = array();
        $tblSpecialDisorderList = (new Data($this->getBinding()))->getSpecialDisorderBySpecial($tblSpecial);
        if($tblSpecialDisorderList){
            foreach($tblSpecialDisorderList as $tblSpecialDisorder){
                $tblStudentDisorderTypeList[] = $tblSpecialDisorder->getTblStudentDisorderType();
            }
        }

        return (!empty($tblStudentDisorderTypeList) ? $tblStudentDisorderTypeList : false);
    }
}

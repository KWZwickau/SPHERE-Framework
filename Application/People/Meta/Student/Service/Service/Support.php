<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblHandyCap;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecial;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Danger;
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
     * @param $Id
     *
     * @return bool|TblSupportFocusType
     */
    public function getSupportFocusTypeById($Id)
    {

        return (new Data($this->getBinding()))->getSupportFocusTypeById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblSupportFocusType
     */
    public function getSupportFocusTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getSupportFocusTypeByName($Name);
    }

    /**
     * @return bool|TblSupportFocusType[]
     */
    public function getSupportFocusTypeAll()
    {

        return (new Data($this->getBinding()))->getSupportFocusTypeAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblSpecialDisorderType
     */
    public function getSpecialDisorderTypeById($Id)
    {

        return (new Data($this->getBinding()))->getSpecialDisorderTypeById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblSpecialDisorderType
     */
    public function getSpecialDisorderTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getSpecialDisorderTypeByName($Name);
    }

    /**
     * @return bool|TblSpecialDisorderType[]
     */
    public function getSpecialDisorderTypeAll()
    {

        return (new Data($this->getBinding()))->getSpecialDisorderTypeAll();
    }

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
        $PersonEditor = '';
        if($tblAccount){
            $tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount);
            $tblPersonEditor = $tblPersonList[0];
            if($tblPersonEditor){
                $PersonEditor = $tblPersonEditor->getLastFirstName();
                if(($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonEditor))){
                    $PersonEditor .= ' ('.$tblTeacher->getAcronym().')';
                }
            }
        }

        $Date = new \DateTime($Data['Date']);
        $tblSupportType = $this->getSupportTypeById($Data['SupportType']);
        $Company = $Data['Company'];
        $PersonSupport = $Data['PersonSupport'];
        $SupportTime = $Data['SupportTime'];
        $Remark = $Data['Remark'];

        if($tblPerson && $tblSupportType && $Date){
            $tblSupport = (new Data($this->getBinding()))->createSupport($tblPerson, $tblSupportType, $Date, $PersonEditor, $Company, $PersonSupport, $SupportTime, $Remark);

            if(isset($Data['PrimaryFocus']) && !empty($Data['PrimaryFocus'])){
                $tblSupportFocusType = $this->getSupportFocusTypeById($Data['PrimaryFocus']);
                if($tblSupportFocusType){
                    $IsPrimary = true;
                    $this->createSupportFocus($tblSupport, $tblSupportFocusType, $IsPrimary);
                }
            }

            if(isset($Data['CheckboxList'])) {
                $CheckboxList = $Data['CheckboxList'];
                if(!empty($CheckboxList)){
                    $IsPrimary = false;
                    foreach($CheckboxList as $Checkbox){
                        $tblSupportFocusType = $this->getSupportFocusTypeById($Checkbox);
                        $this->createSupportFocus($tblSupport, $tblSupportFocusType, $IsPrimary);
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert');
    }

    /**
     * @param TblSupport          $tblSupport
     * @param TblSupportFocusType $tblSupportFocusType
     * @param bool                $IsPrimary
     *
     * @return TblSupportFocus
     */
    public function createSupportFocus(TblSupport $tblSupport, TblSupportFocusType $tblSupportFocusType, $IsPrimary = false)
    {

        return (new Data($this->getBinding()))->createSupportFocus($tblSupport, $tblSupportFocusType, $IsPrimary);
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
                        $PersonEditor .= ' ('.$tblTeacher->getAcronym().')';
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
                        $tblSpecialDisorderType = $this->getSpecialDisorderTypeById($Checkbox);
                        $this->createSpecialDisorder($tblSpecial, $tblSpecialDisorderType);
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert');
    }

    /**
     * @param TblSpecial             $tblSpecial
     * @param TblSpecialDisorderType $tblSpecialDisorderType
     *
     * @return bool|TblSpecialDisorder
     */
    public function createSpecialDisorder(TblSpecial $tblSpecial, TblSpecialDisorderType $tblSpecialDisorderType)
    {

        return (new Data($this->getBinding()))->createSpecialDisorder($tblSpecial, $tblSpecialDisorderType);
    }

    /**
     * @param int   $PersonId
     * @param array $Data
     *
     * @return IFormInterface|Redirect|string
     */
    public function createHandyCap($PersonId, $Data = array())
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
                        $PersonEditor .= ' ('.$tblTeacher->getAcronym().')';
                    }
                }
            }
        }

        $Date = new \DateTime($Data['Date']);
        $Remark = $Data['Remark'];

        if($tblPerson && $Date){
            (new Data($this->getBinding()))->createHandyCap($tblPerson, $Date, $PersonEditor, $Remark);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert');
    }

    /**
     * @param int   $PersonId
     * @param int   $SupportId
     * @param array $Data
     *
     * @return IFormInterface|Redirect|string
     */
    public function updateSupport($PersonId, $SupportId, $Data = array())
    {


        $tblSupport = Student::useService()->getSupportById($SupportId);
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $tblAccount = Account::useService()->getAccountBySession();
        $PersonEditor = '';
        if($tblAccount){
            $tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount);
            $tblPersonEditor = $tblPersonList[0];
            if($tblPersonEditor){
                $PersonEditor = $tblPersonEditor->getLastFirstName();
                if(($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonEditor))){
                    $PersonEditor .= ' ('.$tblTeacher->getAcronym().')';
                }
            }
        }

        $Date = new \DateTime($Data['Date']);
        $tblSupportType = $this->getSupportTypeById($Data['SupportType']);
        $Company = $Data['Company'];
        $PersonSupport = $Data['PersonSupport'];
        $SupportTime = $Data['SupportTime'];
        $Remark = $Data['Remark'];

        if($tblSupport && $tblPerson && $tblSupportType && $Date){
            (new Data($this->getBinding()))->updateSupport($tblSupport, $tblSupportType, $Date, $PersonEditor, $Company, $PersonSupport, $SupportTime, $Remark);

            //delete old entry's
            if(($tblSupportFocusList = Student::useService()->getSupportFocusListBySupport($tblSupport))){
                foreach($tblSupportFocusList as $tblSupportFocus) {
                    $this->deleteSupportFocus($tblSupportFocus);
                }
            }

            if(isset($Data['PrimaryFocus']) && !empty($Data['PrimaryFocus'])){
                $tblSupportFocusType = $this->getSupportFocusTypeById($Data['PrimaryFocus']);
                if($tblSupportFocusType){
                    $IsPrimary = true;
                    $this->createSupportFocus($tblSupport, $tblSupportFocusType, $IsPrimary);
                }
            }

            if(isset($Data['CheckboxList'])) {
                $CheckboxList = $Data['CheckboxList'];
                if(!empty($CheckboxList)){
                    $IsPrimary = false;
                    foreach($CheckboxList as $Checkbox){
                        $tblSupportFocusType = $this->getSupportFocusTypeById($Checkbox);
                        $this->createSupportFocus($tblSupport, $tblSupportFocusType, $IsPrimary);
                    }
                }
            }
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert');
        } else {
            return new Danger(new Remove().' Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param int   $PersonId
     * @param int   $SpecialId
     * @param array $Data
     *
     * @return IFormInterface|Redirect|string
     */
    public function updateSpecial($PersonId, $SpecialId, $Data = array())
    {

        $tblSpecial = Student::useService()->getSpecialById($SpecialId);
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
                        $PersonEditor .= ' ('.$tblTeacher->getAcronym().')';
                    }
                }
            }
        }

        $Date = new \DateTime($Data['Date']);
        $Remark = $Data['Remark'];

        if($tblSpecial && $tblPerson && $Date){
            (new Data($this->getBinding()))->updateSpecial($tblSpecial, $Date, $PersonEditor, $Remark);
            //delete old entry's
            if(($SpecialDisorderList = Student::useService()->getSpecialDisorderAllBySpecial($tblSpecial))){
                foreach($SpecialDisorderList as $SpecialDisorder){
                    $this->deleteSpecialDisorder($SpecialDisorder);
                }
            }

            if(isset($Data['CheckboxList'])) {
                $CheckboxList = $Data['CheckboxList'];
                if(!empty($CheckboxList)){
                    foreach($CheckboxList as $Checkbox){
                        $tblSpecialDisorderType = $this->getSpecialDisorderTypeById($Checkbox);
                        $this->createSpecialDisorder($tblSpecial, $tblSpecialDisorderType);
                    }
                }
            }
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert');
        } else {
            return new Danger(new Remove().' Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param int   $PersonId
     * @param int   $HandyCapId
     * @param array $Data
     *
     * @return IFormInterface|Redirect|string
     */
    public function updateHandyCap($PersonId, $HandyCapId, $Data = array())
    {

        $tblHandyCap = Student::useService()->getHandyCapById($HandyCapId);
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
                        $PersonEditor .= ' ('.$tblTeacher->getAcronym().')';
                    }
                }
            }
        }

        $Date = new \DateTime($Data['Date']);
        $Remark = $Data['Remark'];

        if($tblHandyCap && $tblPerson && $Date){
            (new Data($this->getBinding()))->updateHandyCap($tblHandyCap, $Date, $PersonEditor, $Remark);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert');
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
     * @param $Id
     *
     * @return false|TblHandyCap
     */
    public function getHandyCapById($Id)
    {

        return ( new Data($this->getBinding()) )->getHandyCapById($Id);
    }

    /**
     * @return false|TblSpecial
     */
    public function getHandyCapAll()
    {

        return ( new Data($this->getBinding()) )->getHandyCapAll();
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
     * @return false|TblSpecial[]
     */
    public function getSpecialByPerson(TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->getSpecialByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblHandyCap[]
     */
    public function getHandyCapByPerson(TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->getHandyCapByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param array     $Type
     *
     * @return false|TblSupport
     */
    public function getSupportByPersonNewest(TblPerson $tblPerson, $Type = array())
    {

        $tblSupportMatch = false;
        if(($tblSupportList = $this->getSupportByPerson($tblPerson))){
            foreach($tblSupportList as $tblSupport){
                if(!empty($Type) && ($tblSupportType = $tblSupport->getTblSupportType()) && in_array( $tblSupportType->getName(), $Type)){
                    /** @var TblSupport $tblSupportMatch */
                    if($tblSupportMatch){
                        if(new \DateTime($tblSupportMatch->getDate()) < new \DateTime($tblSupport->getDate())) {
                            $tblSupportMatch = $tblSupport;
                        }
                    } else {
                        $tblSupportMatch = $tblSupport;
                    }
                } elseif(empty($Type)) {
                    /** @var TblSupport $tblSupportMatch */
                    if($tblSupportMatch){
                        if (new \DateTime($tblSupportMatch->getDate()) < new \DateTime($tblSupport->getDate())) {
                            $tblSupportMatch = $tblSupport;
                        }
                    } else {
                        $tblSupportMatch = $tblSupport;
                    }
                }
            }
        }

        return $tblSupportMatch;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblSpecial
     */
    public function getSpecialByPersonNewest(TblPerson $tblPerson)
    {

        $tblSpecialMatch = false;
        if(($tblSpecialList = $this->getSpecialByPerson($tblPerson))){
            $tblSpecialMatch = $tblSpecialList[0];
            foreach($tblSpecialList as $tblSpecial){
                if(new \DateTime($tblSpecialMatch->getDate()) < new \DateTime($tblSpecial->getDate())) {
                    $tblSpecialMatch = $tblSpecial;
                }
            }
        }

        return $tblSpecialMatch;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblHandyCap
     */
    public function getHandyCapByPersonNewest(TblPerson $tblPerson)
    {

        $tblHandyCapMatch = false;
        if(($tblHandyCapList = $this->getHandyCapByPerson($tblPerson))){
            $tblHandyCapMatch = $tblHandyCapList[0];
            foreach($tblHandyCapList as $tblHandyCap){
                if(new \DateTime($tblHandyCapMatch->getDate()) < new \DateTime($tblHandyCap->getDate())) {
                    $tblHandyCapMatch = $tblHandyCap;
                }
            }
        }

        return $tblHandyCapMatch;
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
     * @return TblSupportFocusType|bool
     */
    public function getPrimaryFocusBySupport(TblSupport $tblSupport)
    {

        $FocusList = false;
        $tblSupportFocusList = (new Data($this->getBinding()))->getSupportFocusBySupport($tblSupport);
        if($tblSupportFocusList){
            foreach($tblSupportFocusList as $tblSupportFocus){
                if($tblSupportFocus->getIsPrimary()){
                    $FocusList = $tblSupportFocus->getTblSupportFocusType();
                }
            }
        }

        return $FocusList;
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return TblSupportFocusType[]|bool
     */
    public function getFocusListBySupport(TblSupport $tblSupport)
    {

        $FocusList = array();
        $tblSupportFocusList = (new Data($this->getBinding()))->getSupportFocusBySupport($tblSupport);
        if($tblSupportFocusList){
            foreach($tblSupportFocusList as $tblSupportFocus){
                if(!$tblSupportFocus->getIsPrimary()){
                    $FocusList[] = $tblSupportFocus->getTblSupportFocusType();
                }
            }
        }

        return (!empty($FocusList) ? $FocusList : false);
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return bool|false|TblSupportFocus[]
     */
    public function getSupportFocusListBySupport(TblSupport $tblSupport)
    {

        $tblSupportFocusList = (new Data($this->getBinding()))->getSupportFocusBySupport($tblSupport);
        return (!empty($tblSupportFocusList) ? $tblSupportFocusList : false);
    }

    /**
     * @param TblSpecial $tblSpecial
     *
     * @return bool|TblSpecialDisorderType[]
     */
    public function getSpecialDisorderTypeAllBySpecial(TblSpecial $tblSpecial)
    {

        $tblSpecialDisorderTypeList = array();
        $tblSpecialDisorderList = (new Data($this->getBinding()))->getSpecialDisorderBySpecial($tblSpecial);
        if($tblSpecialDisorderList){
            foreach($tblSpecialDisorderList as $tblSpecialDisorder){
                if($tblSpecialDisorder->getTblSpecialDisorderType()){
                    $tblSpecialDisorderTypeList[] = $tblSpecialDisorder->getTblSpecialDisorderType();
                }
            }
        }

        return (!empty($tblSpecialDisorderTypeList) ? $tblSpecialDisorderTypeList : false);
    }

    /**
     * @param TblSpecial $tblSpecial
     *
     * @return bool|TblSpecialDisorder[]
     */
    public function getSpecialDisorderAllBySpecial(TblSpecial $tblSpecial)
    {

        return (new Data($this->getBinding()))->getSpecialDisorderBySpecial($tblSpecial);
    }

    /**
     * @param int      $PersonId
     * @param array    $Data
     *
     * @param null|int $SupportId
     *
     * @return false|string|Form
     */
    public function checkInputSupport($PersonId, $Data, $SupportId = null)
    {
        $Error = false;
        $form = Student::useFrontend()->formSupport($PersonId, $SupportId);
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['SupportType']) && empty($Data['SupportType'])) {
            $form->setError('Data[SupportType]', 'Bitte geben Sie ein Verhalten des Förderantrag an');
            $Error = true;
        }
        if ($Error) {
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param int      $PersonId
     * @param array    $Data
     * @param null|int $SpecialId
     *
     * @return false|string|Form
     */
    public function checkInputSpecial($PersonId, $Data, $SpecialId = null)
    {

        $Error = false;
        $form = Student::useFrontend()->formSpecial($PersonId, $SpecialId);
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (!isset($Data['CheckboxList'])) {
            $form .= new Danger('Bitte geben Sie Besonderheiten an');
            $Error = true;
        }
        if ($Error) {
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param int      $PersonId
     * @param array    $Data
     * @param null|int $HandyCapId
     *
     * @return false|string|Form
     */
    public function checkInputHandyCap($PersonId, $Data, $HandyCapId = null)
    {

        $Error = false;
        $form = Student::useFrontend()->formHandyCap($PersonId, $HandyCapId);
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Remark']) && empty($Data['Remark'])) {
            $form->setError('Data[Remark]','Bitte geben Sie eine Bemerkung an');
            $Error = true;
        }
        if ($Error) {
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return bool
     */
    public function deleteSupport(TblSupport $tblSupport)
    {

        $IsRemove = true;
        if(($tblSupportFocusList = $this->getSupportFocusListBySupport($tblSupport))){
            foreach($tblSupportFocusList as $tblSupportFocus){
                if($IsRemove){
                    $IsRemove = (new Data($this->getBinding()))->deleteSupportFocus($tblSupportFocus);
                }
            }
            if($IsRemove){
                $IsRemove = (new Data($this->getBinding()))->deleteSupport($tblSupport);
            }
        }
        return $IsRemove;
    }

    /**
     * @param TblSupportFocus $tblSupportFocus
     *
     * @return bool
     */
    public function deleteSupportFocus(TblSupportFocus $tblSupportFocus)
    {

        return (new Data($this->getBinding()))->deleteSupportFocus($tblSupportFocus);
    }

    /**
     * @param TblSpecialDisorder $tblSpecialDisorder
     *
     * @return bool
     */
    public function deleteSpecialDisorder(TblSpecialDisorder $tblSpecialDisorder)
    {

        return (new Data($this->getBinding()))->deleteSpecialDisorder($tblSpecialDisorder);
    }

    /**
     * @param TblSpecial $tblSpecial
     *
     * @return bool
     */
    public function deleteSpecial(TblSpecial $tblSpecial)
    {

        $IsRemove = true;
        if(($tblSpecialDisorderList = $this->getSpecialDisorderAllBySpecial($tblSpecial))){
            foreach($tblSpecialDisorderList as $tblSpecialDisorder){
                if($IsRemove){
                    $IsRemove = (new Data($this->getBinding()))->deleteSpecialDisorder($tblSpecialDisorder);
                }
            }
            if($IsRemove){
                $IsRemove = (new Data($this->getBinding()))->deleteSpecial($tblSpecial);
            }
        }
        return $IsRemove;
    }

    /**
     * @param TblHandyCap $tblHandyCap
     *
     * @return bool
     */
    public function deleteHandyCap(TblHandyCap $tblHandyCap)
    {

        return (new Data($this->getBinding()))->deleteHandyCap($tblHandyCap);
    }
}

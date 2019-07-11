<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Education\ClassRegister\Diary\Service\Data;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryStudent;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
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
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return (new Data($this->getBinding()))->getDiaryById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivision(TblDivision $tblDivision)
    {
        return (new Data($this->getBinding()))->getDiaryAllByDivision($tblDivision);
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return (new Data($this->getBinding()))->getDiaryStudentAllByDiary($tblDiary);
    }

    /**
     * @param TblDivision $tblDivision
     * @param $Data
     * @param TblDiary|null $tblDiary
     *
     * @return bool|Form
     */
    public function checkFormDiary(
        TblDivision $tblDivision,
        $Data,
        TblDiary $tblDiary = null
    ) {
        $error = false;

        $form = Diary::useFrontend()->formDiary($tblDivision, $tblDiary ? $tblDiary->getId() : null);
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            $form->setSuccess('Data[Date]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param $Data
     *
     * @return bool
     */
    public function createDiary(TblDivision $tblDivision, $Data)
    {
        $tblPerson = false;
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount))
        ) {
            $tblPerson = $tblPersonAllByAccount[0];
        }

        if ($tblPerson
            && ($tblYear = $tblDivision->getServiceTblYear())
        ) {
            $tblDiary = (new Data($this->getBinding()))->createDiary(
                $Data['Subject'],
                $Data['Content'],
                $Data['Date'],
                $Data['Location'],
                $tblPerson,
                $tblYear,
                $tblDivision
            );

            if ($tblDiary) {
                if (isset($Data['Students'])) {
                    foreach($Data['Students'] as $personId => $value) {
                        if (($tblPersonItem = Person::useService()->getPersonById($personId))) {
                            (new Data($this->getBinding()))->addDiaryStudent($tblDiary, $tblPersonItem);
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDiary $tblDiary
     * @param $Data
     *
     * @return bool
     */
    public function updateDiary(TblDiary $tblDiary, $Data)
    {
        $tblPerson = false;
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount))
        ) {
            $tblPerson = $tblPersonAllByAccount[0];
        }

        if ($tblPerson
            && ($tblDivision = $tblDiary->getServiceTblDivision())
            && ($tblYear = $tblDivision->getServiceTblYear())
        ) {
            (new Data($this->getBinding()))->updateDiary(
                $tblDiary,
                $Data['Subject'],
                $Data['Content'],
                $Data['Date'],
                $Data['Location'],
                $tblPerson,
                $tblYear,
                $tblDivision
            );

            if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
                foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                    if (($tblPersonRemove = $tblDiaryStudent->getServiceTblPerson())
                        && !isset($Data['Students'][$tblPersonRemove->getId()])
                    ) {
                        (new Data($this->getBinding()))->removeDiaryStudent($tblDiaryStudent);
                    }
                }
            }

            if (isset($Data['Students'])) {
                foreach($Data['Students'] as $personId => $value) {
                    if (($tblPersonAdd = Person::useService()->getPersonById($personId))) {
                        (new Data($this->getBinding()))->addDiaryStudent($tblDiary, $tblPersonAdd);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return bool
     */
    public function destroyDiary(TblDiary $tblDiary)
    {
        if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                (new Data($this->getBinding()))->removeDiaryStudent($tblDiaryStudent);
            }
        }

        return (new Data($this->getBinding()))->destroyDiary($tblDiary);
    }
}
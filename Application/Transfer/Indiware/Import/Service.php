<?php

namespace SPHERE\Application\Transfer\Indiware\Import;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer as FilePointerAlias;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockI;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Transfer\Indiware\Import\Service\Data;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareError;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportLectureship;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportStudent;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportStudentCourse;
use SPHERE\Application\Transfer\Indiware\Import\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Indiware\Import
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
     * @param int $Id
     *
     * @return false|TblIndiwareImportLectureship
     */
    public function getIndiwareImportLectureshipById($Id)
    {

        return (new Data($this->getBinding()))->getIndiwareImportLectureshipById($Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblIndiwareImportStudent
     */
    public function getIndiwareImportStudentById($Id)
    {

        return (new Data($this->getBinding()))->getIndiwareImportStudentById($Id);
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     *
     * @return false|TblIndiwareImportStudentCourse[]
     */
    public function getIndiwareImportStudentCourseByIndiwareImportStudent(
        TblIndiwareImportStudent $tblIndiwareImportStudent
    ) {

        return (new Data($this->getBinding()))->getIndiwareImportStudentCourseByIndiwareImportStudent($tblIndiwareImportStudent);
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param                          $Number
     *
     * @return false|TblIndiwareImportStudentCourse
     */
    public function getIndiwareImportStudentCourseByIndiwareImportStudentAndNumber(
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        $Number
    ) {

        return (new Data($this->getBinding()))->getIndiwareImportStudentCourseByIndiwareImportStudentAndNumber(
            $tblIndiwareImportStudent, $Number);
    }

    /**
     * @param bool $ByAccount
     *
     * @return false|TblIndiwareImportLectureship[]
     */
    public function getIndiwareImportLectureshipAll($ByAccount = false)
    {
        if ($ByAccount) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return (new Data($this->getBinding()))->getIndiwareImportLectureshipAllByAccount($tblAccount);
            }
            return false;
        } else {
            return (new Data($this->getBinding()))->getIndiwareImportLectureshipAll();
        }
    }

    /**
     * @param string Type
     *
     * @return false|TblIndiwareError[]
     */
    public function getIndiwareErrorByType($Type = TblIndiwareError::TYPE_LECTURE_SHIP)
    {

        return (new Data($this->getBinding()))->getIndiwareErrorByType($Type);
    }

    /**
     * @param bool $ByAccount
     *
     * @return false|TblIndiwareImportStudent[]
     */
    public function getIndiwareImportStudentAll($ByAccount = false)
    {
        if ($ByAccount) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return (new Data($this->getBinding()))->getIndiwareImportStudentAllByAccount($tblAccount);
            }
            return false;
        } else {
            return (new Data($this->getBinding()))->getIndiwareImportStudentAll();
        }
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createIndiwareImportLectureShipByImportList($ImportList, TblYear $tblYear, TblAccount $tblAccount)
    {

        (new Data($this->getBinding()))->createIndiwareImportLectureshipBulk($ImportList, $tblYear, $tblAccount);

        return true;
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     * @param string     $LevelString
     *
     * @return bool
     * @internal param $LevelString
     */
    public function createIndiwareImportStudentByImportList(
        $ImportList,
        TblYear $tblYear,
        TblAccount $tblAccount,
        $LevelString
    )
    {

        (new Data($this->getBinding()))->createIndiwareImportStudentBulk($ImportList, $tblYear, $tblAccount,
            $LevelString);

        return true;
    }

    /**
     * @param string $Type
     * @param string $Identifier
     * @param string $Warning
     * @param string $CompareString
     *
     * @return bool
     */
    public function createIndiwareError($Type, $Identifier, $Warning, $CompareString)
    {

        (new Data($this->getBinding()))->createIndiwareError($Type, $Identifier, $Warning, $CompareString);

        return true;
    }

    /**
     * @param IFormInterface|null          $Stage
     * @param TblIndiwareImportLectureship $tblIndiwareImportLectureship
     * @param null|array                   $Data
     * @param bool                         $Visible
     *
     * @return IFormInterface|string
     */
    public function updateIndiwareImportLectureship(
        IFormInterface $Stage = null,
        TblIndiwareImportLectureship $tblIndiwareImportLectureship,
        $Data = null,
        $Visible = false
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        if (isset($Data['DivisionId']) && $Data['DivisionId'] != 0) {
            $tblDivision = Division::useService()->getDivisionById($Data['DivisionId']);
        } else {
            $tblDivision = null;
        }
        if (isset($Data['TeacherId']) && $Data['TeacherId'] != 0) {
            $tblTeacher = Teacher::useService()->getTeacherById($Data['TeacherId']);
        } else {
            $tblTeacher = null;
        }
        if (isset($Data['SubjectId']) && $Data['SubjectId'] != 0) {
            $tblSubject = Subject::useService()->getSubjectById($Data['SubjectId']);
        } else {
            $tblSubject = null;
        }
        if (isset($Data['SubjectGroup'])) {
            $SubjectGroup = $Data['SubjectGroup'];
        } else {
            $SubjectGroup = '';
        }
        if (isset($Data['IsIgnore'])) {
            $IsIgnore = $Data['IsIgnore'];
        } else {
            $IsIgnore = false;
        }
        // Es werden nur vollständige Lehraufträge aus der ImportErrorListe entfernt
        if($tblDivision != null && $tblTeacher != null && $tblSubject != null){
            // remove Error from IndiwareError
            $compareString = TblIndiwareError::fetchCompareString(
                $tblIndiwareImportLectureship->getSchoolClass(), $tblIndiwareImportLectureship->getSubjectName(),
                $tblIndiwareImportLectureship->getTeacherAcronym(), $tblIndiwareImportLectureship->getSubjectGroupName()
            );
            $this->destroyIndiwareErrorByTypeAndCustomString(TblIndiwareError::TYPE_LECTURE_SHIP, $compareString);
        }


        if ((new Data($this->getBinding()))->updateIndiwareImportLectureship(
            $tblIndiwareImportLectureship,
            $tblDivision,
            $tblTeacher,
            $tblSubject,
            $SubjectGroup,
            $IsIgnore)
        ) {
            $Message = new Success('Änderungen gespeichert');
            return $Message.new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS,
                    array('Visible' => $Visible));
        } else {
            $Stage->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Änderungen gespeichert')))));
            return $Stage.new Redirect('/Transfer/Indiware/Import/Lectureship/Edit', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblIndiwareImportLectureship->getId(), 'Visible' => $Visible));
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param null|array $Data
     * @param bool $Visible
     *
     * @return IFormInterface|string
     */
    public function updateIndiwareImportStudentCourse(
        IFormInterface $Stage = null,
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        $Data = null,
        $Visible = false
//        $arraySubjectName = array()
        ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }
        // Entfernen der Kurse ist vorerst nicht mehr nötig
//        $this->destroyIndiwareImportStudent($tblIndiwareImportStudent);

        for ($i = 1; $i <= 17; $i++) {
            $tblSubject = null;
            $SubjectGroup = '';
            $SubjectName = '';

            $tblIndiwareImportStudentCourse =
                Import::useService()->getIndiwareImportStudentCourseByIndiwareImportStudentAndNumber($tblIndiwareImportStudent,
                    $i);

            if (isset($Data['SubjectId'.$i]) && !empty($Data['SubjectId'.$i])) {
                $tblSubject = Subject::useService()->getSubjectById($Data['SubjectId'.$i]);
            }
            if (isset($Data['SubjectGroup'.$i]) && !empty($Data['SubjectGroup'.$i])) {
                $SubjectGroup = $Data['SubjectGroup'.$i];
            }
            if (isset($Data['IsIntensivCourse'.$i]) && !empty($Data['IsIntensivCourse'.$i])) {
                $IsIntensiveCourse = true;
            } else {
                $IsIntensiveCourse = false;
            }
            if (isset($Data['IsIgnoreCourse'.$i]) && !empty($Data['IsIgnoreCourse'.$i])) {
                $IsIgnoreCourse = true;
            } else {
                $IsIgnoreCourse = false;
            }

//            if (isset($arraySubjectName[$i])) {
//                $SubjectName = $arraySubjectName[$i];
//            }
            if ($tblIndiwareImportStudentCourse) { // update bei vorhandenen Feldern immer ausführen
//                if ($tblSubject || $SubjectGroup != '' || $IsIgnoreCourse) {
                (new Data($this->getBinding()))->updateIndiwareImportStudentCourse($tblIndiwareImportStudentCourse,
                    $tblSubject, $SubjectGroup, $IsIntensiveCourse, $IsIgnoreCourse);
//                }
            } else {    //create
                if ($tblSubject || $SubjectGroup != '') {
                    (new Data($this->getBinding()))->createIndiwareImportStudentCourse($SubjectGroup, $SubjectName, $i,
                        $IsIntensiveCourse, $IsIgnoreCourse, $tblIndiwareImportStudent, $tblSubject);
                }
            }
        }

        $Message = new Success('Änderungen gespeichert');
        return $Message.new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                array('Visible' => $Visible));
    }

    /**
     * @param TblIndiwareImportLectureship $tblIndiwareImportLectureship
     * @param bool                         $isIgnore
     *
     * @return bool
     */
    public function updateIndiwareImportLectureshipIsIgnore(
        TblIndiwareImportLectureship $tblIndiwareImportLectureship,
        $isIgnore = true
    ) {
        return (new Data($this->getBinding()))->updateIndiwareImportLectureshipIsIgnore($tblIndiwareImportLectureship,
            $isIgnore);
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     *
     * @return bool|TblDivision
     */
    public function updateIndiwareImportStudentDivision(TblIndiwareImportStudent $tblIndiwareImportStudent)
    {

        $tblYear = $tblIndiwareImportStudent->getServiceTblYear();
        $tblPerson = $tblIndiwareImportStudent->getServiceTblPerson();
        // search Division
        $tblDivision = false;
        if ($tblPerson && $tblYear) {
            $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
            if ($tblDivision) {
                $tblDivision = (new Data($this->getBinding()))->updateIndiwareImportStudentDivision($tblIndiwareImportStudent,
                    $tblDivision);
            }
        }
        return ($tblDivision ? $tblDivision : false);
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param bool                     $isIgnore
     *
     * @return mixed
     */
    public function updateIndiwareImportStudentIsIgnore(
        TblIndiwareImportStudent $tblIndiwareImportStudent,
        $isIgnore = true
    ) {
        return (new Data($this->getBinding()))->updateIndiwareImportStudentIsIgnore($tblIndiwareImportStudent,
            $isIgnore);
    }

    /**
     * @param TblIndiwareImportLectureship|null $tblIndiwareImportLectureship
     *
     * @return bool
     */
    public function destroyIndiwareImportLectureship(TblIndiwareImportLectureship $tblIndiwareImportLectureship = null)
    {

        if ($tblIndiwareImportLectureship == null) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                // Error werden verworfen, wenn Import verworfen wird
                (new Data($this->getBinding()))->destroyIndiwareErrorByType(TblIndiwareError::TYPE_LECTURE_SHIP);
                return (new Data($this->getBinding()))->destroyIndiwareImportLectureshipByAccount($tblAccount);
            }
        } else {
            return (new Data($this->getBinding()))->destroyIndiwareImportLectureship($tblIndiwareImportLectureship);
        }
        return false;
    }

    /**
     * @param string $Type
     * @param string $compareString
     *
     * @return bool
     */
    public function destroyIndiwareErrorByTypeAndCustomString($Type = TblIndiwareError::TYPE_LECTURE_SHIP, $compareString = '')
    {

        return (new Data($this->getBinding()))->destroyIndiwareErrorByTypeAndCustomString($Type, $compareString);
    }

    /**
     * @param string $Type
     *
     * @return bool
     */
    public function destroyIndiwareErrorByType($Type = TblIndiwareError::TYPE_LECTURE_SHIP)
    {

        return (new Data($this->getBinding()))->destroyIndiwareErrorByType($Type);
    }

    /**
     * @return bool
     */
    public function destroyIndiwareImportStudentAll()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblIndiwareImportStudentList = Import::useService()->getIndiwareImportStudentAll(true);
            if ($tblIndiwareImportStudentList) {
                foreach ($tblIndiwareImportStudentList as $tblIndiwareImportStudent) {
                    (new Data($this->getBinding()))->destroyIndiwareImportStudentCourse($tblIndiwareImportStudent);
                }
            }
            return (new Data($this->getBinding()))->destroyIndiwareImportStudentByAccount($tblAccount);
        }
        return false;
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @return bool
     */
    public function destroyIndiwareImportStudent(TblIndiwareImportStudent $tblIndiwareImportStudent)
     {
         return (new Data($this->getBinding()))->destroyIndiwareImportStudentCourse($tblIndiwareImportStudent);
     }

    /**
     * @param TblIndiwareImportLectureship[] $tblIndiwareImportLectureshipList
     *
     * @return TblDivision[]|bool
     */
    public function getDivisionListByIndiwareImportLectureship($tblIndiwareImportLectureshipList)
    {
        $tblDivisionList = array();
        if (!empty($tblIndiwareImportLectureshipList)) {
            foreach ($tblIndiwareImportLectureshipList as $tblIndiwareImportLectureship) {
                $tblDivision = $tblIndiwareImportLectureship->getServiceTblDivision();
                if ($tblDivision) {
                    if (!array_key_exists($tblDivision->getId(), $tblDivisionList)) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }
        }

        return (!empty($tblDivisionList) ? $tblDivisionList : false);
    }

    /**
     * @param TblDivision[] $tblDivisionList
     *
     * @return TblSubjectTeacher[]|bool
     */
    public function getSubjectTeacherListByDivisionList($tblDivisionList = array())
    {

        $SubjectTeacherList = array();
        if (!empty($tblDivisionList)) {
            foreach ($tblDivisionList as $tblDivision) {
                $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectList) {
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        $tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                        if ($tblSubjectTeacherList) {
                            $SubjectTeacherList = array_merge($SubjectTeacherList, $tblSubjectTeacherList);
                        }
                    }
                }
            }
        }
        return (!empty($SubjectTeacherList) ? $SubjectTeacherList : false);
    }

    /**
     * @param TblSubjectTeacher[] $SubjectTeacherList
     *
     * @return bool
     */
    public function removeLectureshipBySubjectTeacherList($SubjectTeacherList = array())
    {

        if (!empty($SubjectTeacherList)) {
            Division::useService()->removeSubjectTeacherList($SubjectTeacherList);
            return true;
        }
        return false;
    }

    /**
     * @return LayoutRow[]
     */
    public function importIndiwareLectureship($isSubjectDeleted = false)
    {
        $InfoList = array();
        $divisionSubjectList = array();
        $tblIndiwareImportLectureshipList = $this->getIndiwareImportLectureshipAll(true);
        if ($tblIndiwareImportLectureshipList) {

            //remove Lectureship (by used import division)
            $tblDivisionList = $this->getDivisionListByIndiwareImportLectureship($tblIndiwareImportLectureshipList);
            if ($tblDivisionList) {
                $tblSubjectTeacherList = $this->getSubjectTeacherListByDivisionList($tblDivisionList);
                if ($tblSubjectTeacherList) {
                    $this->removeLectureshipBySubjectTeacherList($tblSubjectTeacherList);
                }
            }

            $createSubjectTeacherList = array();
            $IsTeacherList = array();
            foreach ($tblIndiwareImportLectureshipList as $Key => $tblIndiwareImportLectureship) {
                $ImportError = 0;
                if (!($tblDivision = $tblIndiwareImportLectureship->getServiceTblDivision())) {
                    $ImportError++;
                }
                if (!($tblTeacher = $tblIndiwareImportLectureship->getServiceTblTeacher())) {
                    $ImportError++;
                }
                if (!($tblSubject = $tblIndiwareImportLectureship->getServiceTblSubject())) {
                    $ImportError++;
                }
                if ($tblIndiwareImportLectureship->getIsIgnore()) {
                    $ImportError++;
                }
                $SubjectGroup = $tblIndiwareImportLectureship->getSubjectGroup();
                // go to next Data if missing critical information / IsIgnore / missing TblPerson
                if ($ImportError >= 1 || !$tblTeacher->getServiceTblPerson()) {
                    continue;
                }
                $tblPerson = $tblTeacher->getServiceTblPerson();

                // get Subject
                $tblDivisionSubject = Division::useService()->getDivisionSubjectBySubjectAndDivisionWithoutGroup($tblSubject,
                    $tblDivision);
                if (!$tblDivisionSubject) {
                    // add Subject
                    $tblDivisionSubject = Division::useService()->addSubjectToDivision($tblDivision, $tblSubject);
                    if ($tblDivisionSubject) {
                        $divisionSubjectList[$tblDivisionSubject->getId()] = $tblDivisionSubject;
                    }
                } else {
                    $divisionSubjectList[$tblDivisionSubject->getId()] = $tblDivisionSubject;
                }

                if ($SubjectGroup) {
                    $tblDivisionSubject = false;
                    // get Group
                    $tblSubjectGroup = Division::useService()->getSubjectGroupByNameAndDivisionAndSubject($SubjectGroup,
                        $tblDivision, $tblSubject);
                    if ($tblSubjectGroup) {
                        // get DivisionSubject with Group
                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision,
                            $tblSubject, $tblSubjectGroup);
                    }
                    if (!$tblSubjectGroup) {
                        // create Group + add/get DivisionSubject
                        $tblDivisionSubject = Division::useService()->addSubjectToDivisionWithGroupImport($tblDivision,
                            $tblSubject, $SubjectGroup);
                    }

                    if ($tblDivisionSubject) {
                        $divisionSubjectList[$tblDivisionSubject->getId()] = $tblDivisionSubject;
                    }
                }
                if ($tblDivisionSubject) {

                    $IsTeacherId = $tblDivisionSubject->getId().'.'.$tblPerson->getId();
                    if (!array_key_exists($IsTeacherId, $IsTeacherList)) {
                        $IsTeacherList[$IsTeacherId] = true;

                        // addInfoList (only success no doubled)
                        $InfoList[$tblDivision->getId()]['DivisionName'] = $tblDivision->getDisplayName();
                        $InfoList[$tblDivision->getId()]['SubjectList'][$tblSubject->getId()][$Key] = $tblSubject->getAcronym().' - '.$tblSubject->getName()
                            .new PullRight($tblPerson->getFullName());
                        $InfoList[$tblDivision->getId()]['PanelColor'][$tblSubject->getId()] = Panel::PANEL_TYPE_WARNING;

                        // add Subject Teacher
                        $createSubjectTeacherList[] = array(
                            'tblDivisionSubject' => $tblDivisionSubject,
                            'tblPerson'          => $tblPerson
                        );
                    }
                }

            }
            // bulkSave for Lectureship
            Division::useService()->addSubjectTeacherList($createSubjectTeacherList);

            // delete divisionSubjects
            $deleteSubjectStudentList = array();
            $deleteDivisionSubjectList = array();
            if ($isSubjectDeleted && $tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (!isset($divisionSubjectList[$tblDivisionSubject->getId()])) {
                                if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject))) {
                                    $deleteSubjectStudentList = array_merge($tblSubjectStudentList, $deleteSubjectStudentList);
                                }

                                // SubjectTeacher sind bereits gelöscht

                                $deleteDivisionSubjectList[] = $tblDivisionSubject;
                            }
                        }
                    }
                }
            }
            if (!empty($deleteSubjectStudentList)) {
                Division::useService()->removeSubjectStudentBulk($deleteSubjectStudentList);
            }
            if (!empty($deleteDivisionSubjectList)) {
                Division::useService()->removeDivisionSubjectBulk($deleteDivisionSubjectList);
            }

            //Delete tblImport
            Import::useService()->destroyIndiwareImportLectureship();
        }

        $LayoutColumnArray = array();
        if (!empty($InfoList)) {
            // better show result
            foreach ($InfoList as $key => $Info) {
                $divisionName[$key] = strtoupper($Info['DivisionName']);
            }
            array_multisort($divisionName, SORT_NATURAL, $InfoList);
            foreach ($InfoList as $Info) {

                if (isset($Info['DivisionName']) && isset($Info['SubjectList'])) {
                    $LayoutColumnList = array();
                    $PanelContent = array();
                    if (!empty($Info['SubjectList'])) {
                        foreach ($Info['SubjectList'] as $SubjectAndTeacherArray) {
                            if (!empty($SubjectAndTeacherArray)) {
                                foreach ($SubjectAndTeacherArray as $SubjectAndTeacher) {
                                    $PanelContent[] = $SubjectAndTeacher;
                                }
                            }
                        }
                        $LayoutColumnList[] = new LayoutColumn(array(
                                new Title('Klasse: '.$Info['DivisionName']),
                                new Panel('Acronym - Fach'.new PullRight('Lehrer'),
                                    $PanelContent, Panel::PANEL_TYPE_SUCCESS)
                            )
                            , 4);
                    }
                    $LayoutColumnArray = array_merge($LayoutColumnArray, $LayoutColumnList);
                }
            }
        }

        // save clean view by LayoutRows
        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($LayoutColumnArray as $LayoutColumn) {
            if ($LayoutRowCount % 3 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($LayoutColumn);
            $LayoutRowCount++;
        }

        return $LayoutRowList;
    }

    /**
     * @return bool
     */
    public function importIndiwareStudentCourse()
    {

//        $InfoList = array();
        $tblIndiwareImportStudentList = $this->getIndiwareImportStudentAll(true);
        if ($tblIndiwareImportStudentList) {
            $tblDivisionList = array();
            array_walk($tblIndiwareImportStudentList,
                function (TblIndiwareImportStudent $tblIndiwareImportStudent) use (&$tblDivisionList) {
                    // keine ignorierten Klassen
                    if (!$tblIndiwareImportStudent->getIsIgnore()) {
                        if (($tblDivision = $tblIndiwareImportStudent->getServiceTblDivision())
                            && !array_key_exists($tblDivision->getId(), $tblDivisionList)
                        ) {
                            $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                        }
                    }
                });


            //remove SubjectStudent (by used Division [clear all Course-Data])
            if (!empty($tblDivisionList)) {
                array_walk($tblDivisionList, function (TblDivision $tblDivision) {
                    $tblSubjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
                    if ($tblSubjectList) {
                        foreach ($tblSubjectList as $tblSubject) {
                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject,
                                $tblDivision);
                            if ($tblDivisionSubjectList) {
                                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                    $tblDivisionStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                    Division::useService()->removeSubjectStudentBulk($tblDivisionStudentList);
                                }
                            }
                        }
                    }
                });
            }


            $createSubjectStudentList = array();
//            $IsTeacherList = array();
            foreach ($tblIndiwareImportStudentList as $Key => $tblIndiwareImportStudent) {
                $tblDivision = $tblIndiwareImportStudent->getServiceTblDivision();

                $tblIndiwareImportStudentCourseList = Import::useService()
                    ->getIndiwareImportStudentCourseByIndiwareImportStudent($tblIndiwareImportStudent);
                if ($tblIndiwareImportStudentCourseList && $tblDivision) {
                    foreach ($tblIndiwareImportStudentCourseList as $tblIndiwareImportStudentCourse) {
                        $SubjectGroup = $tblIndiwareImportStudentCourse->getSubjectGroup();
                        $tblSubject = $tblIndiwareImportStudentCourse->getServiceTblSubject();
                        $tblPerson = $tblIndiwareImportStudent->getServiceTblPerson();

                        if ($SubjectGroup && $tblSubject && !$tblIndiwareImportStudent->getIsIgnore()) {

                            // insert Subject in Division if not exist
                            if (!Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject,
                                $tblDivision)
                            ) {
                                Division::useService()->addSubjectToDivision($tblDivision, $tblSubject);
                            }

                            // Anlegen von Gruppen / Schülern nur wenn diese nicht Ignoriert werden soll
                            if (!$tblIndiwareImportStudentCourse->getisIgnoreCourse()) {
                                // get Group
                                $tblSubjectGroup = Division::useService()->getSubjectGroupByNameAndDivisionAndSubject($SubjectGroup,
                                    $tblDivision, $tblSubject);
                                if ($tblSubjectGroup) {
                                    // get DivisionSubject with Group
                                    $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision,
                                        $tblSubject, $tblSubjectGroup);
                                } else {

                                    // create Group + add/get DivisionSubject
                                    $tblDivisionSubject = Division::useService()->addSubjectToDivisionWithGroupImport($tblDivision,
                                        $tblSubject, $SubjectGroup,
                                        $tblIndiwareImportStudentCourse->getIsIntensiveCourse());
                                }

                                // nur aktuelle Schüler der Klasse werden zugeordnet
                                if ($tblPerson && $tblDivision &&
                                    Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision,
                                        $tblPerson)
                                ) {
                                    if ($tblDivisionSubject) {

                                        // add Subject Teacher
                                        $createSubjectStudentList[] = array(
                                            'tblDivisionSubject' => $tblDivisionSubject,
                                            'tblPerson'          => $tblPerson
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($createSubjectStudentList)) {
                Division::useService()->addSubjectStudentList($createSubjectStudentList);
                Import::useService()->destroyIndiwareImportStudentAll();
            }
        }

        if (!empty($createSubjectStudentList)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $Type
     * @param string $StringCompareDescription
     *
     * @return false|FilePointerAlias
     */
    public function getIndiwareErrorExcel($Type = TblIndiwareError::TYPE_LECTURE_SHIP, $StringCompareDescription = 'Klasse_Fach_Lehrer(_Fachgruppe)')
    {
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $Row = 0;
        $Column = 0;
        $export->setValue($export->getCell($Column++, $Row), $StringCompareDescription);
        $export->setValue($export->getCell($Column++, $Row), 'Kategorie');
        $export->setValue($export->getCell($Column, $Row), 'Warnung');
        $Row = 1;

        if(($tblIndiwareErrorList = Import::useService()->getIndiwareErrorByType($Type))){
            $tblIndiwareErrorList = $this->getSorter($tblIndiwareErrorList)->sortObjectBy('CompareString');
            foreach ($tblIndiwareErrorList as $tblIndiwareError) {
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $tblIndiwareError->getCompareString());
                $export->setValue($export->getCell($Column++, $Row), $tblIndiwareError->getIdentifier());
                $export->setValue($export->getCell($Column, $Row), $tblIndiwareError->getWarning());
                $Row++;
            }

            $export->setStyle($export->getCell(0, 0))->setColumnWidth(31);
            $export->setStyle($export->getCell(2, 0))->setColumnWidth(60);
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param null $Data
     *
     * @return IFormInterface|Danger|string
     */
    public function createSelectedCourseFromFile(IFormInterface $Form = null, UploadedFile $File = null, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (!($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($Data['GenerateCertificateId']))) {
            $Form->setError('Data[GenerateCertificateId]', 'Bitte geben Sie einen Zeugnisauftrag an');
            return $Form;
        }


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
            /** @var PhpExcel $Document */
            $Document = Document::getDocument($File->getPathname());

            $X = $Document->getSheetColumnCount();
            $Y = $Document->getSheetRowCount();

            /**
             * Header -> Location
             */
            $Location = array(
                'Vorname' => null,
                'Name' => null,
                'Geburtsdatum' => null
            );

            for ($j = 1; $j < 5; $j++) {
                for ($i = 1; $i < 18; $i++) {
                    if ($j == 1) {
                        $Location['Fach' . $i] = null;
                    }

                    $Location['Einbringung' . $j . $i] = null;
                }
            }

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
                $error = array();
                $success = array();
                $countPersons = 0;
                $countMissingPersons = 0;
                $countDuplicatePersons = 0;

                $prepareStudents = array();
                // alle möglichen Schüler mit entsprechender Zeugnisvorbereitung ermitteln
                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if (($tblDivision = $tblPrepare->getServiceTblDivision())
                            && ($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getName() == 'Gymnasium'
                            && intval($tblLevel->getName()) == 12
                        ) {
                            if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))) {
                                $prepareStudents = array_merge($prepareStudents, $tblPrepareStudentList);
                            }
                        }
                    }
                }

                for ($RunY = 1; $RunY < $Y; $RunY++) {
                    $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                    $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                    $birthday = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                    if ($birthday) {
                        if (strpos($birthday, '.') === false) {
                            $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
                        }
                    }

                    // person finden
                    $personList = array();
                    $tblPerson = false;
                    $tblDivision = false;
                    $tblPrepareStudent = false;
                    $tblPrepare = false;
                    if ($firstName !== '' && $lastName !== '') {
                        foreach ($prepareStudents as $tblPrepareStudentTemp) {
                            if (($tblPersonTemp = $tblPrepareStudentTemp->getServiceTblPerson())
                                && strtolower($tblPersonTemp->getFirstSecondName()) == strtolower($firstName)
                                && strtolower($tblPersonTemp->getLastName()) == strtolower($lastName)
                            ) {
                                if (($birthdayPerson = $tblPersonTemp->getBirthday())
                                    && $birthday
                                ) {
                                    $birthdayPerson = new DateTime($birthdayPerson);
                                    $birthday = new DateTime($birthday);

                                    if ($birthday != $birthdayPerson) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                            . ' hat ein anderes Geburtsdatum.';
                                        continue;
                                    }
                                }

                                $personList[] = $tblPersonTemp;
                                $tblPerson = $tblPersonTemp;
                                $tblPrepareStudent = $tblPrepareStudentTemp;
                                if (($tblPrepare = $tblPrepareStudentTemp->getTblPrepareCertificate())) {
                                    $tblDivision = $tblPrepare->getServiceTblDivision();
                                }
                            }
                        }

                        if (count($personList) == 1) {
                            $countPersons++;
                        } elseif (count($personList) > 1) {
                            $countDuplicatePersons++;
                            $tblPerson = false;
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde nicht mehrmals gefunden.';
                        } else {
                            $countMissingPersons++;
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde nicht gefunden.';
                        }
                    }

                    if ($tblPerson && $tblPrepareStudent && $tblPrepare && $tblDivision) {
                        // Zensuren kopieren aus Zeugnissen und Stichtagsnotenauftrag
                        $blockI = new BlockI($tblDivision, $tblPerson, $tblPrepare, BlockIView::PREVIEW);

                        // Fächer pro Schüler zuordnen
                        $studentSubjectList = array();
                        for ($i = 1; $i < 18; $i++) {
                            $subject = trim($Document->getValue($Document->getCell($Location['Fach' . $i], $RunY)));
                            if ($subject != '') {
                                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subject))) {
                                    // prüfen ob der Schüler das Fach besucht bzw. noten hat
                                    $studentSubjectList[$i] = $tblSubject;
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Bei Person ' . $firstName . ' ' . $lastName
                                        . ' wurde das Fach' . $i . ':' . $subject . ' nicht gefunden.';
                                }
                            }
                        }

                        // Kurseinbringung der Zensuren updaten aus csv
                        $countSelectedCourse = 0;
                        for ($j = 1; $j < 5; $j++) {
                            switch ($j) {
                                case 1: $identifier = '11-1'; break;
                                case 2: $identifier = '11-2'; break;
                                case 3: $identifier = '12-1'; break;
                                case 4: $identifier = '12-2'; break;
                                default: $identifier = '11-1';
                            }
                            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($identifier))) {
                                for ($i = 1; $i < 18; $i++) {
                                    $selected = trim($Document->getValue($Document->getCell($Location['Einbringung' . $j . $i],
                                        $RunY)));
                                    if ($selected != '') {
                                        if (($isCourseSelected = strtoupper($selected) == 'WAHR')) {
                                            $countSelectedCourse++;
                                        }

                                        if (!isset($studentSubjectList[$i]) && !$isCourseSelected) {
                                            // nicht eingebrachte Kurse stehen auf 'FALSCH', auch wenn es keinen Kurs gibt
                                        } elseif (isset($studentSubjectList[$i])) {
                                            $tblSubject = $studentSubjectList[$i];

                                            // Zensur finden und Kurseinbringung setzen
                                            if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                                                $tblPrepare, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, true
                                            ))) {
                                                Prepare::useService()->updatePrepareAdditionalGrade(
                                                    $tblPrepareAdditionalGrade,
                                                    $tblPrepareAdditionalGrade->getGrade(),
                                                    $isCourseSelected
                                                );
                                            // Spezialfall en2
                                            } elseif ($tblSubject->getAcronym() == 'EN2'
                                                && ($tblSubjectTemp = Subject::useService()->getSubjectByAcronym('EN'))
                                                && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                                                    $tblPrepare, $tblPerson, $tblSubjectTemp, $tblPrepareAdditionalGradeType, true
                                                ))
                                            ) {
                                                Prepare::useService()->updatePrepareAdditionalGrade(
                                                    $tblPrepareAdditionalGrade,
                                                    $tblPrepareAdditionalGrade->getGrade(),
                                                    $isCourseSelected
                                                );
                                            } elseif ($isCourseSelected) {
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Bei Person ' . $firstName . ' ' . $lastName
                                                    . ' wurde für die Einbringung' . $j . $i . ':' . $selected
                                                    . ' keine Zensur in der Schulsoftware gefunden';
                                            } else {
                                                //  Bei 'FALSCH' kann auch keine Zensur vorhanden sein
                                            }
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Bei Person ' . $firstName . ' ' . $lastName
                                                . ' wurde für die Einbringung' . $j . $i . ':' . $selected . ' das Fach nicht gefunden';
                                        }
                                    }
                                }
                            }
                        }

                        $text = $firstName . ' ' . $lastName . ' wurden ' . $countSelectedCourse . ' von 40 Kursen zugeordnet.';
                        $success[] =  $countSelectedCourse == 40
                            ? new SuccessText($text)
                            : new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                    }
                }

                return
//                    new Success('Es wurden ' . $countPersons . ' Personen erfolgreich gefunden.') .
                    new Panel(
                        'Es wurden ' . $countPersons . ' Personen erfolgreich gefunden.',
                        $success,
                        Panel::PANEL_TYPE_SUCCESS
                    ) .
                    ($countDuplicatePersons > 0 ? new Warning($countDuplicatePersons . ' Doppelte Personen gefunden') : '') .
                    ($countMissingPersons > 0 ? new Warning($countMissingPersons . ' Personen nicht gefunden') : '') .
                    (empty($error)
                        ? ''
                        : new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        )))))
                    ;
            } else {
                return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
            }
        }

        return new Danger('File nicht gefunden');
    }
}

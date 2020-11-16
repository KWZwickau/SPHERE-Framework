<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Transfer\Untis\Import\Service\Data;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportStudent;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportStudentCourse;
use SPHERE\Application\Transfer\Untis\Import\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Untis\Import
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
     * @return false|TblUntisImportLectureship
     */
    public function getUntisImportLectureshipById($Id)
    {

        return ( new Data($this->getBinding()) )->getUntisImportLectureshipById($Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblUntisImportStudent
     */
    public function getUntisImportStudentById($Id)
    {

        return ( new Data($this->getBinding()) )->getUntisImportStudentById($Id);
    }

    /**
     * @param bool $ByAccount
     *
     * @return false|TblUntisImportStudent[]
     */
    public function getUntisImportStudentAll($ByAccount = false)
    {
        if ($ByAccount) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return (new Data($this->getBinding()))->getUntisImportStudentAllByAccount($tblAccount);
            }
            return false;
        } else {
            return (new Data($this->getBinding()))->getUntisImportStudentAll();
        }
    }

    /**
     * @param bool $ByAccount
     *
     * @return false|TblUntisImportLectureship[]
     */
    public function getUntisImportLectureshipAll($ByAccount = false)
    {
        if ($ByAccount) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return ( new Data($this->getBinding()) )->getUntisImportLectureshipAllByAccount($tblAccount);
            }
            return false;
        } else {
            return ( new Data($this->getBinding()) )->getUntisImportLectureshipAll();
        }
    }

    /**
     * @param            $ImportList
     * @param TblYear    $tblYear
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function createUntisImportLectureShipByImportList($ImportList, TblYear $tblYear, TblAccount $tblAccount)
    {

        ( new Data($this->getBinding()) )->createUntisImportLectureship($ImportList, $tblYear, $tblAccount);

        return true;
    }

    /**
     * @param IFormInterface|null       $Stage
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     * @param null|array                $Data
     * @param bool                      $Visible
     *
     * @return IFormInterface|string
     */
    public function updateUntisImportLectureship(
        IFormInterface $Stage = null,
        TblUntisImportLectureship $tblUntisImportLectureship,
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

        if (( new Data($this->getBinding()) )->updateUntisImportLectureship(
            $tblUntisImportLectureship,
            $tblDivision,
            $tblTeacher,
            $tblSubject,
            $SubjectGroup,
            $IsIgnore)
        ) {
            $Message = new Success('Änderungen gespeichert');
            return $Message.new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('Visible' => $Visible));
        } else {
            $Stage->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Änderungen gespeichert')))));
            return $Stage.new Redirect('/Transfer/Untis/Import/Lectureship/Edit', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblUntisImportLectureship->getId(), 'Visible' => $Visible));
        }
    }

    /**
     * @param TblUntisImportLectureship $tblUntisImportLectureship
     * @param bool                      $isIgnore
     *
     * @return bool
     */
    public function updateUntisImportLectureshipIsIgnore(TblUntisImportLectureship $tblUntisImportLectureship, $isIgnore = true)
    {
        return ( new Data($this->getBinding()) )->updateUntisImportLectureshipIsIgnore($tblUntisImportLectureship, $isIgnore);
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     *
     * @return bool|TblDivision
     */
    public function updateUntisImportStudentDivision(TblUntisImportStudent $tblUntisImportStudent)
    {

        $tblYear = $tblUntisImportStudent->getServiceTblYear();
        $tblPerson = $tblUntisImportStudent->getServiceTblPerson();
        // search Division
        $tblDivision = false;
        if ($tblPerson && $tblYear) {
            $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
            if ($tblDivision) {
                $tblDivision = (new Data($this->getBinding()))->updateUntisImportStudentDivision($tblUntisImportStudent,
                    $tblDivision);
            }
        }
        return ($tblDivision ? $tblDivision : false);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param null|array $Data
     * @param bool $Visible
     *
     * @return IFormInterface|string
     */
    public function updateUntisImportStudentCourse(
        IFormInterface $Stage = null,
        TblUntisImportStudent $tblUntisImportStudent,
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
//        $this->destroyUntisImportStudent($tblUntisImportStudent);

        for ($i = 1; $i <= 17; $i++) {
            $tblSubject = null;
            $SubjectGroup = '';
            $SubjectName = '';

            $tblUntisImportStudentCourse =
                Import::useService()->getUntisImportStudentCourseByUntisImportStudentAndNumber($tblUntisImportStudent,
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
            if ($tblUntisImportStudentCourse) { // update bei vorhandenen Feldern immer ausführen
//                if ($tblSubject || $SubjectGroup != '' || $IsIgnoreCourse) {
                (new Data($this->getBinding()))->updateUntisImportStudentCourse($tblUntisImportStudentCourse,
                    $tblSubject, $SubjectGroup, $IsIntensiveCourse, $IsIgnoreCourse);
//                }
            } else {    //create
                if ($tblSubject || $SubjectGroup != '') {
                    (new Data($this->getBinding()))->createUntisImportStudentCourse($SubjectGroup, $SubjectName, $i,
                        $IsIntensiveCourse, $IsIgnoreCourse, $tblUntisImportStudent, $tblSubject);
                }
            }
        }

        $Message = new Success('Änderungen gespeichert');
        return $Message.new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                array('Visible' => $Visible));
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param                       $Number
     *
     * @return false|TblUntisImportStudentCourse
     */
    public function getUntisImportStudentCourseByUntisImportStudentAndNumber(
        TblUntisImportStudent $tblUntisImportStudent,
        $Number
    ) {

        return (new Data($this->getBinding()))->getUntisImportStudentCourseByUntisImportStudentAndNumber(
            $tblUntisImportStudent, $Number);
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     *
     * @return false|TblUntisImportStudentCourse[]
     */
    public function getUntisImportStudentCourseByUntisImportStudent(
        TblUntisImportStudent $tblUntisImportStudent
    ) {

        return (new Data($this->getBinding()))->getUntisImportStudentCourseByUntisImportStudent($tblUntisImportStudent);
    }

    /**
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param bool                     $isIgnore
     *
     * @return mixed
     */
    public function updateUntisImportStudentIsIgnore(
        TblUntisImportStudent $tblUntisImportStudent,
        $isIgnore = true
    ) {
        return (new Data($this->getBinding()))->updateUntisImportStudentIsIgnore($tblUntisImportStudent,
            $isIgnore);
    }

    /**
     * @param TblUntisImportLectureship|null $tblUntisImportLectureship
     *
     * @return bool
     */
    public function destroyUntisImportLectureship(TblUntisImportLectureship $tblUntisImportLectureship = null)
    {

        if ($tblUntisImportLectureship == null) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return ( new Data($this->getBinding()) )->destroyUntisImportLectureshipByAccount($tblAccount);
            }
        } else {
            return ( new Data($this->getBinding()) )->destroyUntisImportLectureship($tblUntisImportLectureship);
        }
        return false;
    }

    /**
     * @param TblUntisImportLectureship[] $tblUntisImportLectureshipList
     *
     * @return TblDivision[]|bool
     */
    public function getDivisionListByUntisImportLectureship($tblUntisImportLectureshipList)
    {
        $tblDivisionList = array();
        if (!empty($tblUntisImportLectureshipList)) {
            foreach ($tblUntisImportLectureshipList as $tblUntisImportLectureship) {
                $tblDivision = $tblUntisImportLectureship->getServiceTblDivision();
                if ($tblDivision) {
                    if (!array_key_exists($tblDivision->getId(), $tblDivisionList)) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }
        }

        return ( !empty($tblDivisionList) ? $tblDivisionList : false );
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
        return ( !empty($SubjectTeacherList) ? $SubjectTeacherList : false );
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
    public function importUntisLectureship()
    {

        $InfoList = array();
        $tblUntisImportLectureshipList = $this->getUntisImportLectureshipAll(true);
        if ($tblUntisImportLectureshipList) {

            //remove Lectureship (by used import division)
            $tblDivisionList = $this->getDivisionListByUntisImportLectureship($tblUntisImportLectureshipList);
            if ($tblDivisionList) {
                $tblSubjectTeacherList = $this->getSubjectTeacherListByDivisionList($tblDivisionList);
                if ($tblSubjectTeacherList) {
                    $this->removeLectureshipBySubjectTeacherList($tblSubjectTeacherList);
                }
            }

            $createSubjectTeacherList = array();
            $IsTeacherList = array();
            foreach ($tblUntisImportLectureshipList as $Key => $tblUntisImportLectureship) {
                $ImportError = 0;
                if (!( $tblDivision = $tblUntisImportLectureship->getServiceTblDivision() )) {
                    $ImportError++;
                }
                if (!( $tblTeacher = $tblUntisImportLectureship->getServiceTblTeacher() )) {
                    $ImportError++;
                }
                if (!( $tblSubject = $tblUntisImportLectureship->getServiceTblSubject() )) {
                    $ImportError++;
                }
                if ($tblUntisImportLectureship->getIsIgnore()) {
                    $ImportError++;
                }
                $SubjectGroup = $tblUntisImportLectureship->getSubjectGroup();
                // go to next Data if missing critical information / IsIgnore / missing TblPerson
                if ($ImportError >= 1 || !$tblTeacher->getServiceTblPerson()) {
                    continue;
                }
                $tblPerson = $tblTeacher->getServiceTblPerson();

                // get Subject
                $tblDivisionSubject = Division::useService()->getDivisionSubjectBySubjectAndDivisionWithoutGroup($tblSubject, $tblDivision);
                if (!$tblDivisionSubject) {
                    // add Subject
                    $tblDivisionSubject = Division::useService()->addSubjectToDivision($tblDivision, $tblSubject);
                }

                if ($SubjectGroup) {
                    $tblDivisionSubject = false;
                    // get Group
                    $tblSubjectGroup = Division::useService()->getSubjectGroupByNameAndDivisionAndSubject($SubjectGroup, $tblDivision, $tblSubject);
                    if ($tblSubjectGroup) {
                        // get DivisionSubject with Group
                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision, $tblSubject, $tblSubjectGroup);
                    }
                    if (!$tblSubjectGroup) {
                        // create Group + add/get DivisionSubject
                        $tblDivisionSubject = Division::useService()->addSubjectToDivisionWithGroupImport($tblDivision, $tblSubject, $SubjectGroup);
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

            //Delete tblImport
            Import::useService()->destroyUntisImportLectureship();
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
                                    $PanelContent, Panel::PANEL_TYPE_SUCCESS))
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
     * @param $ImportList
     * @param $tblYear
     * @param $tblAccount
     *
     * @return bool
     */
    public function createUntisImportStudentCourseByImportList($ImportList, $tblYear, $tblAccount)
    {
        (new Data($this->getBinding()))->createUntisImportStudentBulk($ImportList, $tblYear, $tblAccount);
        return true;
    }

    /**
     * @return bool
     */
    public function destroyUntisImportStudentAll()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblUntisImportStudentList = Import::useService()->getUntisImportStudentAll(true);
            if ($tblUntisImportStudentList) {
                foreach ($tblUntisImportStudentList as $tblUntisImportStudent) {
                    (new Data($this->getBinding()))->destroyUntisImportStudentCourse($tblUntisImportStudent);
                }
            }
            return (new Data($this->getBinding()))->destroyUntisImportStudentByAccount($tblAccount);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function importUntisStudentCourse()
    {

//        $InfoList = array();
        $tblUntisImportStudentList = $this->getUntisImportStudentAll(true);
        if ($tblUntisImportStudentList) {
            $tblDivisionList = array();
            array_walk($tblUntisImportStudentList,
                function (TblUntisImportStudent $tblUntisImportStudent) use (&$tblDivisionList) {
                    // keine ignorierten Klassen
                    if (!$tblUntisImportStudent->getIsIgnore()) {
                        if (($tblDivision = $tblUntisImportStudent->getServiceTblDivision())
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
            foreach ($tblUntisImportStudentList as $Key => $tblUntisImportStudent) {
                $tblDivision = $tblUntisImportStudent->getServiceTblDivision();

                $tblUntisImportStudentCourseList = Import::useService()
                    ->getUntisImportStudentCourseByUntisImportStudent($tblUntisImportStudent);
                if ($tblUntisImportStudentCourseList && $tblDivision) {
                    foreach ($tblUntisImportStudentCourseList as $tblUntisImportStudentCourse) {
                        $SubjectGroup = $tblUntisImportStudentCourse->getSubjectGroup();
                        $tblSubject = $tblUntisImportStudentCourse->getServiceTblSubject();
                        $tblPerson = $tblUntisImportStudent->getServiceTblPerson();

                        if ($SubjectGroup && $tblSubject && !$tblUntisImportStudent->getIsIgnore()) {

                            // insert Subject in Division if not exist
                            if (!Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject,
                                $tblDivision)
                            ) {
                                Division::useService()->addSubjectToDivision($tblDivision, $tblSubject);
                            }

                            // Anlegen von Gruppen / Schülern nur wenn diese nicht Ignoriert werden soll
                            if (!$tblUntisImportStudentCourse->getisIgnoreCourse()) {
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
                                        $tblUntisImportStudentCourse->getIsIntensiveCourse());
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
                Import::useService()->destroyUntisImportStudentAll();
            }
        }

        if (!empty($createSubjectStudentList)) {
            return true;
        }
        return false;
    }
}

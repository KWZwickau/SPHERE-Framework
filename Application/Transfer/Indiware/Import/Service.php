<?php

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Transfer\Indiware\Import\Service\Data;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportLectureship;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportStudentCourse;
use SPHERE\Application\Transfer\Indiware\Import\Service\Setup;
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
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class Service extends AbstractService
{
    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {
        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
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
     * @return false|TblIndiwareImportStudentCourse
     */
    public function getIndiwareImportStudentCourseById($Id)
    {

        return (new Data($this->getBinding()))->getIndiwareImportStudentCourseById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblIndiwareImportStudentCourse[]
     *
     */
    public function getIndiwareImportStudentCourseByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getIndiwareImportStudentCourseByPerson($tblPerson);
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
     * @param bool $ByAccount
     *
     * @return false|TblIndiwareImportLectureship[]
     */
    public function getIndiwareImportStudentCourseAll($ByAccount = false)
    {
        if ($ByAccount) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return (new Data($this->getBinding()))->getIndiwareImportStudentCourseAllByAccount($tblAccount);
            }
            return false;
        } else {
            return (new Data($this->getBinding()))->getIndiwareImportStudentCourseAll();
        }
    }

    public function getPersonAllByStudentCourseAll()
    {

        $tblPersonList = array();
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $StudentCourseList = (new Data($this->getBinding()))->getIndiwareImportStudentCourseAllByAccount($tblAccount);
        }
        if (isset($StudentCourseList) && $StudentCourseList) {
            foreach ($StudentCourseList as $StudentCourse) {
                $tblPerson = $StudentCourse->getServiceTblPerson();
                if ($tblPerson && !in_array($tblPerson, $tblPersonList)) {
                    $tblPersonList[] = $tblPerson;
                }
            }
        }
        return (!empty($tblPersonList) ? $tblPersonList : false);
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
     *
     * @return bool
     */
    public function createIndiwareImportStudentCourseByImportList($ImportList, TblYear $tblYear, TblAccount $tblAccount)
    {

        (new Data($this->getBinding()))->createIndiwareImportStudentCourseBulk($ImportList, $tblYear, $tblAccount);

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
     * @param TblIndiwareImportLectureship|null $tblIndiwareImportLectureship
     *
     * @return bool
     */
    public function destroyIndiwareImportLectureship(TblIndiwareImportLectureship $tblIndiwareImportLectureship = null)
    {

        if ($tblIndiwareImportLectureship == null) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return (new Data($this->getBinding()))->destroyIndiwareImportLectureshipByAccount($tblAccount);
            }
        } else {
            return (new Data($this->getBinding()))->destroyIndiwareImportLectureship($tblIndiwareImportLectureship);
        }
        return false;
    }

    /**
     * @param TblIndiwareImportStudentCourse|null $tblIndiwareImportStudentCourse
     *
     * @return bool
     */
    public function destroyIndiwareImportStudentCourse(
        TblIndiwareImportStudentCourse $tblIndiwareImportStudentCourse = null
    ) {

        if ($tblIndiwareImportStudentCourse == null) {
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                return (new Data($this->getBinding()))->destroyIndiwareImportStudentCourseByAccount($tblAccount);
            }
        } else {
            return (new Data($this->getBinding()))->destroyIndiwareImportStudentCourse($tblIndiwareImportStudentCourse);
        }
        return false;
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
    public function importIndiwareLectureship()
    {

        $InfoList = array();
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
}

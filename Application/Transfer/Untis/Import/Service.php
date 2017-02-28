<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Transfer\Untis\Import\Service\Data;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
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
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {
        $Protocol = ( new Setup($this->getStructure()) )->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            ( new Data($this->getBinding()) )->setupDatabaseContent();
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
     * @param array   $ImportList
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function createUntisImportLectureShipByImportList($ImportList, TblYear $tblYear)
    {

        $Manager = false;
        if (!empty($ImportList)) {
            foreach ($ImportList as $Result) {
//                Debugger::screenDump($Result);
                // create new import
                $tblDivision = ( isset($Result['DivisionId']) && $Result['DivisionId'] !== null ? Division::useService()->getDivisionById($Result['DivisionId']) : null );
                $tblTeacher = ( isset($Result['TeacherId']) && $Result['TeacherId'] !== null ? Teacher::useService()->getTeacherById($Result['TeacherId']) : null );
                $tblSubject = ( isset($Result['SubjectId']) && $Result['SubjectId'] !== null ? Subject::useService()->getSubjectById($Result['SubjectId']) : null );

                $tblAccount = Account::useService()->getAccountBySession();
                if (!$tblAccount) {
                    $tblAccount = null;
                }

                $Manager = ( new Data($this->getBinding()) )->createUntisImportLectureship(
                    $tblYear,
                    $Result['FileDivision'],
                    $Result['FileTeacher'],
                    $Result['FileSubject'],
                    $Result['FileSubjectGroup'],
                    $tblDivision,
                    $tblTeacher,
                    $tblSubject,
                    $Result['AppSubjectGroup'],
                    $tblAccount);

            }
        }
        if ($Manager) {
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        }

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

    public function importUntisLectureship()
    {

        $InfoList = array();
        $tblUntisImportLectureshipList = $this->getUntisImportLectureshipAll(true);
        if ($tblUntisImportLectureshipList) {
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
                // go to next Data if missing critical information / IsIgnore
                if ($ImportError >= 1 || !$tblTeacher->getServiceTblPerson()) {
                    continue;
                }

                $tblPerson = $tblTeacher->getServiceTblPerson();
                $InfoList[$tblDivision->getId()]['DivisionName'] = $tblDivision->getDisplayName();
                $InfoList[$tblDivision->getId()]['SubjectList'][$tblSubject->getId()][$Key] = $tblSubject->getAcronym().' - '.$tblSubject->getName()
                    .new PullRight($tblPerson->getFullName());
                $InfoList[$tblDivision->getId()]['PanelColor'][$tblSubject->getId()] = Panel::PANEL_TYPE_WARNING;

                // get Subject
                $tblDivisionSubject = Division::useService()->getDivisionSubjectBySubjectAndDivisionWithoutGroup($tblSubject, $tblDivision);
                if (!$tblDivisionSubject) {
                    // add Subject
                    $tblDivisionSubject = Division::useService()->addSubjectToDivision($tblDivision, $tblSubject);
                    $InfoList[$tblDivision->getId()]['SubjectList'][$tblSubject->getId()][$Key] =
                        new SuccessText($tblSubject->getAcronym().' - '.$tblSubject->getName().' (neu)')
                        .new PullRight($tblPerson->getFullName());
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
                    $tblDivisionTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                    $foundTeacher = false;
                    if ($tblDivisionTeacherList) {
                        foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                            $CreateDate = $tblDivisionTeacher->getEntityCreate();
                            //ToDO gescheites Verfahren um Lehraufträge zu entfernen!!! + Anzeige dieser Daten
                            // aktuell werden Lehraufträge gelöscht (im aktuellen Fach/Klasse) die nicht von "Heute" sind.
                            $tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson();
                            if ($tblPersonTeacher) {
                                if ($CreateDate->format('d.m.Y:i') != ( new \DateTime('now') )->format('d.m.Y:i')
                                    && $tblPerson->getId() != $tblPersonTeacher->getId()
                                ) {
                                    Division::useService()->removeSubjectTeacher($tblDivisionTeacher);
                                    $InfoList[$tblDivision->getId()]['SubjectList'][$tblSubject->getId()][$Key.'L'] =
                                        $tblSubject->getAcronym().' - '.$tblSubject->getName()
                                        .new DangerText(new PullRight($tblPersonTeacher->getFullName().' (entfernt)'));
                                } else {
                                    if ($tblDivisionTeacher->getServiceTblPerson()) {
                                        if ($tblDivisionTeacher->getServiceTblPerson()->getId() == $tblPerson->getId()) {
                                            $foundTeacher = true;
                                        }
                                    }
                                }
                            } else {
                                if ($CreateDate->format('d.m.Y:i') != ( new \DateTime('now') )->format('d.m.Y:i')) {
                                    Division::useService()->removeSubjectTeacher($tblDivisionTeacher);
                                } else {
                                    if ($tblDivisionTeacher->getServiceTblPerson()) {
                                        if ($tblDivisionTeacher->getServiceTblPerson()->getId() == $tblPerson->getId()) {
                                            $foundTeacher = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (!$foundTeacher) {
                        // add Subject Teacher
                        Division::useService()->addSubjectTeacher($tblDivisionSubject, $tblPerson);
                        $InfoList[$tblDivision->getId()]['SubjectList'][$tblSubject->getId()][$Key] =
                            $tblSubject->getAcronym().' - '.$tblSubject->getName()
                            .new SuccessText(new PullRight($tblPerson->getFullName().' (neu)'));
                    }
                }
                // entfernen des erfolgreichen Imports
//                Import::useService()->destroyUntisImportLectureship($tblUntisImportLectureship);
            }
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
        return $LayoutColumnArray;
    }
}

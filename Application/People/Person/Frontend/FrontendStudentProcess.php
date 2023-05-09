<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.12.2018
 * Time: 12:11
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class FrontendStudentProcess
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentProcess extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Schulverlauf';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentProcessContent($PersonId = null, $AllowEdit = 1)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblStudent = $tblPerson->getStudent();

            $VisitedDivisions = array();
            $RepeatedLevels = array();

            $tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
            if ($tblDivisionStudentAllByPerson) {
                foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudent) {
                    $TeacherString = ' | ';
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        $tblTeacherPersonList = Division::useService()->getTeacherAllByDivision($tblDivision);
                        if ($tblTeacherPersonList) {
                            foreach ($tblTeacherPersonList as $tblTeacherPerson) {
                                if ($TeacherString !== ' | ') {
                                    $TeacherString .= ', ';
                                }
                                $tblTeacher = Teacher::useService()->getTeacherByPerson($tblTeacherPerson);
                                if ($tblTeacher) {
                                    $TeacherString .= new Bold($tblTeacher->getAcronym().' ');
                                }
                                $TeacherString .= ($tblTeacherPerson->getTitle() != ''
                                        ? $tblTeacherPerson->getTitle().' '
                                        : '').
                                    $tblTeacherPerson->getFirstName().' '.$tblTeacherPerson->getLastName();
                                $tblDivisionTeacher = Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
                                    $tblTeacherPerson);
                                if ($tblDivisionTeacher && $tblDivisionTeacher->getDescription() != '') {
                                    $TeacherString .= ' ('.$tblDivisionTeacher->getDescription().')';
                                }
                            }
                        }
                        if ($TeacherString === ' | ') {
                            $TeacherString = '';
                        }
                        $tblLevel = $tblDivision->getTblLevel();
                        $tblYear = $tblDivision->getServiceTblYear();
                        if ($tblLevel && $tblYear) {
                            $text = $tblYear->getDisplayName().' Klasse '.$tblDivision->getDisplayName()
                                .new Muted(new Small(' '.($tblLevel->getServiceTblType() ? $tblLevel->getServiceTblType()->getName() : '')))
                                .$TeacherString;
                            $VisitedDivisions[] = $tblDivisionStudent->isInActive()
                                ? new Strikethrough($text)
                                : $text;
                            foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudentTemp) {
                                if ($tblDivisionStudent->getId() !== $tblDivisionStudentTemp->getId()
                                    && $tblDivisionStudentTemp->getTblDivision()
                                    && (
                                        $tblDivisionStudentTemp->getTblDivision()->getTblLevel()
                                        && $tblDivisionStudent->getTblDivision()->getTblLevel()->getId()
                                        === $tblDivisionStudentTemp->getTblDivision()->getTblLevel()->getId()
                                    )
                                    && $tblDivisionStudentTemp->getTblDivision()->getTblLevel()->getName() != ''
                                    && !$tblDivisionStudentTemp->getLeaveDateTime()
                                    && !$tblDivisionStudent->getLeaveDateTime()
                                ) {
                                    $RepeatedLevels[] = $tblYear->getDisplayName().' Klasse '.$tblLevel->getName();
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($VisitedDivisions)) {
                $layoutRows = array();
                foreach ($VisitedDivisions as $visitedDivision) {
                    $layoutRows[] = new LayoutRow(new LayoutColumn($visitedDivision));
                }
                $VisitedDivisions = array(new Layout(new LayoutGroup($layoutRows)));
            }
            $VisitedDivisions[] = new Warning(
                'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt.'
            );

            if (!empty($RepeatedLevels)) {
                $layoutRows = array();
                foreach ($RepeatedLevels as $repeatedLevel) {
                    $layoutRows[] = new LayoutRow(new LayoutColumn($repeatedLevel));
                }
                $RepeatedLevels = array(new Layout(new LayoutGroup($layoutRows)));
            }
            $RepeatedLevels[] = new Warning(
                'Vom System erkannte Schuljahr&shy;wiederholungen.'
                .'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt.'
            );

            $processPanel = self::getStudentTransferProcessPanel($tblStudent ? $tblStudent : null);
            $visitedDivisionsPanel = FrontendReadOnly::getSubContent(
                'Besuchte Schulklassen',
                $VisitedDivisions
            );
            $repeatedDivisionsPanel = FrontendReadOnly::getSubContent(
                'Aktuelle Schuljahrwiederholungen',
                $RepeatedLevels
            );

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($processPanel, 4),
                    new LayoutColumn($visitedDivisionsPanel, 4),
                    new LayoutColumn($repeatedDivisionsPanel, 4)
                ))
            )));

            $editLink = '';
            if($AllowEdit == 1){
                $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentProcessContent($PersonId));
            }
            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                new History()
            );
        }

        return '';
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferProcessPanel(TblStudent $tblStudent = null)
    {
        $processCompany = '';
        $processCourse = '';
        $processRemark = '';

        if ($tblStudent) {
            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeProcess
            );

            if ($tblStudentTransferProcess) {
                $processCompany = ($tblCompany = $tblStudentTransferProcess->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $processCourse = ($tblCourse = $tblStudentTransferProcess->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $processRemark = $tblStudentTransferProcess->getRemark();
            }
        }

//        $contentProcess[] =  new Layout(new LayoutGroup(array(
//            new LayoutRow(array(
//                self::getLayoutColumnLabel('Aktuelle Schule'),
//                self::getLayoutColumnValue($processCompany),
//                self::getLayoutColumnLabel('Aktueller Bildungsgang'),
//                self::getLayoutColumnValue($processCourse),
//                self::getLayoutColumnLabel('Bemerkungen'),
//                self::getLayoutColumnValue($processRemark),
//            )),
//        )));
        $contentProcess[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Aktuelle Schule', 6),
                self::getLayoutColumnValue($processCompany, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Aktueller Bildungsgang', 6),
                self::getLayoutColumnValue($processCourse, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Bemerkungen', 6),
                self::getLayoutColumnValue($processRemark, 6),
            )),
        )));

        $processPanel = FrontendReadOnly::getSubContent(
            'Schulverlauf',
            $contentProcess
        );

        return $processPanel;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentProcessContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {

                $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
                $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
                    $tblStudent, $TransferTypeProcess
                );
                if ($tblStudentTransferProcess) {
                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['School'] = (
                    $tblStudentTransferProcess->getServiceTblCompany()
                        ? $tblStudentTransferProcess->getServiceTblCompany()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Type'] = (
                    $tblStudentTransferProcess->getServiceTblType()
                        ? $tblStudentTransferProcess->getServiceTblType()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Course'] = (
                    $tblStudentTransferProcess->getServiceTblCourse()
                        ? $tblStudentTransferProcess->getServiceTblCourse()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Remark'] = $tblStudentTransferProcess->getRemark();
                }

                $Global->savePost();
            }
        }

        return $this->getEditStudentProcessTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentProcessForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentProcessTitle(TblPerson $tblPerson = null)
    {
        return new Title(new History() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentProcessForm(TblPerson $tblPerson = null)
    {

        FrontendStudentBasic::setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        $tblCompanyAllOwn = array();

        $tblSchoolCourseAll = Course::useService()->getCourseAll();
        if ($tblSchoolCourseAll) {
            array_push($tblSchoolCourseAll, new TblCourse());
        } else {
            $tblSchoolCourseAll = array(new TblCourse());
        }

        $tblStudentTransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');

        // Normaler Inhalt
        $tblSchoolList = School::useService()->getSchoolAll();
        if ($tblSchoolList) {
            foreach ($tblSchoolList as $tblSchool) {
                if ($tblSchool->getServiceTblCompany()) {
                    $tblCompanyAllOwn[] = $tblSchool->getServiceTblCompany();
                }
            }
        }
        if (empty($tblCompanyAllOwn)) {
            $useCompanyAllSchoolProcess = $tblCompanyAllSchool;
        } else {
            $useCompanyAllSchoolProcess = $tblCompanyAllOwn;
        }


        // add selected Company if missing in list
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {

            // Erweiterung der SelectBox wenn Daten vorhanden aber nicht enthalten sind
            // Process
            $tblStudentTransferTypeProcessEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeProcess);
            if ($tblStudentTransferTypeProcessEntity && ($TransferCompanyProcess = $tblStudentTransferTypeProcessEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyProcess->getId(), $useCompanyAllSchoolProcess)) {
                    $TransferCompanyProcessList = array($TransferCompanyProcess->getId() => $TransferCompanyProcess);
                    $useCompanyAllSchoolProcess = array_merge($useCompanyAllSchoolProcess, $TransferCompanyProcessList);
                }
            }
        }

        $NodeProcess = 'Schülertransfer - Aktueller Schulverlauf';

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Schulverlauf', array(
                            ApiMassReplace::receiverField((
                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][School]',
                                'Aktuelle Schule', array(
                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolProcess
                                ))
                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                            .ApiMassReplace::receiverModal($Field, $NodeProcess)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_CURRENT_SCHOOL,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeProcess,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeProcess)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Course]',
                                'Aktueller Bildungsgang', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeProcess)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_CURRENT_COURSE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeProcess,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeProcess)
                            )),
                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Remark]',
                                'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentProcessContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentProcessContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}
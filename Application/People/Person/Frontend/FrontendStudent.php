<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.12.2018
 * Time: 15:57
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Student\MassReplaceStudent;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendTeacher
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudent extends FrontendReadOnly
{
    const TITLE = 'Schülerakte';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentTitle($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            $showLink = (new Link(new EyeOpen() . ' Anzeigen', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStudentContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $showLink,
                array(),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $hasApiRight = Access::useService()->hasAuthorization('/Api/Document/Standard/StudentCard/Create');
            if ($hasApiRight && $tblPerson != null) {
                $listingContent[] = new External(
                        'Herunterladen der Schülerkartei', 'SPHERE\Application\Api\Document\Standard\StudentCard\Create',
                        new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei herunterladen')
                    .new External(
                        'Erstellen der Schulbescheinigung', '\Document\Standard\EnrollmentDocument\Fill',
                        new Download(), array('PersonId' => $tblPerson->getId()),
                        'Erstellen und Herunterladen einer Schulbescheinigung');
            }

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                ApiPersonReadOnly::pipelineLoadStudentBasicContent($PersonId),
                'StudentBasicContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                ApiPersonReadOnly::pipelineLoadStudentTransferContent($PersonId),
                'StudentTransferContent'
            );

            $content = new Listing($listingContent);

            $hideLink = (new Link(new EyeMinus() . ' Ausblenden', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStudentTitle($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($hideLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
                , true
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentBasicContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                $identifier = $tblStudent->getIdentifierComplete();
                $schoolAttendanceStartDate = $tblStudent->getSchoolAttendanceStartDate();
                $hasMigrationBackground = $tblStudent->getHasMigrationBackground() ? 'Ja' : 'Nein';
                $isInPreparationDivisionForMigrants = $tblStudent->isInPreparationDivisionForMigrants() ? 'Ja' : 'Nein';
            } else {
                $identifier = '';
                $schoolAttendanceStartDate = '';
                $hasMigrationBackground = '';
                $isInPreparationDivisionForMigrants = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Identifikation'),
                    self::getLayoutColumnValue($identifier),
                    self::getLayoutColumnLabel('Schulpflichtbeginn'),
                    self::getLayoutColumnValue($schoolAttendanceStartDate),
                    self::getLayoutColumnLabel('Migrationshintergrund'),
                    self::getLayoutColumnValue($hasMigrationBackground)
                )),
                new LayoutRow(array(
                    self::getLayoutColumnEmpty(8),
                    self::getLayoutColumnLabel('Vorbereitungsklasse'),
                    self::getLayoutColumnValue($isInPreparationDivisionForMigrants)
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentBasicContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE . ' - Grunddaten',
                $content,
                array($editLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new TileSmall()
            );
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Year
     * @param $Division
     */
    private function setYearAndDivisionForMassReplace(TblPerson $tblPerson, &$Year, &$Division)
    {
        $Year[ViewYear::TBL_YEAR_ID] = '';
        $Division[ViewDivisionStudent::TBL_LEVEL_ID] = '';
        $Division[ViewDivisionStudent::TBL_DIVISION_NAME] = '';
        $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE] = '';
        // #SSW-1598 Fehlerbehebung Massen-Änderung

        // get information without tblStudent information
        $tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
        if ($tblPerson && $tblDivision) {
            $Division[ViewDivisionStudent::TBL_DIVISION_NAME] = $tblDivision->getName();
            if (($tblLevel = $tblDivision->getTblLevel())) {
                $Division[ViewDivisionStudent::TBL_LEVEL_ID] = $tblLevel->getId();
            }
            if (($tblType = $tblLevel->getServiceTblType())) {
                $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE] = $tblType->getId();
            }
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                $Year[ViewYear::TBL_YEAR_ID] = $tblYear->getId();
            }
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentBasicContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if ($tblStudent = Student::useService()->getStudentByPerson($tblPerson)) {

                $Global->POST['Meta']['Student']['Prefix'] = $tblStudent->getPrefix();
                $Global->POST['Meta']['Student']['Identifier'] = $tblStudent->getIdentifier();
                $Global->POST['Meta']['Student']['SchoolAttendanceStartDate'] = $tblStudent->getSchoolAttendanceStartDate();

                $Global->POST['Meta']['Student']['HasMigrationBackground'] = $tblStudent->getHasMigrationBackground();
                $Global->POST['Meta']['Student']['IsInPreparationDivisionForMigrants'] = $tblStudent->isInPreparationDivisionForMigrants();

                $Global->savePost();
            }
        }

        return $this->getEditStudentBasicTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentBasicForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentBasicTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Tag() . ' ' . self::TITLE . ' - Grunddaten', 'der Person'
                . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '') . ' bearbeiten')
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentBasicForm(TblPerson $tblPerson = null)
    {

        $this->setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        $isIdentifierAuto = false;
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
        if($tblSetting && $tblSetting->getValue()){
            $isIdentifierAuto = true;
        }

        $NodePrefix = 'Grunddaten - Prefix der Schülernummer';
        $StartDatePrefix = 'Grunddaten - Schulpflicht';

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Identifikation', array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ApiMassReplace::receiverField((
                                            $Field = new TextField('Meta[Student][Prefix]',
                                                'Prefix', 'Prefix')
                                            ))
                                            .ApiMassReplace::receiverModal($Field, $NodePrefix)

                                            .new PullRight((new Link('Massen-Änderung',
                                                ApiMassReplace::getEndpoint(), null, array(
                                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceStudent::CLASS_MASS_REPLACE_STUDENT,
                                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceStudent::METHOD_REPLACE_PREFIX,
                                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                                    'Id'                                                            => $tblPerson->getId(),
                                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                                    'Node'                                                          => $NodePrefix,
                                                )))->ajaxPipelineOnClick(
                                                ApiMassReplace::pipelineOpen($Field, $NodePrefix)
                                            ))
                                            , 4)
                                    ,
                                        new LayoutColumn(
                                            ($isIdentifierAuto
                                                ?
                                                (new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                                    'Schülernummer'))->setDisabled()
                                                    ->ajaxPipelineOnKeyUp(ApiStudent::pipelineCompareIdentifier($tblPerson->getId()))
                                                :
                                                (new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                                    'Schülernummer'))
                                                    ->ajaxPipelineOnKeyUp(ApiStudent::pipelineCompareIdentifier($tblPerson->getId()))
                                            )
                                            , 8)
                                    ))
                                )
                            )
                        ,
                            ($isIdentifierAuto
                                ? ''
                                : ApiStudent::receiverControlIdentifier()
                            )

                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Schulpflicht', array(
                            ApiMassReplace::receiverField((
                            $Field = new DatePicker('Meta[Student][SchoolAttendanceStartDate]', '',
                                'Beginnt am', new Calendar())
                            ))
                            .ApiMassReplace::receiverModal($Field, $StartDatePrefix)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceStudent::CLASS_MASS_REPLACE_STUDENT,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceStudent::METHOD_REPLACE_START_DATE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                            => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodePrefix,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodePrefix)
                            ))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Migration', array(
                            new CheckBox(
                                'Meta[Student][HasMigrationBackground]',
                                'Migrationshintergrund',
                                1
                            ),
                            new CheckBox(
                                'Meta[Student][IsInPreparationDivisionForMigrants]',
                                'Besucht Vorbereitungsklasse für Migranten',
                                1
                            )
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentBasicContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentBasicContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentTransferContent($PersonId = null)
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
                                ) {
                                    $RepeatedLevels[] = $tblYear->getDisplayName().' Klasse '.$tblLevel->getName();
                                }
                            }
                        }
                    }
                }
            }

            $enrollmentPanel = self::getStudentTransferEnrollmentPanel($tblStudent ? $tblStudent : null);
            $arrivePanel = self::getStudentTransferArrivePanel($tblStudent ? $tblStudent : null);
            $leavePanel = self::getStudentTransferLeavePanel($tblStudent ? $tblStudent : null);
            $processPanel = self::getStudentTransferProcessPanel($tblStudent ? $tblStudent : null);
            $visitedDivisionsPanel = new Panel('Besuchte Schulklassen',
                $VisitedDivisions,
                Panel::PANEL_TYPE_DEFAULT,
                new Warning(
                    'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
                )
            );
            $repeatedDivisionsPanel = new Panel('Aktuelle Schuljahrwiederholungen',
                $RepeatedLevels,
                Panel::PANEL_TYPE_DEFAULT,
                new Warning(
                    'Vom System erkannte Schuljahr&shy;wiederholungen.'
                    .'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
                )
            );

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($enrollmentPanel)
                )),
                new LayoutRow(array(
                    new LayoutColumn($arrivePanel)
                )),
                new LayoutRow(array(
                    new LayoutColumn($leavePanel)
                )),
                new LayoutRow(array(
                    new LayoutColumn($processPanel)
                )),
                new LayoutRow(array(
                    new LayoutColumn($visitedDivisionsPanel, 6),
                    new LayoutColumn($repeatedDivisionsPanel, 6)
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
               ;// ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentTransferContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE . ' - Schülertransfer',
                $content,
                array($editLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new TileSmall()
            );
        }

        return '';
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferEnrollmentPanel(TblStudent $tblStudent = null)
    {
        $enrollmentCompany = '';
        $enrollmentType = '';
        $enrollmentTransferType = '';
        $enrollmentCourse = '';
        $enrollmentDate = '';
        $enrollmentRemark = '';

        if ($tblStudent) {
            $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
            $tblStudentTransferEnrollment = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeEnrollment
            );

            if ($tblStudentTransferEnrollment) {
                $enrollmentCompany = ($tblCompany = $tblStudentTransferEnrollment->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $enrollmentType = ($tblType = $tblStudentTransferEnrollment->getServiceTblType())
                    ? $tblType->getName() : '';
                $enrollmentTransferType = ($tblStudentSchoolEnrollmentType = $tblStudentTransferEnrollment->getTblStudentSchoolEnrollmentType())
                    ? $tblStudentSchoolEnrollmentType->getName() : '';
                $enrollmentCourse = ($tblCourse = $tblStudentTransferEnrollment->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $enrollmentDate = $tblStudentTransferEnrollment->getTransferDate();
                $enrollmentRemark = $tblStudentTransferEnrollment->getRemark();
            }
        }

        $contentEnrollment[] =  '&nbsp;';
        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Schule'),
                self::getLayoutColumnValue($enrollmentCompany),
                self::getLayoutColumnLabel('Schulart'),
                self::getLayoutColumnValue($enrollmentType),
                self::getLayoutColumnLabel('Einschulungsart'),
                self::getLayoutColumnValue($enrollmentTransferType),
            )),
        )));
        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Bildungsgang'),
                self::getLayoutColumnValue($enrollmentCourse),
                self::getLayoutColumnLabel('Datum'),
                self::getLayoutColumnValue($enrollmentDate),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($enrollmentRemark),
            )),
        )));

        $enrollmentPanel = new Panel(
            'Ersteinschulung',
            $contentEnrollment,
            Panel::PANEL_TYPE_INFO
        );

        return $enrollmentPanel;
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferArrivePanel(TblStudent $tblStudent = null)
    {
        $arriveCompany = '';
        $arriveType = '';
        $arriveCourse = '';
        $arriveDate = '';
        $arriveRemark = '';

        if ($tblStudent) {
            $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
            $tblStudentTransferArrive = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeArrive
            );

            if ($tblStudentTransferArrive) {
                $arriveCompany = ($tblCompany = $tblStudentTransferArrive->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $arriveType = ($tblType = $tblStudentTransferArrive->getServiceTblType())
                    ? $tblType->getName() : '';
                $arriveCourse = ($tblCourse = $tblStudentTransferArrive->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $arriveDate = $tblStudentTransferArrive->getTransferDate();
                $arriveRemark = $tblStudentTransferArrive->getRemark();
            }
        }

        $contentArrive[] =  '&nbsp;';
        $contentArrive[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Abgebende Schule / Kita'),
                self::getLayoutColumnValue($arriveCompany),
                self::getLayoutColumnLabel('Letzte Schulart'),
                self::getLayoutColumnValue($arriveType, 6),
            )),
        )));
        $contentArrive[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Letzter Bildungsgang'),
                self::getLayoutColumnValue($arriveCourse),
                self::getLayoutColumnLabel('Datum'),
                self::getLayoutColumnValue($arriveDate),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($arriveRemark),
            )),
        )));

        $arrivePanel = new Panel(
            'Schüler - Aufnahme',
            $contentArrive,
            Panel::PANEL_TYPE_INFO
        );

        return $arrivePanel;
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferLeavePanel(TblStudent $tblStudent = null)
    {
        $leaveCompany = '';
        $leaveType = '';
        $leaveCourse = '';
        $leaveDate = '';
        $leaveRemark = '';

        if ($tblStudent) {
            $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
            $tblStudentTransferLeave = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeLeave
            );

            if ($tblStudentTransferLeave) {
                $leaveCompany = ($tblCompany = $tblStudentTransferLeave->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $leaveType = ($tblType = $tblStudentTransferLeave->getServiceTblType())
                    ? $tblType->getName() : '';
                $leaveCourse = ($tblCourse = $tblStudentTransferLeave->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $leaveDate = $tblStudentTransferLeave->getTransferDate();
                $leaveRemark = $tblStudentTransferLeave->getRemark();
            }
        }

        $contentLeave[] =  '&nbsp;';
        $contentLeave[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Aufnehmende Schule'),
                self::getLayoutColumnValue($leaveCompany),
                self::getLayoutColumnLabel('Letzte Schulart'),
                self::getLayoutColumnValue($leaveType, 6),
            )),
        )));
        $contentLeave[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Letzter Bildungsgang'),
                self::getLayoutColumnValue($leaveCourse),
                self::getLayoutColumnLabel('Datum'),
                self::getLayoutColumnValue($leaveDate),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($leaveRemark),
            )),
        )));

        $leavePanel = new Panel(
            'Schüler - Abgabe',
            $contentLeave,
            Panel::PANEL_TYPE_INFO
        );

        return $leavePanel;
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferProcessPanel(TblStudent $tblStudent = null)
    {
        $processCompany = '';
        $processType = '';
        $processRemark = '';

        if ($tblStudent) {
            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeProcess
            );

            if ($tblStudentTransferProcess) {
                $processCompany = ($tblCompany = $tblStudentTransferProcess->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $processType = ($tblType = $tblStudentTransferProcess->getServiceTblType())
                    ? $tblType->getName() : '';
                $processRemark = $tblStudentTransferProcess->getRemark();
            }
        }

        $contentProcess[] =  '&nbsp;';
        $contentProcess[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Aktuelle Schule'),
                self::getLayoutColumnValue($processCompany),
                self::getLayoutColumnLabel('Aktueller Bildungsgang'),
                self::getLayoutColumnValue($processType),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($processRemark),
            )),
        )));

        $processPanel = new Panel(
            'Schulverlauf',
            $contentProcess,
            Panel::PANEL_TYPE_INFO
        );

        return $processPanel;
    }
}
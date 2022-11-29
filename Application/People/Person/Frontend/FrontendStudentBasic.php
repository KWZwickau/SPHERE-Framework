<?php
namespace SPHERE\Application\People\Person\Frontend;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Student\MassReplaceStudent;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
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
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendTeacher
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentBasic extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Grunddaten';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentBasicContent($PersonId = null, $AllowEdit = 1)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                $identifier = $tblStudent->getIdentifierComplete();
                $schoolAttendanceStartDate = $tblStudent->getSchoolAttendanceStartDate();
                $hasMigrationBackground = $tblStudent->getHasMigrationBackground() ? 'Ja' : 'Nein';
            } else {
                $identifier = '';
                $schoolAttendanceStartDate = '';
                $hasMigrationBackground = '';
            }

            if (($tblCommon = $tblPerson->getCommon())
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($birthday = $tblCommonBirthDates->getBirthday())
            ) {
                $birthday = new DateTime($birthday);
                $schoolAttendanceDate = $birthday->add(new DateInterval("P18Y"));
                $now = new DateTime('now');
                $hasSchoolAttendance = $now > $schoolAttendanceDate ? 'Ja' : 'Nein';
            } else {
                $hasSchoolAttendance = '';
            }
            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Schülernummer'),
                    self::getLayoutColumnValue($identifier),
                    new LayoutColumn(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                self::getLayoutColumnLabel('Schulpflichtbeginn', 6),
                                self::getLayoutColumnValue($schoolAttendanceStartDate, 6),
                            )),
                            new LayoutRow(array(
                                self::getLayoutColumnLabel('Schulpflicht erfüllt', 6),
                                self::getLayoutColumnValue($hasSchoolAttendance, 6),
                            )),
                        )))
                    ,4),
                    self::getLayoutColumnLabel('Herkunftssprache ist nicht oder nicht ausschließlich Deutsch'),
                    self::getLayoutColumnValue($hasMigrationBackground),
                )),
            )));

            $editLink = '';
            if($AllowEdit == 1){
                $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentBasicContent($PersonId));
            }

            return TemplateReadOnly::getContent(
                self::TITLE . ' - Grunddaten',
                self::getSubContent('Grunddaten', $content),
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                new Nameplate()
            );
        }

        return '';
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
        return new Title(new Nameplate() . ' ' . self::TITLE . ' - Grunddaten', self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentBasicForm(TblPerson $tblPerson = null)
    {
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
                        new Panel('Schülernummer', array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ApiMassReplace::receiverField(($Field = new TextField('Meta[Student][Prefix]', 'Prefix', 'Prefix')))
                                            . ApiMassReplace::receiverModal($Field, $NodePrefix)
                                            . new PullRight((new Link('Massen-Änderung',
                                                ApiMassReplace::getEndpoint(), null, array(
                                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceStudent::CLASS_MASS_REPLACE_STUDENT,
                                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceStudent::METHOD_REPLACE_PREFIX,
                                                    'Id'                                                            => $tblPerson->getId(),
                                                )))->ajaxPipelineOnClick(
                                                ApiMassReplace::pipelineOpen($Field, $NodePrefix)
                                            ))
                                            , 4),
                                        new LayoutColumn(
                                            ($isIdentifierAuto
                                                ? (new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                                    'Schülernummer'))->setDisabled()
                                                    ->ajaxPipelineOnKeyUp(ApiStudent::pipelineCompareIdentifier($tblPerson->getId()))
                                                : (new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                                    'Schülernummer'))
                                                    ->ajaxPipelineOnKeyUp(ApiStudent::pipelineCompareIdentifier($tblPerson->getId()))
                                            )
                                            , 8)
                                    ))
                                )
                            ),
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
                                    'Id'                                                      => $tblPerson->getId(),
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $StartDatePrefix)
                            ))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Kamenz-Statistik', array(
                            new CheckBox(
                                'Meta[Student][HasMigrationBackground]',
                                'Herkunftssprache ist nicht oder nicht ausschließlich Deutsch',
                                1
                            ),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentBasicContent($tblPerson->getId())),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentBasicContent($tblPerson->getId()))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}
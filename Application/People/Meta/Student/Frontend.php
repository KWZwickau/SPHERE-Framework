<?php

namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Student\MassReplaceStudent;
use SPHERE\Application\Api\People\Meta\Subject\MassReplaceSubject;
use SPHERE\Application\Api\People\Meta\Support\ApiSupport;
use SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblHandyCap;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecial;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Aspect;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Bus;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Heart;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Medicine;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Stethoscope;
use SPHERE\Common\Frontend\Icon\Repository\StopSign;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\SuperGlobal;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array     $Meta
     * @param null      $Group
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array(), $Group = null)
    {

        $Stage = new Stage();

        $Info = '';
        $hasApiRight = Access::useService()->hasAuthorization('/Api/Document/Standard/StudentCard/Create');
        if ($hasApiRight && $tblPerson != null) {
            $Info = new External(
                'Herunterladen der Schülerkartei', 'SPHERE\Application\Api\Document\Standard\StudentCard\Create',
                    new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei herunterladen')
                .new External(
                    'Erstellen der Schulbescheinigung', '\Document\Standard\EnrollmentDocument\Fill',
                    new Download(), array('PersonId' => $tblPerson->getId()),
                    'Erstellen und Herunterladen einer Schulbescheinigung');
        }

        $this->setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        $isIdentifierAuto = false;
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
        if($tblSetting && $tblSetting->getValue()){
            $isIdentifierAuto = true;
        }

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            /** @var TblStudent $tblStudent */
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {

                $Global->POST['Meta']['Student']['Prefix'] = $tblStudent->getPrefix();
                $Global->POST['Meta']['Student']['Identifier'] = $tblStudent->getIdentifier();
                $Global->POST['Meta']['Student']['SchoolAttendanceStartDate'] = $tblStudent->getSchoolAttendanceStartDate();

                $Global->POST['Meta']['Student']['HasMigrationBackground'] = $tblStudent->getHasMigrationBackground();
                $Global->POST['Meta']['Student']['IsInPreparationDivisionForMigrants'] = $tblStudent->isInPreparationDivisionForMigrants();
            }
            $Global->savePost();
        }

        $NodePrefix = 'Grunddaten - Prefix der Schülernummer';
        $StartDatePrefix = 'Grunddaten - Schulpflicht';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Danger(
                                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
                            ),
                        ), 8),
                        new LayoutColumn(array(
                            new PullRight($Info),
                        ), 4)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Student::useService()->createMeta(
                                (new Form(array(
                                    new FormGroup(
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
                                        )), new Title(new TileSmall().' Grunddaten ',
                                            new Bold(new Success($tblPerson->getFullName())))
                                    ),
                                    $this->formGroupTransfer($tblPerson, $Year, $Division),
                                    $this->formGroupGeneral($tblPerson),
                                    $this->formGroupSubject($tblPerson, $Year, $Division),
//                                    $this->formGroupIntegration($tblPerson),
                                ), (new Primary('Speichern', new Save()))->disableOnLoad())
                                )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.')
                                , $tblPerson, $Meta, $Group
                            )
                        )
                    )
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Stage
     */
    public function frontendIntegration(TblPerson $tblPerson = null)
    {

        $Stage = new Stage();

        $SupportContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'SupportTable');
        $SpecialContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'SpecialTable');
        $HandyCapContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'HandyCapTable');

        $Accordion = new Accordion('');
        $Accordion->addItem('Förderantrag/Förderbescheid '.ApiSupport::receiverInline('', 'SupportCount'), $SupportContent, true);
        $Accordion->addItem('Entwicklungsbesonderheiten '.ApiSupport::receiverInline('', 'SpecialCount'), $SpecialContent, false);
        $Accordion->addItem('Nachteilsaugleich '.ApiSupport::receiverInline('', 'HandyCapCount'), $HandyCapContent, false);

        $Stage->setContent(
            ApiSupport::pipelineLoadTable($tblPerson->getId())
            .new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Für Lehrer sind nur die aktuellsten Einträge sichtbar')
                        , 6)
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                                ApiSupport::receiverModal(),
                                (new Standard('Förderantrag/Förderbescheid hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSupportModal($tblPerson->getId())),
                                (new Standard('Entwicklungsbesonderheiten hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSpecialModal($tblPerson->getId())),
                                (new Standard('Nachteilsausgleich hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateHandyCapModal($tblPerson->getId())),
                                new Ruler()
                            )
                        )
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            $Accordion,
                        ))
                    )
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param int      $PersonId
     * @param null|int $SupportId
     *
     * @return Form
     */
    public function formSupport($PersonId, $SupportId = null)
    {

        $Global = $this->getGlobal();
        if($SupportId != null && !isset($Global->POST['Data']['Date'])){
            if(($tblSupport = Student::useService()->getSupportById($SupportId))){
                $Global = $this->fillGlobalSupport($tblSupport, $Global);
                $Global->savePost();
            }
        } elseif(!isset($Global->POST['Data']['Date']) && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // fill Field with newest Input (only for new Entity's)
            if(($tblSupport = Student::useService()->getSupportByPersonNewest($tblPerson))){
                $IsEdit = false;
                $Global = $this->fillGlobalSupport($tblSupport, $Global, $IsEdit);
                $Global->savePost();
            }
        }
        $tblSupportFocusList = Student::useService()->getSupportFocusTypeAll();
        $SupportTypeList = Student::useService()->getSupportTypeAll();

        $tblSupportFocusList = $this->getSorter($tblSupportFocusList)->sortObjectBy('Name');
        $CheckboxList = array();
        /** @var TblSupportFocusType $tblSupportFocus */
        foreach($tblSupportFocusList as $tblSupportFocus){
            $CheckboxList[] = new CheckBox('Data[CheckboxList]['.$tblSupportFocus->getName().']', $tblSupportFocus->getName(), $tblSupportFocus->getId());
        }

        if($SupportId === null){
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineCreateSupportSave($PersonId));
        } else {
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineUpdateSupportSave($PersonId, $SupportId));
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                        , 6),
                    new FormColumn(
                        new SelectBox('Data[PrimaryFocus]', 'Primär geförderter Schwerpunkt', array('{{ Name }}' => $tblSupportFocusList))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new SelectBox('Data[SupportType]', 'Vorgang', array('{{ Name }}' => $SupportTypeList), new Education()))->setRequired(),
                        new Warning('Nur "Förderbescheid" ist für Lehrer sichtbar')
                        ), 6),
                    new FormColumn(array(
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Förderschwerpunkte'))))),
                            new Listing($CheckboxList)
                        ), 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('Data[Company]', 'Förderschule', 'Förderschule', new Education()),
                        new Ruler(),
                        new TextField('Data[PersonSupport]', 'Schulbegleitung', 'Schulbegleitung', new PersonIcon()),
                        new Ruler(),
                        new TextField('Data[SupportTime]', 'Stundenbedarf pro Woche', 'Stundenbedarf pro Woche', new Clock()),
                    ), 6),
                    new FormColumn(
                        (new TextArea('Data[Remark]', 'Bemerkung', 'Bemerkung', new Edit()))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        $SaveButton
                    ))
                )),
            ))
        );
    }

    /**
     * @param TblSupport  $tblSupport
     * @param SuperGlobal $Global
     * @param bool        $IsEdit
     *
     * @return SuperGlobal
     */
    private function fillGlobalSupport(TblSupport $tblSupport, SuperGlobal $Global, $IsEdit = true)
    {
        // fill only update Form's
        if($IsEdit){
            $Global->POST['Data']['Date'] = $tblSupport->getDate();
            if(($tblSupportType = $tblSupport->getTblSupportType())){
                $Global->POST['Data']['SupportType'] = $tblSupportType->getId();
            }
            $Global->POST['Data']['Remark'] = $tblSupport->getRemark(false);
        }

        if(($tblSupportFocusPrimary = Student::useService()->getPrimaryFocusBySupport($tblSupport))){
            $Global->POST['Data']['PrimaryFocus'] = $tblSupportFocusPrimary->getId();
        }
        if(($tblSupportFocusTypeList = Student::useService()->getFocusListBySupport($tblSupport))){
            foreach($tblSupportFocusTypeList as $tblSupportFocusType){
                $Global->POST['Data']['CheckboxList'][$tblSupportFocusType->getName()] = $tblSupportFocusType->getId();
            }
        }


        $Global->POST['Data']['Company'] = $tblSupport->getCompany();
        $Global->POST['Data']['PersonSupport'] = $tblSupport->getPersonSupport();
        $Global->POST['Data']['SupportTime'] = $tblSupport->getSupportTime();
        return $Global;
    }

    /**
     * @param int $PersonId
     * @param null|int $SpecialId
     * @param bool $IsCanceled
     * @param bool $IsInit
     *
     * @return Form
     */
    public function formSpecial($PersonId, $SpecialId = null, $IsCanceled = false, $IsInit = false)
    {

        $Global = $this->getGlobal();
        if($SpecialId != null && !isset($Global->POST['Data']['Date'])) {
            if (($tblSpecial = Student::useService()->getSpecialById($SpecialId))) {
                if ($IsInit) {
                    if ($tblSpecial->isCanceled()) {
                       $Global->POST['Data']['IsCanceled'] = $tblSpecial->isCanceled() ? '1' : '0';
                    }
                }

                $Global = $this->fillGlobalSpecial($tblSpecial, $Global);

                $Global->savePost();
            } elseif (!isset($Global->POST['Data']['Date']) && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
                // fill Field with newest Input (only for new Entity's)
                if(($tblSpecial = Student::useService()->getSpecialByPersonNewest($tblPerson))) {
                    $IsEdit = false;
                    $Global = $this->fillGlobalSpecial($tblSpecial, $Global, $IsEdit);
                    $Global->savePost();
                }
            }
        }

        $tblSpecialDisorderList = Student::useService()->getSpecialDisorderTypeAll();
        $tblSpecialDisorderList = $this->getSorter($tblSpecialDisorderList)->sortObjectBy('Name');
        $CheckboxList = array();
        /** @var TblSpecialDisorderType $tblSpecialDisorder*/
        foreach($tblSpecialDisorderList as $tblSpecialDisorder){
            $CheckboxList[] = new CheckBox('Data[CheckboxList]['.$tblSpecialDisorder->getName().']', $tblSpecialDisorder->getName(), $tblSpecialDisorder->getId());
        }

        if($SpecialId === null){
            // create
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineCreateSpecialSave($PersonId));
            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSpecialModal($PersonId));
        } else {
            // edit
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineUpdateSpecialSave($PersonId, $SpecialId));
            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditSpecialModal($PersonId, $SpecialId));
        }

        $arrayRight = array();

        $arrayLeft[] = (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired();
        $arrayLeft[] = $cancelCheckbox;
        if (!$IsCanceled) {
            $arrayLeft[] = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Entwicklungsbesonderheiten '.new DangerText('*'))))));
            $arrayLeft[] = new Listing($CheckboxList);

            $arrayRight[] = new TextArea('Data[Remark]', 'Bemerkung', 'Bemerkung', new Edit());
        }

        $arrayRight[] = new HiddenField('SpecialId');

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn($arrayLeft, 6),
                    new FormColumn($arrayRight, 6)
                )),
                new FormRow(array(
                    new FormColumn(
                        $SaveButton
                    )
                )),
            ))
        );
    }

    /**
     * @param TblSpecial  $tblSpecial
     * @param SuperGlobal $Global
     * @param bool        $IsEdit
     *
     * @return SuperGlobal
     */
    private function fillGlobalSpecial(TblSpecial $tblSpecial, SuperGlobal $Global, $IsEdit = true)
    {
        // fill only update Form's
        if($IsEdit){
            $Global->POST['Data']['Date'] = $tblSpecial->getDate();
            $Global->POST['Data']['Remark'] = $tblSpecial->getRemark(false);
        }

        if (($tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial))) {
            foreach ($tblSpecialDisorderTypeList as $tblSpecialDisorderType) {
                $Global->POST['Data']['CheckboxList'][$tblSpecialDisorderType->getName()] = $tblSpecialDisorderType->getId();
            }
        }

        return $Global;
    }

    /**
     * @param int      $PersonId
     * @param null|int $HandyCapId
     * @param bool $IsCanceled
     * @param bool $IsInit
     *
     * @return Form
     */
    public function formHandyCap($PersonId, $HandyCapId = null, $IsCanceled = false, $IsInit = false)
    {

        $Global = $this->getGlobal();
        if($HandyCapId != null && !isset($Global->POST['Data']['Date'])){
            if(($tblHandyCap = Student::useService()->getHandyCapById($HandyCapId))){
                if ($IsInit) {
                    if ($tblHandyCap->isCanceled()) {
                        $Global->POST['Data']['IsCanceled'] = $tblHandyCap->isCanceled() ? '1' : '0';
                    }
                }

                $Global->POST['Data']['Date'] = $tblHandyCap->getDate();
                $Global->POST['Data']['LegalBasis'] = $tblHandyCap->getLegalBasis();
                $Global->POST['Data']['LearnTarget'] = $tblHandyCap->getLearnTarget();
                $Global->POST['Data']['RemarkLesson'] = $tblHandyCap->getRemarkLesson(false);
                $Global->POST['Data']['RemarkRating'] = $tblHandyCap->getRemarkRating(false);
                $Global->savePost();
            }
        }// don't need pre fill for create new Entity's

        if($HandyCapId === null){
            // create
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineCreateHandyCapSave($PersonId));

            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateHandyCapModal($PersonId));
        } else {
            // edit
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineUpdateHandyCapSave($PersonId, $HandyCapId));

            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditHandyCapModal($PersonId, $HandyCapId));
        }

        $array[] = (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired();
        $array[] = $cancelCheckbox;

        if ($IsCanceled) {
            return new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn($array, 6),
                    )),
                    new FormRow(array(
                        new FormColumn(
                            $SaveButton
                        )
                    )),
                ))
            );
        } else {

            return new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn($array, 6),
                    )),
                    new FormRow(new FormColumn(new Ruler())),
                    new FormRow(array(
                        new FormColumn(array(
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Rechtliche Grundlage'))))),
                        ), 6),
                        new FormColumn(array(
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Lernziel'))))),
                        ), 6)
                    )),
                    new FormRow(array(
                        new FormColumn(
                            new RadioBox('Data[LegalBasis]', 'Schulaufsicht', TblHandyCap::LEGAL_BASES_SCHOOL_VIEW)
                            , 3),
                        new FormColumn(
                            new RadioBox('Data[LegalBasis]', 'Schulintern', TblHandyCap::LEGAL_BASES_INTERN)
                            , 3),
                        new FormColumn(
                            new RadioBox('Data[LearnTarget]', 'lernzielgleich', TblHandyCap::LEARN_TARGET_EQUAL)
                            , 3),
                        new FormColumn(
                            new RadioBox('Data[LearnTarget]', 'lernzieldifferenziert',
                                TblHandyCap::LEARN_TARGET_DIFFERENT)
                            , 3)
                    )),
                    new FormRow(new FormColumn(new Ruler())),
                    new FormRow(
                        new FormColumn(
                            new TextArea('Data[RemarkLesson]', 'Bemerkung', 'Besonderheiten im Unterricht', new Edit())
                            , 12)
                    ),
                    new FormRow(new FormColumn(new Ruler())),
                    new FormRow(
                        new FormColumn(
                            new TextArea('Data[RemarkRating]', 'Bemerkung', 'Besonderheiten bei Leistungsbewertungen',
                                new Edit())
                            , 12)
                    ),
                    new FormRow(array(
                        new FormColumn(
                            $SaveButton
                        )
                    )),
                ))
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TableData|Warning
     */
    public function getSupportTable(TblPerson $tblPerson)
    {

        $tblSupportList = Student::useService()->getSupportByPerson($tblPerson);
        $TableContent = array();
        if($tblSupportList){
            array_walk($tblSupportList, function(TblSupport $tblSupport) use (&$TableContent, $tblPerson){
                $Item['RequestDate'] = $tblSupport->getDate();
                $Item['CoachingRequest'] = ($tblSupport->getTblSupportType() ? $tblSupport->getTblSupportType()->getName() : '');
                $Item['Focus'] = '';
                $Item['IntegrationCompany'] = $tblSupport->getCompany();
                $Item['PersonSupport'] = $tblSupport->getPersonSupport();
                $Item['IntegrationTime'] = $tblSupport->getSupportTime();
                $Item['IntegrationRemark'] = $tblSupport->getRemark();
                $Item['Editor'] = $tblSupport->getPersonEditor();
                $Item['Option'] = (new Standard('', '#', new Edit()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditSupportModal($tblPerson->getId(), $tblSupport->getId()))
                    .(new Standard('', '#', new Remove()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineOpenDeleteSupport($tblPerson->getId(), $tblSupport->getId()));

                $FocusList = array();
                $PrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport);
                $tblFocusList = Student::useService()->getFocusListBySupport($tblSupport);
                if($tblFocusList){
                    foreach($tblFocusList as $tblFocus){
                        if($PrimaryFocus && $PrimaryFocus->getId() == $tblFocus->getId()){
                            $FocusList[] = new Bold($tblFocus->getName().' *');
                        } else {
                            $FocusList[] = $tblFocus->getName();
                        }
                    }
                }
                if(!empty($FocusList)) {
                    $Item['Focus'] = new Listing($FocusList);
                }

                array_push($TableContent, $Item);
            });
        }

        if(empty($TableContent)){
            return new Warning('Es sind keine Daten vorhanden.');
        }

        return new TableData($TableContent, null,
            array('RequestDate' => 'Datum',
                  'CoachingRequest' => 'Vorgang',
                  'Focus' => 'Förderschwerpunkte '.new Muted(new Small('Primary *')),
                  'IntegrationCompany' => 'Förderschule',
                  'PersonSupport' => 'Schulbegleitung',
                  'IntegrationTime' => 'Stundenbedarf',
                  'IntegrationRemark' => 'Bemerkung',
                  'Editor' => 'Bearbeiter',
                  'Option' => '',
            ), array(
                'order' => array(
                    array(0, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
//                'searching' => false,
                'responsive' => false
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TableData|Warning
     */
    public function getSpecialTable(TblPerson $tblPerson)
    {

        $tblSpecialList = Student::useService()->getSpecialByPerson($tblPerson);
        $TableContent = array();
        if($tblSpecialList){
            array_walk($tblSpecialList, function(TblSpecial $tblSpecial) use (&$TableContent, $tblPerson){
                $Item['RequestDate'] = $tblSpecial->getDate();
                $Item['Disorder'] = '';
                $Item['Remark'] = $tblSpecial->getRemark();
                $Item['Editor'] = $tblSpecial->getPersonEditor();
                $Item['Option'] = (new Standard('', '#', new Edit()))
                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditSpecialModal($tblPerson->getId(), $tblSpecial->getId(), true))
                    .(new Standard('', '#', new Remove()))
                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenDeleteSpecial($tblPerson->getId(), $tblSpecial->getId()));

                $DisorderList = array();
                $tblStudentDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial);
                if($tblStudentDisorderTypeList){
                    foreach($tblStudentDisorderTypeList as $tblStudentDisorderType){
                        $DisorderList[] = $tblStudentDisorderType->getName();
                    }
                }
                if(!empty($DisorderList)) {
                    $Item['Disorder'] = new Listing($DisorderList);
                }

                array_push($TableContent, $Item);
            });
        }

        if(empty($TableContent)){
            return new Warning('Es sind keine Daten vorhanden.');
        }

        return new TableData($TableContent, null,
            array('RequestDate' => 'Datum',
                  'Disorder' => 'Entwicklungsbesonderheiten',
                  'Remark' => 'Bemerkung',
                  'Editor' => 'Bearbeiter',
                  'Option' => '',
            ), array(
                'order' => array(
                    array(0, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
//                'searching' => false,
                'responsive' => false
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TableData|Warning
     */
    public function getHandyCapTable(TblPerson $tblPerson)
    {

        $tblSpecialList = Student::useService()->getHandyCapByPerson($tblPerson);
        $TableContent = array();
        if($tblSpecialList){
            array_walk($tblSpecialList, function(TblHandyCap $tblHandyCap) use (&$TableContent, $tblPerson){
                $Item['RequestDate'] = $tblHandyCap->getDate();
                $Item['LegalBasis'] = $tblHandyCap->getLegalBasis();
                $Item['LearnTarget'] = $tblHandyCap->getLearnTarget();
                $Item['RemarkLesson'] = $tblHandyCap->getRemarkLesson();
                $Item['RemarkRating'] = $tblHandyCap->getRemarkRating();
                $Item['Editor'] = $tblHandyCap->getPersonEditor();
                $Item['Option'] = (new Standard('', '#', new Edit()))
                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditHandyCapModal($tblPerson->getId(), $tblHandyCap->getId(), true))
                    .(new Standard('', '#', new Remove()))
                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenDeleteHandyCap($tblPerson->getId(), $tblHandyCap->getId()));

                array_push($TableContent, $Item);
            });
        }

        if(empty($TableContent)){
            return new Warning('Es sind keine Daten vorhanden.');
        }

        return new TableData($TableContent, null,
            array('RequestDate' => 'Datum',
                  'LegalBasis' => 'Rechtliche Grundlage',
                  'LearnTarget' => 'Lernziel',
                  'RemarkLesson' => 'Besonderheiten im Unterricht',
                  'RemarkRating' => 'Besonderheiten bei Leistungsbewertungen',
                  'Editor' => 'Bearbeiter',
                  'Option' => '',
            ), array(
                'order' => array(
                    array(0, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
//                'searching' => false,
                'responsive' => false
            ));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array $Year
     * @param array $Division
     *
     * @return FormGroup
     */
    private function formGroupTransfer(
        TblPerson $tblPerson = null,
        $Year,
        $Division
    ) {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['Meta']['Transfer'])) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
                    /** @var TblStudentTransfer $tblStudentTransferEnrollment */
                    $tblStudentTransferEnrollment = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeEnrollment
                    );
                    if ($tblStudentTransferEnrollment) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['School'] = (
                        $tblStudentTransferEnrollment->getServiceTblCompany()
                            ? $tblStudentTransferEnrollment->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Type'] = (
                        $tblStudentTransferEnrollment->getServiceTblType()
                            ? $tblStudentTransferEnrollment->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Course'] = (
                        $tblStudentTransferEnrollment->getServiceTblCourse()
                            ? $tblStudentTransferEnrollment->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Date'] = $tblStudentTransferEnrollment->getTransferDate();
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Remark'] = $tblStudentTransferEnrollment->getRemark();
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['StudentSchoolEnrollmentType']
                            = $tblStudentTransferEnrollment->getTblStudentSchoolEnrollmentType()
                            ? $tblStudentTransferEnrollment->getTblStudentSchoolEnrollmentType()->getId()
                            : 0;
                    }

                    $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                    /** @var TblStudentTransfer $tblStudentTransferArrive */
                    $tblStudentTransferArrive = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeArrive
                    );
                    if ($tblStudentTransferArrive) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['School'] = (
                        $tblStudentTransferArrive->getServiceTblCompany()
                            ? $tblStudentTransferArrive->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['StateSchool'] = (
                        $tblStudentTransferArrive->getServiceTblStateCompany()
                            ? $tblStudentTransferArrive->getServiceTblStateCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Type'] = (
                        $tblStudentTransferArrive->getServiceTblType()
                            ? $tblStudentTransferArrive->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Course'] = (
                        $tblStudentTransferArrive->getServiceTblCourse()
                            ? $tblStudentTransferArrive->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Date'] = $tblStudentTransferArrive->getTransferDate();
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Remark'] = $tblStudentTransferArrive->getRemark();
                    }

                    $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
                    /** @var TblStudentTransfer $tblStudentTransferLeave */
                    $tblStudentTransferLeave = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeLeave
                    );
                    if ($tblStudentTransferLeave) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['School'] = (
                        $tblStudentTransferLeave->getServiceTblCompany()
                            ? $tblStudentTransferLeave->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Type'] = (
                        $tblStudentTransferLeave->getServiceTblType()
                            ? $tblStudentTransferLeave->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Course'] = (
                        $tblStudentTransferLeave->getServiceTblCourse()
                            ? $tblStudentTransferLeave->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Date'] = $tblStudentTransferLeave->getTransferDate();
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Remark'] = $tblStudentTransferLeave->getRemark();
                    }

                    $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
                    /** @var TblStudentTransfer $tblStudentTransferProcess */
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
        }

        $VisitedDivisions = array();
        $RepeatedLevels = array();
        if ($tblPerson !== null) {
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
        }

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        $tblCompanyAllOwn = array();

        $tblCompanyAllSchoolNursery = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('NURSERY')
        );
        if ($tblCompanyAllSchoolNursery && $tblCompanyAllSchool) {
            $tblCompanyAllSchoolNursery = array_merge($tblCompanyAllSchool, $tblCompanyAllSchoolNursery);
        } else {
            $tblCompanyAllSchoolNursery = $tblCompanyAllSchool;
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();

        $tblSchoolCourseAll = Course::useService()->getCourseAll();
        if ($tblSchoolCourseAll) {
            array_push($tblSchoolCourseAll, new TblCourse());
        } else {
            $tblSchoolCourseAll = array(new TblCourse());
        }

        $tblStudentSchoolEnrollmentTypeAll = Student::useService()->getStudentSchoolEnrollmentTypeAll();

        $tblStudentTransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
        $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
        $tblStudentTransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
        $tblStudentTransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');

        // Normaler Inhalt
        $useCompanyAllSchoolEnrollment = $tblCompanyAllSchool;
        $useCompanyAllSchoolArrive = $tblCompanyAllSchoolNursery;
        $useCompanyAllSchoolLeave = $tblCompanyAllSchool;
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
            // Enrollment
            $tblStudentTransferTypeEnrollmentEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeEnrollment);
            if ($tblStudentTransferTypeEnrollmentEntity && ($TransferCompanyEnrollment = $tblStudentTransferTypeEnrollmentEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyEnrollment->getId(), $useCompanyAllSchoolEnrollment)) {
                    $TransferCompanyEnrollmentList = array($TransferCompanyEnrollment->getId() => $TransferCompanyEnrollment);
                    $useCompanyAllSchoolEnrollment = array_merge($useCompanyAllSchoolEnrollment,
                        $TransferCompanyEnrollmentList);
                }
            }
            // Arrive
            $tblStudentTransferTypeArriveEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeArrive);
            if ($tblStudentTransferTypeArriveEntity && ($TransferCompanyArrive = $tblStudentTransferTypeArriveEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyArrive->getId(), $useCompanyAllSchoolArrive)) {
                    $TransferCompanyArriveList = array($TransferCompanyArrive->getId() => $TransferCompanyArrive);
                    $useCompanyAllSchoolArrive = array_merge($useCompanyAllSchoolArrive, $TransferCompanyArriveList);
                }
            }
            // Leave
            $tblStudentTransferTypeLeaveEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeLeave);
            if ($tblStudentTransferTypeLeaveEntity && ($TransferCompanyLeave = $tblStudentTransferTypeLeaveEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyLeave->getId(), $useCompanyAllSchoolLeave)) {
                    $TransferCompanyLeaveList = array($TransferCompanyLeave->getId() => $TransferCompanyLeave);
                    $useCompanyAllSchoolLeave = array_merge($useCompanyAllSchoolLeave, $TransferCompanyLeaveList);
                }
            }
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

        $NodeEnrollment = 'Schülertransfer - Ersteinschulung';
        $NodeArrive = 'Schülertransfer - Schüler Aufnahme';
        $NodeLeave = 'Schülertransfer - Schüler Abgabe';
        $NodeProcess = 'Schülertransfer - Aktueller Schulverlauf';

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Ersteinschulung', array(
                        ApiMassReplace::receiverField((
                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][School]',
                                'Schule', array(
                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolEnrollment
                                ))
                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_SCHOOL,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeEnrollment,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                        )),

                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Type]',
                            'Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_SCHOOL_TYPE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeEnrollment,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                        )),

                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][StudentSchoolEnrollmentType]',
                            'Einschulungsart', array(
                                '{{ Name }}' => $tblStudentSchoolEnrollmentTypeAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_TYPE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                            => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeEnrollment,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                        )),

                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Course]',
                            'Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_COURSE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeEnrollment,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                        )),
                        ApiMassReplace::receiverField((
                        $Field = new DatePicker('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][Date]',
                            'Datum', 'Datum', new Calendar())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_TRANSFER_DATE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                            => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeEnrollment,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                        )),

//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][School]',
//                            'Schule', array(
//                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Type]',
//                            'Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Course]',
//                            'Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                            ), new Education()),
//                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Date]',
//                            'Datum', 'Datum', new Calendar()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Aufnahme', array(
                        ApiMassReplace::receiverField((
                        $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][School]',
                            'Abgebende Schule / Kita', array(
                                '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolArrive
                            ))
                        )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                        .ApiMassReplace::receiverModal($Field, $NodeArrive)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_SCHOOL,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeArrive,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                        )),
                        ApiMassReplace::receiverField((
                        $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][StateSchool]',
                            'Staatliche Stammschule', array(
                                '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolArrive
                            ))
                        )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                        .ApiMassReplace::receiverModal($Field, $NodeArrive)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_STATE_SCHOOL,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeArrive,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                        )),
                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Type]',
                            'Letzte Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeArrive)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_SCHOOL_TYPE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeArrive,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                        )),

                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Course]',
                            'Letzter Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeArrive)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_COURSE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeArrive,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                        )),
                        ApiMassReplace::receiverField((
                        $Field = new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Date]',
                            'Datum',
                            'Datum', new Calendar())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeArrive)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_TRANSFER_DATE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeArrive,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                        )),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][School]',
//                            'Abgebende Schule / Kita', array(
//                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchoolNursery
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Type]',
//                            'Letzte Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Course]',
//                            'Letzter Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                            ), new Education()),
//                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Date]',
//                            'Datum',
//                            'Datum', new Calendar()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Abgabe', array(
                        ApiMassReplace::receiverField((
                        $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][School]',
                            'Aufnehmende Schule', array(
                                '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolLeave
                            ))
                        )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                        .ApiMassReplace::receiverModal($Field, $NodeLeave)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_SCHOOL,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeLeave,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                        )),

                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Type]',
                            'Letzte Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeLeave)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_SCHOOL_TYPE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeLeave,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                        )),

                        ApiMassReplace::receiverField((
                        $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Course]',
                            'Letzter Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeLeave)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_COURSE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeLeave,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                        )),
                        ApiMassReplace::receiverField((
                        $Field = new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Date]',
                            'Datum',
                            'Datum', new Calendar())
                        ))
                        .ApiMassReplace::receiverModal($Field, $NodeLeave)
                        .new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_TRANSFER_DATE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                      => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $NodeLeave,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                        )),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][School]',
//                            'Aufnehmende Schule', array(
//                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Type]',
//                            'Letzte Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Course]',
//                            'Letzter Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                            ), new Education()),
//                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Date]',
//                            'Datum',
//                            'Datum', new Calendar()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
            //ToDO merken der Stelle
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
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][School]',
//                            'Aktuelle Schule', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll,
//                            ), new Education()),
//                        // removed SchoolType
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Type]',
//                            'Aktuelle Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll,
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Course]',
//                            'Aktueller Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll,
//                            ), new Education()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 6),
                new FormColumn(array(
                    new Panel('Besuchte Schulklassen',
                        $VisitedDivisions,
                        Panel::PANEL_TYPE_DEFAULT,
                        new Warning(
                            'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
                        )
                    ),
                ), 6),
                new FormColumn(array(
                    new Panel('Aktuelle Schuljahrwiederholungen',
                        $RepeatedLevels,
                        Panel::PANEL_TYPE_DEFAULT,
                        new Warning(
                            'Vom System erkannte Schuljahr&shy;wiederholungen.'
                            .'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
                        )
                    ),
                ), 3),
            )),
        ), new Title(new TileSmall().' Schülertransfer ', new Bold(new Success($tblPerson->getFullName()))));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return FormGroup
     */
    private function formGroupGeneral(
        TblPerson $tblPerson = null
    ) {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['Meta']['MedicalRecord'])) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {

                    /** @var TblStudentMedicalRecord $tblStudentMedicalRecord */
                    $tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
                    if ($tblStudentMedicalRecord) {
                        $Global->POST['Meta']['MedicalRecord']['Disease'] = $tblStudentMedicalRecord->getDisease();
                        $Global->POST['Meta']['MedicalRecord']['Medication'] = $tblStudentMedicalRecord->getMedication();
                        $Global->POST['Meta']['MedicalRecord']['AttendingDoctor'] = $tblStudentMedicalRecord->getAttendingDoctor();
                        $Global->POST['Meta']['MedicalRecord']['Insurance']['State'] = $tblStudentMedicalRecord->getInsuranceState();
                        $Global->POST['Meta']['MedicalRecord']['Insurance']['Company'] = $tblStudentMedicalRecord->getInsurance();
                    }

                    $tblStudentLocker = $tblStudent->getTblStudentLocker();
                    if ($tblStudentLocker) {
                        $Global->POST['Meta']['Additional']['Locker']['Number'] = $tblStudentLocker->getLockerNumber();
                        $Global->POST['Meta']['Additional']['Locker']['Location'] = $tblStudentLocker->getLockerLocation();
                        $Global->POST['Meta']['Additional']['Locker']['Key'] = $tblStudentLocker->getKeyNumber();
                    }

                    $tblStudentBaptism = $tblStudent->getTblStudentBaptism();
                    if ($tblStudentBaptism) {
                        $Global->POST['Meta']['Additional']['Baptism']['Date'] = $tblStudentBaptism->getBaptismDate();
                        $Global->POST['Meta']['Additional']['Baptism']['Location'] = $tblStudentBaptism->getLocation();
                    }

                    $tblStudentTransport = $tblStudent->getTblStudentTransport();
                    if ($tblStudentTransport) {
                        $Global->POST['Meta']['Transport']['Route'] = $tblStudentTransport->getRoute();
                        $Global->POST['Meta']['Transport']['Station']['Entrance'] = $tblStudentTransport->getStationEntrance();
                        $Global->POST['Meta']['Transport']['Station']['Exit'] = $tblStudentTransport->getStationExit();
                        $Global->POST['Meta']['Transport']['Remark'] = $tblStudentTransport->getRemark();
                    }

                    $tblStudentBilling = $tblStudent->getTblStudentBilling();
                    if ($tblStudentBilling) {
                        if ($tblStudentBilling->getServiceTblSiblingRank()) {
                            $Global->POST['Meta']['Billing'] = $tblStudentBilling->getServiceTblSiblingRank()->getId();
                        }
                    }

                    $tblStudentAgreementAll = Student::useService()->getStudentAgreementAllByStudent($tblStudent);
                    if ($tblStudentAgreementAll) {
                        foreach ($tblStudentAgreementAll as $tblStudentAgreement) {
                            $Global->POST['Meta']['Agreement']
                            [$tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId()]
                            [$tblStudentAgreement->getTblStudentAgreementType()->getId()] = 1;
                        }
                    }

                    $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
                    if ($tblStudentLiberationAll) {
                        foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
                            $Global->POST['Meta']['Liberation']
                            [$tblStudentLiberation->getTblStudentLiberationType()->getTblStudentLiberationCategory()->getId()]
                                = $tblStudentLiberation->getTblStudentLiberationType()->getId();
                        }
                    }

                    $Global->savePost();
                }
            }
        }

        /**
         * Panel: Agreement
         */
        $AgreementPanel = array();
        if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
            array_walk($tblAgreementCategoryAll,
                function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$AgreementPanel) {
                    array_push($AgreementPanel, new Aspect(new Bold($tblStudentAgreementCategory->getName())));
                    $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                    if ($tblAgreementTypeAll) {
                        $tblAgreementTypeAll = $this->getSorter($tblAgreementTypeAll)->sortObjectBy('Name');
                        array_walk($tblAgreementTypeAll,
                            function (TblStudentAgreementType $tblStudentAgreementType) use (
                                &$AgreementPanel,
                                $tblStudentAgreementCategory
                            ) {
                                array_push($AgreementPanel,
                                    new CheckBox('Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']',
                                        $tblStudentAgreementType->getName(), 1)
                                );
                            }
                        );
                    }
                }
            );
        }
        $AgreementPanel = new Panel('Einverständniserklärung zur Datennutzung', $AgreementPanel,
            Panel::PANEL_TYPE_INFO);

        /**
         * Panel: Liberation
         */
        $tblLiberationCategoryAll = Student::useService()->getStudentLiberationCategoryAll();
        $LiberationPanel = array();
        array_walk($tblLiberationCategoryAll,
            function (TblStudentLiberationCategory $tblStudentLiberationCategory) use (&$LiberationPanel) {

                $tblLiberationTypeAll = Student::useService()->getStudentLiberationTypeAllByCategory($tblStudentLiberationCategory);
                array_push($LiberationPanel,
                    new SelectBox('Meta[Liberation]['.$tblStudentLiberationCategory->getId().']',
                        $tblStudentLiberationCategory->getName(), array(
                            '{{ Name }}' => $tblLiberationTypeAll
                        ))
                );
            }
        );
        $LiberationPanel = new Panel('Unterrichtsbefreiung', $LiberationPanel, Panel::PANEL_TYPE_INFO);

        $tblSiblingRankAll = Relationship::useService()->getSiblingRankAll();
        $tblSiblingRankAll[] = new TblSiblingRank();

        /**
         * Form
         */
        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(new Hospital().' Krankenakte', array(
                        new TextArea('Meta[MedicalRecord][Disease]', 'Krankheiten / Allergien',
                            'Krankheiten / Allergien', new Heart()),
                        new TextArea('Meta[MedicalRecord][Medication]', 'Medikamente', 'Medikamente',
                            new Medicine()),
                        new TextField('Meta[MedicalRecord][AttendingDoctor]', 'Name', 'Behandelnder Arzt',
                            new Stethoscope()),
                        // ToDo -> extra Tabelle für Statustypen
                        new SelectBox('Meta[MedicalRecord][Insurance][State]', 'Versicherungsstatus', array(
                            0 => '',
                            1 => 'Pflicht',
                            2 => 'Freiwillig',
                            3 => 'Privat',
                            4 => 'Familie Vater',
                            5 => 'Familie Mutter',
                        ), new Lock()),
                        new AutoCompleter('Meta[MedicalRecord][Insurance][Company]', 'Krankenkasse', 'Krankenkasse',
                            array(), new Shield()),
                    ), Panel::PANEL_TYPE_DANGER), 3),
                new FormColumn(array(
                    new Panel('Fakturierung', array(
                        new SelectBox('Meta[Billing]', 'Geschwisterkind', array('{{Name}}' => $tblSiblingRankAll),
                            new Child()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Schließfach', array(
                        new TextField('Meta[Additional][Locker][Number]', 'Schließfachnummer', 'Schließfachnummer',
                            new Lock()),
                        new TextField('Meta[Additional][Locker][Location]', 'Schließfach Standort',
                            'Schließfach Standort', new MapMarker()),
                        new TextField('Meta[Additional][Locker][Key]', 'Schlüssel Nummer', 'Schlüssel Nummer',
                            new Key()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Taufe', array(
                        new DatePicker('Meta[Additional][Baptism][Date]', 'Taufdatum', 'Taufdatum',
                            new TempleChurch()
                        ),
                        new TextField('Meta[Additional][Baptism][Location]', 'Taufort', 'Taufort', new MapMarker()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 3),
                new FormColumn(array(
                    new Panel('Schulbeförderung', array(
                        new TextField('Meta[Transport][Route]', 'Buslinie', 'Buslinie', new Bus()),
                        new TextField('Meta[Transport][Station][Entrance]', 'Einstiegshaltestelle',
                            'Einstiegshaltestelle', new StopSign()),
                        new TextField('Meta[Transport][Station][Exit]', 'Ausstiegshaltestelle',
                            'Ausstiegshaltestelle', new StopSign()),
                        new TextArea('Meta[Transport][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                    $LiberationPanel
                ), 3),
                new FormColumn($AgreementPanel, 3),
            )),
        ), new Title(new TileSmall().' Allgemeines', new Bold(new Success($tblPerson->getFullName()))));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array $Year
     * @param array $Division
     *
     * @return FormGroup
     */
    private function formGroupSubject(
        TblPerson $tblPerson = null,
        $Year,
        $Division
    ) {

        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        $Global = $this->getGlobal();

        if ($tblStudent && !isset($Global->POST['Meta']['Subject'])) {

            $tblStudentSubjectAll = Student::useService()->getStudentSubjectAllByStudent($tblStudent);
            if ($tblStudentSubjectAll) {

                array_walk($tblStudentSubjectAll, function (TblStudentSubject $tblStudentSubject) use (&$Global) {

                    $Type = $tblStudentSubject->getTblStudentSubjectType()->getId();
                    $Ranking = $tblStudentSubject->getTblStudentSubjectRanking()->getId();
                    $Subject = $tblStudentSubject->getServiceTblSubject() ? $tblStudentSubject->getServiceTblSubject()->getId() : 0;
                    $Global->POST['Meta']['Subject'][$Type][$Ranking] = $Subject;
                });
                $Global->savePost();
            }
        }

        // Orientation
        $tblSubjectOrientation = Subject::useService()->getSubjectOrientationAll();
        if ($tblSubjectOrientation) {
            array_push($tblSubjectOrientation, new TblSubject());
        } else {
            $tblSubjectOrientation = array(new TblSubject());
        }

//        // Advanced
//        $tblSubjectAdvanced = Subject::useService()->getSubjectAdvancedAll();
//        if ($tblSubjectAdvanced) {
//            array_push($tblSubjectAdvanced, new TblSubject());
//        } else {
//            $tblSubjectAdvanced = array(new TblSubject());
//        }

        // Elective
        $tblSubjectElective = Subject::useService()->getSubjectElectiveAll();
        if ($tblSubjectElective) {
            array_push($tblSubjectElective, new TblSubject());
        } else {
            $tblSubjectElective = array(new TblSubject());
        }

        // Profile
        $tblSubjectProfile = Subject::useService()->getSubjectProfileAll();
        if ($tblSubjectProfile) {
            array_push($tblSubjectProfile, new TblSubject());
        } else {
            $tblSubjectProfile = array(new TblSubject());
        }

        // Religion
        $tblSubjectReligion = Subject::useService()->getSubjectReligionAll();
        if ($tblSubjectReligion) {
            array_push($tblSubjectReligion, new TblSubject());
        } else {
            $tblSubjectReligion = array(new TblSubject());
        }

        // ForeignLanguage
        $tblSubjectForeignLanguage = Subject::useService()->getSubjectForeignLanguageAll();
        if ($tblSubjectForeignLanguage) {
            array_push($tblSubjectForeignLanguage, new TblSubject());
        } else {
            $tblSubjectForeignLanguage = array(new TblSubject());
        }

        // All
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if ($tblSubjectAll) {
            array_push($tblSubjectAll, new TblSubject());
        } else {
            $tblSubjectAll = array(new TblSubject());
        }

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    $this->panelSubjectList('FOREIGN_LANGUAGE', 'Fremdsprachen', 'Fremdsprache',
                        $tblSubjectForeignLanguage, 4, ($tblStudent ? $tblStudent : null), $Year, $Division, $tblPerson),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('RELIGION', 'Religion', 'Religion', $tblSubjectReligion, 1,
                        ($tblStudent ? $tblStudent : null), $Year,
                        $Division, $tblPerson),
                    $this->panelSubjectList('PROFILE', 'Profile', 'Profil', $tblSubjectProfile, 1,
                        ($tblStudent ? $tblStudent : null), $Year,
                        $Division, $tblPerson),
                    $this->panelSubjectList('ORIENTATION', 'Neigungskurse', 'Neigungskurs', $tblSubjectOrientation, 1,
                        ($tblStudent ? $tblStudent : null), $Year, $Division, $tblPerson),
//                    $this->panelSubjectList('ADVANCED', 'Vertiefungskurse', 'Vertiefungskurs', $tblSubjectAdvanced, 1,
//                        null, $Year, $Division, $tblPerson),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('ELECTIVE', 'Wahlfächer', 'Wahlfach', $tblSubjectElective, 3,
                        ($tblStudent ? $tblStudent : null), $Year,
                        $Division, $tblPerson),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('TEAM', 'Arbeitsgemeinschaften', 'Arbeitsgemeinschaft', $tblSubjectAll, 3,
                        ($tblStudent ? $tblStudent : null), $Year, $Division, $tblPerson),
                ), 3),
//                new FormColumn(array(
//                    $this->panelSubjectList('TRACK_INTENSIVE', 'Leistungskurse', 'Leistungskurs', $tblSubjectAll, 2,
//                        null, $Year, $Division, $tblPerson),
//                    $this->panelSubjectList('TRACK_BASIC', 'Grundkurse', 'Grundkurs', $tblSubjectAll, 8, null, $Year,
//                        $Division, $tblPerson),
//                ), 3),
            )),
        ), new Title(new TileSmall().' Unterrichtsfächer', new Bold(new Success($tblPerson->getFullName()))));
    }

    /**
     * @param string $Identifier
     * @param string $Title
     * @param string $Label
     * @param TblSubject[] $SubjectList
     * @param int $Count
     * @param TblStudent $tblStudent
     * @param array $Year
     * @param array $Division
     * @param TblPerson|null $tblPerson
     *
     * @return Panel
     */
    private function panelSubjectList(
        $Identifier,
        $Title,
        $Label,
        $SubjectList,
        $Count = 1,
        TblStudent $tblStudent = null,
        $Year = array(),
        $Division = array(),
        TblPerson $tblPerson = null
    ) {

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(strtoupper($Identifier));
        $Panel = array();
        for ($Rank = 1; $Rank <= $Count; $Rank++) {
            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($Rank);
            $PersonId = false;
            if($tblPerson) {
                $PersonId = $tblPerson->getId();
            }

            $useSubjectList = $SubjectList;
            $tblStudentSubject = false;
            // Vorhandene Werte ergänzen (wenn sie in der SelectBox nicht mehr existieren)
            if ($tblStudent && $tblStudentSubjectType && $tblStudentSubjectRanking) {
                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    if (!array_key_exists($tblSubject->getId(), $SubjectList)) {
                        $tblSubjectList = array($tblSubject->getId() => $tblSubject);
                        $useSubjectList = array_merge($SubjectList, $tblSubjectList);
                    }
                }
            }

            $Node = 'Unterrichtsfächer';
            // activate MassReplace
            if ($Identifier == 'PROFILE'
                || $Identifier == 'RELIGION'
                || $Identifier == 'ORIENTATION'
                || $Identifier == 'FOREIGN_LANGUAGE'
                || $Identifier == 'ELECTIVE'
                || $Identifier == 'TEAM'
            ) {
                array_push($Panel,
                    ApiMassReplace::receiverField((
                    $Field = new SelectBox('Meta[Subject]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        ($Count > 1 ? $tblStudentSubjectRanking->getName().' ' : '') . $Label
                        , array('{{ Acronym }} - {{ Name }} {{ Description }}' => $useSubjectList), new Education())
                    ))
                    .ApiMassReplace::receiverModal($Field, $Node)
                    .new PullRight((new Link('Massen-Änderung',
                        ApiMassReplace::getEndpoint(), null, array(
                            ApiMassReplace::SERVICE_CLASS                                   => MassReplaceSubject::CLASS_MASS_REPLACE_SUBJECT,
                            ApiMassReplace::SERVICE_METHOD                                  => MassReplaceSubject::METHOD_REPLACE_SUBJECT,
                            ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                            MassReplaceSubject::ATTR_TYPE                                   => $tblStudentSubjectType->getId(),
                            MassReplaceSubject::ATTR_RANKING                                => $tblStudentSubjectRanking->getId(),
                            'Id'                                                            => $PersonId,
                            'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                            'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                            'Node'                                                          => $Node,
                        )))->ajaxPipelineOnClick(
                        ApiMassReplace::pipelineOpen($Field, $Node)
                    ))
                );
            } else {
                array_push($Panel,
                    new SelectBox(
                        'Meta[Subject]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        ($Count > 1 ? $tblStudentSubjectRanking->getName().' ' : '').$Label,
                        array('{{ Acronym }} - {{ Name }} {{ Description }}' => $useSubjectList),
                        new Education()
                    ));
            }
            // Student FOREIGN_LANGUAGE: LevelFrom, LevelTill
            if ($tblStudentSubjectType->getIdentifier() == 'FOREIGN_LANGUAGE') {
                $tblLevelAll = Division::useService()->getLevelAll();

                // Gespeicherte Daten ergänzen wenn nicht bereits vorhanden
                $useLevelFromList = $tblLevelAll;
                $useLevelTillList = $tblLevelAll;
                if ($tblStudentSubject && ($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())) {
                    if (!array_key_exists($tblLevelFrom->getId(), $tblLevelAll)) {
                        $tblLevelFromList = array($tblLevelFrom->getId() => $tblLevelFrom);
                        $useLevelFromList = array_merge($tblLevelAll, $tblLevelFromList);
                    }
                }
                if ($tblStudentSubject && ($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill())) {
                    if (!array_key_exists($tblLevelTill->getId(), $tblLevelAll)) {
                        $tblLevelTillList = array($tblLevelTill->getId() => $tblLevelTill);
                        $useLevelTillList = array_merge($tblLevelAll, $tblLevelTillList);
                    }
                }


                // Read StudentSubject Levels from DB
                if ($tblStudent) {
                    $tblStudentSubjectAll = Student::useService()
                        ->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectAll) {
                        foreach ($tblStudentSubjectAll as $tblStudentSubject) {
                            // TblStudentSubject Rank == Panel Rank
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getId() == $Rank) {
                                $Global = $this->getGlobal();
                                if ($tblStudentSubject->getServiceTblLevelFrom()) {
                                    $Global->POST['Meta']['SubjectLevelFrom'][$tblStudentSubjectType->getId()][$tblStudentSubjectRanking->getId()]
                                        = $tblStudentSubject->getServiceTblLevelFrom()->getId();
                                }
                                if ($tblStudentSubject->getServiceTblLevelTill()) {
                                    $Global->POST['Meta']['SubjectLevelTill'][$tblStudentSubjectType->getId()][$tblStudentSubjectRanking->getId()]
                                        = $tblStudentSubject->getServiceTblLevelTill()->getId();
                                }
                                $Global->savePost();
                            }
                        }
                    }
                }
                array_push($Panel,
                    ApiMassReplace::receiverField((
                    $Field = new SelectBox(
                            'Meta[SubjectLevelFrom]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                            new Muted(new Small($tblStudentSubjectRanking->getName() . ' Fremdsprache von Klasse')),
                            array('{{ Name }} {{ ServiceTblType.Name }}' => $useLevelFromList),
                            new Time())))
                    .ApiMassReplace::receiverModal($Field, $Node)
                    .new PullRight((new Link('Massen-Änderung',
                        ApiMassReplace::getEndpoint(), null, array(
                            ApiMassReplace::SERVICE_CLASS                                   => MassReplaceSubject::CLASS_MASS_REPLACE_SUBJECT,
                            ApiMassReplace::SERVICE_METHOD                                  => MassReplaceSubject::METHOD_REPLACE_LEVEL_FROM,
                            ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                            MassReplaceSubject::ATTR_TYPE                                   => $tblStudentSubjectType->getId(),
                            MassReplaceSubject::ATTR_RANKING                                => $tblStudentSubjectRanking->getId(),
                            'Id'                                                            => $PersonId,
                            'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                            'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                            'Node'                                                          => $Node,
                        )))->ajaxPipelineOnClick(
                        ApiMassReplace::pipelineOpen($Field, $Node)
                    ))
                );
                array_push($Panel,
                    ApiMassReplace::receiverField((
                    $Field = new SelectBox(
                        'Meta[SubjectLevelTill]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        new Muted(new Small($tblStudentSubjectRanking->getName() . ' Fremdsprache bis Klasse')),
                        array('{{ Name }} {{ ServiceTblType.Name }}' => $useLevelTillList),
                        new Time())))
                    .ApiMassReplace::receiverModal($Field, $Node)
                    .new PullRight((new Link('Massen-Änderung',
                        ApiMassReplace::getEndpoint(), null, array(
                            ApiMassReplace::SERVICE_CLASS                                   => MassReplaceSubject::CLASS_MASS_REPLACE_SUBJECT,
                            ApiMassReplace::SERVICE_METHOD                                  => MassReplaceSubject::METHOD_REPLACE_LEVEL_TILL,
                            ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                            MassReplaceSubject::ATTR_TYPE                                   => $tblStudentSubjectType->getId(),
                            MassReplaceSubject::ATTR_RANKING                                => $tblStudentSubjectRanking->getId(),
                            'Id'                                                            => $PersonId,
                            'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                            'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                            'Node'                                                          => $Node,
                        )))->ajaxPipelineOnClick(
                        ApiMassReplace::pipelineOpen($Field, $Node)
                    ))
                );
            }
        }
        return new Panel($Title, $Panel, Panel::PANEL_TYPE_INFO);
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
}

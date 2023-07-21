<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\BGyAbitur\LeaveLevelEleven;
use SPHERE\Application\Education\Certificate\Prepare\BGyAbitur\LeavePoints;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

abstract class FrontendLeaveSekTwoBGy extends FrontendLeaveSekTwo
{
    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param Stage $stage
     * @param TblCertificate|null $tblCertificate
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblType|null $tblType
     *
     * @return array
     */
    public function setLeaveContentForSekTwoBGy(
        TblPerson $tblPerson,
        TblYear $tblYear,
        Stage $stage,
        ?TblCertificate $tblCertificate,
        ?TblLeaveStudent $tblLeaveStudent,
        ?TblType $tblType
    ): array {
        $form = false;

        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
        } else {
            $support = false;
        }

        if ($tblCertificate) {
            if ($tblLeaveStudent) {
                $stage->addButton(new External(
                    'Zeugnis als Muster herunterladen',
                    '/Api/Education/Certificate/Generator/PreviewLeave',
                    new Download(),
                    array(
                        'LeaveStudentId' => $tblLeaveStudent->getId(),
                        'Name' => 'Zeugnismuster'
                    ),
                    'Zeugnis als Muster herunterladen'));

                $form = (new LeavePoints($tblLeaveStudent))->getForm();
            }
        }

        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Schüler',
                        $tblPerson->getLastFirstName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schuljahr',
                        $tblYear->getDisplayName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Schulart',
                        $tblType
                            ? $tblType->getName()
                            : new Warning(new Exclamation()
                            . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                        $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                new LayoutColumn(
                    new Panel(
                        'Zeugnisvorlage',
                        $tblCertificate
                            ? $tblCertificate->getName()
                            . ($tblCertificate->getDescription()
                                ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                            : new Warning(new Exclamation()
                            . ' Keine Zeugnisvorlage verfügbar!'),
                        $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                    )
                    , 3),
                ($support
                    ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                    : null
                ),
            )),
        ));

        if ($form && $tblLeaveStudent) {
            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Standard('Punkte bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Points',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                                'SchoolType' => 'BGy'
                            )
                        ),
                        new Standard('Sonstige Informationen bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Information',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                                'SchoolType' => 'BGy'
                            )
                        ),
                        new Standard('Klassenstufe 11 bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/LevelEleven',
                            new Edit(), array(
                                'Id' => $tblLeaveStudent->getId(),
                            )
                        ),
                        '<br />',
                        '<br />'
                    )),
                )),
            ));
        }

        if ($tblCertificate) {
            /** @var Form $form */
            if ($form) {
                $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                    $form
                )));
            }

            $panelList[] = array();
            if (($leaveTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'DateFrom'))) {
                $panelList[] = new Panel(
                    'hat vom (Datum1)',
                    $leaveTermInformation->getValue()
                );
            }
            if (($midTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'DateTo'))) {
                $panelList[] = new Panel(
                    'hat bis (Datum2)',
                    $midTermInformation->getValue()
                );
            }
            if (($dateInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                $panelList[] = new Panel(
                    'Zeugnisdatum',
                    $dateInformation->getValue()
                );
            }
            if (($remarkInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'RemarkWithoutTeam'))) {
                $panelList[] = new Panel(
                    'Bemerkungen',
                    $remarkInformation->getValue()
                );
            }
            if (($bellSubjectInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'BellSubject'))) {
                $panelList[] = new Panel(
                    'Thema der Besonderen Lernleistung',
                    $bellSubjectInformation->getValue()
                );
            }
            if (($bellPointsInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'BellPoints'))) {
                $panelList[] = new Panel(
                    'Punktzahl der besonderen Lernleistung in einfacher Wertung',
                    $bellPointsInformation->getValue()
                );
            }

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Panel(
                    'Sonstige Informationen',
                    $panelList,
                    Panel::PANEL_TYPE_PRIMARY
                )
            )));
        }

        return $layoutGroups;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param bool $isApproved
     * @return Form
     */
    public function getLeaveInformationBGyAbiturForm(TblLeaveStudent $tblLeaveStudent, bool $isApproved): Form
    {
        // Post
        if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
            $global = $this->getGlobal();
            foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                $global->POST['Data'][$tblLeaveInformation->getField()] = $tblLeaveInformation->getValue();
            }
            $global->savePost();
        }

        $datePickerFrom = (new DatePicker('Data[DateFrom]', '', 'hat vom (Datum1)', new Calendar()))->setRequired();
        $datePickerTo = (new DatePicker('Data[DateTo]', '', 'hat bis (Datum2)', new Calendar()))->setRequired();
        $datePicker = (new DatePicker('Data[CertificateDate]', '', 'Zeugnisdatum', new Calendar()))->setRequired();
        $remarkTextArea = new TextArea('Data[RemarkWithoutTeam]', '', 'Bemerkungen');

        // Besondere Lernleistung BELL
        $bellSubject = (new TextField('Data[BellSubject]', '', 'Thema'));
        $bellPoints = (new TextField('Data[BellPoints]', '', 'Punktzahl in einfacher Wertung'));

        // headmaster
        $headmasterNameTextField = new TextField('Data[HeadmasterName]', '',
            'Name des/der Schulleiters/in');
        $headmasterRadioSex1 = (new RadioBox('Data[HeadmasterGender]', 'Männlich',
            ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                ? $tblCommonGender->getId() : 0));
        $headmasterRadioSex2 = (new RadioBox('Data[HeadmasterGender]', 'Weiblich',
            ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                ? $tblCommonGender->getId() : 0));

        // tudor
        $tudorNameTextField = new TextField('Data[TudorName]', '',
            'Name des/der Tutors/in');
        $tudorRadioSex1 = (new RadioBox('Data[TudorGender]', 'Männlich',
            ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                ? $tblCommonGender->getId() : 0));
        $tudorRadioSex2 = (new RadioBox('Data[TudorGender]', 'Weiblich',
            ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                ? $tblCommonGender->getId() : 0));

        if ($isApproved) {
            $datePickerFrom->setDisabled();
            $datePickerTo->setDisabled();
            $datePicker->setDisabled();
            $remarkTextArea->setDisabled();

            $bellSubject->setDisabled();
            $bellPoints->setDisabled();

            $headmasterNameTextField->setDisabled();
            $headmasterRadioSex1->setDisabled();
            $headmasterRadioSex2->setDisabled();

            $tudorNameTextField->setDisabled();
            $tudorRadioSex1->setDisabled();
            $tudorRadioSex2->setDisabled();
        }

        $otherInformationList = array(
            $datePickerFrom,
            $datePickerTo,
            $datePicker,
            $remarkTextArea
        );

        return new Form(new FormGroup(array(
            new FormRow(new FormColumn(
                new Panel(
                    'Sonstige Informationen',
                    $otherInformationList,
                    Panel::PANEL_TYPE_INFO
                )
            )),
            new FormRow(new FormColumn(
                new Panel(
                    'Besondere Lernleistung',
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn($bellSubject, 6),
                        new LayoutColumn($bellPoints, 6)
                    )))),
                    Panel::PANEL_TYPE_INFO
                )
            )),
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Unterzeichner - Schulleiter',
                        array(
                            $headmasterNameTextField,
                            new Panel(
                                new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                array($headmasterRadioSex1, $headmasterRadioSex2),
                                Panel::PANEL_TYPE_DEFAULT
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 6),
                new FormColumn(
                    new Panel(
                        'Unterzeichner - Tutor',
                        array(
                            $tudorNameTextField,
                            new Panel(
                                new Small(new Bold('Geschlecht des/der Tudors/in')),
                                array($tudorRadioSex1, $tudorRadioSex2),
                                Panel::PANEL_TYPE_DEFAULT
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 6),
            )),
        )));
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return string
     */
    public function frontendLeaveStudentAbiturLevelEleven(
        $Id = null,
        $Data = null
    ): Stage {
        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Ergebnisse der Pflichtfächer, die in Klassenstufe 11 abgeschlossen wurden'));

            $tblYear = $tblLeaveStudent->getServiceTblYear();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();

            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                    'PersonId' => $tblPerson->getId(),
                    'YearId' => $tblYear ? $tblYear->getId() : 0
                )
            ));

            $tblType = false;
            if ($tblYear && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblType = $tblStudentEducation->getServiceTblSchoolType();
            }

            if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                $support = ApiSupportReadOnly::openOverViewModal($tblPerson->getId(), false);
            } else {
                $support = false;
            }

            $layoutGroups[] = new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Schüler',
                            $tblPerson->getLastFirstNameWithCallNameUnderline(),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Schuljahr',
                            $tblYear->getDisplayName(),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Schulart',
                            $tblType
                                ? $tblType->getName()
                                : new Warning(new Exclamation()
                                . ' Keine aktuelle Schulart zum Schüler gefunden!'),
                            $tblType ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    new LayoutColumn(
                        new Panel(
                            'Zeugnisvorlage',
                            $tblCertificate
                                ? $tblCertificate->getName()
                                . ($tblCertificate->getDescription()
                                    ? new Muted(' - ' . $tblCertificate->getDescription()) : '')
                                : new Warning(new Exclamation()
                                . ' Keine Zeugnisvorlage verfügbar!'),
                            $tblCertificate ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_WARNING
                        )
                        , 3),
                    ($support
                        ? new LayoutColumn(new Panel('Integration', $support, Panel::PANEL_TYPE_INFO))
                        : null
                    )
                )),
            ));

            $levelEleven = new LeaveLevelEleven($tblPerson, $tblLeaveStudent);
            $content = $levelEleven->getContent($Data);

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                $content
            )));

            $stage->setContent(new Layout($layoutGroups));

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }
}
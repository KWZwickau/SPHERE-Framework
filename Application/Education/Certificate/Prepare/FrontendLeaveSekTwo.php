<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekII;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\LeavePoints;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
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

abstract class FrontendLeaveSekTwo extends FrontendLeave
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
    public function setLeaveContentForSekTwo(
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
                            )
                        ),
                        new Standard('Sonstige Informationen bearbeiten',
                            '/Education/Certificate/Prepare/Leave/Student/Abitur/Information',
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
            if (($leaveTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'LeaveTerm'))) {
                $panelList[] = new Panel(
                    'verlässt das Gymnasium',
                    $leaveTermInformation->getValue()
                );
            }
            if (($midTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'MidTerm'))) {
                $panelList[] = new Panel(
                    'Kurshalbjahr',
                    $midTermInformation->getValue()
                );
            }
            if (($dateInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                $panelList[] = new Panel(
                    'Zeugnisdatum',
                    $dateInformation->getValue()
                );
            }
            if (($remarkInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'Remark'))) {
                $panelList[] = new Panel(
                    'Bemerkungen',
                    $remarkInformation->getValue()
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
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentAbiturPoints($Id = null, $Data = null)
    {
        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Punkte'));

            $tblYear = $tblLeaveStudent->getServiceTblYear();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();

            $tblType = false;
            if ($tblYear && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblType = $tblStudentEducation->getServiceTblSchoolType();
            }

            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Leave/Student', new ChevronLeft(), array(
                    'PersonId' => $tblPerson->getId(),
                    'YearId' => $tblYear ? $tblYear->getId() : 0
                )
            ));

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

            $LeavePoints = new LeavePoints($tblLeaveStudent, BlockIView::EDIT_GRADES);
            $form = $LeavePoints->getForm();

            $layoutGroups[] = new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Well(
                    Prepare::useService()->updateLeaveStudentAbiturPoints($form, $tblLeaveStudent, $Data)
                )
            )));

            $stage->setContent(new Layout($layoutGroups));

            return $stage;
        } else {
            return new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Schüler')
                . new Danger('Schüler nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Prepare/Leave', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendLeaveStudentAbiturInformation($Id = null, $Data = null)
    {
        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($Id))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $stage = new Stage(new Stage('Zeugnisvorbereitung', 'Abgangszeugnis - Sonstige Informationen'));

            $tblYear = $tblLeaveStudent->getServiceTblYear();
            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();
            $isApproved = $tblLeaveStudent->isApproved();

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

            if ($tblCertificate) {
                $leaveTerms = GymAbgSekII::getLeaveTerms();
                $midTerms = GymAbgSekII::getMidTerms();

                // Post
                if (($tblLeaveInformationList = Prepare::useService()->getLeaveInformationAllByLeaveStudent($tblLeaveStudent))) {
                    $global = $this->getGlobal();
                    foreach ($tblLeaveInformationList as $tblLeaveInformation) {
                        if ($tblLeaveInformation->getField() == 'LeaveTerm') {
                            $value = array_search($tblLeaveInformation->getValue(), $leaveTerms);
                        } elseif ($tblLeaveInformation->getField() == 'MidTerm') {
                            $value = array_search($tblLeaveInformation->getValue(), $midTerms);
                        } else {
                            $value = $tblLeaveInformation->getValue();
                        }

                        $global->POST['Data'][$tblLeaveInformation->getField()] = $value;
                    }
                    $global->savePost();
                }

                $leaveTermSelectBox = (new SelectBox(
                    'Data[LeaveTerm]',
                    'verlässt das Gymnasium',
                    $leaveTerms
                ))->setRequired();
                $midTermSelectBox = (new SelectBox(
                    'Data[MidTerm]',
                    'Kurshalbjahr',
                    $midTerms
                ))->setRequired();
                $datePicker = (new DatePicker('Data[CertificateDate]', '', 'Zeugnisdatum',
                    new Calendar()))->setRequired();
                $remarkTextArea = new TextArea('Data[Remark]', '', 'Bemerkungen');
                if ($isApproved) {
                    $datePicker->setDisabled();
                    $remarkTextArea->setDisabled();
                    $leaveTermSelectBox->setDisabled();
                    $midTermSelectBox->setDisabled();
                }
                $otherInformationList = array(
                    $leaveTermSelectBox,
                    $midTermSelectBox,
                    $datePicker,
                    $remarkTextArea
                );

                $headmasterNameTextField = new TextField('Data[HeadmasterName]', '',
                    'Name des/der Schulleiters/in');
                $radioSex1 = (new RadioBox('Data[HeadmasterGender]', 'Männlich',
                    ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                        ? $tblCommonGender->getId() : 0));
                $radioSex2 = (new RadioBox('Data[HeadmasterGender]', 'Weiblich',
                    ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                        ? $tblCommonGender->getId() : 0));
                if ($isApproved) {
                    $headmasterNameTextField->setDisabled();
                    $radioSex1->setDisabled();
                    $radioSex2->setDisabled();
                }

                $form = new Form(new FormGroup(array(
                    new FormRow(new FormColumn(
                        new Panel(
                            'Sonstige Informationen',
                            $otherInformationList,
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
                                        array($radioSex1, $radioSex2),
                                        Panel::PANEL_TYPE_DEFAULT
                                    )
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    )),
                )));
            } else {
                $form = null;
            }

            if ($isApproved) {
                $content = $form;
            } else {
                $form->appendFormButton(new Primary('Speichern', new Save()));
                $content = new Well(
                    Prepare::useService()->updateAbiturLeaveInformation($form, $tblLeaveStudent, $Data)
                );
            }

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
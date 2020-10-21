<?php

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;

class FrontendStudentSpecialNeeds extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Förderschüler';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentSpecialNeedsContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentSpecialNeeds = $tblStudent->getTblStudentSpecialNeeds())
            ) {
                $isMultipleHandicapped = $tblStudentSpecialNeeds->getIsMultipleHandicapped() ? 'Ja' : 'Nein';
                $isHeavyMultipleHandicapped = $tblStudentSpecialNeeds->getIsHeavyMultipleHandicapped() ? 'Ja' : 'Nein';
                $increaseFactorHeavyMultipleHandicappedSchool = $tblStudentSpecialNeeds->getIncreaseFactorHeavyMultipleHandicappedSchool();
                $increaseFactorHeavyMultipleHandicappedRegionalAuthorities = $tblStudentSpecialNeeds->getIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities();
                $remarkHeavyMultipleHandicapped = $tblStudentSpecialNeeds->getRemarkHeavyMultipleHandicapped();
                $degreeOfHandicap = $tblStudentSpecialNeeds->getDegreeOfHandicap();
                $sign = $tblStudentSpecialNeeds->getSign();
                $validTo = $tblStudentSpecialNeeds->getValidTo();
                $level = ($tblStudentSpecialNeedsLevel = $tblStudentSpecialNeeds->getTblStudentSpecialNeedsLevel())
                    ? $tblStudentSpecialNeedsLevel->getName() : '';
            } else {
                $isMultipleHandicapped = '';
                $isHeavyMultipleHandicapped = '';
                $increaseFactorHeavyMultipleHandicappedSchool = '';
                $increaseFactorHeavyMultipleHandicappedRegionalAuthorities = '';
                $remarkHeavyMultipleHandicapped = '';
                $degreeOfHandicap = '';
                $sign = '';
                $validTo = '';
                $level = '';
            }

            if ($tblStudent) {
                $schoolAttendanceYear = $tblStudent->getSchoolAttendanceYear();
            } else {
                $schoolAttendanceYear = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Bildung',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Stufe', 6),
                                    self::getLayoutColumnValue($level, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('SBJ', 6),
                                    self::getLayoutColumnValue($schoolAttendanceYear, 6),
                                )),
                            )))
                        ),
                    ), 3),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            '',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('mehrfachbehindert', 10),
                                    self::getLayoutColumnValue($isMultipleHandicapped, 2),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('schwerstmehrfachbehindert', 10),
                                    self::getLayoutColumnValue($isHeavyMultipleHandicapped, 2),
                                )),
                            )))
                        ),
                    ), 3),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Behinderung',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bemerkung', 6),
                                    self::getLayoutColumnValue($remarkHeavyMultipleHandicapped, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('GdB', 6),
                                    self::getLayoutColumnValue($degreeOfHandicap, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Merkzeichen', 6),
                                    self::getLayoutColumnValue($sign, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('gültig bis', 6),
                                    self::getLayoutColumnValue($validTo, 6),
                                )),
                            )))
                        ),
                    ), 3),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Erhöhungsfaktor',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Erhöhungsfaktor Schule (%)', 10),
                                    self::getLayoutColumnValue($increaseFactorHeavyMultipleHandicappedSchool, 2),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Erhöhungsfaktor LaSuB (%)', 10),
                                    self::getLayoutColumnValue($increaseFactorHeavyMultipleHandicappedRegionalAuthorities, 2),
                                )),
                            )))
                        ),
                    ), 3),
                )),
            )));
            
            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentSpecialNeedsContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Hospital()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentSpecialNeedsContent($PersonId = null)
    {
        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentSpecialNeeds = $tblStudent->getTblStudentSpecialNeeds())
            ) {

                $Global->POST['Meta']['SpecialNeeds']['IsMultipleHandicapped'] = $tblStudentSpecialNeeds->getIsMultipleHandicapped();
                $Global->POST['Meta']['SpecialNeeds']['IsHeavyMultipleHandicapped'] = $tblStudentSpecialNeeds->getIsHeavyMultipleHandicapped();
                $Global->POST['Meta']['SpecialNeeds']['IncreaseFactorHeavyMultipleHandicappedSchool'] = $tblStudentSpecialNeeds->getIncreaseFactorHeavyMultipleHandicappedSchool();
                $Global->POST['Meta']['SpecialNeeds']['IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities'] = $tblStudentSpecialNeeds->getIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities();
                $Global->POST['Meta']['SpecialNeeds']['RemarkHeavyMultipleHandicapped'] = $tblStudentSpecialNeeds->getRemarkHeavyMultipleHandicapped();
                $Global->POST['Meta']['SpecialNeeds']['DegreeOfHandicap'] = $tblStudentSpecialNeeds->getDegreeOfHandicap();
                $Global->POST['Meta']['SpecialNeeds']['Sign'] = $tblStudentSpecialNeeds->getSign();
                $Global->POST['Meta']['SpecialNeeds']['ValidTo'] = $tblStudentSpecialNeeds->getValidTo();
                $Global->POST['Meta']['SpecialNeeds']['TblStudentSpecialNeedsLevel'] = $tblStudentSpecialNeeds->getTblStudentSpecialNeedsLevel();

                $Global->savePost();
            }
        }

        return $this->getEditStudentSpecialNeedsTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentSpecialNeedsForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentSpecialNeedsTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Hospital() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentSpecialNeedsForm(TblPerson $tblPerson = null)
    {
        $tblStudentSpecialNeedsLevelList = Student::useService()->getStudentSpecialNeedsLevelAll();

         // Massenänderung für Stufe
//        $NodeProcess = 'Förderschüler';
//        FrontendStudent::setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Bildung', array(
//                            ApiMassReplace::receiverField((
//                            $Field = (new SelectBox('Meta[SpecialNeeds][TblStudentSpecialNeedsLevel]', 'Stufe',
//                                array('{{ Name }}' => $tblStudentSpecialNeedsLevelList), null, true, null)
//                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
//                            .ApiMassReplace::receiverModal($Field, $NodeProcess)
//                            .new PullRight((new Link('Massen-Änderung',
//                                ApiMassReplace::getEndpoint(), null, array(
//                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
//                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_CURRENT_SCHOOL,
//                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
//                                    'Id'                                                      => $tblPerson->getId(),
//                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
//                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
//                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
//                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
//                                    'Node'                                                          => $NodeProcess,
//                                )))->ajaxPipelineOnClick(
//                                ApiMassReplace::pipelineOpen($Field, $NodeProcess)
//                            ))
                            (new SelectBox('Meta[SpecialNeeds][TblStudentSpecialNeedsLevel]', 'Stufe',
                                array('{{ Name }}' => $tblStudentSpecialNeedsLevelList), null, true, null))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        ), Panel::PANEL_TYPE_INFO)
                    , 3),
                    new FormColumn(
                        new Panel('&nbsp;', array(
                            new CheckBox('Meta[SpecialNeeds][IsMultipleHandicapped]', 'mehrfachbehindert', 1),
                            new CheckBox('Meta[SpecialNeeds][IsHeavyMultipleHandicapped]', 'schwerstmehrfachbehindert', 1),
                        ), Panel::PANEL_TYPE_INFO)
                    , 3),
                    new FormColumn(
                        new Panel('Behinderung', array(
                            new TextField('Meta[SpecialNeeds][RemarkHeavyMultipleHandicapped]', 'Bemerkung zu SMB', 'Bemerkung zu SMB'),
                            new TextField('Meta[SpecialNeeds][DegreeOfHandicap]', 'Grad der Behinderung', 'Grad der Behinderung'),
                            new TextField('Meta[SpecialNeeds][Sign]', 'Merkzeichen', 'Merkzeichen'),
                            new TextField('Meta[SpecialNeeds][ValidTo]', 'gültig bis', 'gültig bis')
                        ), Panel::PANEL_TYPE_INFO)
                    , 3),
                    new FormColumn(
                        new Panel('Erhöhungsfaktor', array(
                            new TextField('Meta[SpecialNeeds][IncreaseFactorHeavyMultipleHandicappedSchool]', '', 'Erhöhungsfaktor Schule in %'),
                            new TextField('Meta[SpecialNeeds][IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities]', '', 'Erhöhungsfaktor LaSuB in %'),
                        ), Panel::PANEL_TYPE_INFO)
                    , 3),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentSpecialNeedsContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentSpecialNeedsContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}
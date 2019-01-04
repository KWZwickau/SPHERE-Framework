<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.12.2018
 * Time: 15:41
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Heart;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Medicine;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Stethoscope;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendStudentMedicalRecord
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentMedicalRecord extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Krankenakte';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentMedicalRecordContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
            ) {
                $disease = $tblStudentMedicalRecord->getDisease();
                $medication = $tblStudentMedicalRecord->getMedication();
                $attendingDoctor = $tblStudentMedicalRecord->getAttendingDoctor();
                $insuranceState = $tblStudentMedicalRecord->getDisplayInsuranceState();
                $insurance = $tblStudentMedicalRecord->getInsurance();
            } else {
                $disease = '';
                $medication = '';
                $attendingDoctor = '';
                $insuranceState = '';
                $insurance = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Krankheiten / Allergien'),
                    self::getLayoutColumnValue($disease, 10),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Medikamente'),
                    self::getLayoutColumnValue($medication, 10),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Behandelnder Arzt'),
                    self::getLayoutColumnValue($attendingDoctor),
                    self::getLayoutColumnLabel('Versicherungsstatus'),
                    self::getLayoutColumnValue($insuranceState),
                    self::getLayoutColumnLabel('Krankenkasse'),
                    self::getLayoutColumnValue($insurance)
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentMedicalRecordContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                self::getSubContent('Krankenakte', $content),
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
    public function getEditStudentMedicalRecordContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
            ) {

                $Global->POST['Meta']['MedicalRecord']['Disease'] = $tblStudentMedicalRecord->getDisease();
                $Global->POST['Meta']['MedicalRecord']['Medication'] = $tblStudentMedicalRecord->getMedication();
                $Global->POST['Meta']['MedicalRecord']['AttendingDoctor'] = $tblStudentMedicalRecord->getAttendingDoctor();
                $Global->POST['Meta']['MedicalRecord']['Insurance']['State'] = $tblStudentMedicalRecord->getInsuranceState();
                $Global->POST['Meta']['MedicalRecord']['Insurance']['Company'] = $tblStudentMedicalRecord->getInsurance();

                $Global->savePost();
            }
        }

        return $this->getEditStudentMedicalRecordTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentMedicalRecordForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentMedicalRecordTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Hospital() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentMedicalRecordForm(TblPerson $tblPerson = null)
    {

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextArea('Meta[MedicalRecord][Disease]', 'Krankheiten / Allergien', 'Krankheiten / Allergien', new Heart())
                    , 4),
                    new FormColumn(
                        new TextArea('Meta[MedicalRecord][Medication]', 'Medikamente', 'Medikamente', new Medicine())
                    , 4),
                    new FormColumn(array(
                        new TextField('Meta[MedicalRecord][AttendingDoctor]', 'Name', 'Behandelnder Arzt',
                            new Stethoscope()),
                        new SelectBox('Meta[MedicalRecord][Insurance][State]', 'Versicherungsstatus', TblStudentMedicalRecord::getInsuranceStateArray(), new Lock()),
                        new AutoCompleter('Meta[MedicalRecord][Insurance][Company]', 'Krankenkasse', 'Krankenkasse', array(), new Shield())
                    ), 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentMedicalRecordContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentMedicalRecordContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}
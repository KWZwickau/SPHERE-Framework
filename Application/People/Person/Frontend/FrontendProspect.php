<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.12.2018
 * Time: 14:19
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendProspect
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendProspect  extends FrontendReadOnly
{
    const TITLE = 'Interessent-Daten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getProspectTitle($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            $showLink = (new Link(new EyeOpen() . ' Anzeigen', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadProspectContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                '',
                array($showLink),
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
    public static function getProspectContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))
                && ($tblProspectAppointment = $tblProspect->getTblProspectAppointment())
                && ($tblProspectReservation = $tblProspect->getTblProspectReservation())
            ) {
                $reservationDate = $tblProspectAppointment->getReservationDate();
                $interviewDate = $tblProspectAppointment->getInterviewDate();
                $trialDate = $tblProspectAppointment->getTrialDate();

                $reservationYear = $tblProspectReservation->getReservationYear();
                $reservationDivision = $tblProspectReservation->getReservationDivision();
                $serviceTblTypeOptionA = $tblProspectReservation->getServiceTblTypeOptionA()
                    ? $tblProspectReservation->getServiceTblTypeOptionA()->getName() : '';
                $serviceTblTypeOptionB = $tblProspectReservation->getServiceTblTypeOptionB()
                    ? $tblProspectReservation->getServiceTblTypeOptionB()->getName() : '';

                $remark = $tblProspect->getRemark();
            } else {
                $reservationDate = '';
                $interviewDate = '';
                $trialDate = '';

                $reservationYear = '';
                $reservationDivision = '';
                $serviceTblTypeOptionA = '';
                $serviceTblTypeOptionB = '';

                $remark = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Eingangsdatum'),
                    self::getLayoutColumnValue($reservationDate),
                    self::getLayoutColumnLabel('Schuljahr'),
                    self::getLayoutColumnValue($reservationYear),
                    self::getLayoutColumnEmpty(4),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Aufnahmegespräch'),
                    self::getLayoutColumnValue($interviewDate),
                    self::getLayoutColumnLabel('Klassenstufe'),
                    self::getLayoutColumnValue($reservationDivision),
                    self::getLayoutColumnEmpty(4),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Schnuppertag'),
                    self::getLayoutColumnValue($trialDate),
                    self::getLayoutColumnLabel('Schulart: Option 1'),
                    self::getLayoutColumnValue($serviceTblTypeOptionA),
                    self::getLayoutColumnEmpty(4),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnEmpty(4),
                    self::getLayoutColumnLabel('Schulart: Option 2'),
                    self::getLayoutColumnValue($serviceTblTypeOptionB),
                    self::getLayoutColumnEmpty(4),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Bemerkungen'),
                    self::getLayoutColumnValue($remark, 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditProspectContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
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
    public function getEditProspectContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                $Global->POST['Meta']['Remark'] = $tblProspect->getRemark();

                if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                    $Global->POST['Meta']['Appointment']['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                    $Global->POST['Meta']['Appointment']['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                    $Global->POST['Meta']['Appointment']['TrialDate'] = $tblProspectAppointment->getTrialDate();
                }

                if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                    $Global->POST['Meta']['Reservation']['Year'] = $tblProspectReservation->getReservationYear();
                    $Global->POST['Meta']['Reservation']['Division'] = $tblProspectReservation->getReservationDivision();
                    $Global->POST['Meta']['Reservation']['SchoolTypeOptionA'] = (
                    $tblProspectReservation->getServiceTblTypeOptionA()
                        ? $tblProspectReservation->getServiceTblTypeOptionA()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Reservation']['SchoolTypeOptionB'] = (
                    $tblProspectReservation->getServiceTblTypeOptionB()
                        ? $tblProspectReservation->getServiceTblTypeOptionB()->getId()
                        : 0
                    );
                }
                $Global->savePost();
            }
        }

        return $this->getEditProspectTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditProspectForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditProspectTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Tag() . ' ' . self::TITLE, 'der Person'
                . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '') . ' bearbeiten')
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditProspectForm(TblPerson $tblPerson = null)
    {

        $reservationYearAll = array();
        $tblProspectReservationAll = Prospect::useService()->getProspectReservationAll();
        if ($tblProspectReservationAll){
            foreach ($tblProspectReservationAll as $tblProspectReservation){
                if ($tblProspectReservation->getReservationYear()) {
                    if (!in_array($tblProspectReservation->getReservationYear(), $reservationYearAll)){
                        array_push($reservationYearAll, $tblProspectReservation->getReservationYear());
                    }
                }
            }
        }

        $tblTypeAll = Type::useService()->getTypeAll();

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Termine', array(
                            new DatePicker('Meta[Appointment][ReservationDate]', 'Eingangsdatum',
                                'Eingangsdatum',
                                new Calendar()
                            ),
                            new DatePicker('Meta[Appointment][InterviewDate]', 'Aufnahmegespräch',
                                'Aufnahmegespräch',
                                new Calendar()
                            ),
                            new DatePicker('Meta[Appointment][TrialDate]', 'Schnuppertag', 'Schnuppertag',
                                new Calendar()
                            ),
                        ), Panel::PANEL_TYPE_INFO)
                    ), 3),
                    new FormColumn(array(
                        new Panel('Voranmeldung für', array(
                            new AutoCompleter('Meta[Reservation][Year]', 'Schuljahr', 'Schuljahr', $reservationYearAll),
                            new TextField('Meta[Reservation][Division]', 'Klassenstufe', 'Klassenstufe'),
                            new SelectBox('Meta[Reservation][SchoolTypeOptionA]', 'Schulart: Option 1',
                                array('{{ Name }} {{ Description }}' => $tblTypeAll), new Education()),
                            new SelectBox('Meta[Reservation][SchoolTypeOptionB]', 'Schulart: Option 2',
                                array('{{ Name }} {{ Description }}' => $tblTypeAll), new Education()),
                        ), Panel::PANEL_TYPE_INFO)
                    ), 4),
                    new FormColumn(array(
                        new Panel('Sonstiges', array(
                            new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO)
                    ), 5),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveProspectContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelProspectContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        ));
    }
}
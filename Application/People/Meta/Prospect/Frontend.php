<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
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
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Prospect
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

        $Stage->setDescription(
            new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        $tblTypeAll = Type::useService()->getTypeAll();
        array_push($tblTypeAll, new TblType());

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta'] )) {
                /** @var TblProspect $tblProspect */
                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $Global->POST['Meta']['Remark'] = $tblProspect->getRemark();
                    /** @var TblProspectAppointment $tblProspectAppointment */
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $Global->POST['Meta']['Appointment']['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                        $Global->POST['Meta']['Appointment']['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                        $Global->POST['Meta']['Appointment']['TrialDate'] = $tblProspectAppointment->getTrialDate();
                    }
                    /** @var TblProspectReservation $tblProspectReservation */
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
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
        }

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

        $Stage->setContent(
            Prospect::useService()->createMeta(
                (new Form(array(
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
                    )),
                ), new Primary('Speichern', new Save())
                ))->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'), $tblPerson, $Meta, $Group)
        );

        return $Stage;
    }
}

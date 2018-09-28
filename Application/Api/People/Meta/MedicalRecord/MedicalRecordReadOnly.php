<?php
namespace SPHERE\Application\Api\People\Meta\MedicalRecord;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Extension;

class MedicalRecordReadOnly extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openOverViewModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverOverViewModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalMedicalRecordOverViewReciever');
    }


    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenOverViewModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverOverViewModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            MedicalRecordReadOnly::API_TARGET => 'openOverViewModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openOverViewModal($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson){
            $HeadPanel = new Warning('Person wurde nicht gefunden');
            $WellDisease = '';
            $WellMedication = '';
            $WellAttendingDoctor = '';
        } else {
            $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO);
            $WellDisease = new Well(new Title('Krankheiten/Allergien').'-');
            $WellMedication = new Well(new Title('Medikamente').'-');
            $WellAttendingDoctor = new Well(new Title('Behandelnder Arzt').'-');
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){

                $WellDisease = new Well(new Title('Krankheiten/Allergien')
                    .($tblMedicalRecord->getDisease() ? nl2br($tblMedicalRecord->getDisease()) : '-')
                );
                $WellMedication = new Well(new Title('Medikamente')
                    .($tblMedicalRecord->getMedication() ? nl2br($tblMedicalRecord->getMedication()) : '-')
                );
                $WellAttendingDoctor = new Well(new Title('Behandelnder Arzt')
                    .($tblMedicalRecord->getAttendingDoctor() ? $tblMedicalRecord->getAttendingDoctor() : '-')
                );
            }
        }


        return new Title('Krankenakte')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $HeadPanel
                            , 12),
                        new LayoutColumn(
                            $WellDisease
                            , 12),
                        new LayoutColumn(
                            $WellMedication
                            , 7),
                        new LayoutColumn(
                            $WellAttendingDoctor
                            , 5),
                    ))
                )
            );
    }
}
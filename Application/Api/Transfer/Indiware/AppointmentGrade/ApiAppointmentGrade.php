<?php
namespace SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

class ApiAppointmentGrade extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('reloadTaskSelect');

        return $Dispatcher->callMethod($Method);
    }

    public static function receiverFormSelect($Content = '')
    {

        return new BlockReceiver($Content);
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineCreateTaskSelect(AbstractReceiver $Receiver)
    {
        $FieldPipeline = new Pipeline();
        $FieldEmitter = new ServerEmitter($Receiver, ApiAppointmentGrade::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiAppointmentGrade::API_TARGET => 'reloadTaskSelect'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Notenaufträge wurden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $YearId
     * @return \SPHERE\Common\Frontend\Form\Repository\AbstractField|SelectBox
     */
    public function reloadTaskSelect($YearId = null)
    {

        if($YearId === null){
            return (new SelectBox('TaskId', 'Auswahl Notenauftrag '.new ToolTip(new Info(),
                    'Aus welchem Notenauftrag sollen die Noten ausgelesen werden?'), array()))->setRequired();
        }
        if(($tblYear = Term::useService()->getYearById($YearId))){

            $tblTestTypeAppointed = Evaluation::useService()->getTestTypeByIdentifier(TblTestType::APPOINTED_DATE_TASK);
            $tblTaskListAppointed = Evaluation::useService()->getTaskAllByTestType($tblTestTypeAppointed);
            $tblTaskList = array(array());
            $YearId = null;
            if ($tblTaskListAppointed) {
                foreach ($tblTaskListAppointed as $tblTask) {
                    if ($tblTask->getServiceTblYear() && $tblTask->getServiceTblYear()->getId() == $tblYear->getId()) {
                        $tblTaskList[$tblTask->getId()] = $tblTask->getDate().' '.$tblTask->getName();
                    }
                }
            }

            return (new SelectBox('TaskId', 'Auswahl Notenauftrag '.new ToolTip(new Info(),
                    'Aus welchem Notenauftrag sollen die Noten ausgelesen werden?'), $tblTaskList))->setRequired();
        }
        return (new SelectBox('TaskId', 'Auswahl Notenauftrag '.new ToolTip(new Info(),
                'Aus welchem Notenauftrag sollen die Noten ausgelesen werden?'), array()))->setRequired();
    }
}
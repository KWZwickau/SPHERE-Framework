<?php
namespace SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
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
        $Dispatcher->registerMethod('reloadPeriodSelect');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverFormSelect(string $Content = '', string $Identifier = ''): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineCreateTaskSelect(AbstractReceiver $Receiver): Pipeline
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
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineCreatePeriodSelect(AbstractReceiver $Receiver): Pipeline
    {
        $FieldPipeline = new Pipeline();
        $FieldEmitter = new ServerEmitter($Receiver, ApiAppointmentGrade::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiAppointmentGrade::API_TARGET => 'reloadPeriodSelect'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Schulhalbjahre wurden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $YearId
     * @return SelectBox
     */
    public function reloadTaskSelect($YearId = null): SelectBox
    {
        if($YearId === null){
            return (new SelectBox('TaskId', 'Auswahl Notenauftrag '.new ToolTip(new Info(),
                'Aus welchem Notenauftrag sollen die Noten ausgelesen werden?'), array()))->setRequired();
        }
        if(($tblYear = Term::useService()->getYearById($YearId))){
            if (($tblTaskList = Grade::useService()->getAppointedDateTaskListByYear($tblYear))) {
                foreach ($tblTaskList as $tblTask) {
                    $tblTaskList[$tblTask->getId()] = $tblTask->getDateString() . ' ' . $tblTask->getName();
                }
            }

            return (new SelectBox('TaskId', 'Auswahl Notenauftrag '.new ToolTip(new Info(),
                'Aus welchem Notenauftrag sollen die Noten ausgelesen werden?'), $tblTaskList))->setRequired();
        }

        return (new SelectBox('TaskId', 'Auswahl Notenauftrag '.new ToolTip(new Info(),
            'Aus welchem Notenauftrag sollen die Noten ausgelesen werden?'), array()))->setRequired();
    }

    /**
     * @param string $SchoolTypeId
     *
     * @return SelectBox
     */
    public function reloadPeriodSelect(string $SchoolTypeId = ''): SelectBox
    {

        $tblType = Type::useService()->getTypeById($SchoolTypeId);

        if(!$tblType){
            return (new SelectBox('Period',
                'Auswahl Schulhalbjahr '.new ToolTip(new Info(),
                    'Indiware benötigt diese Information um den Export zuweisen zu können'),
                array()
            ))->setRequired();
        }
        if($tblType->getName() == TblType::IDENT_BERUFLICHES_GYMNASIUM){
            $PeriodList = array(
                0 => '',
                1 => 'Stufe 12 - 1.Halbjahr',
                2 => 'Stufe 12 - 2.Halbjahr',
                3 => 'Stufe 13 - 1.Halbjahr',
                4 => 'Stufe 13 - 2.Halbjahr'
            );
        }else {
            $PeriodList = array(
                0 => '',
                1 => 'Stufe 11 - 1.Halbjahr',
                2 => 'Stufe 11 - 2.Halbjahr',
                3 => 'Stufe 12 - 1.Halbjahr',
                4 => 'Stufe 12 - 2.Halbjahr'
            );
        }
        return (new SelectBox('Data[Period]',
            'Auswahl Schulhalbjahr '.new ToolTip(new Info(),
                'Indiware benötigt diese Information um den Export zuweisen zu können'),
            $PeriodList
        ))->setRequired();
    }
}
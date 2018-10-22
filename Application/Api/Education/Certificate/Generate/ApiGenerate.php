<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.10.2018
 * Time: 09:27
 */

namespace SPHERE\Application\Api\Education\Certificate\Generate;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiGenerate
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generate
 */
class ApiGenerate extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('reloadAppointedDateTaskSelect');
        $Dispatcher->registerMethod('reloadBehaviorTaskSelect');

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
    public static function pipelineCreateAppointedDateTaskSelect(AbstractReceiver $Receiver)
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, ApiGenerate::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiGenerate::API_TARGET => 'reloadAppointedDateTaskSelect'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Stichtagsnotenaufträge werden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineCreateBehaviorTaskSelect(AbstractReceiver $Receiver)
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, ApiGenerate::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiGenerate::API_TARGET => 'reloadBehaviorTaskSelect'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Kopfnotenaufträge werden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Frontend\Form\Repository\AbstractField|SelectBox
     */
    public function reloadAppointedDateTaskSelect($Data = array())
    {

        if (isset($Data['Year'])) {
            $tblYear = Term::useService()->getYearById($Data['Year']);
        } else {
            $tblYear = false;
        }

        $tblAppointedDateTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
            $tblYear ? $tblYear : null
        );

        if ($tblAppointedDateTaskListByYear) {
            $tblTask = reset($tblAppointedDateTaskListByYear);

            $_POST['Data']['AppointedDateTask'] = $tblTask ? $tblTask->getId() : 0;
        }

        return new SelectBox('Data[AppointedDateTask]', 'Stichtagsnotenauftrag',
            array('{{ Date }} {{ Name }}' => $tblAppointedDateTaskListByYear));
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Frontend\Form\Repository\AbstractField|SelectBox
     */
    public function reloadBehaviorTaskSelect($Data = array())
    {

        if (isset($Data['Year'])) {
            $tblYear = Term::useService()->getYearById($Data['Year']);
        } else {
            $tblYear = false;
        }

        $tblBehaviorTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'),
            $tblYear ? $tblYear : null
        );

        if ($tblBehaviorTaskListByYear) {
            $tblTask = reset($tblBehaviorTaskListByYear);

            $_POST['Data']['BehaviorTask'] = $tblTask ? $tblTask->getId() : 0;
        }

        return new SelectBox('Data[BehaviorTask]', 'Kopfnotenauftrag',
            array('{{ Date }} {{ Name }}' => $tblBehaviorTaskListByYear));
    }
}
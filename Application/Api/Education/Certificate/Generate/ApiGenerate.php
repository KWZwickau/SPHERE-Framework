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
use SPHERE\Application\Education\Certificate\Generate\Frontend;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
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

        $Dispatcher->registerMethod('openCertificateModal');
        $Dispatcher->registerMethod('setCertificate');
        $Dispatcher->registerMethod('changeCertificate');

        return $Dispatcher->callMethod($Method);
    }

    public static function receiverFormSelect($Content = '')
    {

        return new BlockReceiver($Content);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverContent($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
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
     * @param $PrepareId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCertificateModal($PrepareId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCertificateModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PrepareId' => $PrepareId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PrepareId
     *
     * @return Pipeline
     */
    public static function pipelineSetCertificate($PrepareId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'setCertificate',
        ));
        $emitter->setPostPayload(array(
            'PrepareId' => $PrepareId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $certificateId
     * @param $certificateTypeId
     * @param $personId
     *
     * @return Pipeline
     */
    public static function pipelineChangeCertificate($certificateId, $certificateTypeId, $personId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverContent('', 'ChangeCertificate_' . $personId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeCertificate',
        ));
        $ModalEmitter->setPostPayload(array(
            'CertificateId' => $certificateId,
            'CertificateTypeId' => $certificateTypeId,
            'PersonId' => $personId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array $Data
     *
     * @return AbstractField|SelectBox
     */
    public function reloadAppointedDateTaskSelect($Data = array())
    {
        if (isset($Data['Year'])) {
            $tblYear = Term::useService()->getYearById($Data['Year']);
        } else {
            $tblYear = false;
        }

        $tblAppointedDateTaskListByYear = false;
        if ($tblYear) {
            $tblAppointedDateTaskListByYear = Grade::useService()->getAppointedDateTaskListByYear($tblYear);
        }

        if ($tblAppointedDateTaskListByYear) {
            $tblTask = reset($tblAppointedDateTaskListByYear);

            $_POST['Data']['AppointedDateTask'] = $tblTask ? $tblTask->getId() : 0;
        }

        return new SelectBox('Data[AppointedDateTask]', 'Stichtagsnotenauftrag', array('{{ DateString }} {{ Name }}' => $tblAppointedDateTaskListByYear));
    }

    /**
     * @param array $Data
     *
     * @return AbstractField|SelectBox
     */
    public function reloadBehaviorTaskSelect($Data = array())
    {
        if (isset($Data['Year'])) {
            $tblYear = Term::useService()->getYearById($Data['Year']);
        } else {
            $tblYear = false;
        }

        $tblBehaviorTaskListByYear = false;
        if ($tblYear) {
            $tblBehaviorTaskListByYear = Grade::useService()->getBehaviorTaskListByYear($tblYear);
        }

        if ($tblBehaviorTaskListByYear) {
            $tblTask = reset($tblBehaviorTaskListByYear);

            $_POST['Data']['BehaviorTask'] = $tblTask ? $tblTask->getId() : 0;
        }

        return new SelectBox('Data[BehaviorTask]', 'Kopfnotenauftrag', array('{{ DateString }} {{ Name }}' => $tblBehaviorTaskListByYear));
    }

    /**
     * @param $PrepareId
     *
     * @return String
     */
    public function openCertificateModal($PrepareId): string
    {
        $panel = '';
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $panel = new Panel(
                'Zeugnis generieren',
                array(
                    'Name: ' . $tblPrepare->getName(),
                    'Kurs: ' . $tblDivisionCourse->getDisplayName()
                ),
                Panel::PANEL_TYPE_INFO
            );
        }

        $tblCertificateAllByType = array();
        if ($tblPrepare
            && ($tblCertificateType = $tblPrepare->getCertificateType())

        ) {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            $tblCertificateAllStandard = Generator::useService()->getCertificateAllByConsumerAndCertificateType(null, $tblCertificateType);
            $tblCertificateAllConsumer = Generator::useService()->getCertificateAllByConsumerAndCertificateType($tblConsumer, $tblCertificateType);
            if ($tblCertificateAllConsumer) {
                $tblCertificateAllByType = array_merge($tblCertificateAllByType, $tblCertificateAllConsumer);
            }
            if ($tblCertificateAllStandard) {
                $tblCertificateAllByType = array_merge($tblCertificateAllByType, $tblCertificateAllStandard);
            }
        }

        $selectBox = new SelectBox(
            'Certificate',
            '',
            array(
                '{{ serviceTblConsumer.Acronym }} {{ Name }} {{Description}}' => $tblCertificateAllByType
            ),
            null,
            true,
            null
        );

        return
            new Title('Zeugnis generieren - Zeugnisvorlagen des gesamten Kurses auswählen')
            . $panel
            . '<br>'
            . new Warning(
                'Es werden alle Zeugnisvorlagen auf den gewählten Wert vorausgefüllt. Die Daten müssen anschließend noch gespeichert werden.',
                new Exclamation()
            )
            . new Well(new Form(new FormGroup(array(
                new FormRow(array(
                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            '<table><tr><td style="width:120px">&nbsp;Zeugnisvorlage</td><td style="width:700px">' . $selectBox . '</td></tr></table>'
                    ))))),
                )),
                new FormRow(
                    new FormColumn(
                        new Container('&nbsp;')
                    )
                ),
                new FormRow(
                    new FormColumn(
                        (new Primary('Übernehmen', self::getEndpoint()))->ajaxPipelineOnClick(self::pipelineSetCertificate($PrepareId))
                    )
                )
            ))));
    }

    /**
     * @param $PrepareId
     *
     * @return Danger|string
     */
    public function setCertificate($PrepareId)
    {
        if (!($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            return new Danger('Zeugnis generieren nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $certificateId = $Global->POST['Certificate'];

        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblCertificateType = $tblPrepare->getCertificateType())
        ) {
            $result = '';
            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                foreach ($tblPersonList as $tblPerson) {
                    $result .= self::pipelineChangeCertificate($certificateId, $tblCertificateType->getId(), $tblPerson->getId());
                }
            }

            return $result . self::pipelineClose();
        }

        return self::pipelineClose();
    }

    /**
     * @param $CertificateId
     * @param $CertificateTypeId
     * @param $PersonId
     *
     * @return SelectBox
     */
    public function changeCertificate($CertificateId, $CertificateTypeId, $PersonId): SelectBox
    {
        return (new Frontend())->getCertificateSelectBox($PersonId, $CertificateId, $CertificateTypeId);
    }
}
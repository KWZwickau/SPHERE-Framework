<?php

namespace SPHERE\Application\Api\Education\Prepare;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
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
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPrepare
 *
 * @package SPHERE\Application\Api\Education\Prepare
 */
class ApiPrepare extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('openInformationModal');
        $Dispatcher->registerMethod('setInformation');
        $Dispatcher->registerMethod('changeInformation');

        $Dispatcher->registerMethod('loadDiplomaAverage');

        return $Dispatcher->callMethod($Method);
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
     * @param $PrepareId
     * @param $Key
     * @param $CertificateName
     *
     * @return Pipeline
     */
    public static function pipelineOpenInformationModal($PrepareId, $Key, $CertificateName): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openInformationModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PrepareId' => $PrepareId,
            'Key' => $Key,
            'CertificateName' => $CertificateName
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PrepareId
     * @param $Key
     * @param $CertificateName
     *
     * @return Pipeline
     */
    public static function pipelineSetInformation($PrepareId, $Key, $CertificateName): Pipeline
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'setInformation',
        ));
        $emitter->setPostPayload(array(
            'PrepareId' => $PrepareId,
            'Key' => $Key,
            'CertificateName' => $CertificateName
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $informationId
     * @param $personId
     * @param $prepareStudentId
     * @param $Key
     * @param $CertificateName
     *
     * @return Pipeline
     */
    public static function pipelineChangeInformation($informationId, $personId, $prepareStudentId, $Key, $CertificateName)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverContent('', 'ChangeInformation_' . $Key . '_' . $personId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeInformation',
        ));
        $ModalEmitter->setPostPayload(array(
            'InformationId' => $informationId,
            'PersonId' => $personId,
            'PrepareStudentId' => $prepareStudentId,
            'Key' => $Key,
            'CertificateName' => $CertificateName
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PrepareId
     * @param $Key
     * @param $CertificateName
     *
     * @return String
     */
    public function openInformationModal($PrepareId, $Key, $CertificateName): string
    {
        $panel = '';
        $FormField = Generator::useService()->getFormField();
        $KeyFullName = 'Content.Input.' . $Key;
        $label = '';
        $fieldType = $FormField[$KeyFullName] ?? false;
        $inputField = false;
        if (($tblPrepare = \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $FormLabel = Generator::useService()->getFormLabel();
            $label = $FormLabel[$KeyFullName] ?? '';

            $panel = new Panel(
                'Zeugnisvorbereitung',
                array(
                    0 => $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                    1 => $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getDisplayName()
                ),
                Panel::PANEL_TYPE_INFO
            );

            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $CertificateName;
            if (class_exists($CertificateClass)) {
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass();

                if ($fieldType) {
                    $method = 'selectValues' . $Key;
                    if ($fieldType == 'SelectBox' && method_exists($Certificate, $method)) {
                        $list = call_user_func_array(array($Certificate, $method), array());
                        $inputField = new SelectBox(
                            'Information',
                            '',
                            $list
                        );
                    } elseif ($fieldType == 'DatePicker') {
                        $inputField = new DatePicker('Information', '', '');
                    } elseif ($fieldType == 'TextField') {
                        $inputField = new TextField('Information', '', '');
                    }
                }
            }
        }

        return
            new Title('Zeugnisvorbereitung - "' . $label . '" der gesamten Klasse auswählen')
            . $panel
            . '<br>'
            . new Warning(
                'Es werden alle "' . $label . '" auf den gewählten Wert vorausgefüllt. Die Daten müssen anschließend noch gespeichert werden.',
                new Exclamation()
            )
            . ($inputField ? new Well((new Form(new FormGroup(array(
                new FormRow(
                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        '<table><tr><td style="width:200px">&nbsp;' . $label . '</td><td style="width:620px">'
                        . $inputField . '</td></tr></table>'
                    )))))
                ),
                new FormRow(
                    new FormColumn(
                        new Container('&nbsp;')
                    )
                ),
                new FormRow(
                    new FormColumn(
                        (new Primary('Übernehmen', self::getEndpoint()))->ajaxPipelineOnClick(self::pipelineSetInformation($PrepareId, $Key, $CertificateName))
                    )
                )
            ))))->disableSubmitAction())
                : 'Kein passendes Eingabefeld verfügbar!');
    }

    /**
     * @param $PrepareId
     * @param $Key
     * @param $CertificateName
     *
     * @return Danger|string
     */
    public function setInformation($PrepareId, $Key, $CertificateName)
    {
        if (!($tblPrepare = \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->getPrepareById($PrepareId))) {
            return new Danger('Zeugnisvorbereitung nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $informationId = $Global->POST['Information'];

        $result = '';

        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            &&($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                $tblPrepareStudent = \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->getPrepareStudentBy(
                    $tblPrepare, $tblPerson
                );
                $result .= self::pipelineChangeInformation(
                    $informationId,
                    $tblPerson->getId(),
                    $tblPrepareStudent ? $tblPrepareStudent->getId() : 0,
                    $Key,
                    $CertificateName
                );
            }
        }

        return $result === '' ? self::pipelineClose() : $result . self::pipelineClose();
    }

    /**
     * @param $InformationId
     * @param $PersonId
     * @param $PrepareStudentId
     * @param $Key
     * @param $CertificateName
     *
     * @return SelectBox|DatePicker|string
     */
    public function changeInformation($InformationId, $PersonId, $PrepareStudentId, $Key, $CertificateName)
    {
        $global = $this->getGlobal();
        $global->POST['Data'][$PrepareStudentId][$Key] = $InformationId;
        $global->savePost();

        $FormField = Generator::useService()->getFormField();
        $KeyFullName = 'Content.Input.' . $Key;
        $fieldType = $FormField[$KeyFullName] ?? false;

        $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $CertificateName;
        if (class_exists($CertificateClass)) {
            /** @var Certificate $Certificate */
            $Certificate = new $CertificateClass(null);

            if ($fieldType) {
                $method = 'selectValues' . $Key;
                if ($fieldType == 'SelectBox' && method_exists($Certificate, $method)) {
                    $list = call_user_func_array(array($Certificate, $method), array());
                    return new SelectBox(
                        'Data[' . $PrepareStudentId . '][' . $Key . ']',
                        '',
                        $list
                    );
                } elseif ($fieldType == 'DatePicker') {
                    return new DatePicker('Data[' . $PrepareStudentId . '][' . $Key . ']', '', '');
                } elseif ($fieldType == 'TextField') {
                    return new TextField('Data[' . $PrepareStudentId . '][' . $Key . ']', '', '');
                }
            }
        }

        return '';
    }

    /**
     * @param $PrepareStudentId
     * @param $Key
     * @param $Jn
     * @param $SchoolTypeShortName
     *
     * @return Pipeline
     */
    public static function pipelineLoadDiplomaAverage($PrepareStudentId, $Key, $Jn, $SchoolTypeShortName): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverContent('', 'Diploma_' . $Key . '_' . $PrepareStudentId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDiplomaAverage',
        ));
        $ModalEmitter->setPostPayload(array(
            'PrepareStudentId' => $PrepareStudentId,
            'Key' => $Key,
            'Jn' => $Jn,
            'SchoolTypeShortName' => $SchoolTypeShortName
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PrepareStudentId
     * @param $Key
     * @param $Jn
     * @param $SchoolTypeShortName
     * @param $Data
     * @return TextField|string
     */
    public function loadDiplomaAverage($PrepareStudentId, $Key, $Jn, $SchoolTypeShortName, $Data)
    {
        $calc = '';
        if (isset($Data[$PrepareStudentId])) {
            if (is_numeric($Jn)) {
                $gradeList['JN'] = intval($Jn);
            }
            foreach ($Data[$PrepareStudentId] as $identifier => $value) {
                if ($value) {
                    $gradeList[$identifier] = intval($value);
                }
            }
            $calc = \SPHERE\Application\Education\Certificate\Prepare\Prepare::useService()->getCalcDiplomaGrade($gradeList, $Key, $SchoolTypeShortName != 'OS');
        }

        if ($Key == 'Average')
        {
            return $calc;
        } else {
            return \SPHERE\Application\Education\Certificate\Prepare\Prepare::useFrontend()->getTextFieldCertificateGrade(
                'Data[' . $PrepareStudentId . ']', $PrepareStudentId, $calc
            );
        }
    }
}
<?php

namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Window\Error;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiTransfer
 *
 * @package SPHERE\Application\Api\People\Meta
 */
class ApiTransfer extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            'SPHERE\Application\People\Meta\Student/Service/Entity',
            'SPHERE\Application\People\Meta\Student\Service\Entity'
        );
    }

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openModal');
        $Dispatcher->registerMethod('saveModal');
        $Dispatcher->registerMethod('closeModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param AbstractField $Field
     *
     * @return BlockReceiver
     */
    public static function receiverField(AbstractField $Field)
    {
        return (new BlockReceiver($Field))->setIdentifier('Field-Target-'.crc32($Field->getName()));
    }

    /**
     * @param AbstractField $Field
     *
     * @return ModalReceiver
     */
    public static function receiverModal(AbstractField $Field)
    {
        return (new ModalReceiver( /*$Field->getName()*/
            'Massenänderung', new Close()))->setIdentifier('Field-Modal-'.crc32($Field->getName()));
    }

    public static function pipelineOpen(AbstractField $Field, $PersonId, $StudentTransferTypeIdentifier)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField'                    => base64_encode(serialize($Field)),
            'PersonId'                      => $PersonId,
            'StudentTransferTypeIdentifier' => $StudentTransferTypeIdentifier,
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineSave(AbstractField $Field, $PersonId, $StudentTransferTypeIdentifier)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField'                    => base64_encode(serialize($Field)),
            'PersonId'                      => $PersonId,
            'StudentTransferTypeIdentifier' => $StudentTransferTypeIdentifier,
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineClose(AbstractField $Field, $CloneField)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverField($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'closeModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field)),
            'CloneField' => $CloneField
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal($Field)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param AbstractField $modalField
     * @param null          $PersonId
     * @param string        $StudentTransferTypeIdentifier
     * @param string        $Service
     *
     * @return Layout|string
     */
    public function openModal($modalField, $PersonId = null, $StudentTransferTypeIdentifier, $Service = '')
    {

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        $CloneField = $this->cloneField($Field, 'CloneField', 'Test');
        return (new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(array(
                        $CloneField
                    ))
                )
            )
            , new Primary('Ändern'), '', array('Service' => $Service)))
            ->ajaxPipelineOnSubmit(self::pipelineSave($Field, $PersonId, $StudentTransferTypeIdentifier));
    }

    /**
     * @param AbstractField $Field
     * @param string        $Name
     * @param null          $Label
     *
     * @return AbstractField|Error
     */
    private function cloneField(AbstractField $Field, $Name = 'CloneField', $Label = null)
    {
        /** @var AbstractField $Field */
        $Reflection = new \ReflectionObject($Field);
        $FieldParameterList = $Reflection->getConstructor()->getParameters();
        // Read Parent Constructor and create Args List
        $Constructor = array();
        /**
         * @var int                  $Position
         * @var \ReflectionParameter $Parameter
         */
        foreach ($FieldParameterList as $Position => $Parameter) {
            if ($Reflection->hasMethod('get'.$Parameter->getName())) {
                $Constructor[$Position] = $Field->{'get'.$Parameter->getName()}();
            } elseif ($Parameter->isDefaultValueAvailable()) {
                $Constructor[$Position] = $Parameter->getDefaultValue();
            } else {
                if ($Parameter->allowsNull()) {
                    $Constructor[$Position] = null;
                } else {
                    $E = new \Exception($Reflection->getName()." Parameter-Definition missmatch. ");
                    return new Error($E->getCode(), $E->getMessage(), false);
                }
            }
        }
        // Replace Field Name
        $Position = array_search('Name', array_column($FieldParameterList, 'name'));
        $Constructor[$Position] = $Name;
        // Replace Field Label
        if ($Label) {
            if (false !== ($Position = array_search('Label', array_column($FieldParameterList, 'name')))) {
                $Constructor[$Position] = $Label;
            }
        }
        // Create new Field
        /** @var AbstractField $NewField */
        $NewField = $Reflection->newInstanceArgs($Constructor);
        // Set Field Value to Parent
        if (preg_match(
            '!(^|&)'.preg_quote($Field->getName()).'=(.*?)(&|$)!is',
            urldecode(http_build_query($this->getGlobal()->REQUEST)),
            $Value
        )) {
            $NewField->setDefaultValue($Value[2], true);
        }
        return $NewField;
    }

    /**
     * @param AbstractField $modalField
     * @param null          $PersonId
     * @param string        $StudentTransferTypeIdentifier
     * @param null          $Meta
     */
    public static function saveModal(
        AbstractField $modalField,
        $PersonId = null,
        $StudentTransferTypeIdentifier,
        $Meta = null
    ) {

        self::useService()->createTransfer($modalField, $Meta, $PersonId, $StudentTransferTypeIdentifier);

    }

    /**
     * @param $modalField
     *
     * @return CloseModal
     */
    public static function closeModal($modalField)
    {
        return new CloseModal(self::receiverModal($modalField));
    }
}
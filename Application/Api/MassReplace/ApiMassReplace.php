<?php

namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Error;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiMassReplace
 *
 * @package SPHERE\Application\Api\People\Meta
 */
class ApiMassReplace extends Extension implements IApiInterface
{
    use ApiTrait;

    const SERVICE_CLASS = 'ServiceClass';
    const SERVICE_METHOD = 'ServiceMethod';
    const USED_FILTER = 'usedFilter';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openModal');
//        $Dispatcher->registerMethod('showFilter');
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
        return (new BlockReceiver($Field))
            ->setIdentifier('Field-Target-'.crc32($Field->getName()));
    }

    /**
     * @param AbstractField $Field
     *
     * @return ModalReceiver
     */
    public static function receiverModal(AbstractField $Field)
    {
        /** @var SelectBox|TextField $Field */
        return (new ModalReceiver(new Bold('Massenänderung ').$Field->getLabel(), new Close()))
            ->setIdentifier('Field-Modal-'.crc32($Field->getName()));
    }

    /**
     * @param $Name
     * @param $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Name, $Content)
    {
        return (new BlockReceiver($Content))->setIdentifier($Name);
    }

    public static function pipelineOpen(AbstractField $Field)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field))
        ));
        $Emitter->setLoadingMessage('Lädt');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineSave(AbstractField $Field)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveModal'
        ));
        $Emitter->setLoadingMessage('Wird bearbeitet');
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
     * @param null          $usedFilter
     * @param null          $Year
     * @param null          $Division
     *
     * @return Layout|string
     */
    public function openModal($modalField, $usedFilter = null, $Year = null, $Division = null)
    {
        if ($usedFilter == null) {
            return new Warning('Filter einstellen!');
        }
        if ($usedFilter == StudentFilter::STUDENT_FILTER) {
            return (new StudentFilter())->getFrontendStudentFilter($modalField, $Year, $Division);
        }

        // miss Filter match
        return new Danger('Filter nicht gefunden!');
    }

    /**
     * @param AbstractField $Field
     * @param string        $Name
     * @param null          $Label
     *
     * @return AbstractField|Error
     */
    public function cloneField(AbstractField $Field, $Name = 'CloneField', $Label = null)
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
        $ParameterList = array();
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
            $ParameterList[$Position] = $Parameter->getName();
        }
        // Replace Field Name
        $Position = array_search('Name', $ParameterList);
        $Constructor[$Position] = $Name;
        // Replace Field Label
        if ($Label) {
            if (false !== ($Position = array_search('Label', $ParameterList))) {
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
     * @param string $ServiceClass
     * @param string $ServiceMethod
     *
     * @return mixed
     */
    public function saveModal(
        $ServiceClass,
        $ServiceMethod
    ) {

        $Reflection = new \ReflectionClass($ServiceClass);
        $MethodParameterList = $Reflection->getMethod($ServiceMethod)->getParameters();

        // Read Parent Constructor and create Args List
        $Constructor = array();
        /**
         * @var int                  $Position
         * @var \ReflectionParameter $Parameter
         */
        foreach ($MethodParameterList as $Position => $Parameter) {
            if (array_key_exists($Parameter->getName(), $this->getGlobal()->POST)) {
                $Constructor[$Position] = $this->getGlobal()->POST[$Parameter->getName()];
            } else {
                $Constructor[$Position] = null;
            }
        }

        $ServiceClass = $Reflection->newInstanceWithoutConstructor();
        return call_user_func_array(array($ServiceClass, $ServiceMethod), $Constructor);
    }

    /**
     * Create Clone and set new Value
     *
     * @param string $modalField
     * @param string $CloneField
     *
     * @return AbstractField
     */
    public function closeModal($modalField, $CloneField)
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        parse_str($Field->getName().'='.$CloneField, $NewValue);
        $Globals = $this->getGlobal();
        $Globals->POST = array_merge_recursive($Globals->POST, $NewValue);
        $Globals->savePost();
        $ReplaceField = $this->cloneField($Field, $Field->getName());
        return $ReplaceField;
    }
}
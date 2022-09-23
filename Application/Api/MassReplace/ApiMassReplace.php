<?php

namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
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
    const USE_FILTER = 'useFilter';

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
        $Dispatcher->registerMethod('loadDivisionsSelectBox');
        $Dispatcher->registerMethod('loadCoreGroupsSelectBox');

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
     * @param null|string   $Node
     *
     * @return ModalReceiver
     */
    public static function receiverModal(AbstractField $Field, $Node = null)
    {
        /** @var SelectBox|TextField $Field */
        return (new ModalReceiver(new Bold('Massenänderung ').new Bold(new Success($Node)).' - '.$Field->getLabel(),
            new Close()))
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

    /**
     * @param AbstractField $Field
     * @param null|string   $Node
     *
     * @return Pipeline
     */
    public static function pipelineOpen(AbstractField $Field, $Node = null)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal($Field, $Node), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openModal'
        ));
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field)),
            'Node' => $Node
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

    public static function pipelineClose(AbstractField $Field, $CloneField, $IsChange = false)
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
        if($IsChange){
            $Pipeline->appendEmitter($Emitter);
        }
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal($Field)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $modalField
     * @param $Node
     * @param $Id
     * @param string $useFilter
     * @param null $StudentEducationId
     * @param null $Data
     *
     * @return string
     */
    public function openModal($modalField, $Node, $Id, string $useFilter = StudentFilter::STUDENT_FILTER, $StudentEducationId = null, $Data = null): string
    {
        if ($useFilter == StudentFilter::STUDENT_FILTER) {
            $tblStudentEducation = false;
            if ($StudentEducationId && $Data === null) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationById($StudentEducationId);
            } elseif ($Id && $Data === null) {
                if (($tblYearList = Term::useService()->getYearByNow())
                    && ($tblPerson = Person::useService()->getPersonById($Id))
                ) {
                    foreach ($tblYearList as $tblYear) {
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                            break;
                        }
                    }
                }
            }

            if ($tblStudentEducation) {
                $Data['Year'] = ($tblYear = $tblStudentEducation->getServiceTblYear()) ? $tblYear->getId() : 0;
                $Data['SchoolType'] = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType()) ? $tblSchoolType->getId() : 0;
                $Data['Level'] = ($tblStudentEducation->getLevel()) ?: '';
                $Data['Division'] = ($tblDivision = $tblStudentEducation->getTblDivision()) ? $tblDivision->getId() : 0;
                $Data['CoreGroup'] = ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) ? $tblCoreGroup->getId() : 0;
            }

            return (new StudentFilter())->getFrontendStudentFilter($modalField, $Node, $Data);
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
        // für die SelectBox2 muss das korrekte Twig geladen werden
        if ($Reflection->getName() == 'SPHERE\Common\Frontend\Form\Repository\Field\SelectBox') {
            /** @var SelectBox $Field */
            /** @var SelectBox $NewField */
            $NewField = $NewField->configureLibrary($Field->getLibrary());
        }
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

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionsSelectBox($Data): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionsSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionsSelectBox',
        ));
        $ModalEmitter->setPostPayload(array(
            'Data' => $Data,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return SelectBox|null
     */
    public function loadDivisionsSelectBox($Data = null): ?SelectBox
    {
        return (new StudentFilter())->loadDivisionsSelectBox($Data);
    }

    /**
     * @param $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadCoreGroupsSelectBox($Data): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CoreGroupsSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCoreGroupsSelectBox',
        ));
        $ModalEmitter->setPostPayload(array(
            'Data' => $Data,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return SelectBox|null
     */
    public function loadCoreGroupsSelectBox($Data = null): ?SelectBox
    {
        return (new StudentFilter())->loadCoreGroupsSelectBox($Data);
    }
}
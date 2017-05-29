<?php

namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
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
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Window\Error;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload extends AbstractConverter implements IApiInterface
{
    use ApiTrait;

    /**
     * @return AbstractReceiver
     */
    public static function receiverUpload()
    {
        return new BlockReceiver();
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineUploadFile(AbstractReceiver $Receiver)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ClientEmitter($Receiver,
            new Headline('Datei wird hochgeladen', 'Bitte warten ...')
            .new ProgressBar(0, 100, 0, 10)
        );
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter($Receiver, Upload::getEndpoint());
        $Emitter->setGetPayload(array(
            Upload::API_TARGET => 'uploadFile'
        ));
        $Pipeline->appendEmitter($Emitter);
        // TODO: Remove
//        $Emitter = new ClientEmitter($Receiver, new Redirect( '/Example/Upload/Excel/Dynamic' ));
//        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineImportFile(AbstractReceiver $Receiver)
    {
    }

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('uploadFile');
        $Dispatcher->registerMethod('importFile');
        $Dispatcher->registerMethod('tableBasket');
        $Dispatcher->registerMethod('openModal');
        $Dispatcher->registerMethod('saveModal');
        $Dispatcher->registerMethod('closeModal');
        return $Dispatcher->callMethod($Method);
    }

    public function uploadFile($Transfer = null)
    {
        sleep(2);
        $File = null;
        if ($Transfer) {
            if (!$Transfer['File'] instanceof UploadedFile) {
                $Transfer = null;
            } else {
                $File = new FilePointer($Transfer['File']->getClientOriginalExtension(), 'TransferTestFile');
                $File->setFileContent(file_get_contents($Transfer['File']->getRealPath()));
                $File->saveFile();
            }
        }
        if ($File) {
            $this->loadFile($File->getRealPath());
            $this->scanFile(0, 2);
        }
        return $this->ConvertScanResult;
    }

    private $ConvertScanResult = array();

    /**
     * @param array $Row
     *
     * @return mixed|void
     */
    public function runConvert($Row)
    {
        $this->ConvertScanResult[] = $Row;
    }

    public function importFile()
    {
    }

    public static function pipelinePlus()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverBasket(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tableBasket',
            'Type'           => 1
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function pipelineMinus()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverBasket(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tableBasket',
            'Type'           => 0
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public static function receiverBasket($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('BasketReceiver');
    }

    public static function tableBasket($Id = null, $Type = null)
    {
        $Ids = array();
        // Service
        // Plus
        if ($Type == 1) {
            $Ids[$Id] = $Id;
        }
        // Minus
        if ($Type == 0 && isset($Ids[$Id])) {
            unset($Ids[$Id]);
        }
        // Select
        $Table = array();
        foreach ($Ids as $IdEntity) {
            $Table[] = array(
                'Artikel' => $IdEntity,
                'Option'  => (new Standard('-', '#', null,
                    array('Id' => $IdEntity)))->ajaxPipelineOnClick(Upload::pipelineMinus())
            );
        }
        // Anzeige
        return new Table($Table, null, array('Artikel' => 'Artikel', 'Option' => 'Option'));
    }

    public static function receiverField(AbstractField $Field)
    {
        return (new BlockReceiver($Field))->setIdentifier('Field-Target-'.crc32($Field->getName()));
    }

    public static function receiverModal(AbstractField $Field)
    {
        return (new ModalReceiver($Field->getName(),
            new Close()))->setIdentifier('Field-Modal-'.crc32($Field->getName()));
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
        $Emitter->setPostPayload(array(
            'modalField' => base64_encode(serialize($Field))
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

    public function openModal($modalField, $Service)
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        $CloneField = $this->cloneField($Field);
        return (new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(array(
                        $CloneField
                    ))
                )
            )
            , new Primary('Ändern'), '', array('Service' => $Service)))
            ->ajaxPipelineOnSubmit(self::pipelineSave($Field));
    }

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
     * Success
     *  - Save Data
     *  - Call Replace & Close
     * Error
     *  - Show Form
     *
     * @param string $modalField
     * @param string $CloneField
     *
     * @return Danger|Success|string
     */
    public function saveModal($modalField, $CloneField, $Service)
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));
        $Error = false;
        // Service
        if ($Service == 'A') {
        } else {
            $Error = true;
        }
        if (!$Error) {
            return new Success('Erfolg :)')
//                .new Redirect('/Example/Upload');
                .self::pipelineClose($Field, $CloneField);
        } else {
            return new Danger('Schade :(');
        }
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
        sleep(2);
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
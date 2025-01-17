<?php
namespace SPHERE\Common\Frontend\Ajax;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;

/**
 * Class Pipeline
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
class Pipeline implements IFrontendInterface
{

    /** @var string $SuccessTitle */
    private $SuccessTitle = '';
    /** @var string $SuccessMessage */
    private $SuccessMessage = '';
    /** @var string $LoadingTitle */
    private $LoadingTitle = '';
    /** @var string $LoadingMessage */
    private $LoadingMessage = '';
    /** @var AbstractEmitter[] $Emitter */
    private $Emitter = array();
    /** @var bool $Sync */
    private $Sync = true;
    /** @var int $RepeatTimeout */
    private $RepeatTimeout = 0;

    /**
     * Pipeline constructor.
     * @param bool $Sync
     */
    public function __construct( $Sync = true )
    {
        $this->Sync = $Sync;
    }

    /**
     * @param string $Title
     * @param string $Message
     * @return $this
     */
    public function setSuccessMessage($Title, $Message = '')
    {
        $this->SuccessTitle = $Title;
        $this->SuccessMessage = $Message;
        return $this;
    }

    /**
     * @param string $Title
     * @param string $Message
     * @return $this
     */
    public function setLoadingMessage($Title, $Message = '')
    {
        $this->LoadingTitle = $Title;
        $this->LoadingMessage = $Message;
        return $this;
    }

    /**
     * @param AbstractEmitter $AbstractEmitter
     * @return $this
     * @deprecated use appendEmitter
     */
    public function addEmitter(AbstractEmitter $AbstractEmitter)
    {
        return $this->appendEmitter( $AbstractEmitter );
    }

    /**
     * @param AbstractEmitter $AbstractEmitter
     * @return $this
     */
    public function appendEmitter(AbstractEmitter $AbstractEmitter)
    {
        array_push($this->Emitter, $AbstractEmitter);
        return $this;
    }

    /**
     * @param AbstractEmitter $AbstractEmitter
     * @return $this
     */
    public function prependEmitter(AbstractEmitter $AbstractEmitter)
    {
        array_unshift($this->Emitter, $AbstractEmitter);
        return $this;
    }

    /**
     * Append Foreign Pipeline
     *
     * WARNING! ONLY STRUCTURE, NOT DATA!
     *
     * @param Pipeline $Pipeline
     * @return $this
     */
    public function appendForeignEmitter(Pipeline $Pipeline)
    {
        $PipelineEmitter = $Pipeline->getEmitter();
        foreach( $PipelineEmitter as $Emitter ) {
            $this->appendEmitter( $Emitter );
        }
        return $this;
    }

    /**
     * @internal
     * @deprecated
     * @param Pipeline $Pipeline
     * @return $this
     */
    public function addPipeline(Pipeline $Pipeline)
    {
        $this->Emitter = array_merge($this->Emitter, $Pipeline->getEmitter());
        return $this;
    }

    /**
     * @return AbstractEmitter[]
     */
    public function getEmitter()
    {
        return $this->Emitter;
    }

    /**
     *
     */
    public function __toString()
    {
        if( $this->RepeatTimeout > 0 ) {
            return (string)'<script type="text/javascript">'
                . 'executeScript(function() {'
                . 'Client.Use("ModAjax", function(){'
                . 'var handlerPipeline = function(){'
                . $this->parseScript()
                . 'window.setTimeout( handlerPipeline, '.(int)$this->RepeatTimeout.'000 ) '
                . '};'
                . 'handlerPipeline();'
                . '});'
                . '});'
                . '</script>';
        } else {
            return (string)'<script type="text/javascript">'
                . 'executeScript(function() {'
                . 'Client.Use("ModAjax", function(){'
                . $this->parseScript()
                . '});'
                . '});'
                . '</script>';
        }
    }

    /**
     * @param Form|AbstractField|null $FrontendElement
     * @return string
     * @throws \Exception
     */
    public function parseScript($FrontendElement = null)
    {
        foreach ($this->Emitter as $Index => $Emitter) {
            $Method = 'GET';
            $Data = array();
            // ServerEmitter
            if ($Emitter instanceof ServerEmitter) {
                $Url = $Emitter->getAjaxUri() . $Emitter->getAjaxGetPayload();
                if ($FrontendElement === null) {
                    /**
                     * NO Element
                     */
                    if (strlen($Emitter->getAjaxPostPayload()) == 2) {
                        $Method = 'GET';
                    } else {
                        $Method = 'POST';
                    }
                    $Data = $Emitter->getAjaxPostPayload();
                } else if( $FrontendElement instanceof AbstractLink ) {
                    $Method = 'POST';
                    /**
                     * Link
                     */
                    if (strlen($Emitter->getAjaxPostPayload()) > 2) {
                        if( !empty( $FrontendElement->getData() ) ) {
                            $Payload = json_decode( $Emitter->getAjaxPostPayload(), true );
                            $Payload = array_replace_recursive( $FrontendElement->getData(), $Payload );
                            $Payload = json_encode( $Payload, JSON_FORCE_OBJECT );
                        } else {
                            $Payload = json_decode($Emitter->getAjaxPostPayload(), true);
                            $Payload = json_encode($Payload, JSON_FORCE_OBJECT);
                        }
                    } else {
                        if( !empty( $FrontendElement->getData() ) ) {
                            $Payload = json_encode($FrontendElement->getData(), JSON_FORCE_OBJECT);
                        } else {
                            $Payload = json_encode($Data, JSON_FORCE_OBJECT);
                        }
                    }
                    $Data = 'var EmitterData = ' . $Payload . '; ';
                    $Data .= 'var Element = jQuery("#' . $FrontendElement->getHash() . '"); ';
                    $Data .= 'var DataSet = Element.closest("form"); ';
                    $Data .= 'if( DataSet.length ) { DataSet = DataSet.serializeArray(); ';
                    $Data .= 'for( var Index in DataSet ) { EmitterData[DataSet[Index]["name"]] = DataSet[Index]["value"]; };';
                    $Data .= '} ';
                    $Data .= 'return EmitterData;';
                } else if( $FrontendElement instanceof Form ) {
                    /**
                     * Form
                     */
                    $Method = 'POST';
                    if (strlen($Emitter->getAjaxPostPayload()) > 2) {
                        if( !empty( $FrontendElement->getData() ) ) {
                            $Payload = json_decode( $Emitter->getAjaxPostPayload(), true );
                            $Payload = array_replace_recursive( $FrontendElement->getData(), $Payload );
                            $Data = json_encode( $Payload, JSON_FORCE_OBJECT );
                        } else {
                            $Data = json_decode( $Emitter->getAjaxPostPayload(), true );
                            $Data = json_encode( $Data, JSON_FORCE_OBJECT );
                        }
                        $Data = 'var EmitterData = ' . $Data . ';';
                        $Data .= 'var FormData = jQuery("form#' . $FrontendElement->getHash() . '").serializeArray();';
                        $Data .= 'for( var Index in FormData ) { EmitterData[FormData[Index]["name"]] = FormData[Index]["value"]; };';
                        $Data .= 'return EmitterData;';
                    } else {
                        if( !empty( $FrontendElement->getData() ) ) {
                            $Data = json_encode( $FrontendElement->getData(), JSON_FORCE_OBJECT );
                        } else {
                            $Data = json_encode( $Data, JSON_FORCE_OBJECT );
                        }
                        $Data = 'var EmitterData = ' . $Data . ';';
                        $Data .= 'var FormData = jQuery("form#' . $FrontendElement->getHash() . '").serializeArray();';
                        $Data .= 'for( var Index in FormData ) { EmitterData[FormData[Index]["name"]] = FormData[Index]["value"]; };';
                        $Data .= 'return EmitterData;';
                    }
                } else if( $FrontendElement instanceof AbstractField ) {
                    /**
                     * Field
                     */
                    $Method = 'POST';
                    if (strlen($Emitter->getAjaxPostPayload()) > 2) {
                        $Data = 'var EmitterData = ' . $Emitter->getAjaxPostPayload() . ';';
                        $Data .= 'var Element = jQuery("[name=\"' . $FrontendElement->getName() . '\"]");';
                        $Data .= 'var DataSet = Element.closest("form");';
                        $Data .= 'if( DataSet.length ) { DataSet = DataSet.serializeArray(); }';
                        $Data .= 'else { DataSet = jQuery.deparam("'. $FrontendElement->getName() .'=" + Element.val() ); }';
                        $Data .= 'for( var Index in DataSet ) { EmitterData[DataSet[Index]["name"]] = DataSet[Index]["value"]; };';
                        $Data .= 'return EmitterData;';
                    } else {
                        $Data = 'var Element = jQuery("[name=\"' . $FrontendElement->getName() . '\"]");';
                        $Data .= 'var DataSet = Element.closest("form");';
                        $Data .= 'if( DataSet.length ) { DataSet = DataSet.serializeArray(); }';
                        $Data .= 'else { DataSet = jQuery.deparam("'. $FrontendElement->getName() .'=" + Element.val() ); }';
                        $Data .= 'return DataSet';
                    }
                }

                $ReceiverList = $Emitter->getAjaxReceiver();
                $ReceiverContext = array();
                foreach ($ReceiverList as $Receiver) {
                    $ReceiverContext[] = $Receiver->getHandler();
                }

                /** @var IBridgeInterface $Template */
                if (!isset($Template)) {
                    $Template = Template::getTwigTemplateString(
                        'jQuery().ModAjax({ Sync: '.($this->Sync ? 'true' : 'false').', Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} );'
                    );
                } else {
                    $Template->setVariable('Callback',
                        'function(){ jQuery().ModAjax({ Sync: '.($this->Sync ? 'true' : 'false').', Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} ); }'
                    );
                    $Template = Template::getTwigTemplateString($Template->getContent());
                }

                // toDO SR
//                /** @var IBridgeInterface $Template */
//                if (!isset($Template)) {
//                    $Template = Template::getTwigTemplate(__DIR__.'/Receiver/ModalReceiver.twig');
//                    $Template->setVariable('Callback',
//                        'function(){ jQuery().ModAjax({ Sync: '.($this->Sync ? 'true' : 'false').', Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} ); }'
//                    );
//                } else {
//                    $Template->setVariable('Callback',
//                        'function(){ jQuery().ModAjax({ Sync: '.($this->Sync ? 'true' : 'false').', Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} ); }'
//                    );
//                    $Template = Template::getTwigTemplateString($Template->getContent());
//                }

                $Template->setVariable('Method', json_encode($Method));
                $Template->setVariable('Url', json_encode($Url));
                $Template->setVariable('Data', json_encode($Data));
                $Template->setVariable('Receiver', json_encode($ReceiverContext));
                $Template->setVariable('Hash', json_encode(sha1(json_encode($Method) . json_encode($Url) . json_encode($Data) . json_encode($ReceiverContext))));
            }
            // ClientEmitter
            if ($Emitter instanceof ClientEmitter || $Emitter instanceof ScriptEmitter) {
                $Content = $Emitter->getContent();
                $ReceiverList = $Emitter->getAjaxReceiver();
                $ReceiverContext = array();
                foreach ($ReceiverList as $Receiver) {
                    $ReceiverContext[] = $Receiver->getHandler();
                }
                /** @var IBridgeInterface $Template */
                if (!isset($Template)) {
                    $Template = Template::getTwigTemplateString(
                        'jQuery().ModAjax({ Sync: '.($this->Sync ? 'true' : 'false').', Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadContent( {{ Content }}, {{ Callback }} );'
                    );
                } else {
                    $Template->setVariable('Callback',
                        'function(){ jQuery().ModAjax({ Sync: '.($this->Sync ? 'true' : 'false').', Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadContent( {{ Content }}, {{ Callback }} ); }'
                    );
                    $Template = Template::getTwigTemplateString($Template->getContent());
                }
                $Template->setVariable('Content', $Content);
                $Template->setVariable('Receiver', json_encode($ReceiverContext));
                $Template->setVariable('Hash', json_encode(sha1(json_encode($Content) . json_encode($ReceiverContext))));
            }
        }
        if (isset($Template)) {
            $Template->setVariable('Callback', json_encode(false));
            return $Template->getContent();
        } else {
            throw new \Exception('Pipeline has no Emitter');
        }
    }

    /**
     * @param AbstractEmitter $Emitter
     * @return string
     */
    private function getNotifyMessage(AbstractEmitter $Emitter)
    {
        if (empty(($LoadingTitle = $Emitter->getLoadingTitle()))) {
            $LoadingTitle = $this->LoadingTitle;
        }
        if (empty(($LoadingMessage = $Emitter->getLoadingMessage()))) {
            $LoadingMessage = $this->LoadingMessage;
        }
        if (empty(($SuccessTitle = $Emitter->getSuccessTitle()))) {
            $SuccessTitle = $this->SuccessTitle;
        }
        if (empty(($SuccessMessage = $Emitter->getSuccessMessage()))) {
            $SuccessMessage = $this->SuccessMessage;
        }

        return 'onLoad: { Title: ' . json_encode($LoadingTitle)
            . ', Message: ' . json_encode($LoadingMessage)
            . ' }, onSuccess: { Title: ' . json_encode($SuccessTitle)
            . ', Message: ' . json_encode($SuccessMessage)
            . ' }';
    }

    /**
     * @param int $Timeout
     *
     * @return $this
     */
    public function repeatPipeline( $Timeout = 1 )
    {
        $this->RepeatTimeout = $Timeout;
        return $this;
    }
}
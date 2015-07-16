<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Script;
use SPHERE\Common\Style;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Configuration;

/**
 * Class Display
 *
 * @package SPHERE\Common\Window
 */
class Display extends Configuration implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $ClusterNavigation */
    private $ClusterNavigation = array();
    /** @var string $ClusterBreadcrumb */
    private $ClusterBreadcrumb = '';
    /** @var array $ApplicationNavigation */
    private $ApplicationNavigation = array();
    /** @var string $ApplicationBreadcrumb */
    private $ApplicationBreadcrumb = '';
    /** @var array $ModuleNavigation */
    private $ModuleNavigation = array();
    /** @var string $ModuleBreadcrumb */
    private $ModuleBreadcrumb = '';
    /** @var array $ServiceNavigation */
    private $ServiceNavigation = array();
    /** @var array $Content */
    private $Content = array();

    /**
     *
     */
    function __construct()
    {

        $this->Template = $this->getTemplate( __DIR__.'/Display.twig' );
    }

    /**
     * @param Link|null $Link
     *
     * @return Display
     */
    public function setClusterNavigation( Link $Link = null )
    {

        if (null === $Link) {
            $this->ClusterNavigation = array();
        } else {
            $this->ClusterNavigation = array( $Link );
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function addClusterNavigation( Link $Link )
    {

        if ($Link->isActive()) {
            $this->ClusterBreadcrumb = $Link->getName()->getValue();
        }
        array_push( $this->ClusterNavigation, $Link );
        return $this;
    }

    /**
     * @param Link|null $Link
     *
     * @return Display
     */
    public function setApplicationNavigation( Link $Link = null )
    {

        if (null === $Link) {
            $this->ApplicationNavigation = array();
        } else {
            $this->ApplicationNavigation = array( $Link );
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function addApplicationNavigation( Link $Link )
    {

        if ($Link->isActive()) {
            $this->ApplicationBreadcrumb = $Link->getName()->getValue();
        }
        array_push( $this->ApplicationNavigation, $Link );
        return $this;
    }

    /**
     * @param Link|null $Link
     *
     * @return Display
     */
    public function setModuleNavigation( Link $Link = null )
    {

        if (null === $Link) {
            $this->ModuleNavigation = array();
        } else {
            $this->ModuleNavigation = array( $Link );
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function addModuleNavigation( Link $Link )
    {

        if ($Link->isActive()) {
            $this->ModuleBreadcrumb = $Link->getName()->getValue();
        }
        array_push( $this->ModuleNavigation, $Link );
        return $this;
    }

    /**
     * @param Link|null $Link
     *
     * @return Display
     */
    public function setServiceNavigation( Link $Link = null )
    {

        if (null === $Link) {
            $this->ServiceNavigation = array();
        } else {
            $this->ServiceNavigation = array( $Link );
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function addServiceNavigation( Link $Link )
    {

        if ($Link->isActive()) {
            $this->ClusterBreadcrumb = $Link->getName()->getValue();
        }
        array_push( $this->ServiceNavigation, $Link );
        return $this;
    }

    /**
     * @param \Exception $Exception
     * @param string     $Name
     *
     * @return Display
     */
    public function setException( \Exception $Exception, $Name = 'Error' )
    {

        $TraceList = '';
        foreach ((array)$Exception->getTrace() as $Index => $Trace) {
            $TraceList .= nl2br( '<br/><samp class="text-info">'
                .( isset( $Trace['type'] ) && isset( $Trace['function'] ) ? '<br/>Method: '.$Trace['type'].$Trace['function'] : '<br/>Method: ' )
                .( isset( $Trace['class'] ) ? '<br/>Class: '.$Trace['class'] : '<br/>Class: ' )
                .( isset( $Trace['file'] ) ? '<br/>File: '.$Trace['file'] : '<br/>File: ' )
                .( isset( $Trace['line'] ) ? '<br/>Line: '.$Trace['line'] : '<br/>Line: ' )
                .'</samp>' );
        }
        $Hit = '<samp class="text-danger"><p class="h6">'.nl2br( $Exception->getMessage() ).'</p><br/>File: '.$Exception->getFile().'<br/>Line: '.$Exception->getLine().'</samp>'.$TraceList;
        $this->addContent( new Error(
            $Exception->getCode() == 0 ? $Name : $Exception->getCode(), $Hit
        ) );
        return $this;
    }

    /**
     * @param $Content
     *
     * @return Display
     */
    public function addContent( $Content )
    {

        array_push( $this->Content, $Content );
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable( 'ManagerStyle', Style::getManager() );
        $this->Template->setVariable( 'ManagerScript', Script::getManager() );

        $this->Template->setVariable( 'NavigationCluster', implode( '', $this->ClusterNavigation ) );
        $this->Template->setVariable( 'BreadcrumbCluster', $this->ClusterBreadcrumb );
        $this->Template->setVariable( 'NavigationApplication', implode( '', $this->ApplicationNavigation ) );
        $this->Template->setVariable( 'BreadcrumbApplication', $this->ApplicationBreadcrumb );
        $this->Template->setVariable( 'NavigationModule', implode( '', $this->ModuleNavigation ) );
        $this->Template->setVariable( 'BreadcrumbModule', $this->ModuleBreadcrumb );
        $this->Template->setVariable( 'NavigationService', implode( '', $this->ServiceNavigation ) );

        $Debug = $this->getDebugger();
        $this->Template->setVariable( 'DebuggerProtocol', $Debug->getProtocol() );
        $this->Template->setVariable( 'DebuggerHost', gethostname() );
        $this->Template->setVariable( 'DebuggerRuntime', $Debug->getRuntime() );

        $this->Template->setVariable( 'Content', implode( '', $this->Content ) );
        $this->Template->setVariable( 'PathBase', $this->getRequest()->getPathBase() );

        return $this->Template->getContent();
    }

    /**
     * @param $Content
     *
     * @return Display
     */
    public function setContent( $Content )
    {

        $this->Content = array( $Content );
        return $this;
    }
}

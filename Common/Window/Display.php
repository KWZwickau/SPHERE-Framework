<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Roadmap\Roadmap;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Script;
use SPHERE\Common\Style;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Debugger\Logger\QueryLogger;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Display
 *
 * @package SPHERE\Common\Window
 */
class Display extends Extension implements ITemplateInterface
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
    public function __construct()
    {

        $this->Template = $this->getTemplate(__DIR__.'/Display.twig');
    }

    /**
     * @param Link|null $Link
     *
     * @return Display
     */
    public function setClusterNavigation(Link $Link = null)
    {

        if (null === $Link) {
            $this->ClusterNavigation = array();
        } else {
            $this->ClusterNavigation = array($Link);
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function addClusterNavigation(Link $Link)
    {

        if (Access::useService()->hasAuthorization($Link->getRoute()->getValue())) {
            if ($Link->isActive()) {
                $this->ClusterBreadcrumb = strip_tags( $Link->getName()->getValue() );
            }
            array_push($this->ClusterNavigation, $Link);
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function setApplicationNavigation(Link $Link = null)
    {

        if (null === $Link) {
            $this->ApplicationNavigation = array();
        } else {
            $this->ApplicationNavigation = array($Link);
        }
        return $this;
    }

    /**
     * @param Link       $Link
     * @param Link\Route $Restriction
     *
     * @return Display
     */
    public function addApplicationNavigation(Link $Link, Link\Route $Restriction = null)
    {

        // Is Link applicable?
        if ($Restriction !== null) {
            if (0 !== strpos($this->getRequest()->getPathInfo(), $Restriction->getValue())) {
                return $this;
            }
        }
        // Is Link suitable?
        $Target = explode('/', $Link->getRoute()->getValue());
        $Current = explode('/', $this->getRequest()->getPathInfo());
        $Branch = array_diff_assoc($Target, $Current);
        if ($Branch !== null) {
            reset($Branch);
            $Branch = key($Branch);
        }

        if ($Branch === null || $Branch >= 2) {
            if (Access::useService()->hasAuthorization($Link->getRoute()->getValue())) {
                if ($Link->isActive()) {
                    $this->ApplicationBreadcrumb = strip_tags( $Link->getName()->getValue() );
                }
                array_push($this->ApplicationNavigation, $Link);
            }
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function setModuleNavigation(Link $Link = null)
    {

        if (null === $Link) {
            $this->ModuleNavigation = array();
        } else {
            $this->ModuleNavigation = array($Link);
        }
        return $this;
    }

    /**
     * @param Link       $Link
     * @param Link\Route $Restriction
     *
     * @return Display
     */
    public function addModuleNavigation(Link $Link, Link\Route $Restriction = null)
    {

        // Is Link applicable?
        if ($Restriction !== null) {
            if (0 !== strpos($this->getRequest()->getPathInfo(), $Restriction->getValue())) {
                return $this;
            }
        }
        // Is Link suitable?
        $Target = explode('/', $Link->getRoute()->getValue());
        $Current = explode('/', $this->getRequest()->getPathInfo());
        $Branch = array_diff_assoc($Target, $Current);
        if ($Branch !== null) {
            reset($Branch);
            $Branch = key($Branch);
        }

        if ($Branch === null || $Branch >= 3) {
            if (Access::useService()->hasAuthorization($Link->getRoute()->getValue())) {
                if ($Link->isActive()) {
                    $this->ModuleBreadcrumb = strip_tags( $Link->getName()->getValue() );
                }
                array_push($this->ModuleNavigation, $Link);
            }
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function setServiceNavigation(Link $Link = null)
    {

        if (null === $Link) {
            $this->ServiceNavigation = array();
        } else {
            $this->ServiceNavigation = array($Link);
        }
        return $this;
    }

    /**
     * @param Link $Link
     *
     * @return Display
     */
    public function addServiceNavigation(Link $Link)
    {

        if (Access::useService()->hasAuthorization($Link->getRoute()->getValue())) {
            if ($Link->isActive()) {
                $this->ClusterBreadcrumb = strip_tags( $Link->getName()->getValue() );
            }
            array_push($this->ServiceNavigation, $Link);
        }
        return $this;
    }

    /**
     * @param \Exception $Exception
     * @param string     $Name
     *
     * @return Display
     */
    public function setException(\Exception $Exception, $Name = 'Error')
    {

        $TraceList = '';
        foreach ((array)$Exception->getTrace() as $Trace) {
            $TraceList .= nl2br('<samp class="text-info small">'
                .( isset( $Trace['type'] ) && isset( $Trace['function'] ) ? 'Method: '.$Trace['type'].$Trace['function'] : 'Method: ' )
                .( isset( $Trace['class'] ) ? '<br/>Class: '.$Trace['class'] : '<br/>Class: ' )
                .( isset( $Trace['file'] ) ? '<br/>File: '.$Trace['file'] : '<br/>File: ' )
                .( isset( $Trace['line'] ) ? '<br/>Line: '.$Trace['line'] : '<br/>Line: ' )
                .'</samp><br/>');
        }
        $Hit = '<hr/><samp class="text-danger"><div class="h6">'.get_class($Exception).'<br/><br/>'.nl2br($Exception->getMessage()).'</div>File: '.$Exception->getFile().'<br/>Line: '.$Exception->getLine().'</samp><hr/><div class="small">'.$TraceList.'</div>';
        $this->addContent(new Error(
            $Exception->getCode() == 0 ? $Name : $Exception->getCode(), $Hit
        ));
        return $this;
    }

    /**
     * @param $Content
     *
     * @return Display
     */
    public function addContent($Content)
    {

        array_push($this->Content, $Content);
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
     * @param bool $NoConnection
     *
     * @return string
     */
    public function getContent($NoConnection = false)
    {

        $this->Template->setVariable('ManagerStyle', Style::getManager());
        $this->Template->setVariable('ManagerScript', Script::getManager());

        $this->Template->setVariable('NavigationCluster', implode('', $this->ClusterNavigation));
        $this->Template->setVariable('BreadcrumbCluster', $this->ClusterBreadcrumb);
        $this->Template->setVariable('NavigationApplication', implode('', $this->ApplicationNavigation));
        $this->Template->setVariable('BreadcrumbApplication', $this->ApplicationBreadcrumb);
        $this->Template->setVariable('NavigationModule', implode('', $this->ModuleNavigation));
        $this->Template->setVariable('BreadcrumbModule', $this->ModuleBreadcrumb);
        $this->Template->setVariable('NavigationService', implode('', $this->ServiceNavigation));

        $tblAccount = Account::useService()->getAccountBySession();
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        $UpdateDate = $tblConsumer->getEntityUpdate();
        // Anzeige nur eingeloggt
        if($tblAccount){
            // Anzeige ab Datum anzeigen (ältere Datenabgleiche nicht bekannt)
            if($UpdateDate >= new \DateTime('01.10.2024')) {
                // Wo darf die Anzeige abgebildet werden: Server DEMO / Entwicklerumgebungen
                switch (strtolower($this->getRequest()->getHost())) {
                    case 'demo.schulsoftware.schule':
                    case '192.168.109.128':
                        $this->Template->setVariable('ConsumerUpdate', '<i>- Stand: '.$UpdateDate->format('d.m.Y').'</i>');
                        break;
                }
            }
        }


        $Debug = $this->getDebugger();
        $Runtime = $Debug->getRuntime();

        if (Debugger::$Enabled) {
            $Debugger = new Accordion();

            $ProtocolBenchmark = $this->getLogger(new BenchmarkLogger())->getLog();
            if (!empty( $ProtocolBenchmark )) {
                $Debugger->addItem('Debugger (Benchmark)',
                    new Listing($this->getLogger(new BenchmarkLogger())->getLog())
                    , true
                );
            }

            $ProtocolError = $this->getLogger(new ErrorLogger())->getLog();
            if (!empty( $ProtocolError )) {
                $Debugger->addItem('Debugger (Error)',
                    new Listing($this->getLogger(new ErrorLogger())->getLog())
                );
            }

            $ProtocolCache = $this->getLogger(new CacheLogger())->getLog();
            if (!empty( $ProtocolCache )) {
                $Debugger->addItem('Debugger (Cache)',
                    new Listing($this->getLogger(new CacheLogger())->getLog())
                );
            }

            $ProtocolQuery = $this->getLogger(new QueryLogger())->getLog();
            if (!empty( $ProtocolQuery )) {
                $Debugger->addItem('Debugger (Query)',
                    new Listing($this->getLogger(new QueryLogger())->getLog())
                );
            }

            $Protocol = $Debug->getProtocol();
            if (!empty( $Protocol )) {
                $Debugger->addItem('Debug Protocol '.$Runtime, $Protocol);
            }
            $this->Template->setVariable('DebuggerProtocol', $Debugger);
        }
        $this->Template->setVariable('DebuggerHost', gethostname());
        $this->Template->setVariable('DebuggerRuntime', $Runtime);
        if (!$NoConnection) {
            $this->Template->setVariable('DebuggerSessionCount', Account::useService()->countSessionAll());
        } else {
            $this->Template->setVariable('DebuggerSessionCount', '-NA-');
        }

        $this->Template->setVariable('Content', implode('', $this->Content));
        $this->Template->setVariable('CacheSlot', (new MemcachedHandler())->getSlot());
        $this->Template->setVariable('MemoryPeak', $this->formatBytes(memory_get_peak_usage()));

        if( function_exists( 'sys_getloadavg' ) ) {
            $CpuLoad = sys_getloadavg();
            $this->Template->setVariable('CpuLoad',
                number_format(100 / (50 * (2 - $CpuLoad[0])) * (50 * ($CpuLoad[0])), 2, ',', '.') . '%');
        } else {
            $this->Template->setVariable('CpuLoad', '');
        }
        $this->Template->setVariable('PathBase', $this->getRequest()->getPathBase());
        if (!$NoConnection) {
            if (($tblConsumer = Consumer::useService()->getConsumerBySession())) {
                $this->Template->setVariable('Consumer',
                    '[' . $tblConsumer->getAcronym() . '] '
                    . Consumer::useService()->getConsumerBySession()->getName()
                );
            }
        }

        $PreSeo = '';
        // Set Depending Information
        switch (strtolower($this->getRequest()->getHost())) {
            case 'demo.schulsoftware.schule':
            case 'ekbodemo.schulsoftware.schule':
            case 'demo.kreda.schule':
                $PreSeo = ' Demo';
            break;
        }

        $this->Template->setVariable('SeoTitle',
            ( !trim(trim($this->getRequest()->getPathInfo(), '/'))
                ? $PreSeo
                : $PreSeo.': '.str_replace('/', ' - ', trim($this->getRequest()->getPathInfo(), '/'))
            )
        );

        // Read RoadMap-Version
        $VersionRelease = null;
        $VersionPreview = null;
        try {
            if (($Cache = $this->getCache(new MemcachedHandler())) instanceof MemcachedHandler) {
                if(
                    (null === ($VersionRelease = $Cache->getValue( 'RoadMap-VersionRelease', __METHOD__ )))
                    ||
                    (null === ($VersionPreview = $Cache->getValue( 'RoadMap-VersionPreview', __METHOD__ )))
                ) {
                    $Map = (new Roadmap())->getRoadmap();
                    $VersionRelease = $Map->getVersionRelease();
                    $Cache->setValue('RoadMap-VersionRelease', $VersionRelease, 3600, __METHOD__);
                    $VersionPreview = $Map->getVersionPreview();
                    $Cache->setValue('RoadMap-VersionPreview', $VersionPreview, 3600, __METHOD__);
                }
            }
        } catch (\Exception $Exception) {
            // Silent fail
        }

        // Set Depending Information
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.schulsoftware.schule':
            case 'ekbo.schulsoftware.schule':
            case 'www.kreda.schule':
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font.svg" alt="Schulsottware" style="height: 40px">
                </a>';
                $this->Template->setVariable('RoadmapVersion', $VersionRelease ? $VersionRelease : 'Roadmap');
                break;
            case 'trial.schulsoftware.schule':
            case 'trial.kreda.schule':
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font.svg" alt="Schulsottware" style="height: 40px">
                </a><a class="navbar-brand" href="/">
                <span class="text-info" style="margin-top: 3px">Trial</span></a>';
                $this->Template->setVariable('RoadmapVersion', $VersionRelease ? $VersionRelease : 'Roadmap');
                break;
            case 'demo.schulsoftware.schule':
            case 'ekbodemo.schulsoftware.schule':
            case 'demo.kreda.schule':
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font-demo.svg" alt="Schulsottware" style="height: 40px">
                </a>';
                $this->Template->setVariable('RoadmapVersion', $VersionPreview ? $VersionPreview : 'Roadmap');
                break;
            case 'nightly.schulsoftware.schule':
            case 'nightly.kreda.schule':
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font_dev_r.svg" alt="Schulsottware" style="height: 40px">
                </a><a class="navbar-brand" href="/">
                <span class="text-danger">Nightly</span></a>';
                $this->Template->setVariable('RoadmapVersion', $VersionPreview ? $VersionPreview : 'Roadmap');
                break;
            case '192.168.240.128':
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font_dev_g.svg" alt="Schulsottware" style="height: 40px">
                </a><a class="navbar-brand" href="/">
                <span class="text-warning" style="padding-top: 11px;">'.$this->getRequest()->getHost().'
                </span></a>';
                $this->Template->setVariable('RoadmapVersion', 'Roadmap');
                break;
            case '192.168.109.128':
            case '192.168.150.128':
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font_dev_o.svg" alt="Schulsottware" style="height: 40px">
                </a><a class="navbar-brand" href="/">
                <span style="padding-top: 11px; color: #ff9944">'.$this->getRequest()->getHost().'
                </span></a>';   // class="text-primary"
                $this->Template->setVariable('RoadmapVersion', 'Roadmap');
                break;
            default:
                $BrandTitle = '<a class="navbar-brand-icon" href="/">
                <img src="/Common/Style/Resource/Schulsoftware-font.svg" alt="Schulsottware" style="height: 40px">
                </a><a class="navbar-brand" href="/">
                <span class="text-warning" style="padding-top: 11px">'.$this->getRequest()->getHost().'
                </span></a>';
                $this->Template->setVariable('RoadmapVersion', 'Roadmap');
        }
        $this->Template->setVariable('BrandSwitch', $BrandTitle);

        return $this->Template->getContent();
    }

    /**
     * @param $Content
     *
     * @return Display
     */
    public function setContent($Content)
    {

        $this->Content = array($Content);
        return $this;
    }

    /**
     * @param     $Bytes
     * @param int $usePrecision
     *
     * @return string
     */
    private function formatBytes($Bytes, $usePrecision = 2)
    {

        $UnitList = array('B', 'KB', 'MB', 'GB', 'TB');

        $Bytes = max($Bytes, 0);
        $Power = floor(( $Bytes ? log($Bytes) : 0 ) / log(1024));
        $Power = min($Power, count($UnitList) - 1);

        $Bytes /= pow(1024, $Power);

        return round($Bytes, $usePrecision).' '.$UnitList[$Power];
    }
}

<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;

class Parser extends Connection
{

    private $YoutrackFilter = 'Beheben in: 1.* Typ: Feature,Bug,Aufgabe Teilsystem: {10*},{03*},{2*} Status: Erfasst,Offen,{In Bearbeitung},Behoben,{Zu besprechen}';
    /** @var bool $Authenticated */
    private $Authenticated = false;

    public function __construct(Credentials $Credentials, $Filter = '')
    {

        if ($Filter) {
            $this->YoutrackFilter = $Filter;
        }
        parent::__construct($Credentials);
    }

    public function getMap()
    {

        $Map = new Map();
        $Issues = $this->getIssues();

        $Response = $this->requestCurl(
            $this->getCredentials()->getHost().'/rest/admin/agile'
        );

        /** @var \SimpleXMLElement $Response */
        $Response = simplexml_load_string($Response);
        $Board = (string)current($Response->xpath('//agileSettings/@id'));

        $Sprints = $Response->xpath('//agileSettings/sprints//id');
        foreach ((array)$Sprints as $Sprint) {
            $Response = $this->requestCurl(
                $this->getCredentials()->getHost().'/rest/admin/agile/'.$Board.'/sprint/'.$Sprint
            );
            $Response = simplexml_load_string($Response);
            /** @var Sprint $Sprint */
            $Sprint = new Sprint($Response);
            // Add Issues to Sprint
            foreach ((array)$Issues as $Issue) {
                $Sprint->addIssue($Issue);
            }
            // Add Sprint to Map
            if (count($Sprint->getIssues())) {
                $Map->addSprint($Sprint);
            }
        }

        return $Map;
    }

    /**
     * @return Issue[]
     */
    private function getIssues()
    {

        $Url = $this->getCredentials()->getHost()
            .'/rest/issue/byproject/KREDA'
            .'?filter='.urlencode($this->YoutrackFilter)
            .'&max='.urlencode('1000');
        $Response = $this->requestCurl($Url);

        $Key = md5($Url);
        $Cache = $this->getCache(new MemcachedHandler(), 'Memcached');
        if (!( $Result = $Cache->getValue($Key, __METHOD__) )) {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Roadmap (Issues): '.$Url);

            /** @var \SimpleXMLElement $Response */
            $Response = simplexml_load_string($Response);
            $Issues = $Response->xpath('//issues/issue');

            /** @var Issue[] $Result */
            $Result = array();
            foreach ((array)$Issues as $Issue) {
                $Result[] = new Issue($Issue);
            }

            $Cache->setValue($Key, $Result, ( 60 * 60 * 1 ), __METHOD__);
        } else {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Roadmap (Cache:Issues): '.$Url);
        }
        return $Result;
    }

    /**
     * @param $Url
     *
     * @return mixed
     * @throws \Exception
     */
    private function requestCurl($Url)
    {

        $Key = md5($Url);
        $Cache = $this->getCache(new MemcachedHandler(), 'Memcached');
        if (!( $Response = $Cache->getValue($Key, __METHOD__) )) {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Roadmap (Request): '.$Url);
            if (!$this->Authenticated) {
                $this->doLogin();
                $this->Authenticated = true;
            }
            $CurlHandler = curl_init();
            curl_setopt($CurlHandler, CURLOPT_URL, $Url);
            curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($CurlHandler, CURLOPT_HEADER, false);
            curl_setopt($CurlHandler, CURLOPT_VERBOSE, false);
            curl_setopt($CurlHandler, CURLOPT_COOKIE, $this->getCookie());
            curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, 1);
            $Response = curl_exec($CurlHandler);
            curl_close($CurlHandler);
            $Cache->setValue($Key, $Response, ( 60 * 60 * 1 ), __METHOD__);
        } else {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Roadmap (Cache:Request): '.$Url);
        }
        return $Response;
    }

}

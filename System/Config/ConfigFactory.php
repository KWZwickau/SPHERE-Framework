<?php
namespace SPHERE\System\Config;

use SPHERE\System\Config\Reader\ArrayReader;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class ConfigFactory
 *
 * @package SPHERE\System\Config
 */
class ConfigFactory implements ConfigInterface
{

    /**
     * @var ReaderInterface
     */
    private static $InstanceCache = array();

    /**
     * @param string|array    $Content
     * @param ReaderInterface $Reader
     *
     * @return ReaderInterface
     */
    public function createReader($Content, ReaderInterface $Reader = null)
    {

        if (null === $Reader) {
            if (is_array($Content)) {
                $Reader = new ArrayReader();
            } else {
                $Reader = new IniReader();
            }
        }

        if (is_string($Content)) {
            $Source = realpath($Content);
            if ($Source) {
                if (!$this->isAvailable($Source)) {
                    (new DebuggerFactory())->createLogger()->addLog(__METHOD__.': '.$Source);
                    $this->setReader($Reader, $Source);
                }
                return $this->getReader($Source);
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: File not available ('.$Content.')');
                return $Reader;
            }
        } else {
            if (!$this->isAvailable($Content)) {
                (new DebuggerFactory())->createLogger()->addLog(__METHOD__.': '.json_encode($Content));
                $this->setReader($Reader, $Content);
            }
            return $this->getReader($Content);
        }
    }

    /**
     * @param string $File
     *
     * @return bool
     */
    private function isAvailable($File)
    {

        return isset( self::$InstanceCache[$this->getHash($File)] );
    }

    /**
     * @param mixed $Mixed
     *
     * @return string
     */
    private function getHash($Mixed)
    {

        return json_encode($Mixed);
    }

    /**
     * @param ReaderInterface $Reader
     * @param string          $File
     */
    private function setReader(ReaderInterface $Reader, $File)
    {

        self::$InstanceCache[$this->getHash($File)] = $Reader->setConfig($File);
    }

    /**
     * @param string $File
     *
     * @return ReaderInterface
     */
    private function getReader($File)
    {

        return self::$InstanceCache[$this->getHash($File)];
    }
}

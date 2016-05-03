<?php
namespace SPHERE\Common\Frontend;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\QueryLogger;

/**
 * Class Cache
 *
 * @package SPHERE\Common\Frontend
 */
class Cache
{

    /** @var string $Identifier */
    private $Identifier = 'Default';
    /** @var Element[] $Dependency */
    private $Dependency = array();
    /** @var null|MemcachedHandler $Handler */
    private $Handler = null;

    /**
     * Frontend-Cache constructor.
     *
     * @param string    $Identifier
     * @param Element[] $Dependencies
     */
    public function __construct($Identifier, $Dependencies = array())
    {

        $this->Identifier = $Identifier;
        $this->Handler = (new CacheFactory())->createHandler(new MemcachedHandler());
        if (!empty( $Dependencies )) {
            foreach ($Dependencies as $Element) {
                $this->addDependency($Element);
            }
        }
    }

    /**
     * Add Entity-Dependency
     *
     * @param Element $Entity
     *
     * @return $this
     */
    public function addDependency(Element $Entity)
    {

        array_push($this->Dependency, $Entity);
        return $this;
    }

    /**
     * Save Value
     *
     * @param mixed $Value
     * @param int   $Timeout
     *
     * @return $this
     */
    public function setData($Value, $Timeout = 3600)
    {

        if ($this->Handler->isEnabled()) {
            (new DebuggerFactory())->createLogger(new QueryLogger())->addLog('Save Gui: '.md5($this->Identifier));
            $this->Handler->setValue($this->createKey(), $Value, $Timeout, 'GUI');
        }
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function createKey()
    {

        $KeyList = $this->createKeyList();
        array_unshift($KeyList, md5($this->Identifier));
        return json_encode($KeyList);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function createKeyList()
    {

        if (empty( $this->Dependency )) {
            throw new \Exception(__METHOD__.' Missing Dependency-Entities');
        } else {
            $KeyList = array();
            array_walk($this->Dependency, function (Element $Entity) use (&$KeyList) {

                array_push($KeyList, md5(get_class($Entity)));
            });
            return $KeyList;
        }
    }

    /**
     * Load Value
     *
     * @return mixed|null
     */
    public function getData()
    {

        if ($this->Handler->isEnabled()) {
            (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Load Gui: '.md5($this->Identifier));
            return $this->Handler->getValue($this->createKey(), 'GUI');
        }
        return null;
    }

    /**
     * Clear Frontend-Cache
     */
    public function clearData()
    {

        if ($this->Handler->isEnabled()) {
            $Pattern = $this->createPattern();
            $CacheList = $this->Handler->fetchKeys();
            $KeyList = preg_grep($Pattern, $CacheList);
            if (!empty( $KeyList )) {
                (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Clear Gui: '.implode(',', $KeyList));
                $this->Handler->removeKeys($KeyList);
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function createPattern()
    {

        $KeyList = $this->createKeyList();
        return '!("'.implode('"|"', $KeyList).'")!is';
    }
}

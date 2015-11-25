<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Config\Reader\ReaderInterface;

/**
 * Class MemoryHandler
 * @package SPHERE\System\Cache\Handler
 */
class MemoryHandler extends AbstractHandler implements HandlerInterface
{
    /** @var array $MemoryRegister */
    private $MemoryRegister = array();

    /**
     * @param string $Key
     * @param mixed $Value
     * @param int $Timeout
     * @param string $Region
     * @return MemoryHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {
        $this->MemoryRegister[$Region][$Key] = new MemoryContainer($Value, $Timeout);
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'Default')
    {
        $Container = $this->getContainer($Region, $Key);
        if (false !== $Container) {
            return $Container->getValue();
        }
        return null;
    }

    /**
     * @param string $Region
     * @param string $Key
     * @return bool|MemoryContainer
     */
    private function getContainer($Region, $Key)
    {
        if (isset($this->MemoryRegister[$Region])) {
            if (isset($this->MemoryRegister[$Region][$Key])) {
                /** @var MemoryContainer $Container */
                $Container = $this->MemoryRegister[$Region][$Key];
                if ($Container->isValid()) {
                    return $Container;
                } else {
                    unset($this->MemoryRegister[$Region][$Key]);
                }
            }
        }
        return false;
    }

    /**
     * @param ReaderInterface $Name
     * @param ReaderInterface $Config
     * @return MemoryHandler
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {
        // Nothing to configure
        return $this;
    }

    /**
     * @return MemoryHandler
     */
    public function clearCache()
    {
        $this->MemoryRegister = array();
        return $this;
    }
}

<?php
namespace SPHERE\System\Cache\Handler;

use MOC\V\Component\Template\Component\Bridge\Repository\SmartyTemplate;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class SmartyHandler
 * @package SPHERE\System\Cache\Handler
 */
class SmartyHandler extends AbstractHandler implements HandlerInterface
{

    /**
     * @param $Name
     * @param ReaderInterface $Config
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        return $this;
    }

    /**
     * @param string $Key
     * @param mixed $Value
     * @param int $Timeout
     * @param string $Region
     * @return SmartyHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {
        // MUST NOT USE
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Error: SET - MUST NOT BE USED!');
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     * @return mixed
     */
    public function getValue($Key, $Region = 'Default')
    {
        // MUST NOT USE
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Error: GET - MUST NOT BE USED!');
        return null;
    }

    /**
     * @return SmartyHandler
     */
    public function clearCache()
    {
        (new SmartyTemplate())->createInstance()->clearAllCache();
        return $this;
    }
}

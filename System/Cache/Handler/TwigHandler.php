<?php
namespace SPHERE\System\Cache\Handler;

use MOC\V\Component\Template\Component\Bridge\Repository\TwigTemplate;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class TwigHandler
 * @package SPHERE\System\Cache\Handler
 */
class TwigHandler extends AbstractHandler implements HandlerInterface
{

    /**
     * @param ReaderInterface $Name
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
     * @return TwigHandler
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
     * @return TwigHandler
     */
    public function clearCache()
    {
        (new TwigTemplate())->createInstance()->clearCacheFiles();
        (new TwigTemplate())->createInstance()->clearTemplateCache();
        return $this;
    }
}

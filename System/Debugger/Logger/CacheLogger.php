<?php
namespace SPHERE\System\Debugger\Logger;

use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Debugger\DebuggerFactory;

/**
 * Class CacheLogger
 *
 * @package SPHERE\System\Debugger\Logger
 */
class CacheLogger extends AbstractLogger implements LoggerInterface
{

    /**
     * @param string $Content
     *
     * @return LoggerInterface
     */
    public function addLog($Content)
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())
            ->addLog(new Label('Cache', Label::LABEL_TYPE_SUCCESS).' '.new Muted($Content));
        return parent::addLog($Content);
    }
}

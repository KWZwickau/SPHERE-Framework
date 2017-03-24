<?php
namespace SPHERE\System\Debugger\Logger;

use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Debugger\DebuggerFactory;

/**
 * Class QueryLogger
 *
 * @package SPHERE\System\Debugger\Logger
 */
class QueryLogger extends AbstractLogger implements LoggerInterface
{

    /**
     * @param string $Content
     *
     * @return LoggerInterface
     */
    public function addLog($Content)
    {

        if( preg_match( '!Result: Ok$!s', $Content ) ) {
            $Content = new Label('Ok',Label::LABEL_TYPE_SUCCESS).' '.preg_replace('!Result: Ok$!s', '', $Content);
        }
        if( preg_match( '!Result: Error$!s', $Content ) ) {
            $Content = new Label('Fail',Label::LABEL_TYPE_DANGER).' '.preg_replace('!Result: Error$!s', '', $Content);
        }

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())
            ->addLog(new Label('Query', Label::LABEL_TYPE_WARNING).' '.new Muted( $Content ));
        return parent::addLog($Content);
    }
}

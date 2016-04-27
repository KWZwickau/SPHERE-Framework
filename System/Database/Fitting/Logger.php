<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\DBAL\Logging\SQLLogger;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\System\Debugger\Logger\QueryLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Logger
 *
 * @package SPHERE\System\Database\Fitting
 */
class Logger extends Extension implements SQLLogger
{

    private $Data = array();

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {

        $this->Data = func_get_args();
        $this->Data[3] = $this->getDebugger()->getTimeGap();

        $Parsed = $sql;
        $placeholder = '!\?!is';
        if (is_array($params)) {
            foreach ($params as $param) {
                if (!is_object($param) && !is_array($param)) {
                    $Parsed = preg_replace($placeholder, "'" . $param . "'", $Parsed, 1);
                } elseif (is_array($param) && count($param) == 1) {
                    $Parsed = preg_replace($placeholder, "'" . current($param) . "'", $Parsed, 1);
                } else {
                    $Parsed = preg_replace($placeholder, "'" . json_encode($param) . "'", $Parsed, 1);
                }
            }
        }
        $this->Data[4] = $Parsed;
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {

        $this->getLogger(new QueryLogger())->addLog(
            new Label(
                number_format(( $this->getDebugger()->getTimeGap() - $this->Data[3] ) * 1000, 3, '.', ',')
                .'ms'
            )
            .' '.$this->Data[4]
        );
    }
}

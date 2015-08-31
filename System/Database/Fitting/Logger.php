<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\DBAL\Logging\SQLLogger;
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
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {

        $this->Data = func_get_args();
        $this->Data[3] = $this->getDebugger()->getTimeGap();

        $Log = $sql;

        ob_start();
        var_dump($params);
        $params = ob_get_clean();

        $Log .= ' '.$params;

        ob_start();
        var_dump($types);
        $types = ob_get_clean();

        $Log .= ' '.$types;

        $this->getDebugger()->protocolDump($Log);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {

        $this->getDebugger()->addProtocol(
            number_format(( $this->getDebugger()->getTimeGap() - $this->Data[3] ) * 1000, 3, ',', '')
        );
    }
}

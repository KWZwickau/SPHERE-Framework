<?php
namespace SPHERE\System\Proxy;

use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Proxy
 *
 * @package SPHERE\System\Proxy
 */
class Proxy extends Extension
{

    /** @var ITypeInterface $Type */
    private $Type = null;

    /**
     * @param ITypeInterface $Type
     *
     * @throws \Exception
     */
    public function __construct(ITypeInterface $Type)
    {

        $this->Type = $Type;
        if ($this->Type->getConfiguration() !== null) {
            $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__);
            $Configuration = parse_ini_file(__DIR__.'/Configuration.ini', true);
            if (isset( $Configuration[$this->Type->getConfiguration()] )) {
                $this->Type->setConfiguration($Configuration[$this->Type->getConfiguration()]);
            }
        }
    }

    /**
     * @return ITypeInterface
     */
    public function getProxy()
    {

        return $this->Type;
    }
}

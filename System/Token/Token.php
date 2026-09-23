<?php

namespace SPHERE\System\Token;

use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Token
 *
 * @package SPHERE\System\Token
 */
class Token extends Extension
{
    private ?ITypeInterface $Type = null;

    public function __construct(ITypeInterface $Type)
    {
        $this->Type = $Type;
        if ($this->Type->getConfiguration() !== null) {
            $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__);
            $Configuration = parse_ini_file(__DIR__ . '/Configuration.ini', true);
            if (isset($Configuration[$this->Type->getConfiguration()])) {
                $this->Type->setConfiguration($Configuration[$this->Type->getConfiguration()]);
            }
        }
    }

    public function getToken(): ITypeInterface
    {
        return $this->Type;
    }
}

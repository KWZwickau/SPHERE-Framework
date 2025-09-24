<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\Authentication\Process\Service\Data;
use SPHERE\Application\App\Authentication\Process\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 *
 */
class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {
        $Protocol = '';
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }
}

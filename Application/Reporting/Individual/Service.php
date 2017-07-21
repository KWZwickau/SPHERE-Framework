<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Reporting\Individual\Service\Data;
use SPHERE\Application\Reporting\Individual\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return (new Data($this->getBinding()))->getView();
    }
}

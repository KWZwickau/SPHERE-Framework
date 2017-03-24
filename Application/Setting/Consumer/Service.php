<?php
namespace SPHERE\Application\Setting\Consumer;

use SPHERE\Application\Setting\Consumer\Service\Data;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Application\Setting\Consumer\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Setting\Consumer
 */
class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Cluster
     * @param $Application
     * @param null $Module
     * @param $Identifier
     * @return false|TblSetting
     */
    public function getSetting(
        $Cluster,
        $Application,
        $Module = null,
        $Identifier
    ) {

        return (new Data($this->getBinding()))->getSetting(
            $Cluster, $Application, $Module, $Identifier
        );
    }
}
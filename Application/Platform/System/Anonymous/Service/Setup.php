<?php
namespace SPHERE\Application\Platform\System\Anonymous\Service;

use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        // no table exist

        /**
         * Migration & Protocol
         */
        // UTF8 repair
        if ($UTF8){
            $this->getConnection()->setUTF8();
        }
        return '';
    }
}

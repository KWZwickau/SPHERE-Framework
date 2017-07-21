<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Reporting\Individual\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    public function getView()
    {
//        $Manager = $this->getEntityManager();
//        $QueryBuilder = $Manager->getQueryBuilder();
//
//
//        $SqlReturn = $QueryBuilder
//            ->select('viewStudent')
//            ->from( 'SettingConsumer_DEMO.viewStudent', 'viewStudent' )
//            ->getQuery()->execute();

//        Debugger::screenDump($Query);

//        if($MaxYear) {
//            return $MaxYear;
//        }
//        else {
//            return null;
//        }
        return '';
    }

}

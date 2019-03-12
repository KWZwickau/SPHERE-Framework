<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.03.2019
 * Time: 14:55
 */

namespace SPHERE\Application\Billing\Inventory\Document;


use SPHERE\Application\Billing\Inventory\Document\Service\Data;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\Application\Billing\Inventory\Document\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Billing\Inventory\Document
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
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($Id)
    {
        return (new Data($this->getBinding()))->getDocumentById($Id);
    }
    /**
     * @return false|TblDocument[]
     */
    public function getDocumentAll()
    {
        return (new Data($this->getBinding()))->getDocumentAll();
    }
}
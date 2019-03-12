<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 09:37
 */

namespace SPHERE\Application\Billing\Inventory\Document\Service;

use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Billing\Inventory\Document\Service
 */
class Data extends AbstractData
{
    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDocument', $Id);
    }

    /**
     * @return false|TblDocument[]
     */
    public function getDocumentAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDocument');
    }
}
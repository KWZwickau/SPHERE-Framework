<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service;

use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Document\Explorer\Storage\Service
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

    /**
     * @param int $Id
     * @return false|TblFile
     */
    public function getFileById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile', $Id);
    }
}

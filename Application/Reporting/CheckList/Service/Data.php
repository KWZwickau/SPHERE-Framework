<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:39
 */

namespace SPHERE\Application\Reporting\CheckList\Service;

use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblElementType;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListType;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\CheckList\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {


    }

    /**
     * @param $Id
     *
     * @return bool|TblList
     */
    public function getListById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblList', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblListType
     */
    public function getListTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblListType', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblListElementList
     */
    public function getListElementListById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblListElementList', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblElementType
     */
    public function getElementTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblElementType', $Id);
        return (null === $Entity ? false : $Entity);
    }
}
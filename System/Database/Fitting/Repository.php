<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;

/**
 * Class Repository
 *
 * @package SPHERE\System\Database\Fitting
 */
class Repository extends EntityRepository implements ObjectRepository, Selectable
{

    /**
     * @return int
     */
    public function count()
    {

        $Query = $this->createQueryBuilder( 'e' )->select( 'count(e)' )->getQuery()->useQueryCache(true);
        return $Query->getSingleScalarResult();
    }

    /**
     * @param $Criteria
     *
     * @return int
     */
    public function countBy( $Criteria = array() )
    {

        $EntityPersister = $this->_em->getUnitOfWork()->getEntityPersister( $this->_entityName );
        return $EntityPersister->count( $Criteria );
    }
}

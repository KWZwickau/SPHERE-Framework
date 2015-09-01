<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

/**
 * Class ColumnHydrator
 *
 * @package SPHERE\System\Database\Fitting
 */
class ColumnHydrator extends AbstractHydrator
{

    /**
     * Hydrates all rows from the current statement instance at once.
     *
     * @return array
     */
    protected function hydrateAllData()
    {

        return $this->_stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

}

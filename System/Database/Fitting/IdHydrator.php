<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\ORM\Internal\Hydration\ObjectHydrator;

/**
 * Class IdHydrator
 *
 * @package SPHERE\System\Database\Fitting
 */
class IdHydrator extends ObjectHydrator
{

    const HYDRATION_MODE = 'ID_HYDRATOR';

    /**
     * Hydrates all rows from the current statement instance at once.
     *
     * @return array
     * @throws \Exception
     */
    protected function hydrateAllData()
    {

        $Result = array();
        foreach ($this->_stmt->fetchAll(\PDO::FETCH_ASSOC) as $Row) {
            $Cache = array();
            $this->hydrateRowData($Row, $Cache);

            $PossibleId = preg_grep('!^Id_[\d]!is', array_keys($Row));
            if (!empty( $PossibleId )) {
                $Result[$Row[current($PossibleId)]] = current($Cache);
            } else {
                throw new \Exception('Id field not found in '.json_encode($Row));
            }
        }
        return $Result;
    }
}

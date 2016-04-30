<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Filter\Logic\AbstractLogic;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractData
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractData extends Cacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    final public function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    /**
     * @return void
     */
    abstract public function setupDatabaseContent();

    /**
     * Internal
     *
     * @param Element $Entity
     * @param AbstractLogic $Logic
     * @return bool|Element[]
     */
    protected function getAllByLogic(Element $Entity, $Logic)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $Builder->select('E')->from( $Entity->getEntityFullName(), 'E' );
        $Builder->andWhere( $Logic->getExpression() );
        $Query = $Builder->getQuery();
        $Query->useQueryCache(true);
        $Query->useResultCache(true,300);

        $this->getDebugger()->screenDump( $Query->getSQL() );

        return $Query->getResult();
    }

    /**
     * @return Binding
     */
    final public function getConnection()
    {

        return $this->Connection;
    }
}

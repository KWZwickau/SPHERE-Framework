<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Filter\Logic\AbstractLogic;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Probe
 *
 * @package SPHERE\System\Database\Filter\Link
 */
class Probe
{

    /** @var null|AbstractService $Service */
    private $Service = null;
    /** @var null|Element $Entity */
    private $Entity = null;

    /**
     * Probe constructor.
     *
     * @param AbstractService $Service
     * @param Element         $Entity
     *
     * @throws \Exception
     */
    final public function __construct(AbstractService $Service, Element $Entity)
    {

        $this->setupService($Service);
        $this->Entity = $Entity;
    }

    /**
     * @param AbstractService $Service
     *
     * @return $this
     * @throws \Exception
     */
    final private function setupService(AbstractService $Service)
    {

        if ($this->isValidService($Service)) {
            $this->Service = $Service;
        } else {
            throw new \Exception('Invalid Service '.get_class($Service));
        }
        return $this;
    }

    /**
     * @param string|object $Class
     *
     * @return bool
     */
    final private function isValidService($Class)
    {

        $Service = new \ReflectionClass($Class);
        return $Service->isSubclassOf('\SPHERE\System\Database\Binding\AbstractService');
    }

    /**
     * @param AbstractLogic $Logic
     *
     * @return Element[]
     */
    final public function findLogic(AbstractLogic $Logic)
    {

        return $this->useBinding()->getEntityAllByLogic($this->getEntity(), $Logic);
    }

    /**
     * @return Data
     */
    final public function useBinding()
    {

        return new Data($this->Service->getBinding());
    }

    /**
     * @return null|Element
     */
    public function getEntity()
    {

        return $this->Entity;
    }

    /**
     * @param AbstractLogic $Logic
     * @param string        $Column
     *
     * @return array
     */
    final public function findLogicColumn(AbstractLogic $Logic, $Column = 'Id')
    {

        return $this->useBinding()->getColumnAllByLogic($this->getEntity(), $Logic, $Column);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    final public function useBuilder()
    {

        return $this->useBinding()->getConnection()->getEntityManager()->getQueryBuilder();
    }
}

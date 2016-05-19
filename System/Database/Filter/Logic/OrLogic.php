<?php
namespace SPHERE\System\Database\Filter\Logic;

/**
 * Class OrLogic
 * @package SPHERE\System\Database\Filter\Logic
 */
class OrLogic extends AbstractLogic
{

    /**
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    final public function getExpression()
    {

        return $this->getExpressionBuilder()->orX()->addMultiple($this->getLogic());
    }
}

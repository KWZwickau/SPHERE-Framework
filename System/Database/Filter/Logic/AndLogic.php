<?php
namespace SPHERE\System\Database\Filter\Logic;

/**
 * Class AndLogic
 * @package SPHERE\System\Database\Filter\Logic
 */
class AndLogic extends AbstractLogic
{

    /**
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    final public function getExpression()
    {

        return $this->getExpressionBuilder()->andX()->addMultiple($this->getLogic());
    }
}

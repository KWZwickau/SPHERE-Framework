<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Filter\Logic\AbstractLogic;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\System\Database\Filter\Link
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
     * Internal
     *
     * @param Element $Entity
     * @param AbstractLogic $Logic
     * @return bool|Element[]
     */
    public function getAllByLogic(Element $Entity, $Logic)
    {
        return parent::getAllByLogic($Entity, $Logic);
    }
}

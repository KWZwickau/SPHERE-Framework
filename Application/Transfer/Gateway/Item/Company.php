<?php
namespace SPHERE\Application\Transfer\Gateway\Item;

class Company extends AbstractItem
{

    const FIELD_NAME = 'Name';

    public function __construct($EntityList)
    {

        $this->setEssential( self::FIELD_NAME );

        parent::__construct($EntityList);
    }
}

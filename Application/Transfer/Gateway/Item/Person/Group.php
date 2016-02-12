<?php
namespace SPHERE\Application\Transfer\Gateway\Item\Person;

use SPHERE\Application\Transfer\Gateway\Item\AbstractItem;

class Group extends AbstractItem
{

    const FIELD_NAME = 'Name';
    const FIELD_META_TABLE = 'MetaTable';

    public function __construct($EntityList)
    {

        $this->setEssential( self::FIELD_NAME );
        $this->setEssential( self::FIELD_META_TABLE );

        parent::__construct($EntityList);
    }

}

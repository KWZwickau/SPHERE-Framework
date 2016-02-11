<?php
namespace SPHERE\Application\Transfer\Gateway\Item;

class Person extends AbstractItem
{

    const FIELD_SALUTATION = 'tblSalutation';
    const FIELD_FIRST_NAME = 'FirstName';
    const FIELD_LAST_NAME = 'LastName';

    public function __construct($EntityList)
    {

        $this->setEssential( self::FIELD_FIRST_NAME );
        $this->setEssential( self::FIELD_LAST_NAME );

        parent::__construct($EntityList);
    }


}

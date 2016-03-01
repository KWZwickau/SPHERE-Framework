<?php
namespace SPHERE\Application\Transfer\Gateway\Item\Contact;

use SPHERE\Application\Transfer\Gateway\Item\AbstractItem;

class Address extends AbstractItem
{

    const FIELD_STREET_NAME = 'StreetName';
    const FIELD_STREET_NUMBER = 'StreetNumber';

    const FIELD_CITY_CODE = 'Code';
    const FIELD_CITY_NAME = 'Name';
    const FIELD_CITY_DISTRICT = 'District';

    public function __construct($EntityList)
    {
        $this->setEssential( self::FIELD_STREET_NAME );
        $this->setEssential( self::FIELD_STREET_NUMBER );

        $this->setEssential( self::FIELD_CITY_CODE );
        $this->setEssential( self::FIELD_CITY_NAME );

        parent::__construct($EntityList);
    }
}

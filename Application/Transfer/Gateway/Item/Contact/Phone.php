<?php
namespace SPHERE\Application\Transfer\Gateway\Item\Contact;

use SPHERE\Application\Transfer\Gateway\Item\AbstractItem;

class Phone extends AbstractItem
{

    const FIELD_NUMBER = 'Number';

    public function __construct($EntityList)
    {
        $this->setEssential( self::FIELD_NUMBER );

        parent::__construct($EntityList);
    }
}

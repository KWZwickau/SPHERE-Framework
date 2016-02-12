<?php
namespace SPHERE\Application\Transfer\Gateway\Item\Contact;

use SPHERE\Application\Transfer\Gateway\Item\AbstractItem;

class Mail extends AbstractItem
{

    const FIELD_ADDRESS = 'Address';

    public function __construct($EntityList)
    {
        $this->setEssential( self::FIELD_ADDRESS );

        parent::__construct($EntityList);
    }
}

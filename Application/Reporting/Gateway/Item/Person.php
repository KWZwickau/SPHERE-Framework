<?php
namespace SPHERE\Application\Reporting\Gateway\Item;

use SPHERE\System\Database\Fitting\Element;

class Person extends AbstractItem
{

    const FIELD_SALUTATION = 'Person_Salutation';
    const FIELD_FIRST_NAME = 'Person_FirstName';
    const FIELD_LAST_NAME = 'Person_LastName';

    /**
     * Person constructor.
     *
     * @param Element[] $EntityList
     * @param string    $Name
     */
    public function __construct($EntityList, $Name = 'TblPerson')
    {

        $this->EntityList = $EntityList;
        $this->setXmlType($Name);
    }
}

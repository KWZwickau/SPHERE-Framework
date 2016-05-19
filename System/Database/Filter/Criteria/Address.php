<?php
namespace SPHERE\System\Database\Filter\Criteria;

/**
 * Class Address
 *
 * @package SPHERE\System\Database\Filter\Criteria
 */
class Address extends AbstractCriteria
{

    /**
     * Address constructor.
     */
    public function __construct()
    {

        $this->setupService(\SPHERE\Application\Contact\Address\Address::useService());
        $this->setupGetterAll('getAddressAll');
        $this->setupGetterId('getAddressById');

        $this->setTitle('Adressdaten');

        $this->addField('StreetName', 'Straße');
        $this->addField('StreetNumber', 'Hausnummer');
    }
}

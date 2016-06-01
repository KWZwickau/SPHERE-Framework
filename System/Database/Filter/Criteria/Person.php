<?php
namespace SPHERE\System\Database\Filter\Criteria;

/**
 * Class Person
 *
 * @package SPHERE\System\Database\Filter\Criteria
 */
class Person extends AbstractCriteria
{

    /**
     * Person constructor.
     */
    public function __construct()
    {

        $this->setupService(\SPHERE\Application\People\Person\Person::useService());
        $this->setupGetterAll('getPersonAll');
        $this->setupGetterId('getPersonById');

        $this->setTitle('Personendaten');

        $this->addField('Title', 'Titel');
        $this->addField('FirstName', 'Vorname');
        $this->addField('LastName', 'Nachname');

        $this->addLink(new Address());
    }
}

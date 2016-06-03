<?php
namespace SPHERE\System\Database\Filter\Repository;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress as ContactAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity as ContactAddressCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup as PeopleGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember as PeopleGroupMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson as PeoplePerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson as PeopleRelationship;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Test
 *
 * @package SPHERE\System\Database\Filter\Repository
 */
class Test
{

    /**
     * Test constructor.
     */
    public function __construct()
    {

        Debugger::screenDump((new \SPHERE\System\Database\Filter\Preparation\Address(1))->__toArray());

        $Result = (new Pile())
            ->addPile(Group::useService(), new PeopleGroup(''), null, 'Id')
            ->addPile(Group::useService(), new PeopleGroupMember(), 'tblGroup', 'serviceTblPerson')
            ->addPile(Person::useService(), new PeoplePerson(), 'Id', 'Id')
            ->addPile(Relationship::useService(), new PeopleRelationship(), 'serviceTblPersonTo',
                'serviceTblPersonFrom')
            ->addPile(Person::useService(), new PeoplePerson(), 'Id', 'Id')
            ->addPile(Address::useService(), new TblToPerson(),
                'serviceTblPerson', 'tblAddress')
            ->addPile(Address::useService(), new ContactAddress(), 'Id', 'tblCity')
            ->addPile(Address::useService(), new ContactAddressCity(), 'Id', null)
            ->searchPile(array(
//                array(),
//                array(),
//                array('FirstName' => 'Lehr'),
//                array('tblType' => '1'),
//                array(),
//                array()
            ));

        Debugger::screenDump($Result);
    }
}

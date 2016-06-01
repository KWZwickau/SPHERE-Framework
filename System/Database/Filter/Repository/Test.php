<?php
namespace SPHERE\System\Database\Filter\Repository;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
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
            ->addPile(Group::useService(), new TblGroup(''), null, 'Id')
            ->addPile(Group::useService(), new TblMember(), 'tblGroup', 'serviceTblPerson')
            ->addPile(Person::useService(), new TblPerson(), 'Id', 'Id')
            ->addPile(Relationship::useService(), new TblToPerson(), 'serviceTblPersonTo', 'serviceTblPersonFrom')
            ->addPile(Person::useService(), new TblPerson(), 'Id', 'Id')
            ->addPile(Address::useService(), new \SPHERE\Application\Contact\Address\Service\Entity\TblToPerson(),
                'serviceTblPerson', 'tblAddress')
//            ->addPile(Address::useService(), new TblAddress(), 'Id', 'tblCity')
//            ->addPile(Address::useService(), new TblCity(), 'Id', null)
            ->searchPile(array(
                array('Name' => 'ü'),
                array(),
                array(),
                array('tblType' => '1'),
//                array(),
//                array()
            ));

        Debugger::screenDump($Result);
    }
}

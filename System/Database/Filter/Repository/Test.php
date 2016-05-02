<?php
namespace SPHERE\System\Database\Filter\Repository;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Filter\Link\RecursiveLink;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Test
 * @package SPHERE\System\Database\Filter\Repository
 */
class Test
{

    /**
     * Test constructor.
     */
    public function __construct()
    {

//        $Link = new ConnectLink();
//        $Link
//            ->addProbe(Group::useService(), new TblGroup(''))
//            ->addPath('Id')
//            ->addProbe(Group::useService(), new TblMember())
//            ->addPath('tblGroup')
//            ->addPath('serviceTblPerson')
//            ->addProbe(Person::useService(), new TblPerson())
//            ->addPath('Id');
//
//        $Result = $Link->searchData(
//            array('Name' => array('ü')),
//            array('LastName' => array('me','na'))
//        );
//
//        Debugger::screenDump($Result);

        $Link = new RecursiveLink();
        $Link
            ->addProbe(Group::useService(), new TblGroup(''))
            ->addPath(null, 'Id')
            ->addProbe(Group::useService(), new TblMember())
            ->addPath('tblGroup', 'serviceTblPerson')
            ->addProbe(Person::useService(), new TblPerson())
            ->addPath('Id', null);

        $Result = $Link->searchData(array(
            0 => array('Name' => array('ü')),
            2 => array('LastName' => array('me', 'na'))
        ));

        Debugger::screenDump($Result);

//        $Link = new SingleLink();
//        $Link
//            ->addProbe(Person::useService(), new TblPerson())
//            ->addPath('tblSalutation')
//            ->addProbe(Person::useService(), new TblSalutation(''))
//            ->addPath('Id');
//
//        $Result = $Link->searchData(
//            array('LastName' => array('me','na')),
//            array('Salutation' => '')
//        );
//
//        Debugger::screenDump($Result);


        // TODO: Connect Links to Graph
    }
}

<?php
namespace SPHERE\System\Database\Filter\Repository;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\System\Database\Filter\Link\ConnectLink;
use SPHERE\System\Database\Filter\Link\SingleLink;
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

        $Link = new ConnectLink();
        $Link
            ->setupProbeLeft(Group::useService(), new TblGroup(''))
            ->addLinkPath('Id')
            ->setupProbeCenter(Group::useService(), new TblMember())
            ->addLinkPath('tblGroup')
            ->addLinkPath('serviceTblPerson')
            ->setupProbeRight(Person::useService(), new TblPerson())
            ->addLinkPath('Id');

        $Result = $Link->searchData(
            array('Name' => array('e')),
            array('LastName' => array('a', 'l'), 'FirstName' => 'la')
        );

        Debugger::screenDump($Result);


        $Link = new SingleLink();
        $Link
            ->setupProbeLeft(Person::useService(), new TblPerson())
            ->addLinkPath('tblSalutation')
            ->setupProbeRight(Person::useService(), new TblSalutation(''))
            ->addLinkPath('Id');

        $Result = $Link->searchData(
            array('LastName' => array('g', 'a'), 'FirstName' => array('w', 'l')),
            array('Salutation' => 'ü')
        );

        Debugger::screenDump($Result);


        // TODO: Connect Links to Graph
    }
}

<?php
namespace SPHERE\System\Database\Filter\Repository;

use SPHERE\System\Database\Filter\Link\AbstractLink;
use SPHERE\System\Database\Filter\Link\MultipleLink;
use SPHERE\System\Database\Filter\Link\Probe;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

class Test
{

    /**
     * Test constructor.
     */
    public function __construct()
    {

        $GroupToPerson = new MultipleLink();
        $GroupToPerson
            ->setupProbeLeft(\SPHERE\Application\People\Group\Group::useService(), 'getGroupAll', 'getGroupById')
            ->setupProbeCenter(\SPHERE\Application\People\Group\Group::useService(), 'getMemberAll', 'getMemberById')
            ->setupProbeRight(\SPHERE\Application\People\Person\Person::useService(), 'getPersonAll', 'getPersonById');

        Debugger::screenDump($this->searchData($GroupToPerson, array('Name' => 'a'), array('LastName' => '')));
    }

    /**
     * @param AbstractLink|MultipleLink $Link
     * @param array                     $SearchLeft
     * @param array                     $SearchRight
     *
     * @return bool|Element[]
     */
    public function searchData(AbstractLink $Link, $SearchLeft = array(), $SearchRight = array())
    {

        $EntityListLeft = $Link->getProbeLeft()->findAll($SearchLeft);
//        Debugger::screenDump($EntityListLeft);
        $SearchCenter = array(
            'tblGroup' => implode(' ', array_map(function (Element $Entity) {

                $Entity = $Entity->__toArray();
                return $Entity['Id'];
            }, $EntityListLeft))
        );
        $EntityListCenter = $Link->getProbeCenter()->findAll($SearchCenter, Probe::LOGIC_OR);
//        Debugger::screenDump($EntityListCenter);
        $SearchId = array(
            'Id' => implode(' ', array_map(function (Element $Entity) {

                $Entity = $Entity->__toArray();
                return $Entity['serviceTblPerson'];
            }, $EntityListCenter))
        );
        $SearchRight = array(Probe::LOGIC_OR => $SearchId, Probe::LOGIC_AND => $SearchRight);
        $EntityListRight = $Link->getProbeRight()->findAll($SearchRight);
//        Debugger::screenDump($EntityListRight);

        $Result = array();
        array_walk($EntityListRight, function (Element $Right) use (&$Result, $EntityListCenter, $EntityListLeft) {

            array_walk($EntityListCenter, function (Element $Center) use (&$Result, $EntityListLeft, $Right) {

                array_walk($EntityListLeft, function (Element $Left) use (&$Result, $Right, $Center) {

                    $LeftData = $Left->__toArray();
                    $CenterData = $Center->__toArray();
                    $RightData = $Right->__toArray();

                    if ($LeftData['Id'] == $CenterData['tblGroup'] && $CenterData['serviceTblPerson'] == $RightData['Id']) {
                        $Result[] = array(
                            $Left->getEntityShortName()   => $Left,
                            $Center->getEntityShortName() => $Center,
                            $Right->getEntityShortName()  => $Right,
                        );
                    }

                });
            });
        });

        return $Result;
    }
}

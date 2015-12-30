<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool|false|int $Id
     *
     * @return Stage
     */
    public function frontendSearch($Id = false)
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->enableLog();

        $Stage = new Stage('Suche', 'nach Gruppe');

        $tblGroupAll = Group::useService()->getGroupAll();
        if (!empty($tblGroupAll)) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName() . '&nbsp;&nbsp;' . new Label(Group::useService()->countPersonAllByGroup($tblGroup)),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            });
        }

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

//            $idPersonAll = Group::useService()->fetchIdPersonAllByGroup($tblGroup);
//            $tblPersonAll = Person::useService()->fetchPersonAllByIdList($idPersonAll);
            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

//            $Cache = $this->getCache(new MemcachedHandler());
//            if (null === ($Result = $Cache->getValue($Id, __METHOD__))) {

            // Check ESZC
            $Acronym = Consumer::useService()->getConsumerBySession()->getAcronym();

            $Result = array();
            if ($tblPersonAll) {
                (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(__METHOD__ . ':StartRun');
                array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($tblGroup, &$Result, $Acronym) {

                    $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($tblPerson);
                    $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                    if (!empty($tblAddressAll)) {
                        $tblAddress = current($tblAddressAll)->getGuiString();
                    } else {
                        $tblAddress = false;
                    }

                    array_push($Result, array(
                        'FullName' => $tblPerson->getFullName(),
                        'Address' => ($tblAddress
                            ? $tblAddress
                            : new Warning('Keine Adresse hinterlegt')
                        ),
                        'Option' => new Standard('', '/People/Person', new Pencil(), array(
                            'Id' => $tblPerson->getId(),
                            'Group' => $tblGroup->getId()
                        ), 'Bearbeiten'),
                        'Remark' => (
                        $Acronym == 'ESZC'
                            ? (($Common = Common::useService()->getCommonByPerson($tblPerson)) ? $Common->getRemark() : '')
                            : ''
                        )
                    ));
                });
                (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(__METHOD__ . ':StopRun');

//                    $Cache->setValue($Id, $Result, 0, __METHOD__);
//                }
            }

            if ($Acronym == 'ESZC') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Remark' => 'Bemerkung',
                    'Option' => 'Optionen',
                );
            } else {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => 'Optionen',
                );
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        new Panel(new PersonGroup() . ' Gruppe', array(
                            new Bold($tblGroup->getName()),
                            ($tblGroup->getDescription() ? new Small($tblGroup->getDescription()) : ''),
                            ($tblGroup->getRemark() ? new Danger(new Italic(nl2br($tblGroup->getRemark()))) : '')
                        ), Panel::PANEL_TYPE_SUCCESS
                        )
                    )),
                    new LayoutRow(new LayoutColumn(array(
                        new Headline('Verfügbare Personen', 'in dieser Gruppe'),
                        new TableData($Result, null, $ColumnArray)
                    )))
                )))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }
}

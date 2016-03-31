<?php
namespace SPHERE\Application\Corporation\Search\Group;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
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
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Corporation\Search\Group
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

        $Stage = new Stage('Suche', 'nach Gruppe');

        $tblGroupAll = Group::useService()->getGroupAll();
        if (!empty( $tblGroupAll )) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName().'&nbsp;&nbsp;'.new Label(Group::useService()->countCompanyAllByGroup($tblGroup)),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            });
        }

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

            $tblCompanyAll = Group::useService()->getCompanyAllByGroup($tblGroup);
            $Result = array();
            if ($tblCompanyAll) {
                $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__.':StartRun');
                array_walk($tblCompanyAll, function (TblCompany &$tblCompany) use ($tblGroup, &$Result) {

                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                    if ($tblAddressAll) {
                        $tblToPerson = $tblAddressAll[0];
                        $tblAddressAll = $tblToPerson->getTblAddress()->getGuiString()
                            .( $tblToPerson->getRemark()
                                ? '<br/>'.new Small(new Muted($tblToPerson->getRemark()))
                                : ''
                            );
                    }

                    array_push($Result, array(
                        'Name'         => $tblCompany->getName(),
                        'ExtendedName' => $tblCompany->getExtendedName(),
                        'Address'      => ( $tblAddressAll
                            ? $tblAddressAll
                            : new Warning('Keine Adresse hinterlegt')
                        ),
                        'Option'       => (new Standard('', '/Corporation/Company', new Pencil(), array(
                            'Id'    => $tblCompany->getId(),
                            'Group' => $tblGroup->getId()
                        ), 'Bearbeiten'))
                        . (new Standard('', '/Corporation/Company/Destroy', new Remove(), array(
                                'Id'    => $tblCompany->getId(),
                                'Group' => $tblGroup->getId()
                            ), 'Löschen')),
                        'Description'  => $tblCompany->getDescription()
                    ));
                });
                $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__.':StopRun');
            }
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        new Panel(new PersonGroup().' Gruppe', array(
                            new Bold($tblGroup->getName()),
                            ( $tblGroup->getDescription() ? new Small($tblGroup->getDescription()) : '' ),
                            ( $tblGroup->getRemark() ? new Danger(new Italic(nl2br($tblGroup->getRemark()))) : '' )
                        ), Panel::PANEL_TYPE_INFO
                        )
                    )),
                    new LayoutRow(new LayoutColumn(array(
                        new Headline('Verfügbare Firmen', 'in dieser Gruppe'),
                        new TableData($Result, null,
                            array(
                                'Name'         => 'Name',
                                'ExtendedName' => 'Zusatz',
                                'Address'      => 'Adresse',
                                'Description'  => 'Beschreibung',
                                'Option'       => 'Optionen',
                            )
                        )
                    )))
                )))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }
}

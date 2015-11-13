<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
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

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Frontend implements IFrontendInterface
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
        if (!empty($tblGroupAll)) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup, $Index, Stage $Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName(),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            }, $Stage);
        }

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

            if ($tblPersonAll) {
                array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($tblGroup) {

                    $tblAddressAll = Address::useService()->getAddressAllByPerson($tblPerson);
                    if ($tblAddressAll) {
                        $tblToPerson = $tblAddressAll[0];
                        $tblAddressAll =
                            $tblToPerson->getTblAddress()->getStreetName() . ' '
                            . $tblToPerson->getTblAddress()->getStreetNumber() . ' '
                            . $tblToPerson->getTblAddress()->getTblCity()->getCode() . ' '
                            . $tblToPerson->getTblAddress()->getTblCity()->getName() . ' '
                            . ($tblToPerson->getTblAddress()->getTblState() ? $tblToPerson->getTblAddress()->getTblState()->getName() : '')
                            . ($tblToPerson->getRemark()
                                ? '<br/>' . new Small(new Muted($tblToPerson->getRemark()))
                                : ''
                            );
                    }

                    $tblPerson->FullName = $tblPerson->getFullName();
                    $tblPerson->Address = ($tblAddressAll
                        ? $tblAddressAll
                        : new Warning('Keine Adresse hinterlegt')
                    );
                    $tblPerson->Option = new Standard('', '/People/Person', new Pencil(), array(
                        'Id' => $tblPerson->getId(),
                        'Group' => $tblGroup->getId()
                    ), 'Bearbeiten');
                });
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
                        new TableData($tblPersonAll, null,
                            array(
                                'FullName' => 'Name',
                                'Address' => 'Adresse',
                                'Option' => 'Optionen',
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

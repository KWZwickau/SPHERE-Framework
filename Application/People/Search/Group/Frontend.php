<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
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
        if (!empty( $tblGroupAll )) {
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
            $Stage->setMessage(
                new PullClear(new Bold($tblGroup->getName()).' '.new Small($tblGroup->getDescription())).
                new PullClear(new Danger(new Italic(nl2br($tblGroup->getRemark()))))
            );
            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

            if ($tblPersonAll) {
                array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

                    $tblPerson->FullName = $tblPerson->getFullName();
                    $tblPerson->Option = new Standard('', '/People/Person', new Pencil(),
                        array('Id' => $tblPerson->getId()), 'Bearbeiten');
                });
            }
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($tblPersonAll, null,
                        array(
                            'FullName' => 'Name',
                            'Option'   => 'Optionen',
                        )
                    )
                ))))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }
}

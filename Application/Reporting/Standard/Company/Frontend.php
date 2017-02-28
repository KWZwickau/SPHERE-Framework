<?php

namespace SPHERE\Application\Reporting\Standard\Company;

use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Standard\Company
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $GroupId
     *
     * @return Stage
     */
    public function frontendGroupList($GroupId = null)
    {

        $Stage = new Stage('Auswertung', 'Institutionengruppenlisten');
        $tblGroupAll = Group::useService()->getGroupAll();
        $groupList = array();

        if ($GroupId === null) {
            if ($tblGroupAll){
                foreach ($tblGroupAll as &$tblGroup){
                    $tblGroup->Count = Group::useService()->countMemberAllByGroup($tblGroup);
                    $tblGroup->Option = new Standard(new Select(), '/Reporting/Standard/Company/GroupList', null, array(
                        'GroupId' => $tblGroup->getId()
                    ));
                }
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData(
                                    $tblGroupAll, null, array('Name' => 'Name', 'Count' => 'Institutionen', 'Option' => '')
                                )
                            )
                        )
                    )
                )
            );
        } else {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/Standard/Company/GroupList', new ChevronLeft())
            );
            $tblGroup = Group::useService()->getGroupById($GroupId);
            if ($tblGroup) {
                $groupList = Company::useService()->createGroupList($tblGroup);
                if ($groupList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Company/GroupList/Download', new Download(),
                            array('GroupId' => $tblGroup->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Gruppe:', $tblGroup->getName(),
                                    Panel::PANEL_TYPE_SUCCESS), 12
                            )
                        )
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($groupList, null,
                                    array(
                                        'Number'           => 'lfd. Nr.',
                                        'Name'             => 'Name',
                                        'ExtendedName'     => 'Zusatz',
                                        'Description'      => 'Beschreibung',
                                        'Address'          => 'Anschrift',
                                        'PhoneNumber'      => 'Telefon Festnetz',
                                        'MobilPhoneNumber' => 'Telefon Mobil',
                                        'Mail'             => 'E-mail',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Anzahl', array(
                                    'Gesamt: '.count($groupList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        ))
                    )
                ))
            );
        }

        return $Stage;
    }
}

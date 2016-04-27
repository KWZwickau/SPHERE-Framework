<?php

namespace SPHERE\Application\Reporting\Standard\Company;

use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
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
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendGroupList($Select = null)
    {

        $Stage = new Stage('Auswertung', 'Firmengruppenlisten');
        $tblGroupAll = Group::useService()->getGroupAll();
        $tblGroup = new TblGroup('');
        $groupList = array();

        if (isset( $Select['Group'] ) && $Select['Group'] != 0) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/Standard/Company/GroupList', new ChevronLeft())
            );

            $tblGroup = Group::useService()->getGroupById($Select['Group']);
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
        } else {
            $Select = null;
        }

        if ($Select === null) {
            $Stage->setContent(
                new Well(
                    Company::useService()->getGroup(
                        new Form(new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    new Panel('Auswahl', array(
                                        new SelectBox('Select[Group]', 'Gruppe', array(
                                            '{{ Name }}' => $tblGroupAll
                                        ))
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 12
                                )
                            )),
                        )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                        , $Select, '/Reporting/Standard/Company/GroupList')
                )
            );
        } else {
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

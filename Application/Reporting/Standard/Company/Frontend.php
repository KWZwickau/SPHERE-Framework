<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.11.2015
 * Time: 15:45
 */

namespace SPHERE\Application\Reporting\Standard\Company;

use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $GroupId
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendGroupList($GroupId = null, $Select = null)
    {

        $Stage = new Stage('Auswertung', 'Firmengruppenlisten');
        $tblGroupAll = Group::useService()->getGroupAll();
        $tblGroup = new TblGroup('');
        $groupList = array();

        if ($GroupId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Group'] = $GroupId;
                $Global->savePost();
            }

            $tblGroup = Group::useService()->getGroupById($GroupId);
            if ($tblGroup) {
                $groupList = Company::useService()->createGroupList($tblGroup);
                if ($groupList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Company/GroupList/Download', new Download(),
                            array('GroupId' => $tblGroup->getId()))
                    );
                }
            }
        }

        $Stage->setContent(
            Company::useService()->getGroup(
                new Form(new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            new SelectBox('Select[Group]', 'Gruppe', array(
                                '{{ Name }}' => $tblGroupAll
                            )), 12
                        )
                    )),
                )), new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Auswählen', new Select()))
                , $Select, '/Reporting/Standard/Company/GroupList')
            .
            ($GroupId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Gruppe:', $tblGroup->getName(),
                            Panel::PANEL_TYPE_SUCCESS), 12
                    ),
                )))))
                .
                new TableData($groupList, null,
                    array(
                        'Number' => 'lfd. Nr.',
                        'Name' => 'Name',
                        'Description' => 'Beschreibung',
                        'Address' => 'Anschrift',
                        'PhoneNumber' => 'Telefon Festnetz',
                        'MobilPhoneNumber' => 'Telefon Mobil',
                        'Mail' => 'E-mail',
                    ),
                    false
                )
                : ''
            )
        );

        return $Stage;
    }
}
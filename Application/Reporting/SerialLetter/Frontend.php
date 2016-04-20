<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.04.2016
 * Time: 08:10
 */

namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendSerialLetter($Check = null, $Radio = null)
    {
        $Stage = new Stage('Adresslisten', 'für Serienbriefe');

        $dataList = array();
        $columnList = array('Number' => 'Nr.', 'Student' => 'Schüler');
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        $Global = $this->getGlobal();
        if ($tblGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                $count = 1;
                foreach ($tblPersonList as $tblPerson) {
                    $panelData = array();
                    $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
                    $Global->POST['Check'][$tblPerson->getId()] = 1;
                    $Global->savePost();
                    $panelTitle = new CheckBox('Check[' . $tblPerson->getId() . ']',
                        $tblPerson->getLastFirstName(), 1);
                    if ($tblAddressToPersonList) {

                        // ToDo Johk erste Hauptadresse selektieren
                        $Global->POST['Radio'][$tblPerson->getId()] = reset($tblAddressToPersonList)->getId();
                        $Global->savePost();

                        foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                            $panelData[] = new RadioBox('Radio[' . $tblPerson->getId() . ']',
                                $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                $tblAddressToPerson->getId());
                        }


                    } else {
                        $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[$tblPerson->getId()]['Number'] = $count++;
                    $dataList[$tblPerson->getId()]['Student'] = new Panel($panelTitle, $panelData, Panel::PANEL_TYPE_INFO);
                }
            }
        }

        // ToDo JohK Sortierung
        // ToDo JohK Warnung bei nicht ausgewähltem Schüler, Adresse

        $Stage->setContent(
            new Form(new FormGroup(new FormRow(new FormColumn(
                new TableData($dataList, null, $columnList, null)
            ))))
        );

        return $Stage;
    }
}
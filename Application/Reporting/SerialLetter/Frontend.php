<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.04.2016
 * Time: 08:10
 */

namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Relationship\Relationship;
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

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendSerialLetter(
        $Check = null,
        $RadioStudent = null,
        $RadioCustody1 = null,
        $RadioCustody2 = null,
        $RadioFamily = null
    ) {
        $Stage = new Stage('Adresslisten', 'für Serienbriefe');

        $dataList = array();
        $columnList = array(
            'Number' => 'Nr.',
            'Student' => 'Schüler',
            'Family' => 'Familie',
            'Custody1' => 'Sorgeberechtigter 1',
            'Custody2' => 'Sorgeberechtigter 2'
        );
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        $Global = $this->getGlobal();
        if ($tblGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                $studentCount = 0;
                foreach ($tblPersonList as $tblPerson) {
                    $addressListAll = array();

                    $panelData = array();
                    $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
                    $Global->POST['Check'][$tblPerson->getId()]['Student'] = 1;
                    $Global->savePost();
                    $panelTitle = new CheckBox('Check[' . $tblPerson->getId() . '][Student]',
                        $tblPerson->getLastFirstName(), 1);
                    if ($tblAddressToPersonList) {

                        // ToDo Johk erste Hauptadresse selektieren
                        $Global->POST['RadioStudent'][$tblPerson->getId()] = reset($tblAddressToPersonList)->getId();
                        $Global->savePost();

                        foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                            $addressListAll[$tblAddressToPerson->getTblAddress()->getId()] = $tblAddressToPerson;
                            $panelData[] = new RadioBox('RadioStudent[' . $tblPerson->getId() . ']',
                                $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                $tblAddressToPerson->getId());
                        }

                    } else {
                        $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[$tblPerson->getId()]['Number'] = ++$studentCount;
                    $dataList[$tblPerson->getId()]['Student'] = new Panel($panelTitle, $panelData,
                        Panel::PANEL_TYPE_INFO);

                    // Sorgeberechtigte
                    $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    $count = 0;
                    if ($tblRelationshipAll) {
                        foreach ($tblRelationshipAll as $tblRelationship) {
                            if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                                if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                                    if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt' && $count < 2) {
                                        $count++;
                                        $panelData = array();
                                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                                        $panelTitle = new CheckBox('Check[' . $tblPerson->getId() . '][Custody' . $count . ']',
                                            $tblRelationship->getServiceTblPersonFrom()->getFullName(), 1);
                                        if ($tblAddressToPersonList) {

                                            // ToDo Johk erste Hauptadresse selektieren
                                            $Global->POST['RadioCustody' . $count][$tblPerson->getId()] = reset($tblAddressToPersonList)->getId();
                                            $Global->savePost();

                                            foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                                                $addressListAll[$tblAddressToPerson->getTblAddress()->getId()] = $tblAddressToPerson;
                                                $panelData[] = new RadioBox('RadioCustody' . $count . '[' . $tblPerson->getId() . ']',
                                                    $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                                    $tblAddressToPerson->getId());
                                            }

                                        } else {
                                            $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                                        }

                                        $dataList[$tblPerson->getId()]['Custody' . $count] = new Panel($panelTitle,
                                            $panelData,
                                            Panel::PANEL_TYPE_INFO);
                                    }
                                }
                            }
                        }
                    }

                    if (!isset($dataList[$tblPerson->getId()]['Custody1'])) {
                        $dataList[$tblPerson->getId()]['Custody1'] = '';
                    }
                    if (!isset($dataList[$tblPerson->getId()]['Custody2'])) {
                        $dataList[$tblPerson->getId()]['Custody2'] = '';
                    }

                    // Family
                    $panelData = array();
                    $panelTitle = new CheckBox('Check[Family][' . $tblPerson->getId() . ']',
                        'Familie', 1);
                    if (!empty($addressListAll)) {

                        // ToDo Johk erste Hauptadresse selektieren
                        $Global->POST['RadioFamily'][$tblPerson->getId()] = reset($addressListAll)->getId();
                        $Global->savePost();

                        /** @var TblToPerson $tblAddressToPerson */
                        foreach ($addressListAll as $tblAddressToPerson) {
                            $panelData[] = new RadioBox('RadioFamily[' . $tblPerson->getId() . ']',
                                $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                $tblAddressToPerson->getId());
                        }

                    } else {
                        $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[$tblPerson->getId()]['Family'] = new Panel($panelTitle, $panelData,
                        Panel::PANEL_TYPE_INFO);
                }
            }
        }

        // ToDo JohK Sortierung
        // ToDo JohK Warnung bei nicht ausgewähltem Schüler, Adresse

        $Stage->setContent(
            new Form(new FormGroup(new FormRow(new FormColumn(
                new TableData($dataList, null, $columnList, false)
            ))))
        );

        return $Stage;
    }
}

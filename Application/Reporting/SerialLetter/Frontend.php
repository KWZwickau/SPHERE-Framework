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
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $SerialLetter
     *
     * @return Stage
     */
    public function frontendSerialLetter($SerialLetter = null)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Übersicht');

        $tblSerialLetterAll = SerialLetter::useService()->getSerialLetterAll();

        if ($tblSerialLetterAll) {
            foreach ($tblSerialLetterAll as &$tblSerialLetter) {
                $tblSerialLetter->Group = $tblSerialLetter->getServiceTblGroup() ? $tblSerialLetter->getServiceTblGroup()->getName() : '';
                $tblSerialLetter->Option =
                    (new Standard(new Select(), '/Reporting/SerialLetter/Select', null,
                        array('Id' => $tblSerialLetter->getId()), 'Addressliste für Serienbriefe auswählen'));
            }
        }

        $Form = $this->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblSerialLetterAll, null, array(
                                'Name' => 'Name',
                                'Group' => 'Personengruppe',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(SerialLetter::useService()->createSerialLetter($Form, $SerialLetter))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSerialLetter()
    {

        $tblGroupAll = Group::useService()->getGroupAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('SerialLetter[Name]', 'Name', 'Name'), 8
                ),
                new FormColumn(
                    new SelectBox('SerialLetter[Group]', 'Personengruppe', array('Name' => $tblGroupAll)), 4
                ),
                new FormColumn(
                    new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 12
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $Check
     * @param null $RadioStudent
     * @param null $RadioCustody1
     * @param null $RadioCustody2
     * @param null $RadioFamily
     *
     * @return Stage
     */
    public function frontendSerialLetterSelected(
        $Id = null,
        $Check = null,
        $RadioStudent = null,
        $RadioCustody1 = null,
        $RadioCustody2 = null,
        $RadioFamily = null
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));

        if (($tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id))) {

            $dataList = array();
            $columnList = array();

            $tblGroup = $tblSerialLetter->getServiceTblGroup();
            $Global = $this->getGlobal();
            if ($tblGroup) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');

                    // is Student only List
                    $isStudentList = true;
                    foreach ($tblPersonList as $tblPerson) {
                        $tblStudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                        if (Group::useService()->existsGroupPerson($tblStudentGroup, $tblPerson)) {
                            continue;
                        } else {
                            $isStudentList = false;
                            break;
                        }
                    }

                    if (!$isStudentList) {
                        // is Prospect only List
                        $isProspectList = true;
                        foreach ($tblPersonList as $tblPerson) {
                            $tblStudentGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
                            if (Group::useService()->existsGroupPerson($tblStudentGroup, $tblPerson)) {
                                continue;
                            } else {
                                $isProspectList = false;
                                break;
                            }
                        }
                    } else {
                        $isProspectList = false;
                    }

                    if ($isStudentList) {
                        $columnList = array(
                            'Number' => 'Nr.',
                            'Student' => 'Schüler',
                            'Family' => 'Familie',
                            'Custody1' => 'Sorgeberechtigter 1',
                            'Custody2' => 'Sorgeberechtigter 2'
                        );
                    } elseif ($isProspectList) {
                        $columnList = array(
                            'Number' => 'Nr.',
                            'Student' => 'Interessent',
                            'Family' => 'Familie',
                            'Custody1' => 'Sorgeberechtigter 1',
                            'Custody2' => 'Sorgeberechtigter 2'
                        );
                    } else {
                        $columnList = array(
                            'Number' => 'Nr.',
                            'Student' => 'Person',
                        );
                    }

                    $personCount = 0;
                    /** @var TblPerson $tblPerson */
                    foreach ($tblPersonList as $tblPerson) {
                        $addressListAll = array();

                        $panelData = array();
                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);

                        if ($tblAddressToPersonList) {

                            // Werte aus der Datenbank laden bzw. vorselektieren der Adressen
                            if ($Check == null) {
                                $addressPerson = false;
                                foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                                    if (($addressPerson = SerialLetter::useService()->getAddressPerson(
                                        $tblSerialLetter, $tblPerson, $tblAddressToPerson,
                                        SerialLetter::useService()->getTypeByIdentifier('PERSON')
                                    ))
                                    ) {
                                        break;
                                    }
                                }

                                if ($addressPerson) {
                                    $Global->POST['Check'][$tblPerson->getId()]['Student'] = 1;
                                    $Global->POST['RadioStudent'][$tblPerson->getId()] = $addressPerson->getServiceTblToPerson()->getId();
                                } else {
                                    $Global->POST['RadioStudent'][$tblPerson->getId()] = reset($tblAddressToPersonList)->getId();
                                }
                                $Global->savePost();
                            }

                            foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                                $addressListAll[$tblAddressToPerson->getTblAddress()->getId()] = $tblAddressToPerson;
                                $panelData[] = new RadioBox('RadioStudent[' . $tblPerson->getId() . ']',
                                    $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                    $tblAddressToPerson->getId());
                            }

                        } else {
                            $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                        }

                        $panelTitle = new CheckBox('Check[' . $tblPerson->getId() . '][Student]',
                            $tblPerson->getLastFirstName(), 1);
                        $dataList[$tblPerson->getId()]['Number'] = ++$personCount . new HiddenField('Check[' . $tblPerson->getId() . '][Hidden]');
                        $dataList[$tblPerson->getId()]['Student'] = new Panel($panelTitle, $panelData,
                            Panel::PANEL_TYPE_INFO);

                        if ($isStudentList || $isProspectList) {
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
                                                if ($tblAddressToPersonList) {

                                                    // Werte aus der Datenbank laden bzw. vorselektieren der Adressen
                                                    if ($Check == null) {
                                                        $addressPerson = false;
                                                        foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                                                            if (($addressPerson = SerialLetter::useService()->getAddressPerson(
                                                                $tblSerialLetter, $tblPerson, $tblAddressToPerson,
                                                                SerialLetter::useService()->getTypeByIdentifier('CUSTODY')
                                                            ))
                                                            ) {
                                                                break;
                                                            }
                                                        }

                                                        if ($addressPerson) {
                                                            $Global->POST['Check'][$tblPerson->getId()]['Custody' . $count]
                                                                = $addressPerson->getServiceTblToPerson()->getServiceTblPerson()->getId();
                                                            $Global->POST['RadioCustody' . $count][$tblPerson->getId()]
                                                                = $addressPerson->getServiceTblToPerson()->getId();
                                                        } else {
                                                            $Global->POST['RadioCustody' . $count][$tblPerson->getId()]
                                                                = reset($tblAddressToPersonList)->getId();
                                                        }
                                                        $Global->savePost();
                                                    }

                                                    foreach ($tblAddressToPersonList as $tblAddressToPerson) {
                                                        $addressListAll[$tblAddressToPerson->getTblAddress()->getId()] = $tblAddressToPerson;
                                                        $panelData[] = new RadioBox('RadioCustody' . $count . '[' . $tblPerson->getId() . ']',
                                                            $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                                            $tblAddressToPerson->getId());
                                                    }

                                                } else {
                                                    $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                                                }
                                                $panelTitle = new CheckBox('Check[' . $tblPerson->getId() . '][Custody' . $count . ']',
                                                    $tblRelationship->getServiceTblPersonFrom()->getFullName(),
                                                    $tblRelationship->getServiceTblPersonFrom()->getId());
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

                            if (!empty($addressListAll)) {

                                // Werte aus der Datenbank laden bzw. vorselektieren der Adressen
                                if ($Check == null) {
                                    $addressPerson = false;
                                    /** @var TblToPerson $tblAddressToPerson */
                                    foreach ($addressListAll as $tblAddressToPerson) {
                                        if (($addressPerson = SerialLetter::useService()->getAddressPerson(
                                            $tblSerialLetter, $tblPerson, $tblAddressToPerson,
                                            SerialLetter::useService()->getTypeByIdentifier('FAMILY')
                                        ))
                                        ) {
                                            break;
                                        }
                                    }

                                    if ($addressPerson) {
                                        $Global->POST['Check'][$tblPerson->getId()]['Family'] = 1;
                                        $Global->POST['RadioFamily'][$tblPerson->getId()]
                                            = $addressPerson->getServiceTblToPerson()->getId();
                                    } else {
                                        $Global->POST['RadioFamily'][$tblPerson->getId()]
                                            = reset($addressListAll)->getId();
                                    }
                                    $Global->savePost();
                                }

                                /** @var TblToPerson $tblAddressToPerson */
                                foreach ($addressListAll as $tblAddressToPerson) {
                                    $panelData[] = new RadioBox('RadioFamily[' . $tblPerson->getId() . ']',
                                        $tblAddressToPerson->getTblType()->getName() . ' ' . $tblAddressToPerson->getTblAddress()->getGuiString(),
                                        $tblAddressToPerson->getId());
                                }

                            } else {
                                $panelData = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                            }
                            $panelTitle = new CheckBox('Check[' . $tblPerson->getId() . '][Family]',
                                'Familie', 1);
                            $dataList[$tblPerson->getId()]['Family'] = new Panel($panelTitle, $panelData,
                                Panel::PANEL_TYPE_INFO);
                        }
                    }
                }
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $tblSerialLetter->getName() . ' '
                            . new Small(new Muted($tblSerialLetter->getDescription())), Panel::PANEL_TYPE_INFO), 8
                    ),
                    new LayoutColumn(
                        new Panel('Gruppe',
                            $tblSerialLetter->getServiceTblGroup() ? $tblSerialLetter->getServiceTblGroup()->getName() : '',
                            Panel::PANEL_TYPE_INFO), 4
                    ),
                    new LayoutColumn(
                        SerialLetter::useService()->setPersonAddressSelection(
                            new Form(new FormGroup(new FormRow(
                                new FormColumn(array(
                                        new TableData($dataList, null, $columnList, false)
                                    ,
                                        new Primary('Speichern', new Save())
                                    )
                                )))), $tblSerialLetter, $Check, $RadioStudent, $RadioCustody1, $RadioCustody2,
                            $RadioFamily
                        )
                    )
                ))))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
    }
}

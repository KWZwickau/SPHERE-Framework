<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Frontend extends Extension implements IFrontendInterface
{


    /**
     * @param bool|false $TabActive
     *
     * @param null|int   $Id
     * @param null|array $Company
     * @param null|array $Meta
     *
     * @return Stage
     */
    public function frontendCompany($TabActive = false, $Id = null, $Company = null, $Meta = null, $Group = null)
    {

        $Stage = new Stage('Firmen', 'Datenblatt');
        if ($Group) {
            $Stage->addButton(new Standard('Zurück', '/Corporation/Search/Group', new ChevronLeft(),
                array('Id' => $Group)));
        }

        if (!$Id) {

            $BasicTable = Company::useService()->createCompany(
                $this->formCompany()
                    ->appendFormButton(new Primary('Grunddaten anlegen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Company);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(new LayoutColumn($BasicTable)),
                        new Title(new Building().' Grunddaten', 'der Firma')
                    ),
                ))
            );

        } else {
            $tblCompany = Company::useService()->getCompanyById($Id);

            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Company'] )) {
                $Global->POST['Company']['Name'] = $tblCompany->getName();
                $Global->POST['Company']['Description'] = $tblCompany->getDescription();
                $tblGroupAll = Group::useService()->getGroupAllByCompany($tblCompany);
                if (!empty( $tblGroupAll )) {
                    /** @var TblGroup $tblGroup */
                    foreach ((array)$tblGroupAll as $tblGroup) {
                        $Global->POST['Company']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                    }
                }
                $Global->savePost();
            }

            $BasicTable = Company::useService()->updateCompany(
                $this->formCompany()
                    ->appendFormButton(new Primary('Grunddaten speichern'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $tblCompany, $Company, $Group);

            $MetaTabs = Group::useService()->getGroupAllByCompany($tblCompany);
            // Sort by Name
            usort($MetaTabs, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                return strnatcmp($ObjectA->getName(), $ObjectB->getName());
            });
            // Create Tabs
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($MetaTabs, function (TblGroup &$tblGroup, $Index, TblCompany $tblCompany) {

                switch (strtoupper($tblGroup->getMetaTable())) {
//                    case 'COMMON':
//                        $tblGroup = new LayoutTab( 'Allgemein', $tblGroup->getMetaTable(),
//                            array( 'tblCompany' => $tblCompany->getId() )
//                        );
//                        break;
                    default:
                        $tblGroup = false;
                }
            }, $tblCompany);
            /** @var LayoutTab[] $MetaTabs */
            $MetaTabs = array_filter($MetaTabs);
            // Folded ?
            if (!empty( $MetaTabs )) {
                if (!$TabActive || $TabActive == '#') {
                    array_unshift($MetaTabs, new LayoutTab('&nbsp;'.new ChevronRight().'&nbsp;', '#',
                        array('Id' => $tblCompany->getId())
                    ));
                    $MetaTabs[0]->setActive();
                } else {
                    array_unshift($MetaTabs, new LayoutTab('&nbsp;'.new ChevronUp().'&nbsp;', '#',
                        array('Id' => $tblCompany->getId())
                    ));
                }
            }

//            switch (strtoupper($TabActive)) {
//                case 'COMMON':
//                    $MetaTable = Common::useFrontend()->frontendMeta( $tblCompany, $Meta );
//                    break;
//                default:
//                    if (!empty( $MetaTabs )) {
//                        $MetaTable = new Well(new Muted('Bitte wählen Sie eine Rubrik'));
//                    } else {
//                        $MetaTable = new Well(new Warning('Keine Informationen verfügbar'));
//                    }
//            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(new LayoutColumn(array(
                            $BasicTable
                        ))),
                        new Title(new Building().' Grunddaten', 'der Firma')
                    ),
//                    new LayoutGroup(array(
//                        new LayoutRow(new LayoutColumn(new LayoutTabs($MetaTabs))),
//                        new LayoutRow(new LayoutColumn($MetaTable)),
//                    ), new Title(new Tag().' Informationen', 'zur Firma')),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            Address::useFrontend()->frontendLayoutCompany($tblCompany)
                        )),
                    ), (new Title(new TagList().' Adressdaten', 'der Firma'))
                        ->addButton(
                            new Standard('Adresse hinzufügen', '/Corporation/Company/Address/Create',
                                new ChevronDown(), array('Id' => $tblCompany->getId())
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            Phone::useFrontend()->frontendLayoutCompany($tblCompany)
                            .Mail::useFrontend()->frontendLayoutCompany($tblCompany)
                        )),
                    ), (new Title(new TagList().' Kontaktdaten', 'der Firma'))
                        ->addButton(
                            new Standard('Telefonnummer hinzufügen', '/Corporation/Company/Phone/Create',
                                new ChevronDown(), array('Id' => $tblCompany->getId())
                            )
                        )
                        ->addButton(
                            new Standard('E-Mail Adresse hinzufügen', '/Corporation/Company/Mail/Create',
                                new ChevronDown(), array('Id' => $tblCompany->getId())
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            Relationship::useFrontend()->frontendLayoutCompany($tblCompany)
                        ))),
                    ), (new Title(new TagList().' Beziehungen', 'zu Personen'))
                    ),
                ))
            );

        }

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formCompany()
    {

        $tblGroupList = Group::useService()->getGroupAll();
        if ($tblGroupList) {
            // Sort by Name
            usort($tblGroupList, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                return strnatcmp($ObjectA->getName(), $ObjectB->getName());
            });
            // Create CheckBoxes
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupList, function (TblGroup &$tblGroup) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Company']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Company[Group]['.$tblGroup->getId().']',
                            $tblGroup->getName().' '.new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = new CheckBox(
                            'Company[Group]['.$tblGroup->getId().']',
                            $tblGroup->getName().' '.new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Firmenname', array(
                            new TextField('Company[Name]', 'Name', 'Name'),
                            new TextField('Company[Description]', 'Beschreibung', 'Beschreibung'),
                        ), Panel::PANEL_TYPE_INFO), 8),
                    new FormColumn(
                        new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO), 4),
                ))
            ))
        );
    }
}

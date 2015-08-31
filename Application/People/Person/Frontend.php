<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Person
 */
class Frontend extends Extension implements IFrontendInterface
{


    /**
     * @param bool|false $TabActive
     *
     * @param null|int   $Id
     * @param null|array $Person
     * @param null|array $Meta
     *
     * @return Stage
     */
    public function frontendPerson($TabActive = false, $Id = null, $Person = null, $Meta = null)
    {

        $Stage = new Stage('Personen', 'Datenblatt');
        $Stage->setMessage(new Danger(new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'));

        $tblGroupAll = Group::useService()->getGroupAll();
        if ($tblGroupAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup, $Index, Stage $Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName(),
                        new Route('/People/Search/Group'), null,
                        array('Id' => $tblGroup->getId()),
                        $tblGroup->getDescription()
                    )
                );
            }, $Stage);
        }

        if (!$Id) {

            $BasicTable = Person::useService()->createPerson(
                $this->formPerson()
                    ->appendFormButton(new Primary('Grunddaten anlegen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Person);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(new LayoutColumn($BasicTable)),
                        new Title(new PersonParent().' Grunddaten', 'der Person')
                    ),
                ))
            );

        } else {
            $tblPerson = Person::useService()->getPersonById($Id);

            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Person'] )) {
                $Global->POST['Person']['Salutation'] = $tblPerson->getTblSalutation()->getId();
                $Global->POST['Person']['Title'] = $tblPerson->getTitle();
                $Global->POST['Person']['FirstName'] = $tblPerson->getFirstName();
                $Global->POST['Person']['SecondName'] = $tblPerson->getSecondName();
                $Global->POST['Person']['LastName'] = $tblPerson->getLastName();
                $tblGroupAll = Group::useService()->getGroupAllByPerson($tblPerson);
                if (!empty( $tblGroupAll )) {
                    /** @var TblGroup $tblGroup */
                    foreach ((array)$tblGroupAll as $tblGroup) {
                        $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                    }
                }
                $Global->savePost();
            }

            $BasicTable = Person::useService()->updatePerson(
                $this->formPerson()
                    ->appendFormButton(new Primary('Grunddaten speichern'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $tblPerson, $Person);

            $MetaTabs = Group::useService()->getGroupAllByPerson($tblPerson);
            // Sort by Name
            usort($MetaTabs, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                return strnatcmp($ObjectA->getName(), $ObjectB->getName());
            });
            // Create Tabs
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($MetaTabs, function (TblGroup &$tblGroup, $Index, TblPerson $tblPerson) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $tblGroup = new LayoutTab('Allgemein', $tblGroup->getMetaTable(),
                            array('Id' => $tblPerson->getId())
                        );
                        break;
                    case 'PROSPECT':
                        $tblGroup = new LayoutTab('Interessent', $tblGroup->getMetaTable(),
                            array('Id' => $tblPerson->getId())
                        );
                        break;
                    case 'STUDENT':
                        $tblGroup = new LayoutTab('Schülerakte', $tblGroup->getMetaTable(),
                            array('Id' => $tblPerson->getId())
                        );
                        break;
                    case 'CUSTODY':
                        $tblGroup = new LayoutTab('Sorgeberechtigt', $tblGroup->getMetaTable(),
                            array('Id' => $tblPerson->getId())
                        );
                        break;
                    default:
                        $tblGroup = false;
                }
            }, $tblPerson);
            /** @var LayoutTab[] $MetaTabs */
            $MetaTabs = array_filter($MetaTabs);
            // Folded ?
            if (!empty( $MetaTabs )) {
                if (!$TabActive || $TabActive == '#') {
                    array_unshift($MetaTabs, new LayoutTab('&nbsp;'.new ChevronRight().'&nbsp;', '#',
                        array('Id' => $tblPerson->getId())
                    ));
                    $MetaTabs[0]->setActive();
                } else {
                    array_unshift($MetaTabs, new LayoutTab('&nbsp;'.new ChevronUp().'&nbsp;', '#',
                        array('Id' => $tblPerson->getId())
                    ));
                }
            }

            switch (strtoupper($TabActive)) {
                case 'COMMON':
                    $MetaTable = Common::useFrontend()->frontendMeta($tblPerson, $Meta);
                    break;
                case 'PROSPECT':
                    $MetaTable = Prospect::useFrontend()->frontendMeta($tblPerson, $Meta);
                    break;
                case 'STUDENT':
                    $MetaTable = Student::useFrontend()->frontendMeta($tblPerson, $Meta);
                    break;
                case 'CUSTODY':
                    $MetaTable = Custody::useFrontend()->frontendMeta($tblPerson, $Meta);
                    break;
                default:
                    if (!empty( $MetaTabs )) {
                        $MetaTable = new Well(new Muted('Bitte wählen Sie eine Rubrik'));
                    } else {
                        $MetaTable = new Well(new Warning('Keine Informationen verfügbar'));
                    }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(new LayoutColumn($BasicTable)),
                        new Title(new PersonParent().' Grunddaten', 'der Person')
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(new LayoutTabs($MetaTabs))),
                        new LayoutRow(new LayoutColumn($MetaTable)),
                    ), new Title(new Tag().' Informationen', 'zur Person')),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            Address::useFrontend()->frontendLayoutPerson($tblPerson)
                        )),
                    ), (new Title(new TagList().' Adressdaten', 'der Person'))
                        ->addButton(
                            new Standard('Adresse hinzufügen', '/People/Person/Address/Create',
                                new ChevronDown(), array('Id' => $tblPerson->getId())
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            Phone::useFrontend()->frontendLayoutPerson($tblPerson)
                            .Mail::useFrontend()->frontendLayoutPerson($tblPerson)
                        )),
                    ), (new Title(new TagList().' Kontaktdaten', 'der Person'))
                        ->addButton(
                            new Standard('Telefonnummer hinzufügen', '/People/Person/Phone/Create',
                                new ChevronDown(), array('Id' => $tblPerson->getId())
                            )
                        )
                        ->addButton(
                            new Standard('E-Mail Adresse hinzufügen', '/People/Person/Mail/Create',
                                new ChevronDown(), array('Id' => $tblPerson->getId())
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            Relationship::useFrontend()->frontendLayoutPerson($tblPerson)
                        )),
                    ), (new Title(new TagList().' Beziehungen', 'zu Personen'))
                        ->addButton(
                            new Standard('Beziehung hinzufügen', '/People/Person/Relationship/Create',
                                new ChevronDown(), array('Id' => $tblPerson->getId())
                            )
                        )
                    ),
                ))
            );

        }

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formPerson()
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

                $tblGroup = new CheckBox(
                    'Person[Group]['.$tblGroup->getId().']',
                    $tblGroup->getName().' '.new Muted(new Small($tblGroup->getDescription())),
                    $tblGroup->getId()
                );
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        $tblSalutationAll = Person::useService()->getSalutationAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anrede', array(
                            new SelectBox('Person[Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
                                new Conversation()),
                            new AutoCompleter('Person[Title]', 'Titel', 'Titel', array('Dipl.- Ing.'),
                                new Conversation()),
                        ), Panel::PANEL_TYPE_INFO), 4),
                    new FormColumn(
                        new Panel('Name', array(
                            new TextField('Person[FirstName]', 'Vorname', 'Vorname'),
                            new TextField('Person[SecondName]', 'Zweitname', 'Zweitname'),
                            new TextField('Person[LastName]', 'Nachname', 'Nachname'),
                        ), Panel::PANEL_TYPE_INFO), 4),
                    new FormColumn(
                        new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO), 4),
                ))
            ))
        );
    }
}

<?php

namespace SPHERE\Application\People\Person;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class FrontendEdit
 *
 * @package SPHERE\Application\People\Person
 */
class FrontendEdit extends FrontendReadOnly
{

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditBasicContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if ($tblPerson->getTblSalutation()) {
                $Global->POST['Person']['Salutation'] = $tblPerson->getTblSalutation()->getId();
            }
            $Global->POST['Person']['Title'] = $tblPerson->getTitle();
            $Global->POST['Person']['FirstName'] = $tblPerson->getFirstName();
            $Global->POST['Person']['SecondName'] = $tblPerson->getSecondName();
            $Global->POST['Person']['CallName'] = $tblPerson->getCallName();
            $Global->POST['Person']['LastName'] = $tblPerson->getLastName();
            $Global->POST['Person']['BirthName'] = $tblPerson->getBirthName();
            $tblGroupAll = Group::useService()->getGroupAllByPerson($tblPerson);
            if (!empty($tblGroupAll)) {
                /** @var TblGroup $tblGroup */
                foreach ((array)$tblGroupAll as $tblGroup) {
                    $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                }
            }

            $Global->savePost();
        }

        return $this->getEditBasicTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditBasicForm($tblPerson ? $tblPerson : null));
    }

    private function getEditBasicTitle(TblPerson $tblPerson = null)
    {
        return new Title(new PersonParent() . ' Grunddaten', 'der Person'
            . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '') . ' bearbeiten');
    }

    private function getEditBasicForm(TblPerson $tblPerson = null)
    {
        $tblSalutationAll = Person::useService()->getSalutationAll();

        $tblGroupList = Group::useService()->getGroupAllSorted();
        if ($tblGroupList) {
            // Create CheckBoxes
            $tabIndex = 8;
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupList, function (TblGroup &$tblGroup) use (&$tabIndex) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Person[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = (new CheckBox(
                            'Person[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        ))->setTabIndex($tabIndex++);
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anrede', array(
                            (new SelectBox('Person[Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
                                new Conversation()))->setTabIndex(1),
                            (new AutoCompleter('Person[Title]', 'Titel', 'Titel', array('Dipl.- Ing.'),
                                new Conversation()))->setTabIndex(4),
                        ), Panel::PANEL_TYPE_INFO), 2),
                    new FormColumn(
                        new Panel('Vorname', array(
                            (new TextField('Person[FirstName]', 'Vorname', 'Vorname'))->setRequired()
                                ->setTabIndex(2),
                            (new TextField('Person[SecondName]', 'weitere Vornamen',
                                'Zweiter Vorname'))->setTabIndex(5),
                            (new TextField('Person[CallName]', 'Rufname', 'Rufname'))->setTabIndex(6),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Nachname', array(
                            (new TextField('Person[LastName]', 'Nachname', 'Nachname'))->setRequired()
                                ->setTabIndex(3),
                            (new TextField('Person[BirthName]', 'Geburtsname', 'Geburtsname'))->setTabIndex(7),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveBasicContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelBasicContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return bool|string
     */
    public function checkInputBasicContent(TblPerson $tblPerson = null, $Person)
    {
        $error = false;
        $form = $this->getEditBasicForm($tblPerson ? $tblPerson : null);
        if (isset($Person['FirstName'] ) && empty($Person['FirstName'] )) {
            $form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $error = true;
        }
        if (isset($Person['LastName'] ) && empty($Person['LastName'] )) {
            $form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $error = true;
        }

        if ($error) {
            return $this->getEditBasicTitle($tblPerson ? $tblPerson : null)
                . new Well($form);
        }

        return $error;
    }
}
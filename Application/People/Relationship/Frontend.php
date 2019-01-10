<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\Api\Contact\ApiRelationshipToCompany;
use SPHERE\Application\Api\Contact\ApiRelationshipToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Relationship
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $PersonId
     * @param null $ToPersonId
     * @param bool $setPost
     * @param string $Search
     * @param IMessageInterface|null $message
     *
     * @return Form|bool
     */
    public function formRelationshipToPerson($PersonId, $ToPersonId = null, $setPost = false, $Search = '', IMessageInterface $message = null)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return false;
        }

        if ($ToPersonId && ($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->POST['To'] = $tblToPerson->getServiceTblPersonTo()
                    ? $tblToPerson->getServiceTblPersonTo()->getId() : 0;
                $Global->savePost();
            }

            $currentPerson = $tblToPerson->getServiceTblPersonTo();
        } else {
            $currentPerson = false;
        }

        if ($ToPersonId) {
            $saveButton = (new PrimaryLink('Speichern', ApiRelationshipToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineEditRelationshipToPersonSave($PersonId, $ToPersonId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiRelationshipToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineCreateRelationshipToPersonSave($PersonId));
        }

        $tblGroup = Relationship::useService()->getGroupByIdentifier('PERSON');
        if ((($tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT'))
                && (Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)))
            || (($tblGroupProspect = Group::useService()->getGroupByMetaTable('PROSPECT'))
                && (Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)))
        ) {
            $tblTypeAll[] = Relationship::useService()->getTypeByName('Geschwisterkind');
            $tblTypeChild = new TblType();
            $tblTypeChild->setId(TblType::CHILD_ID);
            $tblTypeChild->setName('Kind');
            $tblTypeAll[] = $tblTypeChild;
        } else {
            $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
        }

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        FrontendReadOnly::getDataProtectionMessage()
                    ))
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('hat folgende Beziehung ' . new Link(),
                            array(
                                (new SelectBox('Type[Type]', 'Beziehungstyp',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                new TextField('Type[Remark]', 'Bemerkungen - z.B: Mutter / Vater / ..', 'Bemerkungen',
                                    new Pencil()
                                )
                            )
                            , Panel::PANEL_TYPE_INFO
                        ),
                    )),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'zur folgenden Person ' . new PersonIcon(),
                            array(
                                $currentPerson
                                    ? new RadioBox('To', $currentPerson->getLastFirstName(), $currentPerson->getId())
                                    : null,
                                (new TextField(
                                    'Search',
                                    '',
                                    'Suche',
                                    new Search()
                                ))->ajaxPipelineOnKeyUp(ApiRelationshipToPerson::pipelineSearchPerson()),
                                ApiRelationshipToPerson::receiverBlock($this->loadPersonSearch($Search, $message), 'SearchPerson'),
                                new Standard('Neue Person anlegen', '/People/Person/Create', new PersonIcon()
                                    , array(), 'Die aktuell gewählte Person verlassen'
                                )
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                    ))
                )),
                new FormRow(array(
                    new FormColumn(array(
                        $saveButton
                    ))
                ))
            ))
        ))
        )->disableSubmitAction();
    }

    /**
     * @param $Search
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadPersonSearch($Search, IMessageInterface $message = null)
    {

        if ($Search != '' && strlen($Search) > 2) {
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $resultList = array();
                foreach ($tblPersonList as $tblPerson) {
                    $resultList[] = array(
                        'Select' => new RadioBox('To', '&nbsp;', $tblPerson->getId()),
                        'FirstName' => $tblPerson->getFirstSecondName(),
                        'LastName' => $tblPerson->getLastName(),
                        'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : ''
                    );
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Select' => '',
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'Address' => 'Adresse'
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            } else {
                $result = new Warning('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result . ($message ? $message : '');
    }

//    /**
//     * @param $PersonId
//     * @param null $ToPersonId
//     * @param bool $setPost
//     *
//     * @return Form|bool
//     */
//    public function formRelationshipToPerson($PersonId, $ToPersonId = null, $setPost = false)
//    {
//
//        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
//            return false;
//        }
//
//        if ($ToPersonId && ($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
//            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
//            if ($setPost) {
//                $Global = $this->getGlobal();
//                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
//                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
//                $Global->POST['To'] = $tblToPerson->getServiceTblPersonTo()
//                    ? $tblToPerson->getServiceTblPersonTo()->getId() : 0;
//                $Global->savePost();
//            }
//
//            $currentPerson = $tblToPerson->getServiceTblPersonTo();
//        } else {
//            $currentPerson = false;
//        }
//
//        if ($ToPersonId) {
//            $saveButton = (new PrimaryLink('Speichern', ApiRelationshipToPerson::getEndpoint(), new Save()))
//                ->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineEditRelationshipToPersonSave($PersonId, $ToPersonId));
//        } else {
//            $saveButton = (new PrimaryLink('Speichern', ApiRelationshipToPerson::getEndpoint(), new Save()))
//                ->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineCreateRelationshipToPersonSave($PersonId));
//        }
//
//        $tblGroup = Relationship::useService()->getGroupByIdentifier('PERSON');
//        if ((($tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT'))
//            && (Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)))
//            || (($tblGroupProspect = Group::useService()->getGroupByMetaTable('PROSPECT'))
//                && (Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)))
//        ) {
//            $tblTypeAll[] = Relationship::useService()->getTypeByName('Geschwisterkind');
//            $tblTypeChild = new TblType();
//            $tblTypeChild->setId(TblType::CHILD_ID);
//            $tblTypeChild->setName('Kind');
//            $tblTypeAll[] = $tblTypeChild;
//        } else {
//            $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
//        }
//
//        $tblPersonAll = Person::useService()->getPersonAll();
//
//        if ($tblPersonAll) {
//            array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($currentPerson) {
//
//                $tblAddress = $tblPerson->fetchMainAddress();
//
//                if ($currentPerson && $currentPerson->getId() == $tblPerson->getId()) {
//                    $tblPerson = array(
//                        'Person' => new \SPHERE\Common\Frontend\Text\Repository\Warning($tblPerson->getLastFirstName()) . ' (Aktuell hinterlegt)',
//                        'Address' => $tblAddress ? $tblAddress->getGuiString() : ''
//                    );
//                } else {
//                    $tblPerson = array(
//                        'Select' => new RadioBox('To', '&nbsp;', $tblPerson->getId()),
//                        'Person' => $tblPerson->getLastFirstName() . ' '
//                            . new \SPHERE\Common\Frontend\Link\Repository\Link(
//                            new PersonIcon(),
//                            '/People/Person',
//                            null,
//                            array('Id' => $tblPerson->getId()),
//                            'zu ' . $tblPerson->getLastFirstName() . ' wechseln'
//                        ),
//                        'Address' => $tblAddress ? $tblAddress->getGuiString() : ''
//                    );
//                }
//            });
//            $tblPersonAll = array_filter($tblPersonAll);
//        } else {
//            $tblPersonAll = array();
//        }
//
//        $columns = array(
//            'Select' => '',
//            'Person' => 'Name',
//            'Address' => 'Adresse'
//        );
//
//        $interactive =  array(
//            'order' => array(
//                array(1, 'asc'),
//            ),
//            'pageLength' => 0,
//            'responsive' => false
//        );
//
//        // Person Panel
//        if ($currentPerson) {
//            $PanelPerson = new Panel('zur folgenden Person ' . new PersonIcon(),
//                array(
//                    new \SPHERE\Common\Frontend\Text\Repository\Danger('AKTUELL hinterlegte Person, '),
//                    new PullLeft(new RadioBox('To', $currentPerson->getLastFirstName(), $currentPerson->getId()))
//                    . new PullRight(new Standard('', '/People/Person',
//                        new PersonIcon(),
//                        array('Id' => $currentPerson->getId()),
//                        'zu ' . $currentPerson->getLastFirstName() . ' wechseln')),
//                    new \SPHERE\Common\Frontend\Text\Repository\Danger('ODER eine andere Person wählen: '),
//                    new TableData(
//                        $tblPersonAll,
//                        null,
//                        $columns,
//                        $interactive
//                    ),
//                ), Panel::PANEL_TYPE_INFO,
//                new Standard('Neue Person anlegen', '/People/Person/Create', new PersonIcon()
//                    , array(), 'Die aktuell gewählte Person verlassen'
//                )
//            );
//        } else {
//            $PanelPerson = new Panel('zur folgenden Person ' . new PersonIcon(),
//                array(
//                    new TableData(
//                        $tblPersonAll,
//                        null,
//                        $columns,
//                        $interactive
//                    ),
//                ), Panel::PANEL_TYPE_INFO,
//                new Standard('Neue Person anlegen', '/People/Person/Create', new PersonIcon()
//                    , array(), 'Die aktuell gewählte Person verlassen'
//                )
//            );
//        }
//
//        return (new Form(
//            new FormGroup(array(
//                new FormRow(array(
//                    new FormColumn(array(
//                        new Panel('hat folgende Beziehung',
//                            array(
//                                new SelectBox('Type[Type]', 'Beziehungstyp',
//                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
//                                ),
//                                new TextArea('Type[Remark]', 'Bemerkungen - z.B: Mutter / Vater / ..', 'Bemerkungen',
//                                    new Pencil()
//                                ),
//                                new \SPHERE\Common\Frontend\Text\Repository\Danger(
//                                    new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
//                                ),
//                            ), Panel::PANEL_TYPE_INFO
//                        ),
//                    ), 4),
//                    new FormColumn(array(
//                        $PanelPerson
//                    ), 8),
//                    new FormColumn(
//                        $saveButton
//                    )
//                )),
//            ))
//        ))->disableSubmitAction();
//    }

    /**
     * @param $PersonId
     * @param null $ToCompanyId
     * @param bool $setPost
     * @param string $Search
     * @param IMessageInterface|null $message
     *
     * @return Form|bool
     */
    public function formRelationshipToCompany($PersonId, $ToCompanyId = null, $setPost = false, $Search = '', IMessageInterface $message = null)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return false;
        }

        if ($ToCompanyId && ($tblToCompany = Relationship::useService()->getRelationshipToCompanyById($ToCompanyId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->POST['To'] = $tblToCompany->getServiceTblCompany()
                    ? $tblToCompany->getServiceTblCompany()->getId() : 0;
                $Global->savePost();
            }

            $currentCompany = $tblToCompany->getServiceTblCompany();
        } else {
            $currentCompany = false;
        }

        if ($ToCompanyId) {
            $saveButton = (new PrimaryLink('Speichern', ApiRelationshipToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiRelationshipToCompany::pipelineEditRelationshipToCompanySave($PersonId, $ToCompanyId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiRelationshipToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiRelationshipToCompany::pipelineCreateRelationshipToCompanySave($PersonId));
        }

        $tblGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
        $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        FrontendReadOnly::getDataProtectionMessage()
                    ))
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('hat folgende Beziehung ' . new Link(),
                            array(
                                (new SelectBox('Type[Type]', 'Beziehungstyp',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                new TextField('Type[Remark]', 'Bemerkungen - z.B: Schulleiter / Geschäftsführer / ..', 'Bemerkungen',
                                    new Pencil()
                                )
                            )
                            , Panel::PANEL_TYPE_INFO
                        ),
                    )),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'zur folgenden Institution ' . new Building(),
                            array(
                                $currentCompany
                                    ? new RadioBox('To', $currentCompany->getDisplayName(), $currentCompany->getId())
                                    : null,
                                (new TextField(
                                    'Search',
                                    '',
                                    'Suche',
                                    new Search()
                                ))->ajaxPipelineOnKeyUp(ApiRelationshipToCompany::pipelineSearchCompany()),
                                ApiRelationshipToCompany::receiverBlock($this->loadCompanySearch($Search, $message), 'SearchCompany'),
                                new Standard('Neue Institution anlegen', '/Corporation/Company/Create', new Building()
                                    , array(), 'Die aktuell gewählte Person verlassen'
                                )
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                    ))
                )),
                new FormRow(array(
                    new FormColumn(array(
                        $saveButton
                    ))
                ))
            ))
        ))
        )->disableSubmitAction();
    }

    /**
     * @param $Search
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadCompanySearch($Search, IMessageInterface $message = null)
    {

        if ($Search != '' && strlen($Search) > 2) {
            if (($tblCompanyList = Company::useService()->getCompanyListLike($Search))) {
                $resultList = array();
                foreach ($tblCompanyList as $tblCompany) {
                    $resultList[] = array(
                        'Select' => new RadioBox('To', '&nbsp;', $tblCompany->getId()),
                        'Name' => $tblCompany->getDisplayName(),
                        'Address' => ($tblAddress = $tblCompany->fetchMainAddress()) ? $tblAddress->getGuiString() : ''
                    );
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Select' => '',
                        'Name' => 'Name',
                        'Address' => 'Adresse'
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            } else {
                $result = new Warning('Es wurden keine entsprechenden Institutionen gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result . ($message ? $message : '');
    }

    // todo remove
    /**
     * @param TblPerson $tblPerson
     * @param null $Group
     *
     * @return Layout
     */
    public function frontendLayoutPerson(TblPerson $tblPerson, $Group = null)
    {

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToPerson &$tblToPerson) use ($tblPerson, $Group) {

                if ($tblToPerson->getServiceTblPersonFrom() && $tblToPerson->getServiceTblPersonTo()) {
                    if ($tblToPerson->getTblType()->isBidirectional()) {
                        $sign = ' ' . new ChevronLeft() . new ChevronRight() . ' ';
                    } else {
                        $sign = ' ' . new ChevronRight() . ' ';
                    }
                    $Panel = array(
                        ($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                            ? $tblPerson->getLastFirstName() . $sign . $tblToPerson->getServiceTblPersonTo()->getLastFirstName()
                            : $tblToPerson->getServiceTblPersonFrom()->getLastFirstName() . $sign . $tblPerson->getLastFirstName()
                        )
                    );
                    if ($tblToPerson->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                    }

                    $tblToPerson = new LayoutColumn(
                        new Panel(
                            new PersonIcon() . ' ' . new Link() . ' ' . $tblToPerson->getTblType()->getName(),
                            $Panel,
                            ($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                            || $tblToPerson->getTblType()->isBidirectional()
                                ? Panel::PANEL_TYPE_SUCCESS
                                : Panel::PANEL_TYPE_DEFAULT
                            ),
                            ($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                                ? new Standard(
                                    '', '/People/Person/Relationship/Edit', new Edit(),
                                    array('Id' => $tblToPerson->getId(), 'Group' => $Group),
                                    'Bearbeiten'
                                )
                                . new Standard(
                                    '', '/People/Person/Relationship/Destroy', new Remove(),
                                    array('Id' => $tblToPerson->getId(), 'Group' => $Group), 'Löschen'
                                )
                                . new Standard(
                                    '', '/People/Person', new PersonIcon(),
                                    array('Id' => $tblToPerson->getServiceTblPersonTo()->getId()), 'zur Person'
                                )
                                :
                                new Standard(
                                    '', '/People/Person', new PersonIcon(),
                                    array('Id' => $tblToPerson->getServiceTblPersonFrom()->getId()), 'zur Person'
                                )

                            )
                        )
                        , 3);
                } else {
                    $tblToPerson = false;
                }
            });
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Personenbeziehungen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param TblPerson $tblPerson
     * @param null $Group
     *
     * @return string
     */
    public function frontendLayoutPersonNew(TblPerson $tblPerson, $Group = null)
    {

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToPerson &$tblToPerson) use ($tblPerson, $Group) {

                if ($tblToPerson->getServiceTblPersonFrom() && $tblToPerson->getServiceTblPersonTo()) {
                    if ($tblToPerson->getTblType()->isBidirectional()) {
                        $sign = ' ' . new ChevronLeft() . new ChevronRight() . ' ';
                    } else {
                        $sign = ' ' . new ChevronRight() . ' ';
                    }

                    if (($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId())) {
                        $content[] = $tblPerson->getLastFirstName()
                            . $sign
                            . new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new PersonIcon() . ' ' . $tblToPerson->getServiceTblPersonTo()->getLastFirstName(),
                                '/People/Person',
                                null,
                                array('Id' => $tblToPerson->getServiceTblPersonTo()->getId()),
                                'zur Person'
                            );
                        $panelType = Panel::PANEL_TYPE_SUCCESS;
                        $options =
                            (new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new Edit(),
                                ApiRelationshipToPerson::getEndpoint(),
                                null,
                                array(),
                                'Bearbeiten'
                            ))->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineOpenEditRelationshipToPersonModal(
                                $tblPerson->getId(),
                                $tblToPerson->getId()
                            ))
                            . ' | '
                            . (new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                                ApiRelationshipToPerson::getEndpoint(),
                                null,
                                array(),
                                'Löschen'
                            ))->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineOpenDeleteRelationshipToPersonModal(
                                $tblPerson->getId(),
                                $tblToPerson->getId()
                            ));
                    } else {
                        $content[] = new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new PersonIcon() . ' ' . $tblToPerson->getServiceTblPersonFrom()->getLastFirstName(),
                                '/People/Person',
                                null,
                                array('Id' => $tblToPerson->getServiceTblPersonFrom()->getId()),
                                'zur Person'
                            )
                            . $sign
                            . $tblPerson->getLastFirstName();
                        $panelType = Panel::PANEL_TYPE_DEFAULT;
                        $options = '';
                    }

                    if ($tblToPerson->getRemark()) {
                        $content[] = new Muted(new Small($tblToPerson->getRemark()));
                    }

                    $tblToPerson = new LayoutColumn(
                        FrontendReadOnly::getContactPanel(
                            new PersonIcon() . ' ' . new Link() . ' ' . $tblToPerson->getTblType()->getName(),
                            $content,
                            $options,
                            $panelType
                        )
                        , 3);
                } else {
                    $tblToPerson = false;
                }
            });
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Personenbeziehungen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }

        return (string) new Layout(new LayoutGroup($LayoutRowList));
    }

    // todo remove
    /**
     * @param TblCompany|TblPerson|Element $tblEntity
     * @param null $Group
     *
     * @return Layout
     */
    public function frontendLayoutCompany(Element $tblEntity, $Group = null)
    {

        if ($tblEntity instanceof TblPerson) {
            $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByPerson($tblEntity);
        } else {
            if ($tblEntity instanceof TblCompany) {
                $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblEntity);
            } else {
                $tblRelationshipAll = false;
            }
        }

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToCompany &$tblToCompany) use ($tblEntity, $Group) {

                if ($tblToCompany->getServiceTblPerson() && $tblToCompany->getServiceTblCompany()) {
                    $Panel = array(
                        $tblToCompany->getServiceTblPerson()->getFullName(),
                        $tblToCompany->getServiceTblCompany()->getName()
                    );
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            new Building() . ' ' . new Link() . ' ' . $tblToCompany->getTblType()->getName(), $Panel,
                            ($tblEntity instanceof TblPerson
                                ? Panel::PANEL_TYPE_INFO
                                : Panel::PANEL_TYPE_DEFAULT
                            ),
                            ($tblEntity instanceof TblPerson
                                ? new Standard(
                                    '', '/Corporation/Company/Relationship/Edit', new Edit(),
                                    array('Id' => $tblToCompany->getId(), 'Group' => $Group),
                                    'Bearbeiten'
                                )
                                . new Standard(
                                    '', '/Corporation/Company/Relationship/Destroy', new Remove(),
                                    array('Id' => $tblToCompany->getId(), 'Group' => $Group), 'Löschen'
                                )
                                . new Standard(
                                    '', '/Corporation/Company', new Building(),
                                    array('Id' => $tblToCompany->getServiceTblCompany()->getId()), 'zur Institution'
                                )
                                :
                                new Standard(
                                    '', '/People/Person', new PersonIcon(),
                                    array('Id' => $tblToCompany->getServiceTblPerson()->getId()), 'zur Person'
                                )
                            )
                        )
                        , 3);
                } else {
                    $tblToCompany = false;
                }
            });
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Institutionenbeziehungen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param Element $tblEntity
     *
     * @return string
     */
    public function frontendLayoutCompanyNew(Element $tblEntity)
    {

        if ($tblEntity instanceof TblPerson) {
            $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByPerson($tblEntity);
        } else {
            if ($tblEntity instanceof TblCompany) {
                $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblEntity);
            } else {
                $tblRelationshipAll = false;
            }
        }

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToCompany &$tblToCompany) use ($tblEntity) {

                if ($tblToCompany->getServiceTblPerson() && $tblToCompany->getServiceTblCompany()) {
                    if ($tblEntity instanceof TblPerson) {
                        $content[] = $tblToCompany->getServiceTblPerson()->getFullName();
                        $content[] =
                            new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new Building() . ' ' . $tblToCompany->getServiceTblCompany()->getName(),
                                '/Corporation/Company',
                                null,
                                array('Id' => $tblToCompany->getServiceTblCompany()->getId()),
                                'zur Institution'
                            );
                        $options =
                            (new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new Edit(),
                                ApiRelationshipToCompany::getEndpoint(),
                                null,
                                array(),
                                'Bearbeiten'
                            ))->ajaxPipelineOnClick(ApiRelationshipToCompany::pipelineOpenEditRelationshipToCompanyModal(
                                $tblEntity->getId(),
                                $tblToCompany->getId()
                            ))
                            . ' | '
                            . (new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                                ApiRelationshipToCompany::getEndpoint(),
                                null,
                                array(),
                                'Löschen'
                            ))->ajaxPipelineOnClick(ApiRelationshipToCompany::pipelineOpenDeleteRelationshipToCompanyModal(
                                $tblEntity->getId(),
                                $tblToCompany->getId()
                            ));
                        $panelType = Panel::PANEL_TYPE_SUCCESS;
                    } else {
                        $content[] =
                            new \SPHERE\Common\Frontend\Link\Repository\Link(
                                new PersonIcon() . ' ' . $tblToCompany->getServiceTblPerson()->getFullName(),
                                '/People/Person',
                                null,
                                array('Id' => $tblToCompany->getServiceTblPerson()->getId()), 'zur Person'
                            );
//                        $content[] = $tblToCompany->getServiceTblCompany()->getName();
                        $options = '';
                        $panelType = Panel::PANEL_TYPE_DEFAULT;
                    }

                    if ($tblToCompany->getRemark()) {
                        $content[] = new Muted(new Small($tblToCompany->getRemark()));
                    }

                    $tblToCompany = new LayoutColumn(
                        FrontendReadOnly::getContactPanel(
                            new Building() . ' ' . new Link() . ' ' . $tblToCompany->getTblType()->getName(),
                            $content,
                            $options,
                            $panelType
                        )
                        , 3);
                } else {
                    $tblToCompany = false;
                }
            });
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Institutionenbeziehungen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }

        return (string) new Layout(new LayoutGroup($LayoutRowList));
    }
}

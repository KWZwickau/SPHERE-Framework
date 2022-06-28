<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\Api\Contact\ApiRelationshipToCompany;
use SPHERE\Application\Api\Contact\ApiRelationshipToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
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
     * @param bool $IsGuardianRelationship
     * @param IMessageInterface|null $messageOptions
     *
     * @return Form|bool
     */
    public function formRelationshipToPerson(
        $PersonId,
        $ToPersonId = null,
        $setPost = false,
        $Search = '',
        IMessageInterface $message = null,
        $IsGuardianRelationship = false,
        IMessageInterface $messageOptions = null
    ) {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return false;
        }

        if ($IsGuardianRelationship) {
            $contentExtraOptions = $this->loadExtraOptions($messageOptions);
        } else {
            $contentExtraOptions = null;
        }

        if ($ToPersonId && ($tblToPerson = Relationship::useService()->getRelationshipToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Type']['Type'] = ($tblType = $tblToPerson->getTblType()) ? $tblType->getId() : 0;
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->POST['Type']['Ranking'] = $tblToPerson->getRanking();
                $Global->POST['Type']['IsSingleParent'] = $tblToPerson->isSingleParent();

                $Global->POST['To'] = $tblToPerson->getServiceTblPersonTo()
                    ? $tblToPerson->getServiceTblPersonTo()->getId() : 0;
                $Global->savePost();

                if ($tblType && $tblType->getName() == TblType::IDENTIFIER_GUARDIAN) {
                    $contentExtraOptions = $this->loadExtraOptions();
                }
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
                && (Group::useService()->existsGroupPerson($tblGroupProspect, $tblPerson)))
        ) {
            $tblTypeAll[] = Relationship::useService()->getTypeByName('Geschwisterkind');
            $tblTypeChild = new TblType();
            $tblTypeChild->setId(TblType::CHILD_ID);
            $tblTypeChild->setName('Kind');
            $tblTypeAll[] = $tblTypeChild;
            $isChild = true;
        } else {
            $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
            $isChild = false;
        }

        $receiverExtraOptions = ApiRelationshipToPerson::receiverBlock($contentExtraOptions, 'ExtraOptions');

        if ($isChild) {
            $formRowRelationship = new FormRow(array(
                new FormColumn(array(
                    new Panel('hat folgende Beziehung ' . new Link(),
                        array(
                            (new SelectBox('Type[Type]', 'Beziehungstyp',
                                array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                            ))
                                ->setRequired()
                                ->ajaxPipelineOnChange(ApiRelationshipToPerson::pipelineLoadExtraOptions($PersonId))
                        )
                        , Panel::PANEL_TYPE_INFO
                    ),
                )),
            ));

            $formRowToPerson = new FormRow(array(
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
                            ))->ajaxPipelineOnKeyUp(ApiRelationshipToPerson::pipelineSearchPerson($isChild)),
                            ApiRelationshipToPerson::receiverBlock($this->loadPersonSearch($Search, $message, $isChild), 'SearchPerson'),
                            new Standard('Neue Person anlegen', '/People/Person/Create', new PersonIcon()
                                , array(), 'Die aktuell gewählte Person verlassen'
                            ),
                            new TextField('Type[Remark]', 'Bemerkungen - z.B: Mutter / Vater / ..', 'Bemerkungen',
                                new Pencil()
                            ),
                            $receiverExtraOptions
                        ),
                        Panel::PANEL_TYPE_INFO
                    )
                ))
            ));
        } else {
            $formRowRelationship = new FormRow(array(
                new FormColumn(array(
                    new Panel('hat folgende Beziehung ' . new Link(),
                        array(
                            (new SelectBox('Type[Type]', 'Beziehungstyp',
                                array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                            ))
                                ->setRequired()
                                ->ajaxPipelineOnChange(ApiRelationshipToPerson::pipelineLoadExtraOptions($PersonId))
                            ,
                            new TextField('Type[Remark]', 'Bemerkungen - z.B: Mutter / Vater / ..', 'Bemerkungen',
                                new Pencil()
                            ),
                            $receiverExtraOptions
                        )
                        , Panel::PANEL_TYPE_INFO
                    ),
                )),
            ));

            $formRowToPerson = new FormRow(array(
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
                            ))->ajaxPipelineOnKeyUp(ApiRelationshipToPerson::pipelineSearchPerson($isChild)),
                            ApiRelationshipToPerson::receiverBlock($this->loadPersonSearch($Search, $message, $isChild), 'SearchPerson'),
                            new Standard('Neue Person anlegen', '/People/Person/Create', new PersonIcon()
                                , array(), 'Die aktuell gewählte Person verlassen'
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    )
                ))
            ));
        }

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Danger(
                            new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
                        ),
                        new Container('&nbsp;')
                    ))
                )),
                $formRowRelationship,
                $formRowToPerson,
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
     * @param IMessageInterface|null $message
     * @param null $Post
     *
     * @return Layout
     */
    public function loadExtraOptions(IMessageInterface $message = null, $Post = null)
    {
        if ($Post) {
            $global = $this->getGlobal();
            $global->POST['Type']['Ranking'] = $Post;
            $global->savePost();
        }

        if (($tblSetting = Consumer::useService()->getSetting(
                'People', 'Person', 'Relationship', 'GenderOfS1'
            ))
            && ($value = $tblSetting->getValue())
        ) {
            if (($genderSetting = Common::useService()->getCommonGenderById($value))) {
                $genderSetting = $genderSetting->getName();
            }
        } else {
            $genderSetting = '';
        }

        return  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Bold('Merkmal') . new Danger(' *')
                        . ($genderSetting ? new Muted('&nbsp;&nbsp;&nbsp; Geschlecht: ' . $genderSetting . ' ist für S1 voreingestellt (Mandanteneinstellung).') : '')
                ),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    '&nbsp;'
                ),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    (new RadioBox('Type[Ranking]', 'S1', 1))
                    , 1),
                new LayoutColumn(
                    (new RadioBox('Type[Ranking]', 'S2', 2))
                    , 1),
                new LayoutColumn(
                    (new RadioBox('Type[Ranking]', 'S3', 3))
                    , 1),
                new LayoutColumn(
                    (new CheckBox('Type[IsSingleParent]', 'alleinerziehend', 1))
                    , 6),
            )),
            $message
                ?  new LayoutRow(array(
                    new LayoutColumn(
                        '<br>' . $message
                    )
                )) : null
        )));
    }

    /**
     * @param $Search
     * @param IMessageInterface|null $message
     * @param bool $IsChild
     *
     * @return string
     */
    public function loadPersonSearch($Search, IMessageInterface $message = null, $IsChild = false)
    {

        if ($Search != '' && strlen($Search) > 2) {
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $resultList = array();
                foreach ($tblPersonList as $tblPerson) {
                    // onchange only by student, prospect
                    $radio = new RadioBox('To', '&nbsp;', $tblPerson->getId());
                    if ($IsChild) {
                        $radio->ajaxPipelineOnChange(
                            ApiRelationshipToPerson::pipelineLoadExtraOptions(null)
                        );
                    }
                    $resultList[] = array(
                        'Select' => $radio,
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
                        'Extended' => $tblCompany->getExtendedName(),
                        'Description' => $tblCompany->getDescription(),
                        'Address' => ($tblAddress = $tblCompany->fetchMainAddress()) ? $tblAddress->getGuiString() : ''
                    );
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Select' => '',
                        'Name' => 'Name',
                        'Extended' => 'Zusatz',
                        'Description' => 'Beschreibung',
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

                    $contentExtra = '';
                    $ranking = $tblToPerson->getRanking();
                    if ($ranking > 0) {
                        $contentExtra = 'S' . $ranking;
                    }
                    if ($tblToPerson->isSingleParent()) {
                        $contentExtra .= ($contentExtra == '' ? '' : ', ') . 'alleinerziehend';
                    }
                    if ($contentExtra != '') {
                        $content[] = new Muted($contentExtra);
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

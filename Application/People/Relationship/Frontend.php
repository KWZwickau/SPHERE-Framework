<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Relationship
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int   $Id
     * @param int   $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Hinzufügen');
        $Stage->setMessage(
            'Eine Beziehungen zur gewählten Person hinzufügen'
            .'<br/>Beispiel: Die Person (Vater) > hat folgende Beziehung (Sorgeberechtigt), Bemerkung: Vater > zu folgender Person (Kind)'
        );

        $tblPerson = Person::useService()->getPersonById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon().' Die Person',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array('Id' => $tblPerson->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Relationship::useService()->createRelationshipToPerson(
                                $this->formRelationshipToPerson()
                                    ->appendFormButton(new Primary('Beziehungen hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblPerson, $To, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return Form
     */
    private function formRelationshipToPerson(TblToPerson $tblToPerson = null)
    {

        $PanelSelectPersonTitle = new PullClear(
            'zu folgender Person'
            .new PullRight(
                new Standard('Neue Person anlegen', '/People/Person', new PersonIcon()
                    , array(), 'Die aktuell gewählte Person verlassen'
                ))
        );

        if ($tblToPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['To'] )) {
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->POST['To'] = $tblToPerson->getServiceTblPersonTo()->getId();
                $Global->POST['PanelSearch-'.sha1($PanelSelectPersonTitle)] = $tblToPerson->getServiceTblPersonTo()->getFullName();
                $Global->savePost();
            }
        }

        $tblGroup = Relationship::useService()->getGroupByIdentifier('PERSON');
        $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
        $tblPersonAll = Person::useService()->getPersonAll();

        array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

            $tblPerson = new PullClear(
                new PullLeft(new RadioBox('To', $tblPerson->getFullName(), $tblPerson->getId()))
                .new PullRight(
                    new Standard('', '/People/Person/Relationship/Create',
                        new PersonIcon(), array('Id' => $tblPerson->getId()),
                        'zu'
                        .' '.$tblPerson->getSalutation()
                        .' '.$tblPerson->getTitle()
                        .' '.$tblPerson->getLastName()
                        .' wechseln'
                    )));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('hat folgende Beziehung',
                            array(
                                new SelectBox('Type[Type]', 'Beziehungstyp',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                                new TextArea('Type[Remark]', 'Bemerkungen - z.B: Mutter / Vater / ..', 'Bemerkungen',
                                    new Pencil()
                                ),
                                new \SPHERE\Common\Frontend\Text\Repository\Danger(
                                    new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
                                ),
                            ), Panel::PANEL_TYPE_INFO
                        ),
                    ), 6),
                    new FormColumn(array(
                        new Panel($PanelSelectPersonTitle, $tblPersonAll, Panel::PANEL_TYPE_INFO, null, 15),
                    ), 6),
                )),
            ))
        );
    }

    /**
     * @param int   $Id
     * @param int   $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToCompany($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Hinzufügen');
        $Stage->setMessage(
            'Eine Beziehungen zur gewählten Person hinzufügen'
        );

        $tblPerson = Person::useService()->getPersonById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon().' Die Person',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array('Id' => $tblPerson->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Relationship::useService()->createRelationshipToCompany(
                                $this->formRelationshipToCompany()
                                    ->appendFormButton(new Primary('Beziehungen hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblPerson, $To, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return Form
     */
    private function formRelationshipToCompany(TblToCompany $tblToCompany = null)
    {

        $PanelSelectCompanyTitle = new PullClear(
            'zu folgender Firma'
            .new PullRight(
                new Standard('Neue Firma anlegen', '/Corporation/Company', new Building()
                    , array(), 'Die aktuell gewählte Person verlassen'
                ))
        );

        if ($tblToCompany) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['To'] )) {
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->POST['To'] = $tblToCompany->getServiceTblCompany()->getId();
                $Global->POST['PanelSearch-'.sha1($PanelSelectCompanyTitle)] = $tblToCompany->getServiceTblCompany()->getName();
                $Global->savePost();
            }
        }

        $tblGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
        $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
        $tblCompanyAll = Company::useService()->getCompanyAll();

        array_walk($tblCompanyAll, function (TblCompany &$tblCompany) {

            $tblCompany = new PullClear(new RadioBox('To', $tblCompany->getName().(
                $tblCompany->getDescription()
                    ? ' - '.$tblCompany->getDescription()
                    : '' ),
                $tblCompany->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('hat folgende Beziehung',
                            array(
                                new SelectBox('Type[Type]', 'Beziehungstyp',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                                new TextArea('Type[Remark]', 'Bemerkungen - z.B: Schulleiter / Geschäftsführer / ..',
                                    'Bemerkungen',
                                    new Pencil()
                                ),
                                new \SPHERE\Common\Frontend\Text\Repository\Danger(
                                    new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
                                ),
                            ), Panel::PANEL_TYPE_INFO
                        ),
                    ), 6),
                    new FormColumn(array(
                        new Panel($PanelSelectCompanyTitle, $tblCompanyAll, Panel::PANEL_TYPE_INFO, null, 15),
                    ), 6),
                )),
            ))
        );
    }

    /**
     * @param int   $Id
     * @param int   $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToPerson($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Bearbeiten');
        $Stage->setMessage('Eine Beziehungen der gewählten Person ändern');

        /** @Var TblToPerson $tblToPerson */
        $tblToPerson = Relationship::useService()->getRelationshipToPersonById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon().' Person',
                                $tblToPerson->getServiceTblPersonFrom()->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array('Id' => $tblToPerson->getServiceTblPersonFrom()->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Relationship::useService()->updateRelationshipToPerson(
                                $this->formRelationshipToPerson($tblToPerson)
                                    ->appendFormButton(new Primary('Änderungen speichern'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblToPerson, $tblToPerson->getServiceTblPersonFrom(), $To, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param int   $Id
     * @param int   $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToCompany($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Bearbeiten');
        $Stage->setMessage('Eine Beziehungen der gewählten Person ändern');

        /** @Var TblToPerson $tblToPerson */
        $tblToCompany = Relationship::useService()->getRelationshipToCompanyById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon().' Person',
                                $tblToCompany->getServiceTblPerson()->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array('Id' => $tblToCompany->getServiceTblPerson()->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Relationship::useService()->updateRelationshipToCompany(
                                $this->formRelationshipToCompany($tblToCompany)
                                    ->appendFormButton(new Primary('Änderungen speichern'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblToCompany, $tblToCompany->getServiceTblPerson(), $To, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function frontendLayoutPerson(TblPerson $tblPerson)
    {

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToPerson &$tblToPerson, $Index, TblPerson $tblPerson) {

                $Panel = array(
                    ( $tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                        ? $tblToPerson->getServiceTblPersonTo()->getFullName()
                        : $tblToPerson->getServiceTblPersonFrom()->getFullName()
                    )
                );
                if ($tblToPerson->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                }

                $tblToPerson = new LayoutColumn(
                    new Panel(
                        new PersonIcon().' '.new Link().' '.$tblToPerson->getTblType()->getName(), $Panel,
                        ( $tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                            ? Panel::PANEL_TYPE_SUCCESS
                            : Panel::PANEL_TYPE_DEFAULT
                        ),
                        ( $tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                            ? new Standard(
                                '', '/People/Person/Relationship/Edit', new Pencil(),
                                array('Id' => $tblToPerson->getId()),
                                'Bearbeiten'
                            )
                            .new Standard(
                                '', '/People/Person/Relationship/Destroy', new Remove(),
                                array('Id' => $tblToPerson->getId()), 'Löschen'
                            )
                            .new Standard(
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
            }, $tblPerson);
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
     * @param TblCompany|TblPerson|Element $tblEntity
     *
     * @return Layout
     */
    public function frontendLayoutCompany(Element $tblEntity)
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
            array_walk($tblRelationshipAll, function (TblToCompany &$tblToCompany, $Index, Element $tblEntity) {

                $Panel = array(
                    $tblToCompany->getServiceTblPerson()->getFullName(),
                    $tblToCompany->getServiceTblCompany()->getName()
                );
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new Building().' '.new Link().' '.$tblToCompany->getTblType()->getName(), $Panel,
                        ( $tblEntity instanceof TblPerson
                            ? Panel::PANEL_TYPE_INFO
                            : Panel::PANEL_TYPE_DEFAULT
                        ),
                        ( $tblEntity instanceof TblPerson
                            ? new Standard(
                                '', '/Corporation/Company/Relationship/Edit', new Pencil(),
                                array('Id' => $tblToCompany->getId()),
                                'Bearbeiten'
                            )
                            .new Standard(
                                '', '/Corporation/Company/Relationship/Destroy', new Remove(),
                                array('Id' => $tblToCompany->getId()), 'Löschen'
                            )
                            .new Standard(
                                '', '/Corporation/Company', new Building(),
                                array('Id' => $tblToCompany->getServiceTblCompany()->getId()), 'zur Firma'
                            )
                            :
                            new Standard(
                                '', '/People/Person', new PersonIcon(),
                                array('Id' => $tblToCompany->getServiceTblPerson()->getId()), 'zur Person'
                            )
                        )
                    )
                    , 3);
            }, $tblEntity);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Firmenbeziehungen hinterlegt')
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
     * @param int  $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToPerson($Id, $Confirm = false)
    {

        $Stage = new Stage('Beziehung', 'Löschen');
        if ($Id) {
            $tblToPerson = Relationship::useService()->getRelationshipToPersonById($Id);
            $tblPersonFrom = $tblToPerson->getServiceTblPersonFrom();
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Person',
                            $tblPersonFrom->getFullName(),
                            Panel::PANEL_TYPE_SUCCESS,
                            new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                array('Id' => $tblPersonFrom->getId())
                            )
                        ),
                        new Panel(new Question().' Diese Beziehung wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName().' '.$tblToPerson->getTblType()->getDescription(),
                            $tblToPerson->getServiceTblPersonTo()->getFullName(),
                            new Muted(new Small($tblToPerson->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Relationship/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/People/Person', new Disable(),
                                array('Id' => $tblPersonFrom->getId())
                            )
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Relationship::useService()->removePersonRelationshipToPerson($tblToPerson)
                                ? new Success('Die Beziehung wurde gelöscht')
                                : new Danger('Die Beziehung konnte nicht gelöscht werden')
                            ),
                            new Redirect('/People/Person', 1, array('Id' => $tblPersonFrom->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Beziehung konnte nicht gefunden werden'),
                        new Redirect('/People/Search/Group')
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param int  $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToCompany($Id, $Confirm = false)
    {

        $Stage = new Stage('Beziehung', 'Löschen');
        if ($Id) {
            $tblToCompany = Relationship::useService()->getRelationshipToCompanyById($Id);
            $tblPerson = $tblToCompany->getServiceTblPerson();
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Person',
                            $tblPerson->getFullName(),
                            Panel::PANEL_TYPE_SUCCESS,
                            new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                array('Id' => $tblPerson->getId())
                            )
                        ),
                        new Panel(new Question().' Diese Beziehung wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription(),
                            $tblToCompany->getServiceTblCompany()->getName(),
                            new Muted(new Small($tblToCompany->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Relationship/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/People/Person', new Disable(),
                                array('Id' => $tblPerson->getId())
                            )
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Relationship::useService()->removeCompanyRelationshipToPerson($tblToCompany)
                                ? new Success('Die Beziehung wurde gelöscht')
                                : new Danger('Die Beziehung konnte nicht gelöscht werden')
                            ),
                            new Redirect('/People/Person', 1, array('Id' => $tblPerson->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Beziehung konnte nicht gefunden werden'),
                        new Redirect('/People/Search/Group')
                    )))
                )))
            );
        }
        return $Stage;
    }
}

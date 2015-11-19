<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Phone
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Id
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Hinzufügen');
        $Stage->setMessage('Eine Telefonnummer zur gewählten Person hinzufügen');

        $tblPerson = Person::useService()->getPersonById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
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
                            Phone::useService()->createPhoneToPerson(
                                $this->formNumber()
                                    ->appendFormButton(new Primary('Telefonnummer hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblPerson, $Number, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formNumber()
    {

        $tblPhoneAll = Phone::useService()->getPhoneAll();
        $tblTypeAll = Phone::useService()->getTypeAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Telefonnummer',
                            array(
                                new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                                new AutoCompleter('Number', 'Telefonnummer', 'Telefonnummer',
                                    array('Number' => $tblPhoneAll), new PhoneIcon()
                                )
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToCompany($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Hinzufügen');
        $Stage->setMessage('Eine Telefonnummer zur gewählten Firma hinzufügen');

        $tblCompany = Company::useService()->getCompanyById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Firma',
                                $tblCompany->getName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Firma', '/Corporation/Company', new ChevronLeft(),
                                    array('Id' => $tblCompany->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Phone::useService()->createPhoneToCompany(
                                $this->formNumber()
                                    ->appendFormButton(new Primary('Telefonnummer hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblCompany, $Number, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToPerson($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Bearbeiten');
        $Stage->setMessage('Die Telefonnummer der gewählten Person ändern');

        $tblToPerson = Phone::useService()->getPhoneToPersonById($Id);

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Number'])) {
            $Global->POST['Number'] = $tblToPerson->getTblPhone()->getNumber();
            $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Person',
                                $tblToPerson->getServiceTblPerson()->getFullName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                    array('Id' => $tblToPerson->getServiceTblPerson()->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Phone::useService()->updatePhoneToPerson(
                                $this->formNumber()
                                    ->appendFormButton(new Primary('Änderungen speichern'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblToPerson, $Number, $Type
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToCompany($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Bearbeiten');
        $Stage->setMessage('Die Telefonnummer der gewählten Firma ändern');

        $tblToCompany = Phone::useService()->getPhoneToCompanyById($Id);

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Number'])) {
            $Global->POST['Number'] = $tblToCompany->getTblPhone()->getNumber();
            $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Firma',
                                $tblToCompany->getServiceTblCompany()->getName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zur Firma', '/Corporation/Company', new ChevronLeft(),
                                    array('Id' => $tblToCompany->getServiceTblCompany()->getId())
                                )
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            Phone::useService()->updatePhoneToCompany(
                                $this->formNumber()
                                    ->appendFormButton(new Primary('Änderungen speichern'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblToCompany, $Number, $Type
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

        $tblPhoneAll = Phone::useService()->getPhoneAllByPerson($tblPerson);
        if ($tblPhoneAll !== false) {
            array_walk($tblPhoneAll, function (TblToPerson &$tblToPerson) {

                $Panel = array($tblToPerson->getTblPhone()->getNumber());
                if ($tblToPerson->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                }

                $tblToPerson = new LayoutColumn(
                    new Panel(
                        (preg_match('!Fax!is',
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription())
                            ? new PhoneFax()
                            : (preg_match('!Mobil!is',
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription())
                                ? new PhoneMobil()
                                : new PhoneIcon()
                            )
                        ) . ' ' . $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                        $Panel,
                        (preg_match('!Notfall!is',
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription())
                            ? Panel::PANEL_TYPE_DANGER
                            : Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Standard(
                            '', '/People/Person/Phone/Edit', new Pencil(),
                            array('Id' => $tblToPerson->getId()),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/People/Person/Phone/Destroy', new Remove(),
                            array('Id' => $tblToPerson->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblRelationshipPhoneAll = Phone::useService()->getPhoneAllByPerson($tblRelationship->getServiceTblPersonFrom());
                        if ($tblRelationshipPhoneAll) {
                            foreach ($tblRelationshipPhoneAll as $tblPhone) {

                                $Panel = array($tblPhone->getTblPhone()->getNumber());
                                if ($tblPhone->getRemark()) {
                                    array_push($Panel, new Muted(new Small($tblPhone->getRemark())));
                                }

                                $tblPhone = new LayoutColumn(
                                    new Panel(
                                        (preg_match('!Fax!is',
                                            $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                            ? new PhoneFax()
                                            : (preg_match('!Mobil!is',
                                                $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                ? new PhoneMobil()
                                                : new PhoneIcon()
                                            )
                                        ) . ' ' . $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription(),
                                        $Panel,
                                        (preg_match('!Notfall!is',
                                            $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                            ? Panel::PANEL_TYPE_DANGER
                                            : Panel::PANEL_TYPE_DEFAULT
                                        ),
                                        $tblRelationship->getServiceTblPersonFrom()->getFullName()
                                        . ' (' . $tblRelationship->getTblType()->getName() . ')'
                                    )
                                    , 3);

                                if ($tblPhoneAll !== false) {
                                    $tblPhoneAll[] = $tblPhone;
                                } else {
                                    $tblPhoneAll = array();
                                    $tblPhoneAll[] = $tblPhone;
                                }

                            }
                        }
                    }

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                        $tblRelationshipPhoneAll = Phone::useService()->getPhoneAllByPerson($tblRelationship->getServiceTblPersonTo());
                        if ($tblRelationshipPhoneAll) {
                            foreach ($tblRelationshipPhoneAll as $tblPhone) {

                                $Panel = array($tblPhone->getTblPhone()->getNumber());
                                if ($tblPhone->getRemark()) {
                                    array_push($Panel, new Muted(new Small($tblPhone->getRemark())));
                                }

                                $tblPhone = new LayoutColumn(
                                    new Panel(
                                        (preg_match('!Fax!is',
                                            $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                            ? new PhoneFax()
                                            : (preg_match('!Mobil!is',
                                                $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                ? new PhoneMobil()
                                                : new PhoneIcon()
                                            )
                                        ) . ' ' . $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription(),
                                        $Panel,
                                        (preg_match('!Notfall!is',
                                            $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                            ? Panel::PANEL_TYPE_DANGER
                                            : Panel::PANEL_TYPE_DEFAULT
                                        ),
                                        $tblRelationship->getServiceTblPersonTo()->getFullName()
                                        . ' (' . $tblRelationship->getTblType()->getName() . ')'
                                    )
                                    , 3);

                                if ($tblPhoneAll !== false) {
                                    $tblPhoneAll[] = $tblPhone;
                                } else {
                                    $tblPhoneAll = array();
                                    $tblPhoneAll[] = $tblPhone;
                                }

                            }
                        }
                    }
                }
            }
        }

        if ($tblPhoneAll === false) {
            $tblPhoneAll = array(
                new LayoutColumn(
                    new Warning('Keine Telefonnummern hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($tblPhoneAll as $tblPhone) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblPhone);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToPerson($Id, $Confirm = false)
    {

        $Stage = new Stage('Telefonnummer', 'Löschen');
        if ($Id) {
            $tblToPerson = Phone::useService()->getPhoneToPersonById($Id);
            $tblPerson = $tblToPerson->getServiceTblPerson();
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            $tblPerson->getFullName(),
                            Panel::PANEL_TYPE_SUCCESS,
                            new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
                                array('Id' => $tblPerson->getId())
                            )
                        ),
                        new Panel(new Question() . ' Diese Telefonnummer wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            $tblToPerson->getTblPhone()->getNumber(),
                            new Muted(new Small($tblToPerson->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Phone/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
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
                            (Phone::useService()->removePhoneToPerson($tblToPerson)
                                ? new Success('Die Telefonnummer wurde gelöscht')
                                : new Danger('Die Telefonnummer konnte nicht gelöscht werden')
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
                        new Danger('Die Telefonnummer konnte nicht gefunden werden'),
                        new Redirect('/People/Search/Group')
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToCompany($Id, $Confirm = false)
    {

        $Stage = new Stage('Telefonnummer', 'Löschen');
        if ($Id) {
            $tblToCompany = Phone::useService()->getPhoneToCompanyById($Id);
            $tblCompany = $tblToCompany->getServiceTblCompany();
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new Building() . ' Firma',
                            $tblCompany->getName(),
                            Panel::PANEL_TYPE_SUCCESS,
                            new Standard('Zurück zur Firma', '/Corporation/Company', new ChevronLeft(),
                                array('Id' => $tblCompany->getId())
                            )
                        ),
                        new Panel(new Question() . ' Diese Telefonnummer wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                            $tblToCompany->getTblPhone()->getNumber(),
                            new Muted(new Small($tblToCompany->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Phone/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Corporation/Company', new Disable(),
                                array('Id' => $tblCompany->getId())
                            )
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Phone::useService()->removePhoneToCompany($tblToCompany)
                                ? new Success('Die Telefonnummer wurde gelöscht')
                                : new Danger('Die Telefonnummer konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Corporation/Company', 1, array('Id' => $tblCompany->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Telefonnummer konnte nicht gefunden werden'),
                        new Redirect('/Corporation/Search/Group')
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return Layout
     */
    public function frontendLayoutCompany(TblCompany $tblCompany)
    {

        $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
        if ($tblPhoneAll !== false) {
            array_walk($tblPhoneAll, function (TblToCompany &$tblToCompany) {

                $Panel = array($tblToCompany->getTblPhone()->getNumber());
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        (preg_match('!Fax!is',
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription())
                            ? new PhoneFax()
                            : (preg_match('!Mobil!is',
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription())
                                ? new PhoneMobil()
                                : new PhoneIcon()
                            )
                        ) . ' ' . $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                        $Panel,
                        (preg_match('!Notfall!is',
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription())
                            ? Panel::PANEL_TYPE_DANGER
                            : Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Standard(
                            '', '/Corporation/Company/Phone/Edit', new Pencil(),
                            array('Id' => $tblToCompany->getId()),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/Corporation/Company/Phone/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblPhoneAll = array(
                new LayoutColumn(
                    new Warning('Keine Telefonnummern hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($tblPhoneAll as $tblPhone) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblPhone);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }
}

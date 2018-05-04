<?php
namespace SPHERE\Application\Contact\Web;

use SPHERE\Application\Contact\Web\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Web\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
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
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Web
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage|string
     */
    public function frontendCreateToPerson($Id, $Address, $Type)
    {

        $Stage = new Stage('Web Adresse', 'Hinzufügen');
        $Stage->setMessage('Eine Web Adresse zur gewählten Person hinzufügen');

        $tblPerson = Person::useService()->getPersonById($Id);
        if(!$tblPerson){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/People/Person', new ChevronLeft(),
                array('Id' => $tblPerson->getId())
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Web::useService()->createWebToPerson(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblPerson, $Address, $Type
                                )
                            )
                        )
                    )
                ), new Title(new PlusSign() . ' Hinzufügen')),
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formAddress()
    {

        $tblTypeAll = Web::useService()->getTypeAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Web Adresse',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new TextField('Address', 'Web Adresse', 'Web Adresse', new Globe() ))->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage|string
     */
    public function frontendCreateToCompany($Id, $Address, $Type)
    {

        $Stage = new Stage('Web Adresse', 'Hinzufügen');
        $Stage->setMessage('Eine Web Adresse zur gewählten Institution hinzufügen');

        $tblCompany = Company::useService()->getCompanyById($Id);
        if ($tblCompany) {

            $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
                array('Id' => $tblCompany->getId())
            ));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PersonIcon().' Institution',
                                    array(
                                        new Bold($tblCompany->getName()),
                                        $tblCompany->getExtendedName()),
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            )
                        ),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Web::useService()->createWebToCompany(
                                        $this->formAddress()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $tblCompany, $Address, $Type
                                    )
                                )
                            )
                        )
                    ), new Title(new PlusSign() . ' Hinzufügen')),
                ))
            );

            return $Stage;
        } else {
            return $Stage.new Danger(new Ban().' Institution nicht gefunden.')
            . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage|string
     */
    public function frontendUpdateToPerson($Id, $Address, $Type)
    {

        $Stage = new Stage('Web Adresse', 'Bearbeiten');
        $Stage->setMessage('Die Web Adresse der gewählten Person ändern');

        $tblToPerson = Web::useService()->getWebToPersonById($Id);

        if (!$tblToPerson->getServiceTblPerson()){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/People/Person', new ChevronLeft(),
                array('Id' => $tblToPerson->getServiceTblPerson()->getId())
            )
        );

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Address'])) {
            $Global->POST['Address'] = $tblToPerson->getTblWeb()->getAddress();
            $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblToPerson->getServiceTblPerson()->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Web::useService()->updateWebToPerson(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToPerson, $Address, $Type
                                )
                            )
                        )
                    )
                ), new Title(new Edit() . ' Bearbeiten')),
            ))
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage|string
     */
    public function frontendUpdateToCompany($Id, $Address, $Type)
    {

        $Stage = new Stage('Web Adresse', 'Bearbeiten');
        $Stage->setMessage('Die Web Adresse der gewählten Institution ändern');

        $tblToCompany = Web::useService()->getWebToCompanyById($Id);

        if (!$tblToCompany->getServiceTblCompany()){
            return $Stage.new Danger('Institution nicht gefunden', new Ban())
            . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
            array('Id' => $tblToCompany->getServiceTblCompany()->getId())
        ));

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Address'])) {
            $Global->POST['Address'] = $tblToCompany->getTblWeb()->getAddress();
            $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building().' Institution', array(
                                new Bold($tblToCompany->getServiceTblCompany()->getName()),
                                $tblToCompany->getServiceTblCompany()->getExtendedName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Web::useService()->updateWebToCompany(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToCompany, $Address, $Type
                                )
                            )
                        )
                    )
                ), new Title(new Edit() . ' Bearbeiten')),
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

        $WebExistsList = array();
        $tblWebAll = Web::useService()->getWebAllByPerson($tblPerson);
        if ($tblWebAll !== false) {
            array_walk($tblWebAll, function (TblToPerson &$tblToPerson) use ($WebExistsList) {

                if (array_key_exists($tblToPerson->getId(), $WebExistsList)){
                    $tblToPerson = false;
                } else {
                    $WebExistsList[$tblToPerson->getId()] = $tblToPerson;

                    $Panel = array(
                        $tblToPerson->getTblWeb()->getAddress()
                    );
                    if ($tblToPerson->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                    }

                    $tblToPerson = new LayoutColumn(
                        new Panel(
                            new Globe() . ' ' . $tblToPerson->getTblType()->getName(), $Panel,
                            Panel::PANEL_TYPE_SUCCESS,

                            new Standard(
                                '', '/People/Person/Web/Edit', new Edit(),
                                array('Id' => $tblToPerson->getId()),
                                'Bearbeiten'
                            )
                            . new Standard(
                                '', '/People/Person/Web/Destroy', new Remove(),
                                array('Id' => $tblToPerson->getId()), 'Löschen'
                            )
                        )
                        , 3);
                }
            });

            $tblWebAll = array_filter($tblWebAll);
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblRelationshipWebAll = Web::useService()->getWebAllByPerson($tblRelationship->getServiceTblPersonFrom());
                        if ($tblRelationshipWebAll) {
                            foreach ($tblRelationshipWebAll as $tblWeb) {
                                if (!array_key_exists($tblWeb->getId(), $WebExistsList)) {
                                    $WebExistsList[$tblWeb->getId()] = $tblWeb;

                                    $Panel = array(
                                        $tblWeb->getTblWeb()->getAddress()
                                    );
                                    if ($tblWeb->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblWeb->getRemark())));
                                    }

                                    $tblWeb = new LayoutColumn(
                                        new Panel(
                                            new Globe() . ' ' . $tblWeb->getTblType()->getName(), $Panel,
                                            Panel::PANEL_TYPE_DEFAULT,
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonFrom()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonFrom()->getFullName()
                                        )
                                        , 3);

                                    if ($tblWebAll !== false) {
                                        $tblWebAll[] = $tblWeb;
                                    } else {
                                        $tblWebAll = array();
                                        $tblWebAll[] = $tblWeb;
                                    }

                                }
                            }
                        }
                    }

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                        $tblRelationshipWebAll = Web::useService()->getWebAllByPerson($tblRelationship->getServiceTblPersonTo());
                        if ($tblRelationshipWebAll) {
                            foreach ($tblRelationshipWebAll as $tblWeb) {
                                if (!array_key_exists($tblWeb->getId(), $WebExistsList)) {
                                    $WebExistsList[$tblWeb->getId()] = $tblWeb;

                                    $Panel = array(
                                        $tblWeb->getTblWeb()->getAddress()
                                    );
                                    if ($tblWeb->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblWeb->getRemark())));
                                    }

                                    $tblWeb = new LayoutColumn(
                                        new Panel(
                                            new Globe() . ' ' . $tblWeb->getTblType()->getName(), $Panel,
                                            Panel::PANEL_TYPE_DEFAULT,
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonTo()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonTo()->getFullName()
                                        )
                                        , 3);

                                    if ($tblWebAll !== false) {
                                        $tblWebAll[] = $tblWeb;
                                    } else {
                                        $tblWebAll = array();
                                        $tblWebAll[] = $tblWeb;
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }

        if ($tblWebAll === false) {
            $tblWebAll = array(
                new LayoutColumn(
                    new Warning('Keine Web Adressen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblWeb
         */
        foreach ($tblWebAll as $tblWeb) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblWeb);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyToPerson($Id, $Confirm = false)
    {

        $Stage = new Stage('Web Adresse', 'Löschen');
        if ($Id) {
            $tblToPerson = Web::useService()->getWebToPersonById($Id);
            $tblPerson = $tblToPerson->getServiceTblPerson();

            if (!$tblPerson){
                return $Stage . new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            $Stage->addButton(
                new Standard('Zurück', '/People/Person', new ChevronLeft(),
                    array('Id' => $tblPerson->getId())
                )
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            new Bold($tblPerson->getFullName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese Web Adresse wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            $tblToPerson->getTblWeb()->getAddress(),
                            new Muted(new Small($tblToPerson->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Web/Destroy', new Ok(),
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
                            (Web::useService()->removeWebToPerson($tblToPerson)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Web Adresse wurde gelöscht')
                                : new Danger(new Ban() . ' Die Web Adresse konnte nicht gelöscht werden')
                            ),
                            new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblPerson->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Web Adresse konnte nicht gefunden werden'),
                        new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
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
     * @return Stage|string
     */
    public function frontendDestroyToCompany($Id, $Confirm = false)
    {

        $Stage = new Stage('Web Adresse', 'Löschen');
        if ($Id) {
            $tblToCompany = Web::useService()->getWebToCompanyById($Id);
            $tblCompany = $tblToCompany->getServiceTblCompany();

            if (!$tblCompany){
                return $Stage.new Danger('Institution nicht gefunden', new Ban())
                . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            $Stage->addButton( new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
                array('Id' => $tblCompany->getId())
            ));
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Institution',
                            array(
                                new Bold($tblCompany->getName()),
                                $tblCompany->getExtendedName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese Web Adresse wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                            $tblToCompany->getTblWeb()->getAddress(),
                            new Muted(new Small($tblToCompany->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Web/Destroy', new Ok(),
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
                            (Web::useService()->removeWebToCompany($tblToCompany)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .  ' Die Web Adresse wurde gelöscht')
                                : new Danger(new Ban() . ' Die Web Adresse konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Web Adresse konnte nicht gefunden werden'),
                        new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR)
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

        $tblWebAll = Web::useService()->getWebAllByCompany($tblCompany);
        if ($tblWebAll !== false) {
            array_walk($tblWebAll, function (TblToCompany &$tblToCompany) {

                $Panel = array(
                    $tblToCompany->getTblWeb()->getAddress()
                );
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new Globe() . ' ' . $tblToCompany->getTblType()->getName(), $Panel,
                        Panel::PANEL_TYPE_SUCCESS,

                        new Standard(
                            '', '/Corporation/Company/Web/Edit', new Edit(),
                            array('Id' => $tblToCompany->getId()),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/Corporation/Company/Web/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblWebAll = array(
                new LayoutColumn(
                    new Warning('Keine Web Adressen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblWeb
         */
        foreach ($tblWebAll as $tblWeb) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblWeb);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }
}

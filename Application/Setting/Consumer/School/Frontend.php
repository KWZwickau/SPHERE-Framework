<?php
namespace SPHERE\Application\Setting\Consumer\School;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Consumer\School
 */
class Frontend extends Extension implements IFrontendInterface
{


    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Übersicht', 'Schulen');

        $Stage->setContent(
            new Standard('Schule hinzufügen', '/Setting/Consumer/School/Create')
            .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Es ist noch keine Schule eingetragen'))))));

        if ($tblSchoolAll = School::useService()->getSchoolAll()) {

            $Form = null;
            foreach ($tblSchoolAll as $tblSchool) {
                $tblCompany = $tblSchool->getServiceTblCompany();
                $Form .= new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            self::LayoutCombine($tblCompany)
                        )),
                    ), (new Title(new TagList().' Kontaktdaten', 'von '.$tblCompany->getName().' '
                            .new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSchool->getTblType()->getName())))
                    ),
                ));
            }

            $Stage->setContent(
                new Standard('Schule hinzufügen', '/Setting/Consumer/School/Create')
                .new Standard('Schule entfernen', '/Setting/Consumer/School/Delete')
                .$Form
            );
        }
//            .Main::getDispatcher()->fetchDashboard('School'));
        return $Stage;
    }

    public function LayoutCombine(TblCompany $tblCompany)
    {

        $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
        $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
        $tblMailAll = Mail::useService()->getMailAllByCompany($tblCompany);
        $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);

        if ($tblAddressAll !== false) {
            array_walk($tblAddressAll, function (TblToCompany &$tblToCompany) {

                $Panel = array($tblToCompany->getTblAddress()->getLayout());
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new MapMarker().' '.$tblToCompany->getTblType()->getName(), $Panel, Panel::PANEL_TYPE_SUCCESS,
                        new Standard(
                            '', '/Corporation/Company/Address/Edit', new Pencil(),
                            array('Id' => $tblToCompany->getId()),
                            'Bearbeiten'
                        )
                        .new Standard(
                            '', '/Corporation/Company/Address/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblAddressAll = array(
                new LayoutColumn(
                    new Warning('Keine Adressen hinterlegt')
                )
            );
        }

        if ($tblPhoneAll !== false) {
            array_walk($tblPhoneAll,
                function (\SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany &$tblToCompany) {

                    $Panel = array($tblToCompany->getTblPhone()->getNumber());
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            ( preg_match('!Fax!is',
                                $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription())
                                ? new PhoneFax()
                                : ( preg_match('!Mobil!is',
                                    $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription())
                                    ? new PhoneMobil()
                                    : new PhoneIcon()
                                )
                            ).' '.$tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription(),
                            $Panel,
                            ( preg_match('!Notfall!is',
                                $tblToCompany->getTblType()->getName().' '.$tblToCompany->getTblType()->getDescription())
                                ? Panel::PANEL_TYPE_DANGER
                                : Panel::PANEL_TYPE_SUCCESS
                            ),
                            new Standard(
                                '', '/Corporation/Company/Phone/Edit', new Pencil(),
                                array('Id' => $tblToCompany->getId()),
                                'Bearbeiten'
                            )
                            .new Standard(
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

        if ($tblMailAll !== false) {
            array_walk($tblMailAll,
                function (\SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany &$tblToCompany) {

                    $Panel = array($tblToCompany->getTblMail()->getAddress());
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            new MailIcon().' '.$tblToCompany->getTblType()->getName(), $Panel,
                            Panel::PANEL_TYPE_SUCCESS,

                            new Standard(
                                '', '/Corporation/Company/Mail/Edit', new Pencil(),
                                array('Id' => $tblToCompany->getId()),
                                'Bearbeiten'
                            )
                            .new Standard(
                                '', '/Corporation/Company/Mail/Destroy', new Remove(),
                                array('Id' => $tblToCompany->getId()), 'Löschen'
                            )
                        )
                        , 3);
                });
        } else {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning('Keine E-Mail Adressen hinterlegt')
                )
            );
        }
        if ($tblRelationshipAll !== false) {
            array_walk($tblRelationshipAll, function (
                \SPHERE\Application\People\Relationship\Service\Entity\TblToCompany &$tblToCompany,
                $Index,
                TblCompany $tblCompany
            ) {

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
                        Panel::PANEL_TYPE_DEFAULT,
                        new Standard(
                            '', '/People/Person', new PersonIcon(),
                            array('Id' => $tblToCompany->getServiceTblPerson()->getId()), 'zur Person')
                    )
                    , 3);
            }, $tblCompany);
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
         * @var LayoutColumn $tblAddress
         */
        foreach ($tblAddressAll as $tblAddress) {
            if ($LayoutRowCount % 50 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblAddress);
            $LayoutRowCount++;
        }
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($tblPhoneAll as $tblPhone) {
            if ($LayoutRowCount % 50 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblPhone);
            $LayoutRowCount++;
        }
        /**
         * @var LayoutColumn $tblMail
         */
        foreach ($tblMailAll as $tblMail) {
            if ($LayoutRowCount % 50 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblMail);
            $LayoutRowCount++;
        }

        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 50 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }
        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param $School
     * @param $Type
     *
     * @return Stage
     */
    public function frontendSchoolCreate($School, $Type)
    {

        $Stage = new Stage('Schule', 'anlegen');

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel(new Standard(new ChevronLeft(),
                                    '/Setting/Consumer/School').'Zurück zur Übersicht Schulen',
                                array(),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                )
            )
            .
            School::useService()->createSchool(
                $this->formSchoolCompanyCreate()
                    ->appendFormButton(new Primary('Schule hinzufügen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Type, $School
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSchoolCompanyCreate()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Schule auswählen:'
            .new PullRight(
                new Standard('Neue Firma anlegen', '/Corporation/Company', new Building()
                    , array(), '"Schule hinzufügen" verlassen'
                ))
        );
        $tblTypeAll = School::useService()->getTypeAll();
        $tblCompanyAll = Company::useService()->getCompanyAll();
        array_walk($tblCompanyAll, function (TblCompany &$tblCompany) {

            $tblCompany = new PullClear(new RadioBox('School',
                $tblCompany->getName().' '.new Success($tblCompany->getDescription()),
                $tblCompany->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Schulgrad',
                            array(
                                new SelectBox('Type[Type]', '',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                            ), Panel::PANEL_TYPE_INFO
                        ),
                    ), 4),
                    new FormColumn(array(
                        new Panel($PanelSelectCompanyTitle, $tblCompanyAll, Panel::PANEL_TYPE_INFO, null, 15),
                    ), 8),
                )),
            ))
        );
    }

    /**
     * @param $School
     *
     * @return Stage
     */
    public function frontendSchoolDelete($School)
    {

        $Stage = new Stage('Schule', 'von der Anzeige entfernen');

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel(new Standard(new ChevronLeft(),
                                    '/Setting/Consumer/School').'Zurück zur Übersicht Schulen',
                                array(),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                )
            )
            .
            School::useService()->removeSchool(
                $this->formSchoolCompanyDelete()
                    ->appendFormButton(new Danger('Schule Entfernen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $School
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSchoolCompanyDelete()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Implementierte Schulen:'
        );
        $tblSchoolAll = School::useService()->getSchoolAll();
        array_walk($tblSchoolAll, function (TblSchool &$tblSchool) {

            $tblCompany = $tblSchool->getServiceTblCompany();
            $tblType = $tblSchool->getTblType();

            $tblSchool = new PullClear(new RadioBox('School',
                $tblCompany->getName().' <b>Typ: '.$tblType->getName().'</b>',
                $tblSchool->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel($PanelSelectCompanyTitle, $tblSchoolAll, Panel::PANEL_TYPE_INFO, null, 15),
                    ), 12),
                )),
            ))
        );
    }

}

<?php
namespace SPHERE\Application\Setting\Consumer\School;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Type;
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
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\Icon\Repository\Question;
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
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Window\Redirect;
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

        $Stage = new Stage('Schulen', 'Übersicht');

        $Stage->setContent(
            new Standard('Schule hinzufügen', '/Setting/Consumer/School/Create')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Es ist noch keine Schule eingetragen')
                        )
                    ), new Title('')
                )
            )
        );

        if (( $tblSchoolAll = School::useService()->getSchoolAll() )) {

            $Form = null;
            foreach ($tblSchoolAll as $tblSchool) {
                $tblCompany = $tblSchool->getServiceTblCompany();
                $Form .= new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            self::frontendLayoutCombine($tblCompany)
                        )),
                    ),
                        (new Title(new TagList().' '.
                            new \SPHERE\Common\Frontend\Text\Repository\Warning($tblSchool->getServiceTblType()->getName()).' '
                            .$tblCompany->getName(), ' Kontaktdaten'
                        ))
                    ),
                ));
            }

            $Stage->setContent(
                new Standard('Schule hinzufügen', '/Setting/Consumer/School/Create')
                .new Standard('Schule entfernen', '/Setting/Consumer/School/Delete')
                .$Form
            );
        }
        return $Stage;
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return Layout
     */
    public function frontendLayoutCombine(TblCompany $tblCompany)
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
                        new MapMarker().' '.$tblToCompany->getTblType()->getName(), $Panel, Panel::PANEL_TYPE_SUCCESS)
                    , 3);
            });
        } else {
            $tblAddressAll = array(
                new LayoutColumn(
                    new Warning('Keine Adressen hinterlegt')
                    , 3)
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
                            ))
                        , 3);
                });
        } else {
            $tblPhoneAll = array(
                new LayoutColumn(
                    new Warning('Keine Telefonnummern hinterlegt')
                    , 3)
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
                            Panel::PANEL_TYPE_SUCCESS)
                        , 3);
                });
        } else {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning('Keine E-Mail Adressen hinterlegt')
                    , 3)
            );
        }
        if ($tblRelationshipAll !== false) {
            array_walk($tblRelationshipAll, function (
                \SPHERE\Application\People\Relationship\Service\Entity\TblToCompany &$tblToCompany
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
                        Panel::PANEL_TYPE_DEFAULT)
                    , 3);
            }, $tblCompany);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Firmenbeziehungen hinterlegt')
                    , 3)
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;

        /**
         * @var LayoutColumn $tblAddress
         */
        foreach ($tblAddressAll as $tblAddress) {
            if ($LayoutRowCount % 4 == 0) {
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
            if ($LayoutRowCount % 4 == 0) {
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
            if ($LayoutRowCount % 4 == 0) {
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
     * @param $School
     * @param $Type
     *
     * @return Stage
     */
    public function frontendSchoolCreate($School, $Type)
    {

        $Stage = new Stage('Schule', 'anlegen');
        $tblCompanyAll = Company::useService()->getCompanyAll();
        if (!empty( $tblCompanyAll )) {
            $Stage->setContent(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new Panel(new Standard(new ChevronLeft(),
                                        '/Setting/Consumer/School').'Zurück zur Übersicht',
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
        } else {
            $Stage->setContent(new Warning('Es gibt noch keine Firmen die als Schule eingetragen werden kann.'));
        }

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
        $tblTypeAll = Type::useService()->getTypeAll();
        $tblCompanyAll = Company::useService()->getCompanyAll();
        array_walk($tblCompanyAll, function (TblCompany &$tblCompany) {

            $tblCompany = new PullClear(new RadioBox('School',
                $tblCompany->getName().' '.new SuccessText($tblCompany->getDescription()),
                $tblCompany->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Schulart',
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
     * @return Stage
     */
    public function frontendSchoolDelete()
    {

        $Stage = new Stage('Schule', 'entfernen');

        $tblSchoolAll = School::useService()->getSchoolAll();
        if ($tblSchoolAll) {
            array_walk($tblSchoolAll, function (TblSchool &$tblSchool) {

                $tblCompany = $tblSchool->getServiceTblCompany();

                $Address = array();
                $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                if ($tblAddressAll) {
                    foreach ($tblAddressAll as $tblAddress) {
                        $Address[] = $tblAddress->getTblAddress()->getStreetName().' '
                            .$tblAddress->getTblAddress()->getStreetNumber().' '
                            .$tblAddress->getTblAddress()->getTblCity()->getName();
                    }
                }
                $Content = array(
                    ( $tblCompany->getName() ? $tblCompany->getName() : false ),
                    ( isset( $Address[0] ) ? new Small(new Muted($Address[0])) : false ),
                    ( isset( $Address[1] ) ? new Small(new Muted($Address[1])) : false ),
                    ( isset( $Address[2] ) ? new Small(new Muted($Address[2])) : false ),
                    (new Standard('', '/Setting/Consumer/School/Destroy', new Remove(),
                        array('Id' => $tblSchool->getId())))
                );
                $Content = array_filter($Content);
                $Type = Panel::PANEL_TYPE_WARNING;
                $tblSchool = new LayoutColumn(
                    new Panel($tblSchool->getServiceTblType()->getName(), $Content, $Type)
                    , 6);
            });

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblSchool
             */
            foreach ($tblSchoolAll as $tblSchool) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblSchool);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = false;
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendSchoolDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Schule', 'Löschen');
        if ($Id) {
            $tblSchool = School::useService()->getSchoolById($Id);
            if (!$Confirm) {

                $Address = array();
                $tblAddressAll = Address::useService()->getAddressAllByCompany($tblSchool->getServiceTblCompany());
                if ($tblAddressAll) {
                    foreach ($tblAddressAll as $tblAddress) {
                        $Address[] = $tblAddress->getTblAddress()->getStreetName().' '
                            .$tblAddress->getTblAddress()->getStreetNumber().' '
                            .$tblAddress->getTblAddress()->getTblCity()->getName();
                    }
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diese Schule mit der Schulart "'.$tblSchool->getServiceTblType()->getName().'" wirklich löschen?',
                            array(
                                $tblSchool->getServiceTblCompany()->getName().' '.$tblSchool->getServiceTblCompany()->getDescription(),
                                ( isset( $Address[0] ) ? new Muted(new Small($Address[0])) : false ),
                                ( isset( $Address[1] ) ? new Muted(new Small($Address[1])) : false ),
                                ( isset( $Address[2] ) ? new Muted(new Small($Address[2])) : false ),
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Consumer/School/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Setting/Consumer/School', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( School::useService()->destroySchool($tblSchool)
                                ? new Success('Die Schule wurde gelöscht')
                                .new Redirect('/Setting/Consumer/School', 0)
                                : new Danger('Die Schule konnte nicht gelöscht werden')
                                .new Redirect('/Setting/Consumer/School', 10)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Schule konnte nicht gefunden werden'),
                        new Redirect('/Setting/Consumer/School', 3)
                    )))
                )))
            );
        }

        return $Stage;
    }

}

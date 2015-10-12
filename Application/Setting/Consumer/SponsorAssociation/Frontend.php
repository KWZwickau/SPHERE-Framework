<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
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
use SPHERE\Common\Frontend\Message\Repository\Danger;
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
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Förderverein', 'Übersicht');

        $Stage->setContent(
            new Standard('Förderverein hinzufügen', '/Setting/Consumer/SponsorAssociation/Create')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Es ist noch kein Förderverein eingetragen')
                        )
                    ), new Title('')
                )
            )
        );

        if (( $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll() )) {

            $tblCompanyAll[] = null;

            $Form = null;
            foreach ($tblSponsorAssociationAll as $tblSponsorAssociation) {
                $tblCompany = $tblSponsorAssociation->getServiceTblCompany();

                $Form .= new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            School::useFrontend()->frontendLayoutCombine($tblCompany)
                        )),
                    ), (new Title(new TagList().' Kontaktdaten', 'von '.$tblCompany->getName()))
                    ),
                ));
            }
            $Stage->setContent(
                new Standard('Förderverein hinzufügen', '/Setting/Consumer/SponsorAssociation/Create')
                .new Standard('Förderverein entfernen', '/Setting/Consumer/SponsorAssociation/Delete')
                .$Form);
        }

        return $Stage;
    }

    /**
     * @param $SponsorAssociation
     *
     * @return Stage
     */
    public function frontendSponsorAssociationCreate($SponsorAssociation)
    {

        $Stage = new Stage('Förderverein', 'anlegen');

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel(new Standard(new ChevronLeft(),
                                    '/Setting/Consumer/SponsorAssociation').'Zurück zur Übersicht',
                                array(),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                )
            )
            .
            SponsorAssociation::useService()->createSponsorAssociation(
                $this->formSponsorAssociationCompanyCreate()
                    ->appendFormButton(new Primary('Förderverein hinzufügen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $SponsorAssociation
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSponsorAssociationCompanyCreate()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Förderverein auswählen:'
            .new PullRight(
                new Standard('Neue Firma anlegen', '/Corporation/Company', new Building()
                    , array(), '"Förderverein hinzufügen" verlassen'
                ))
        );
        $tblCompanyAll = Company::useService()->getCompanyAll();
        array_walk($tblCompanyAll, function (TblCompany &$tblCompany) {

            $tblCompany = new PullClear(new RadioBox('SponsorAssociation',
                $tblCompany->getName().' '.new SuccessText($tblCompany->getDescription()),
                $tblCompany->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel($PanelSelectCompanyTitle, $tblCompanyAll, Panel::PANEL_TYPE_INFO, null, 15),
                    ), 12),
                )),
            ))
        );
    }

    /**
     *
     * @return Stage
     */
    public function frontendSponsorAssociationDelete()
    {

        $Stage = new Stage('Förderverein', 'entfernen');

        $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll();
        if ($tblSponsorAssociationAll) {
            array_walk($tblSponsorAssociationAll, function (TblSponsorAssociation &$tblSponsorAssociation) {

                $tblCompany = $tblSponsorAssociation->getServiceTblCompany();

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
                    (new Standard('', '/Setting/Consumer/SponsorAssociation/Destroy', new Remove(),
                        array('Id' => $tblSponsorAssociation->getId())))
                );
                $Content = array_filter($Content);
                $Type = Panel::PANEL_TYPE_WARNING;
                $tblSponsorAssociation = new LayoutColumn(
                    new Panel('Förderverein', $Content, $Type)
                    , 6);
            });

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblSponsorAssociation
             */
            foreach ($tblSponsorAssociationAll as $tblSponsorAssociation) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblSponsorAssociation);
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
    public function formSponsorAssociationCompanyDelete($Id, $Confirm = false)
    {

        $Stage = new Stage('Förderverein', 'Löschen');
        if ($Id) {
            $tblSponsorAssociation = SponsorAssociation::useService()->getSponsorAssociationById($Id);
            if (!$Confirm) {

                $Address = array();
                $tblAddressAll = Address::useService()->getAddressAllByCompany($tblSponsorAssociation->getServiceTblCompany());
                if ($tblAddressAll) {
                    foreach ($tblAddressAll as $tblAddress) {
                        $Address[] = $tblAddress->getTblAddress()->getStreetName().' '
                            .$tblAddress->getTblAddress()->getStreetNumber().' '
                            .$tblAddress->getTblAddress()->getTblCity()->getName();
                    }
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Förderverein wirklich löschen?', array(
                            $tblSponsorAssociation->getServiceTblCompany()->getName().' '.$tblSponsorAssociation->getServiceTblCompany()->getDescription(),
                            ( isset( $Address[0] ) ? new Muted(new Small($Address[0])) : false ),
                            ( isset( $Address[1] ) ? new Muted(new Small($Address[1])) : false ),
                            ( isset( $Address[2] ) ? new Muted(new Small($Address[2])) : false ),
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Consumer/SponsorAssociation/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Setting/Consumer/SponsorAssociation', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( SponsorAssociation::useService()->destroySponsorAssociation($tblSponsorAssociation)
                                ? new Success('Der Förderverein wurde gelöscht')
                                .new Redirect('/Setting/Consumer/SponsorAssociation', 0)
                                : new Danger('Der Förderverein konnte nicht gelöscht werden')
                                .new Redirect('/Setting/Consumer/SponsorAssociation', 10)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Förderverein konnte nicht gefunden werden'),
                        new Redirect('/Setting/Consumer/SponsorAssociation', 3)
                    )))
                )))
            );
        }

        return $Stage;
    }

}

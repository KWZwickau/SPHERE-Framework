<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Success;
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

        $Stage = new Stage('Dashboard', 'Förderverein');

        $Stage->setContent(
            new Standard('Förderverein hinzufügen', '/Setting/Consumer/SponsorAssociation/Create')
            .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Es ist noch kein Förderverein eingetragen'))))));

        if ($tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll()) {

            $tblCompanyAll[] = null;

            $Form[] = null;
            foreach ($tblSponsorAssociationAll as $tblSponsorAssociation) {
                $tblCompany = $tblSponsorAssociation->getServiceTblCompany();

                $Stage->setContent(
                    new Standard('Förderverein hinzufügen', '/Setting/Consumer/SponsorAssociation/Create')
                    .new Standard('Förderverein entfernen', '/Setting/Consumer/SponsorAssociation/Delete')
                    .new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                Address::useFrontend()->frontendLayoutCompany($tblCompany)
                                .Phone::useFrontend()->frontendLayoutCompany($tblCompany)
                                .Mail::useFrontend()->frontendLayoutCompany($tblCompany)
                                .Relationship::useFrontend()->frontendLayoutCompany($tblCompany)
                            )),
                        ), (new Title(new TagList().' Kontaktdaten', 'von '.$tblCompany->getName()))
                        ),
                    ))
                );
            }
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
                                    '/Setting/Consumer/SponsorAssociation').'Zurück zur Übersicht Förderverein',
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
                $tblCompany->getName().' '.new Success($tblCompany->getDescription()),
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
     * @param $SponsorAssociation
     *
     * @return Stage
     */
    public function frontendSponsorAssociationDelete($SponsorAssociation)
    {

        $Stage = new Stage('Förderverein', 'von der Anzeige entfernen');

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel(new Standard(new ChevronLeft(),
                                    '/Setting/Consumer/SponsorAssociation').'Zurück zur Übersicht Förderverein',
                                array(),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                )
            )
            .
            SponsorAssociation::useService()->removeSponsorAssociation(
                $this->formSponsorAssociationCompanyDelete()
                    ->appendFormButton(new Danger('Förderverein Entfernen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $SponsorAssociation
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSponsorAssociationCompanyDelete()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Implementierte Fördervereine:'
        );
        $tblSponsorAssociationAll = SponsorAssociation::useService()->getSponsorAssociationAll();
        array_walk($tblSponsorAssociationAll, function (TblSponsorAssociation &$tblSponsorAssociation) {

            $tblCompany = $tblSponsorAssociation->getServiceTblCompany();

            $tblSponsorAssociation = new PullClear(new RadioBox('SponsorAssociation',
                $tblCompany->getName().' '.new Success($tblCompany->getDescription()),
                $tblSponsorAssociation->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel($PanelSelectCompanyTitle, $tblSponsorAssociationAll, Panel::PANEL_TYPE_INFO, null,
                            15),
                    ), 12),
                )),
            ))
        );
    }

}

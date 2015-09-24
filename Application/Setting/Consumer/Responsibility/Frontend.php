<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
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
 * @package SPHERE\Application\Setting\Consumer\Responsibility
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Schulträger');

        $Stage->setContent(new Standard('Schulträger hinzufügen', '/Setting/Consumer/Responsibility/Create')
            .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Es ist noch kein Schulträger eingetragen'))))));

        if ($tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll()) {

            $tblCompanyAll[] = null;

            $Form[] = null;
            foreach ($tblResponsibilityAll as $tblResponsibility) {
                $tblCompany = $tblResponsibility->getServiceTblCompany();

                $Stage->setContent(
                    new Standard('Schulträger hinzufügen', '/Setting/Consumer/Responsibility/Create')
                    .new Standard('Schulträger entfernen', '/Setting/Consumer/Responsibility/Delete')
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
     * @param $Responsibility
     *
     * @return Stage
     */
    public function frontendResponsibilityCreate($Responsibility)
    {

        $Stage = new Stage('Schulträger', 'anlegen');

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel(new Standard(new ChevronLeft(),
                                    '/Setting/Consumer/Responsibility').'Zurück zur Übersicht Schulträger',
                                array(),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                )
            )
            .
            Responsibility::useService()->createResponsibility(
                $this->formResponsibilityCompanyCreate()
                    ->appendFormButton(new Primary('Schulträger hinzufügen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Responsibility
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formResponsibilityCompanyCreate()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Schulträger auswählen:'
            .new PullRight(
                new Standard('Neue Firma anlegen', '/Corporation/Company', new Building()
                    , array(), '"Schulträger hinzufügen" verlassen'
                ))
        );
        $tblCompanyAll = Company::useService()->getCompanyAll();
        array_walk($tblCompanyAll, function (TblCompany &$tblCompany) {

            $tblCompany = new PullClear(new RadioBox('Responsibility',
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
     * @param $Responsibility
     *
     * @return Stage
     */
    public function frontendResponsibilityDelete($Responsibility)
    {

        $Stage = new Stage('Schulträger', 'von der Anzeige entfernen');

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel(new Standard(new ChevronLeft(),
                                    '/Setting/Consumer/Responsibility').'Zurück zur Übersicht Schulträger',
                                array(),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                )
            )
            .
            Responsibility::useService()->removeResponsibility(
                $this->formResponsibilityCompanyDelete()
                    ->appendFormButton(new Danger('Schulträger Entfernen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Responsibility
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formResponsibilityCompanyDelete()
    {

        $PanelSelectCompanyTitle = new PullClear(
            'Implementierte Schulträger:'
        );
        $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll();
        array_walk($tblResponsibilityAll, function (TblResponsibility &$tblResponsibility) {

            $tblCompany = $tblResponsibility->getServiceTblCompany();

            $tblResponsibility = new PullClear(new RadioBox('Responsibility',
                $tblCompany->getName().' '.new Success($tblCompany->getDescription()),
                $tblResponsibility->getId()));
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel($PanelSelectCompanyTitle, $tblResponsibilityAll, Panel::PANEL_TYPE_INFO, null,
                            15),
                    ), 12),
                )),
            ))
        );
    }

}

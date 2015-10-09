<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
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
 * @package SPHERE\Application\Setting\Consumer\Responsibility
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Schulträger', 'Übersicht');

        $Stage->setContent(new Standard('Schulträger hinzufügen', '/Setting/Consumer/Responsibility/Create')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Es ist noch kein Schulträger eingetragen')
                        )
                    ), new Title('')
                )
            )
        );

        if (( $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll() )) {

            $tblCompanyAll[] = null;

            $Form = null;
            foreach ($tblResponsibilityAll as $tblResponsibility) {
                $tblCompany = $tblResponsibility->getServiceTblCompany();

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
                new Standard('Schulträger hinzufügen', '/Setting/Consumer/Responsibility/Create')
                .new Standard('Schulträger entfernen', '/Setting/Consumer/Responsibility/Delete')
                .$Form
            );
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
                                    '/Setting/Consumer/Responsibility').'Zurück zur Übersicht',
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
     * @return Stage
     */
    public function frontendResponsibilityDelete()
    {

        $Stage = new Stage('Schulträger', 'entfernen');

        $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll();
        if ($tblResponsibilityAll) {
            array_walk($tblResponsibilityAll, function (TblResponsibility &$tblResponsibility) {

                $tblCompany = $tblResponsibility->getServiceTblCompany();

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
                    (new Standard('', '/Setting/Consumer/Responsibility/Destroy', new Remove(),
                        array('Id' => $tblResponsibility->getId())))
                );
                $Content = array_filter($Content);
                $Type = Panel::PANEL_TYPE_WARNING;
                $tblResponsibility = new LayoutColumn(
                    new Panel('Schulträger', $Content, $Type)
                    , 6);
            });

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblResponsibility
             */
            foreach ($tblResponsibilityAll as $tblResponsibility) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblResponsibility);
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
    public function frontendResponsibilityDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Schulträger', 'Löschen');
        if ($Id) {
            $tblResponsibility = Responsibility::useService()->getResponsibilityById($Id);
            if (!$Confirm) {

                $Address = array();
                $tblAddressAll = Address::useService()->getAddressAllByCompany($tblResponsibility->getServiceTblCompany());
                if ($tblAddressAll) {
                    foreach ($tblAddressAll as $tblAddress) {
                        $Address[] = $tblAddress->getTblAddress()->getStreetName().' '
                            .$tblAddress->getTblAddress()->getStreetNumber().' '
                            .$tblAddress->getTblAddress()->getTblCity()->getName();
                    }
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Schulträger wirklich löschen?', array(
                            $tblResponsibility->getServiceTblCompany()->getName().' '.$tblResponsibility->getServiceTblCompany()->getDescription(),
                            ( isset( $Address[0] ) ? new Muted(new Small($Address[0])) : false ),
                            ( isset( $Address[1] ) ? new Muted(new Small($Address[1])) : false ),
                            ( isset( $Address[2] ) ? new Muted(new Small($Address[2])) : false ),
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Consumer/Responsibility/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Setting/Consumer/Responsibility', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Responsibility::useService()->destroyResponsibility($tblResponsibility)
                                ? new Success('Der Schulträger wurde gelöscht')
                                .new Redirect('/Setting/Consumer/Responsibility', 0)
                                : new Danger('Der Schulträger konnte nicht gelöscht werden')
                                .new Redirect('/Setting/Consumer/Responsibility', 10)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Schulträger konnte nicht gefunden werden'),
                        new Redirect('/Setting/Consumer/Responsibility', 3)
                    )))
                )))
            );
        }

        return $Stage;
    }

}

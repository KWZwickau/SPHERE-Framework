<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Es ist noch kein Schulträger eingetragen')
                        )
                    ), new Title('')
                )
            )
        );

        if (($tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll())) {

            $Form = null;
            foreach ($tblResponsibilityAll as $tblResponsibility) {
                $tblCompany = $tblResponsibility->getServiceTblCompany();
                $CompanyNumber = $tblResponsibility->getCompanyNumber();
                $CompanyNumberPanel = new Panel(new PullClear('Unternehmensnr. des Unfallversicherungsträgers'
                        .new PullRight(($CompanyNumber == '' ? '(leer)' : '')))
                    , $CompanyNumber,
                    ($CompanyNumber != '' ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING),
                    new PullRight(new Standard('', '/Setting/Consumer/Responsibility/Edit', new Edit(),
                        array('Id' => $tblResponsibility->getId()),
                        'Bearbeiten')));

                if ($tblCompany) {

                    $Form .= new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                School::useFrontend()->frontendLayoutCombine($tblCompany)
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    $CompanyNumberPanel
                                    , 3)
                            )
                        ), (new Title(new TagList().' Kontaktdaten', 'von '.$tblCompany->getDisplayName()))
                        ),
                    ));
                }
            }
            $Stage->setContent(
                new Standard('Schulträger hinzufügen', '/Setting/Consumer/Responsibility/Create')
                . new Standard('Schulträger entfernen', '/Setting/Consumer/Responsibility/Delete')
                . $Form
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

        $Stage = new Stage('Schulträger', 'Hinzufügen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/Responsibility', new ChevronLeft()));
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Responsibility::useService()->createResponsibility(
                                $this->formResponsibilityCompanyCreate()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                                $Responsibility
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen')
                )
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
            . new PullRight(
                new Standard('Neue Institution anlegen', '/Corporation/Company', new Building()
                    , array(), '"Schulträger hinzufügen" verlassen'
                ))
        );
        $tblCompanyAll = Company::useService()->getCompanyAll();
        $TableContent = array();
        if ($tblCompanyAll) {
            array_walk($tblCompanyAll, function (TblCompany $tblCompany) use (&$TableContent) {
                $temp['Select'] = new RadioBox('Responsibility', '&nbsp;', $tblCompany->getId());
                $temp['Content'] = $tblCompany->getName()
                    .new Container($tblCompany->getExtendedName())
                    .new Container(new Muted($tblCompany->getDescription()));
                array_push($TableContent, $temp);
            });
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        !empty($TableContent) ?
                            new Panel($PanelSelectCompanyTitle,
                                new TableData($TableContent, null, array(
                                    'Select'  => 'Auswahl',
                                    'Content' => 'Institution',
                                ), array(
                                    'columnDefs' => array(
                                        array('width' => '1%', 'targets' => array(0))
                                    ),
                                    'order' => array(
                                        array(1, 'asc'),
                                    ),
                                ))
                                , Panel::PANEL_TYPE_INFO, null, 15)
                            : new Panel($PanelSelectCompanyTitle,
                            new Warning('Es ist keine Institution vorhanden die ausgewählt werden kann')
                            , Panel::PANEL_TYPE_INFO)
                    ), 12),
                )),
            ))
        );
    }

    /**
     * @param null $Id
     * @param null $CompanyNumber
     * @param null $Responsibility
     *
     * @return Stage
     */
    public function frontendResponsibilityEdit($Id = null, $CompanyNumber = null, $Responsibility = null)
    {

        $Stage = new Stage('Unternehmensnr. des Unfallversicherungsträgers', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/Responsibility', new ChevronLeft()));
        $tblResponsibility = Responsibility::useService()->getResponsibilityById($Id);
        if (!$tblResponsibility) {
            return $Stage->setContent(new Warning('Dieser Schulträger wurde nicht gefunden.')
                .new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_ERROR));
        }
        $Form = new Form(new FormGroup(new FormRow(array(new FormColumn(
            new Panel('Unternehmensnr. des Unfallversicherungsträgers', new TextField('CompanyNumber', '', ''),
                Panel::PANEL_TYPE_SUCCESS)),
            new FormColumn(new HiddenField('Responsibility[IsSubmit]'))
        ))));
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $tblCompany = $tblResponsibility->getServiceTblCompany();
        if ($tblCompany) {
            $PanelHead = new Panel('Institution der eine Unternehmensnr. des Unfallversicherungsträgers bearbeitet werden soll'
                , $tblCompany->getDisplayName(), Panel::PANEL_TYPE_INFO);
        } else {
            $PanelHead = new Panel('Institution wird nicht mehr gefunden!', '', Panel::PANEL_TYPE_DANGER);
        }


        $Global = $this->getGlobal();
        if ($tblResponsibility->getCompanyNumber()) {
            $Global->POST['CompanyNumber'] = $tblResponsibility->getCompanyNumber();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelHead
                            , 6)
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(Responsibility::useService()->updateResponsibility(
                                $Form, $tblResponsibility, $CompanyNumber, $Responsibility
                            ))
                            , 6)
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendResponsibilityDelete()
    {

        $Stage = new Stage('Schulträger', 'Entfernen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Consumer/Responsibility', new ChevronLeft()));
        $tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll();
        if ($tblResponsibilityAll) {
            array_walk($tblResponsibilityAll, function (TblResponsibility &$tblResponsibility) {

                $tblCompany = $tblResponsibility->getServiceTblCompany();
                if ($tblCompany) {
                    $Address = array();
                    $Address[] = $tblCompany->getName().new Container($tblCompany->getExtendedName());
                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
                    if ($tblAddressAll) {
                        foreach ($tblAddressAll as $tblAddress) {
                            $Address[] = new Muted(new Small($tblAddress->getTblAddress()->getStreetName().' '
                                . $tblAddress->getTblAddress()->getStreetNumber() . ' '
                                .$tblAddress->getTblAddress()->getTblCity()->getName()));
                        }
                    }
                    $Address[] = (new Standard('', '/Setting/Consumer/Responsibility/Destroy', new Remove(),
                        array('Id' => $tblResponsibility->getId())));
                    $Content = array_filter($Address);
                    $Type = Panel::PANEL_TYPE_WARNING;
                    $tblResponsibility = new LayoutColumn(
                        new Panel('Schulträger', $Content, $Type)
                        , 6);
                } else {
                    $tblResponsibility = false;
                }
            });
            $tblResponsibilityAll = array_filter($tblResponsibilityAll);

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
            if ($tblResponsibility->getServiceTblCompany()) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Der Schulträger konnte nicht gefunden werden'),
                            new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            }

            if (!$Confirm) {
                $Address = array();
                if ($tblResponsibility->getServiceTblCompany()) {
                    $Address[] = $tblResponsibility->getServiceTblCompany()->getName()
                        .new Container($tblResponsibility->getServiceTblCompany()->getExtendedName())
                        .new Container(new Muted($tblResponsibility->getServiceTblCompany()->getDescription()));

                    $tblAddressAll = Address::useService()->getAddressAllByCompany($tblResponsibility->getServiceTblCompany());
                    if ($tblAddressAll) {
                        foreach ($tblAddressAll as $tblAddress) {
                            $Address[] = new Muted(new Small($tblAddress->getTblAddress()->getStreetName().' '
                                .$tblAddress->getTblAddress()->getStreetNumber().' '
                                .$tblAddress->getTblAddress()->getTblCity()->getName()));
                        }
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Schulträger wirklich löschen?', $Address,
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Consumer/Responsibility/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
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
                            (Responsibility::useService()->destroyResponsibility($tblResponsibility)
                                ? new Success('Der Schulträger wurde gelöscht')
                                . new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_SUCCESS)
                                : new Danger('Der Schulträger konnte nicht gelöscht werden')
                                . new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_ERROR)
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
                        new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

}

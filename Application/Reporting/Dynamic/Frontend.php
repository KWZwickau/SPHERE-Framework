<?php
namespace SPHERE\Application\Reporting\Dynamic;

use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilter;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterMask;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\View;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Structure\LinkGroup;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Dynamic
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $DynamicFilter
     *
     * @return Stage
     */
    public function frontendSetupFilter($DynamicFilter = 0)
    {

        $Stage = new Stage('Flexible Auswertung', 'Filter definieren');
        $Stage->setMessage('');

        $StartViewList = array(
            new ViewPeopleGroupMember()
        );

        $tblDynamicFilter = Dynamic::useService()->getDynamicFilterById($DynamicFilter);
        $tblDynamicFilterMaskAll = Dynamic::useService()->getDynamicFilterMaskAllByFilter($tblDynamicFilter);

        $AvailableViewList = array();
        if (!$tblDynamicFilterMaskAll) {
            $AvailableViewList = $StartViewList;
        } else {
            // Get LAST Mask
            $tblDynamicFilterMaskAll = $this->getSorter($tblDynamicFilterMaskAll)
                ->sortObjectBy(TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER);
            /** @var TblDynamicFilterMask $tblDynamicFilterMaskLast */
            $tblDynamicFilterMaskLast = end($tblDynamicFilterMaskAll);
            // Get ForeignView-List
            $ForeignViewList = $tblDynamicFilterMaskLast->getFilterClassInstance()->getForeignViewList();
            // Define as AvailableView-List
            foreach ($ForeignViewList as $ForeignView) {
                // Index 1 contains FQ Class-Name
                array_push($AvailableViewList, new $ForeignView[1]);
            }
        }

        $Panel = array();
        /** @var AbstractView $AvailableView */
        foreach ($AvailableViewList as $Index => $AvailableView) {

            $Panel[] = (string)new Panel($AvailableView->getViewGuiName(), array(
                new Muted(new Small(implode(', ', $AvailableView->getNameDefinitionList())))
            ), Panel::PANEL_TYPE_INFO, array(
                    new Standard('Hinzufügen', new Route(__NAMESPACE__.'/Setup'), new ChevronLeft(), array(
                        'DynamicFilter' => $DynamicFilter
                    ))
                )

            );
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $Panel
                        )
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ''
                        )
                    )
                ),
            ))
        );

        return $Stage;
    }


    /**
     * @param string $FilterName
     * @param int    $IsPublic
     *
     * @return Stage
     */
    public function frontendCreateFilter($FilterName = null, $IsPublic = 0)
    {

        $Stage = new Stage('Flexible Auswertung', 'Filter erstellen');
        $Stage->setMessage('');

        $tblAccount = Account::useService()->getAccountBySession();

        $tblDynamicFilterListOwner = Dynamic::useService()->getDynamicFilterAll($tblAccount);
        if (!$tblDynamicFilterListOwner) {
            $tblDynamicFilterListOwner = array();
        }

        $tblDynamicFilterListPublic = Dynamic::useService()->getDynamicFilterAllByIsPublic();
        if (!$tblDynamicFilterListPublic) {
            $tblDynamicFilterListPublic = array();
        }

        $tblDynamicFilterList = array_unique(
            array_merge($tblDynamicFilterListPublic, $tblDynamicFilterListOwner)
        );

        $DynamicFilterList = array();
        array_walk($tblDynamicFilterList, function (TblDynamicFilter $tblDynamicFilter)
        use (&$DynamicFilterList, $tblAccount) {

            $Option = '';
            if ($tblAccount == $tblDynamicFilter->getServiceTblAccount()) {
                $Option = ( new LinkGroup() )
                    ->addLink(new Standard('', '', new Edit(), array(), 'Bearbeiten'))
                    ->addLink(new Standard('', '', new Remove(), array(), 'Löschen'));
                $Option .= ( new LinkGroup() )
                    ->addLink(new Standard('', new Route(__NAMESPACE__.'/Setup'), new Setup(),
                        array('DynamicFilter' => $tblDynamicFilter->getId()), 'Einstellungen'
                    ));
            }
            $Option .= ( new LinkGroup() )->addLink(new Standard('', new Route(__NAMESPACE__.'/Run'), new View(),
                array('DynamicFilter' => $tblDynamicFilter->getId()),
                'Anzeigen / Verwenden'
            ));

            array_push($DynamicFilterList, array(
                'Option'                               => $Option,
                TblDynamicFilter::PROPERTY_FILTER_NAME => $tblDynamicFilter->getFilterName(),
                TblDynamicFilter::PROPERTY_IS_PUBLIC   => new Center(
                    ( $tblAccount == $tblDynamicFilter->getServiceTblAccount()
                        ? ( $tblDynamicFilter->isPublic()
                            ? new Person().new Container(new Label('Sichtbar', Label::LABEL_TYPE_WARNING))
                            : new Person().new Container(new Label('Privat', Label::LABEL_TYPE_SUCCESS))
                        )
                        : new PersonGroup().new Container(new Label('Geteilt', Label::LABEL_TYPE_INFO))
                    )
                )
            ));
        });

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($DynamicFilterList, null, array(
                                'IsPublic'   => 'Sichtbarkeit',
                                'FilterName' => 'Name der Auswertung',
                                'Option'     => ''
                            ), array(
                                "columnDefs" => array(
                                    array("width" => "5%", "targets" => array(0)),
                                    array("width" => "15%", "targets" => array(2))
                                )
                            ))
                        )
                    ), new Title(new Listing().' Verfügbare Auswertungen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Dynamic::useService()->createDynamicFilter(
                                    $this->formCreateFilter()->appendFormButton(new Primary('Speichern', new Save())),
                                    $FilterName, $IsPublic
                                )
                            )
                        )
                    ), new Title(new PlusSign().' Neue Auswertung anlegen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formCreateFilter()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Filter definieren', array(
                            new TextField('FilterName', 'Name der Auswertung', 'Name der Auswertung', new Nameplate()),
                            new CheckBox('IsPublic', 'Auswertung für Alle sichtbar machen', 1),
                        ), Panel::PANEL_TYPE_INFO)
                    ),
                ))
            )
        );
    }
}

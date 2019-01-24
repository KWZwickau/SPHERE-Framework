<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendBalance($Balance = array())
    {

        $Stage = new Stage('Belegdruck');

        if(!isset($_POST['Balance']['Item']) && ($tblItem = Item::useService()->getItemByName('Schulgeld'))) {
            $_POST['Balance']['Item'] = $tblItem->getId();
        }
        if(!isset($Balance['Year'])) {
            $Now = new \DateTime();
            $_POST['Balance']['Year'] = $Now->format('Y');
        }
        if(!isset($Balance['From'])) {
            $_POST['Balance']['From'] = '1';
        }
        if(!isset($Balance['To'])) {
            $_POST['Balance']['To'] = '12';
        }
        // Standard Download
        $Download = (new PrimaryLink('Herunterladen', '', new Download()))->setDisabled();
        $tableContent = array();
        if(!empty($Balance)) {

            if(($tblItem = Item::useService()->getItemById($Balance['Item']))) {
                $PriceList = Balance::useService()->getPriceListByItemAndYear($tblItem, $Balance['Year'],
                    $Balance['From'], $Balance['To']);
                $tableContent = Balance::useService()->getTableContentByPriceList($PriceList);
                $Download = new PrimaryLink('Herunterladen', '/Api/Billing/Balance/Balance/Print/Download',
                    new Download(), array(
                        'ItemId' => $tblItem->getId(),
                        'Year'   => $Balance['Year'],
                        'From'   => $Balance['From'],
                        'To'     => $Balance['To']
                    ));
            }
        }

        // Selectbox soll nach unten aufklappen (tritt nur noch bei Anwendungsansicht auf)
        $Space = '<div style="height: 100px;"></div>';
        if(empty($Balance)) {

            $Table = new Info('Bitte benutzen sie die Filterung');
        } else {
            $Table = new Warning('Keine Ergebnisse gefunden');
        }
        if(!empty($tableContent)) {
            $Table = new TableData($tableContent, null, array(
                'Debtor' => 'Beitragszahler',
                'Causer' => 'Bietragsverursacher',
                'Value'  => 'Summe',
            ), array(
                'columnDefs' => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0, 1)),
                    array('type' => 'natural', 'targets' => array(2)),
//                    array("orderable" => false, "targets"   => array(5, -1)),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'asc')
                ),
                // First column should not be with Tabindex
                // solve the problem with responsive false
                "responsive" => false,
            ));
            $Space = '';
        } else {
            $Download->setDisabled();
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formBalanceFilter())
                ),
                new LayoutRow(
                    new LayoutColumn(new Container($Download).new Container('&nbsp;'))
                ),
                new LayoutRow(
                    new LayoutColumn($Table)
                ),
                new LayoutRow(
                    new LayoutColumn($Space)
                )
            ))
        ));

        return $Stage;
    }

    public function formBalanceFilter()
    {

        // SelectBox content
        $YearList = Invoice::useService()->getYearList(1, 1);
        $MonthList = Invoice::useService()->getMonthList();
        $tblItemAll = Item::useService()->getItemAll();

        return new Well(
            new Title('Filterung für Belegdruck', '').
            new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 4),
                        new FormColumn(new SelectBox('Balance[From]', 'Zeitraum Von', $MonthList, null, true, null), 4),
                        new FormColumn(new SelectBox('Balance[To]', 'Zeitraum Bis', $MonthList, null, true, null), 4),
                    )),
                    new FormRow(array(
                        new FormColumn((new SelectBox('Balance[Item]', 'Beitragsart',
                            array('{{ Name }}' => $tblItemAll)))->setRequired(), 4),
                    )),
                    new FormRow(
                        new FormColumn(new Primary('Filtern', new Filter()))
                    )
                ))
            ));
    }
}

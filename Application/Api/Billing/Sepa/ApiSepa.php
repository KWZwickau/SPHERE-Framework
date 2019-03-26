<?php
namespace SPHERE\Application\Api\Billing\Sepa;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary as PrimaryForm;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSepa
 * @package SPHERE\Application\Api\Billing\Sepa
 */
class ApiSepa extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('showOpenInvoice');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     *
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('SepaModal');
    }

    /**
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCauserModal($BasketId = '')
    {

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showOpenInvoice'
        ));
        $Emitter->setPostPayload(array(
            'BasketId' => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketId
     *
     * @return string
     */
    public function showOpenInvoice($BasketId = '')
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            $year = $tblBasket->getYear();
            $month = $tblBasket->getMonth();
            $BasketName = $tblBasket->getName();
            $TableContent = array();
            if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByIsPaid())){
                array_walk($tblInvoiceItemDebtorList, function(TblInvoiceItemDebtor $tblInvoiceItemDebtor) use (&$TableContent){
                    $CauserName = '';
                    $InvoiceTime = '';
                    $InvoiceNumber = '';
                    if(($tblInvoice = $tblInvoiceItemDebtor->getTblInvoice())){
                        $InvoiceNumber = $tblInvoice->getInvoiceNumber();
                        $CauserName = $tblInvoice->getLastName().', '.$tblInvoice->getFirstName();
                        $InvoiceTime = $tblInvoice->getYear().'.'.$tblInvoice->getMonth();
                    }

                    $item['Option'] = new CheckBox('Invoice[CheckboxList][]', '&nbsp;', $tblInvoiceItemDebtor->getId());
                    $item['InvoiceNumber'] = $InvoiceNumber;
                    $item['CauserName'] = $CauserName;
                    $item['InvoiceTime'] = $InvoiceTime;
                    $item['Name'] = $tblInvoiceItemDebtor->getName();
                    $item['SummaryPrice'] = $tblInvoiceItemDebtor->getSummaryPrice();
                    $item['Owner'] = $tblInvoiceItemDebtor->getOwner();
//                    $item[''] = ;

                    array_push($TableContent, $item);
                });
            }

        } else {
            return new Warning('Der Warenkorb wurde nicht gefunden.');
        }

        $form = '';
        $toggleCheckbox = '';
        if(!empty($TableContent)){

            $FormColumnTable = new FormColumn(
                new TableData($TableContent, null, array(
                    'Option' => 'Erneut',
                    'InvoiceNumber' => 'R.Nr.',
                    'CauserName' => 'Beitragsverursacher',
                    'InvoiceTime' => 'Abr. Monat',
                    'Name' => 'Beitragsart',
                    'SummaryPrice' => 'Preis',
                    'Owner' => 'Beitragszahler',
                ), null)
            );
            $form = new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            new HiddenField('Invoice[Year]'),
                            new HiddenField('Invoice[Month]'),
                            new HiddenField('Invoice[BasketName]'),
                        )),
                        new FormColumn(
                            new Title('Offene Posten', 'erneut in SEPA-Lastschrift aufnehmen')
                        ),
                        $FormColumnTable,
                    ))
                ), new PrimaryForm('&nbsp;SEPA Download', new Download(), true), '\Api\Billing\Sepa\Download'
            );
            $toggleCheckbox = new ToggleCheckbox( 'Alle wählen/abwählen', $form );
        }


        // set hidden POST
        $_POST['Invoice']['Year'] = $year;
        $_POST['Invoice']['Month'] = $month;
        $_POST['Invoice']['BasketName'] = $BasketName;

        return $toggleCheckbox.$form;
    }
}
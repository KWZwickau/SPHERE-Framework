<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasketRepaymentAddPerson
 * @package SPHERE\Application\Api\Billing\Bookkeeping
 */
class ApiBasketRepaymentAddPerson extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // AddDebtorSelection
        $Dispatcher->registerMethod('showSearchModal');
        $Dispatcher->registerMethod('showSearch');
        $Dispatcher->registerMethod('addDebtorSelection');

        // reload Table
        $Dispatcher->registerMethod('getTableLayout');

        // Item Price
        $Dispatcher->registerMethod('changeItemPrice');

        // remove Entry
        $Dispatcher->registerMethod('showDeleteBasketSelection');
        $Dispatcher->registerMethod('deleteDebtorSelection');


        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     * @param string $Identifier
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '', $Identifier = '')
    {

        return (new ModalReceiver($Header, new Close()))->setIdentifier('Modal'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverSearch($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('Block'.$Identifier);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverTableLayout($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BasketVerificationLayout');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverService()
    {

        return (new InlineReceiver())->setIdentifier('Service');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemPrice($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Price'.$Identifier);
    }

    /**
     * @param string $BasketId
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineReloadTable($BasketId = '', $Identifier = '')
    {
        $Pipeline = new Pipeline(false);
        // reload columns
        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverTableLayout(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getTableLayout'
        ));
        $Emitter->setPostPayload(array(
            'BasketId' => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);
        if($Identifier){
            $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        }
        return $Pipeline;
    }

    /**
     * @param string $BasketId
     *
     * @return Layout|string
     */
    public function getTableLayout($BasketId = '')
    {

        if($BasketId){
            return Basket::useFrontend()->getBasketVerificationRepaymentLayout($BasketId);
        }
        return 'Fehler';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemPrice($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return $tblBasketVerification->getPrice();
        }
        return '';
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddPersonModal($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showSearchModal'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $searchIdentifier
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineSearch($Identifier, $searchIdentifier = '', $BasketId = '')
    {

        $Receiver = self::receiverSearch('', $searchIdentifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showSearch'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'       => $Identifier,
            'BasketId'         => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineAddDebtorSelection($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverService();
//        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'addDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId' => $BasketId
        ));
        $Pipeline->setSuccessMessage('Gutschrift erfolgreich hinzugefügt!');
        $Pipeline->setLoadingMessage('Gutschrift erfolgreich hinzugefügt!');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineChangePrice($BasketVerificationId = '')
    {

        $Receiver = self::receiverService();
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeItemPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDebtorSelectionModal($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBasketSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDebtorSelection($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return string
     */
    public function showSearchModal($Identifier = '', $BasketId = '')
    {

        return new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                (new TextField('Search', '', 'Suche', new Search()))
                    ->ajaxPipelineOnKeyUp(self::pipelineSearch($Identifier, 'SearchPerson', $BasketId))
            , 8),
            new FormColumn(
                new TextField('Price', '', 'Betrag der Gutschrift', new Search())
            , 4),
            new FormColumn(
                new CheckBox('KeepOpen', 'Fenster bei Speicherung geöffnet lassen (Schnellere Bearbeiung)', 1)
            ),
            new FormColumn(
                self::receiverSearch('', 'SearchPerson')
            ),
        ))));
    }

    /**
     * @param string $Identifier
     * @param string $Search
     * @param string $BasketId
     *
     * @return string
     */
    public function showSearch($Identifier = '', $Search = '', $BasketId = '')
    {

        return self::loadPersonSearch($Identifier, $Search, $BasketId);
    }

    /**
     * @param string $Identifier
     * @param string $Search
     * @param string $BasketId
     *
     * @return Warning|string
     */
    private static function loadPersonSearch($Identifier = '',$Search = '', $BasketId = '')
    {

        if ($Search != '' && strlen($Search) > 2) {
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $tableData = array();
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tableData, $BasketId){
                    $item['Select'] = '';
                    $item['FirstName'] = $tblPerson->getFirstSecondName();
                    $item['LastName'] = $tblPerson->getLastName();
                    $item['Address'] = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : '';
                    $item['DebtorSelection'] = new Warning('Kein Beitragszahler (zur Bietragsart)', null, false, 2, 0);
                    $IsDebtorSelection = false;
                    if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
                        // Es kann nur eine Beitragsart hinterlegt sein
                        if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
                            $tblBasketItem = current($tblBasketItemList);
                            $tblItem = $tblBasketItem->getServiceTblItem();
                            if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson, $tblItem))){
                                foreach($tblDebtorSelectionList as $tblDebtorSelection){
                                    $IsDebtorSelection = true;
                                    $item['DebtorSelection'] = 'Zahlung vorhanden';
                                    $BankInfo = 'Keine Bankinforamtion gefunden!';
                                    if(($tblBankAccount = $tblDebtorSelection->getTblBankAccount())){
                                        $BankInfo = $tblBankAccount->getOwner().'<br/>'.$tblBankAccount->getBankName().'<br/>'.$tblBankAccount->getIBANFrontend();
                                    }
                                    if($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor()){
                                        $item['DebtorSelection'] = $tblPersonDebtor->getLastFirstName().' '.
                                            (new ToolTip(new InfoIcon(), htmlspecialchars($BankInfo)))->enableHtml();
                                    }
                                    $item['Select'] = new RadioBox('DebtorSelection', '&nbsp;', $tblDebtorSelection->getId());
                                    // Für jede Zahlungszuweisung einen eigenen Eintrag erzeugen
                                    array_push($tableData, $item);
                                }
                            }
                        }
                    }
                    // Eintrag wird nur angenommen, wenn keine Zahlungszuweisungen vorhanden sind.
                    // (Anzeige ohne Radiobox, damit der Nutzer sieht, was fehlt)
                    if(false === $IsDebtorSelection){
                        array_push($tableData, $item);
                    }
                });

                return new TableData(
                    $tableData,
                    null,
                    array(
                        'Select' => '',
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'Address' => 'Adresse',
                        'DebtorSelection' => 'Zahlungszuweisung',
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                ). (new Primary('Speichern', self::getEndpoint()))->ajaxPipelineOnClick(
                    self::pipelineAddDebtorSelection($Identifier, $BasketId)
                    );
            } else {
                $warning = new Warning('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } else {
            $warning = new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }
        return $warning;
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     * @param string $DebtorSelection
     * @param string $Price
     * @param string $KeepOpen
     *
     * @return string
     */
    public function addDebtorSelection($Identifier = '', $BasketId = '', $DebtorSelection = '', $Price = '', $KeepOpen = '')
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        $tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelection);
        $PriceCorrection = round(str_replace(',', '.', $Price), 2);
        $tblBasketVerification = Basket::useService()->createBasketVerification($tblBasket, $tblDebtorSelection, $PriceCorrection);
        if($tblBasketVerification){
            if($KeepOpen === ''){
                return self::pipelineReloadTable($tblBasket->getId(), $Identifier);
            } else {
                // schließt das Modal nicht
                $Identifier = false;
                return self::pipelineReloadTable($tblBasket->getId(), $Identifier);
            }


        }
        return '';
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $Price
     */
    public function changeItemPrice($BasketVerificationId = '', $Price = array())
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $Price = str_replace(',', '.', str_replace('-', '', $Price[$BasketVerificationId]));
            if($Price && is_numeric($Price) || '0' === $Price){
                Basket::useService()->changeBasketVerificationInPrice($tblBasketVerification, $Price);
            }
        }
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return string
     */
    public function showDeleteBasketSelection($Identifier = '', $BasketVerificationId = '')
    {

        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
        if($tblBasketVerification){

            $ItemName = '';
            $PersonCauser = '';
            $PersonDebtor = '';
            $Price = $tblBasketVerification->getPrice();
            $Quantity = $tblBasketVerification->getQuantity();
            $Summary = $tblBasketVerification->getSummaryPrice();
            if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                $ItemName = $tblItem->getName();
            }
            if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                $PersonCauser = $tblPersonCauser->getLastFirstName();
            }
            if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())){
                $PersonDebtor = $tblPersonDebtor->getLastFirstName();
            }
            //column width
            $left = 4;
            $right = 8;

            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsart: ', $left),
                new LayoutColumn(new Bold($ItemName), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsverursacher: ', $left),
                new LayoutColumn(new Bold($PersonCauser), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragszahler: ', $left),
                new LayoutColumn(new Bold($PersonDebtor), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Einzelpreis: ', $left),
                new LayoutColumn(new Bold($Price), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Anzahl: ', $left),
                new LayoutColumn(new Bold($Quantity), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Gesamtpreis: ', $left),
                new LayoutColumn(new Bold($Summary), $right),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Gutschrift wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDebtorSelection($Identifier,
                                    $BasketVerificationId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Zahlungszuweisung wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Danger|string
     */
    public function deleteDebtorSelection($Identifier = '', $BasketVerificationId = '')
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $tblBasket = $tblBasketVerification->getTblBasket();
            Basket::useService()->destroyBasketVerification($tblBasketVerification);
            if($tblBasket){
                return new Success('Abrechnung wurde erfolgreich entfernt').self::pipelineReloadTable($tblBasket->getId(),
                        $Identifier);
            }
        }
        return new Danger('Gutschrift konnte nicht entfernt werden');
    }
}
<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasket
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiBasket extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Panel content
        $Dispatcher->registerMethod('getBasketTable');
        // Basket
        $Dispatcher->registerMethod('showAddBasket');
        $Dispatcher->registerMethod('saveAddBasket');
        $Dispatcher->registerMethod('showEditBasket');
        $Dispatcher->registerMethod('saveEditBasket');
        $Dispatcher->registerMethod('showDeleteBasket');
        $Dispatcher->registerMethod('deleteBasket');
        $Dispatcher->registerMethod('setArchiveBasket');
        $Dispatcher->registerMethod('showDeleteBasketVerification');
        $Dispatcher->registerMethod('deleteBasketVerification');

        $Dispatcher->registerMethod('reloadPersonFilterSelect');

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
     *
     * @return BlockReceiver
     */
    public static function receiverContent($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockBasketTableContent');
    }

    public static function receiverFormSelect($Content = '')
    {

        return new BlockReceiver($Content);
    }

    /**
     * @param string $Content
     *
     * @return InlineReceiver
     */
    public static function receiverService($Content = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('ServiceBasket');
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param array  $Basket
     * @param array  $ErrorHelp
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddBasketModal($Identifier = '', $Type = '', $Basket = array(), $ErrorHelp = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Type'       => $Type,
            'Basket'     => $Basket,
            'ErrorHelp'  => $ErrorHelp
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $Type
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddBasket(string $Identifier = '', string $Type = '', $Basket = array(), $isLoad = 'true')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();

        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddBasket'
        ));
//        if(!empty($Basket)){
            $Emitter->setPostPayload(array(
                'Identifier' => $Identifier,
                'Type'       => $Type,
                'Basket'     => $Basket,
                'isLoad'     => $isLoad,
            ));
//        } else {
//            $Emitter->setPostPayload(array(
//                'Identifier' => $Identifier,
//                'Type'       => $Type,
//                'isLoad'     => $isLoad,
//            ));
//        }

        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $Type
     * @param int|string $BasketId
     * @param array      $Basket
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditBasketModal(
        $Identifier = '',
        $Type = '',
        $BasketId = '',
        $Basket = array()
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Type' => $Type,
            'BasketId'   => $BasketId,
            'Basket'     => $Basket
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $Type
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditBasket($Identifier = '', $Type = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Type'       => $Type,
            'BasketId'   => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteBasketModal($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteBasket($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|string $BasketId
     * @param bool       $IsArchive
     *
     * @return Pipeline
     */
    public static function pipelineBasketArchive($BasketId = '', $IsArchive = false)
    {

        $Receiver = self::receiverService();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'setArchiveBasket'
        ));
        $Emitter->setPostPayload(array(
            'BasketId'  => $BasketId,
            'IsArchive' => $IsArchive,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param bool $IsArchive
     *
     * @return Pipeline
     */
    public static function pipelineRefreshTable($IsArchive = false)
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverContent(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getBasketTable'
        ));
        $Emitter->setPostPayload(array(
            'IsArchive' => $IsArchive
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteBasketVerificationModal($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBasketVerification'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteBasketVerification($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteBasketVerification'
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
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '')
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverContent(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getBasketTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param int $YearId
     *
     * @return Pipeline
     */
    public static function pipelineLoadPersonFilterSelect(AbstractReceiver $Receiver)
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, ApiBasket::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiBasket::API_TARGET => 'reloadPersonFilterSelect'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Personenfilterung wird aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param bool $IsArchive
     *
     * @return string
     */
    public function getBasketTable($IsArchive = false)
    {

        return Basket::useFrontend()->getBasketTable($IsArchive);
    }

    /**
     * @param string     $Identifier
     * @param string     $Type
     * @param int|string $BasketId
     *
     * @return IFormInterface $Form
     */
    public function formBasket($Identifier = '', $Type = '', $BasketId = '')
    {

        // SelectBox content
        $YearList = Invoice::useService()->getYearList(1, 1);
        $MonthList = Invoice::useService()->getMonthList();
        $CreditorList = Creditor::useService()->getCreditorAll();

        $FormContentLeft[] = (new TextField('Basket[Name]', 'Name der Abrechnug', 'Name'))->setRequired();
        $FormContentLeft[] = new TextField('Basket[Description]', 'Beschreibung', 'Beschreibung');
        $FormContentLeft[] = (new SelectBox('Basket[Creditor]', 'Gläubiger', array('{{ Owner }} - {{ CreditorId }}' => $CreditorList)))->setRequired();
        $FormContentLeft[] = (new DatePicker('Basket[TargetTime]', '', 'Fälligkeitsdatum'))->setRequired();
        //Rechnungsdatum ist nur bei Datev Pflichtfeld
        $IsDatev = false;
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV))){
            $IsDatev = $tblSetting->getValue();
        }
        if($IsDatev){
            $FormContentLeft[] = (new DatePicker('Basket[BillTime]', '', 'Rechnungsdatum'))->setRequired();
        } else {
            $FormContentLeft[] = new DatePicker('Basket[BillTime]', '', 'Rechnungsdatum');
        }

        if(!isset($_POST['Basket']['Creditor'])
            && $CreditorList
            && count($CreditorList) == 1){
            $_POST['Basket']['Creditor'] = $CreditorList[0]->getId();
        }

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());


        if('' !== $BasketId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditBasket($Identifier, $Type, $BasketId));

            // zusatzinhalt nur bei Gutschriften
            $rightForm = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(''))));
            if($Type == TblBasketType::IDENT_GUTSCHRIFT){
                $rightForm = $this->getRightFormColumn($Type);
            }

            $Content = (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new Panel($Type, $FormContentLeft, Panel::PANEL_TYPE_INFO)
                    , 6),
                new FormColumn(
                    $rightForm
                    , 6),
                new FormColumn(
                    $SaveButton
                )
            )))))->disableSubmitAction();
        } else {
            // set Date to now
            $Now = new \DateTime();
            $Month = (int)$Now->format('m');
            if(!isset($_POST['Basket']['Year'])){
                $_POST['Basket']['Year'] = $Now->format('Y');
            }
            if(!isset($_POST['Basket']['Month'])){
                $_POST['Basket']['Month'] = $Month;
            }
            // Monatlich oder Jährlich gibt es nur Bei Abrechnungen oder Auszahlungen
            if($Type == TblBasketType::IDENT_ABRECHNUNG || $Type == TblBasketType::IDENT_AUSZAHLUNG){
                if(!isset($_POST['Basket']['DebtorPeriodType'])){
                    if($tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeByName('Monatlich')){
                        $_POST['Basket']['DebtorPeriodType'] = $tblDebtorPeriodType->getId();
                    } else {
                        $_POST['Basket']['DebtorPeriodType'] = '1';
                    }
                }
            }

            $FormContentLeft[] = (new SelectBox('Basket[Year]', 'Jahr', $YearList))->setRequired();
            $FormContentLeft[] = (new SelectBox('Basket[Month]', 'Monat', $MonthList, null, true, null))->setRequired();

            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddBasket($Identifier, $Type));
            $Content = (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new Panel($Type, $FormContentLeft, Panel::PANEL_TYPE_INFO)
                    , 6),
                new FormColumn(
                    $this->getRightFormColumn($Type)
                    , 6),
                new FormColumn(
                    $SaveButton
                )
            )))))->disableSubmitAction();
        }
        /* @var Form $Content */
        return $Content;
    }

    /**
     * @return array
     */
    private function getRightFormColumn($Type)
    {
        if($Type == TblBasketType::IDENT_GUTSCHRIFT){
            $Account = '(Standard) ';
            $ToAccount = '(Standard) ';
            if(($tblSettingAccount = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_ACCOUNT))){
                $Account .= $tblSettingAccount->getValue();
            }
            if(($tblSettingToAccount = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_TO_ACCOUNT))){
                $ToAccount .= $tblSettingToAccount->getValue();
            }

            return array(new Panel('Fibu'.new DangerText('*'), array(
                new TextField('Basket[FibuAccount]', $Account, 'Konto'),
                new TextField('Basket[FibuToAccount]', $ToAccount, 'Gegenkonto'),
            ), Panel::PANEL_TYPE_INFO));

        } else {
            $CheckboxList = '';
            if(($tblItemList = Item::useService()->getItemAll())){
                foreach($tblItemList as $tblItem) {
                    $CheckboxList .= new CheckBox('Basket[Item]['.$tblItem->getId().']', $tblItem->getName(),
                        $tblItem->getId());
                }
            }

            $YearList = array();
            // aktuelles & folgendes Schuljahr
            for($i = 0; $i <= 1; $i++){
                if(($YearListTmp = Term::useService()->getYearAllFutureYears($i))){
                    foreach($YearListTmp as $YearTmp){
                        $YearList[] = $YearTmp;
                    }
                }
            }

            $PeriodRadioBox = array();
            if(($tblDebtorPeriodTypeAll = Debtor::useService()->getDebtorPeriodTypeAll())){
                foreach($tblDebtorPeriodTypeAll as $tblDebtorPeriodType){
                    if($tblDebtorPeriodType->getName() == TblDebtorPeriodType::ATTR_YEAR){
                        $PeriodRadioBox[] = new RadioBox('Basket[DebtorPeriodType]', $tblDebtorPeriodType->getName().' (Schuljahr)', $tblDebtorPeriodType->getId().';SJ');
                        $PeriodRadioBox[] = new RadioBox('Basket[DebtorPeriodType]', $tblDebtorPeriodType->getName().' (Kalenderjahr)', $tblDebtorPeriodType->getId().';KJ');
                    } else {
                        $PeriodRadioBox[] = new RadioBox('Basket[DebtorPeriodType]', $tblDebtorPeriodType->getName(), $tblDebtorPeriodType->getId());
                    }
                }
            }

            $receiverPersonFilter = ApiBasket::receiverFormSelect(
                (new ApiBasket())->reloadPersonFilterSelect()
            );

            if(empty($YearList)){
                $YearList[] = new TblYear();
            }
            $tblTypeList = School::useService()->getConsumerSchoolTypeAll();
            return array(
                new Panel('Beitragsarten '.new DangerText('*'), $CheckboxList, Panel::PANEL_TYPE_INFO),
                new Panel('Erweiterte Personenfilterung', array(
                    (new SelectBox('Basket[SchoolYear]', 'Schuljahr', array('{{ Year }} {{ Description }}' => $YearList)))
                        ->ajaxPipelineOnChange(ApiBasket::pipelineLoadPersonFilterSelect($receiverPersonFilter)),
                    $receiverPersonFilter,
                    new SelectBox('Basket[SchoolType]', 'Schulart', array('{{ Name }}' => $tblTypeList))
                        )
                    , Panel::PANEL_TYPE_INFO
                ),
                new Panel('Zahlungszeitraum '.new DangerText('*'), $PeriodRadioBox, Panel::PANEL_TYPE_INFO),
            );
        }
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param string $BasketId
     * @param array  $Basket
     *
     * @return bool|Well
     */
    private function checkInputBasket(
        $Identifier = '',
        $Type = '',
        $BasketId = '',
        $Basket = array()
    ){

        $Error = false;
        $Warning = array();
        $form = $this->formBasket($Identifier, $Type, $BasketId);
        if(isset($Basket['Name']) && empty($Basket['Name'])){
            $form->setError('Basket[Name]', 'Bitte geben Sie einen Namen der Abrechnung an');
            $Error = true;
        } else {
            if(isset($Basket['Month']) && isset($Basket['Year'])){
                // Filtern doppelter Namen Mit Zeitangabe (Namen sind mit anderem Datum wiederverwendbar)
                if(($tblBasket = Basket::useService()->getBasketByName($Basket['Name'], $Basket['Month'],
                    $Basket['Year']))){
                    if($BasketId !== $tblBasket->getId()){
                        $form->setError('Basket[Name]',
                            'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung '.$Basket['Month'].'.'.$Basket['Year'].' an');
                        $Error = true;
                    }
                }
            } else {
                // Filtern doppelter Namen ohne Zeitangabe
                if($BasketId && ($tblBasketEdit = Basket::useService()->getBasketById($BasketId))){
                    $TargetMonth = $tblBasketEdit->getMonth();
                    $TargetYear = $tblBasketEdit->getYear();
                    if(($tblBasket = Basket::useService()->getBasketByName($Basket['Name'], $TargetMonth,
                        $TargetYear))){
                        if($BasketId !== $tblBasket->getId()){
                            $form->setError('Basket[Name]',
                                'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung an');
                            $Error = true;
                        }
                    }
                } else {
                    // fallback if error
                    if(Basket::useService()->getBasketByName($Basket['Name'])){
                        $form->setError('Basket[Name]',
                            'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung an');
                        $Error = true;
                    }
                }
            }
        }
        if(isset($Basket['TargetTime']) && empty($Basket['TargetTime'])){
            $form->setError('Basket[TargetTime]', 'Bitte geben Sie ein Fälligkeitsdatum an');
            $Error = true;
        }
        //Rechnungsdatum ist nur bei Datev Pflichtfeld
        $IsDatev = false;
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV))){
            $IsDatev = $tblSetting->getValue();
        }
        if($IsDatev && isset($Basket['BillTime']) && empty($Basket['BillTime'])){
            $form->setError('Basket[BillTime]', 'Bitte geben Sie ein Rechnungsdatum an');
            $Error = true;
        }
        if(isset($Basket['Creditor']) && empty($Basket['Creditor'])){
            $form->setError('Basket[Creditor]', 'Bitte geben Sie einen Gläubiger an');
            $Error = true;
        }
        // Abrechnung Auszahlung mit Checkboxen (mehrere)
        if($BasketId == '' && !isset($Basket['Item'])){
            $Warning[] = 'Es wird mindestens eine Beitragsart benötigt';
            $Error = true;
        }

        $WarningText = '';
        if(!empty($Warning)){
            $WarningText = new Warning(implode('<br/>', $Warning));
        }

        if($Error){
            return new Well($WarningText.$form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param array  $ErrorHelp
     *
     * @return string
     */
    public function showAddBasket($Identifier = '', $Type = '', $ErrorHelp = array())
    {

        if(!empty($ErrorHelp)){
            $ErrorHelp = new Warning(implode('<br/>', $ErrorHelp));
        } else {
            $ErrorHelp = '';
        }

        return $ErrorHelp.new Well($this->formBasket($Identifier, $Type));
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param array  $Basket
     * @param string $isLoad
     *
     * @return string
     */
    public function saveAddBasket($Identifier = '', $Type = '', $Basket = array(), $isLoad = 'true')
    {

        // Handle error's
        if($form = $this->checkInputBasket($Identifier, $Type, '', $Basket)){

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            $Global->POST['Basket']['Year'] = $Basket['Year'];
            $Global->POST['Basket']['Month'] = $Basket['Month'];
            $Global->POST['Basket']['TargetTime'] = $Basket['TargetTime'];
            $Global->POST['Basket']['BillTime'] = $Basket['BillTime'];
            $Global->POST['Basket']['Creditor'] = $Basket['Creditor'];
            $Global->POST['Basket']['DivisionCourse'] = (isset($Basket['DivisionCourse']) ? $Basket['DivisionCourse'] : '');
            $Global->POST['Basket']['SchoolType'] = $Basket['SchoolType'];
            $Global->POST['Basket']['DebtorPeriodType'] = $Basket['DebtorPeriodType'];
            $Global->POST['Basket']['SchoolYear'] = $Basket['SchoolYear'];
            if(isset($Basket['Item']) && is_array($Basket['Item'])){
                foreach($Basket['Item'] as $ItemId) {
                    $Global->POST['Basket']['Item'][$ItemId] = $ItemId;
                }
            }
            $Global->savePost();
            return $form;
        }

        if($isLoad == 'true'){
            return new Info('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
                .new Container((new ProgressBar(0, 100, 0, 10))
                    ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
            ).self::pipelineSaveAddBasket($Identifier, $Type, $Basket, 'false');
        }

        $tblBasketType = Basket::useService()->getBasketTypeByName($Type);
        if(!isset($Basket['DivisionCourse'])
            || !$Basket['DivisionCourse']
            || !($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Basket['DivisionCourse']))){
            $tblDivisionCourse = null;
        }
        if(!isset($Basket['SchoolType'])
            || !$Basket['SchoolType']
            || !($tblType = Type::useService()->getTypeById($Basket['SchoolType']))){
            $tblType = null;
        }
        $tblYear = null;
        if(isset($Basket['SchoolYear']) && $Basket['SchoolYear']){
            $tblYear = Term::useService()->getYearById($Basket['SchoolYear']);
        }

        $DebtorPeriodType = explode(';', $Basket['DebtorPeriodType']);
        if(!($tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeById($DebtorPeriodType[0]))){
            $tblDebtorPeriodType = null;
        }
        $PeriodExtended = '';
        if($tblDebtorPeriodType->getName() == TblDebtorPeriodType::ATTR_YEAR){
            $PeriodExtended = $DebtorPeriodType[1];
        }

        $tblBasket = Basket::useService()->createBasket($Basket['Name'], $Basket['Description'], $Basket['Year']
            , $Basket['Month'], $Basket['TargetTime'], $Basket['BillTime'], $tblBasketType, $Basket['Creditor'], $tblDivisionCourse, $tblType,
            $tblDebtorPeriodType);

        foreach($Basket['Item'] as $ItemId) {
            if(($tblItem = Item::useService()->getItemById($ItemId))){
                $tblItemList[] = $tblItem;
                Basket::useService()->createBasketItem($tblBasket, $tblItem);
            }
        }

        $ItemPriceFound = true;
        $MissingItemPriceList = array('Es existieren Preis-Varianten denen für das Fälligkeitsdatum '.$Basket['TargetTime']
            .' kein Preis hinterlegt ist.');
        $MissingItemPriceList[] = 'Bitte stellen Sie sicher, das alle Preisvarianten der Beitragsart gepflegt sind.';
        $MissingItemPriceList[] = '&nbsp;';
        $MissingItemPriceList[] = 'Beitragsart - Variante';
        $isCreate = false;
        $PersonMissing = array();
        if(!empty($tblItemList)){

            // Kontrolle, ob alle Varianten zum Fälligkeitsdatum ein gültigen Preis haben
            $TargetTime = new \DateTime($Basket['TargetTime']);
            /** @var TblItem $tblItemPriceControl */
            foreach($tblItemList as $tblItemPriceControl) {
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItemPriceControl))){
                    foreach($tblItemVariantList as $tblItemVariant){
                        if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))){
                            $IsCalculationTest = false;
                            foreach($tblItemCalculationList as $tblItemCalculation){
                                if($tblItemCalculation->getDateTo()
                                    && $tblItemCalculation->getDateFrom(true) <= $TargetTime
                                    && $tblItemCalculation->getDateTo(true) >= $TargetTime
                                    || !$tblItemCalculation->getDateTo()
                                    && $tblItemCalculation->getDateFrom(true) <= $TargetTime
                                ){
                                    $IsCalculationTest = true;
                                    break;
                                }
                            }
                            if(!$IsCalculationTest){
                                $MissingItemPriceList[] = $tblItemPriceControl->getName().' - '.$tblItemVariant->getName();
                                $ItemPriceFound = false;
                            }
                        }
                    }
                }
            }
            // ungültige Preise hindern die Erstellung einer Abrechnung
            if(!$ItemPriceFound){
                Basket::useService()->destroyBasket($tblBasket);
                return self::pipelineOpenAddBasketModal($Identifier, $Type, $Basket, $MissingItemPriceList);
            }

            /** @var TblItem $tblItem */
            foreach($tblItemList as $tblItem) {
                $VerificationResult = Basket::useService()->createBasketVerificationBulk($tblBasket, $tblItem, $tblDivisionCourse,
                    $tblType, $tblYear, $PeriodExtended);
                if($isCreate == false){
                    $isCreate = $VerificationResult['IsCreate'];
                }
                if(!empty($VerificationResult))
                foreach($VerificationResult as $PersonId => $ErrorMessageList){
                    if(is_numeric($PersonId) && $tblPerson = Person::useService()->getPersonById($PersonId)){
                        $PersonMissing[] = new Bold($tblPerson->getLastFirstName()).':<br/>'.implode('<br/>', $ErrorMessageList);
                    }
                }
            }
        }
        // Abrechnung nicht gefüllt
        if(!$isCreate){
            Basket::useService()->destroyBasket($tblBasket);

            $ErrorHelp[] = 'Abrechnung kann nicht erstellt werden. Mögliche Ursachen:';
            $ErrorHelp[] = '- Es wurden im Abrechnungsmonat bereits für alle ausgewählten Beitragsarten
                und alle zutreffenden Personen eine Rechnung erstellt';
            $ErrorHelp[] = '- Aktuelle Filterung lässt keine Personen zur Abrechnung zu';
            $ErrorHelp[] = '- Es stehen keine aktiven Zahlungszuweisungen für das Fälligkeitsdatum bereit.';

            if(!empty($PersonMissing)){
                $ErrorHelp[] = '&nbsp;';
                $ErrorHelp[] = 'Folgende Zahlungszuweisungen wurden herausgefiltert:';
                $ErrorHelp = array_merge($ErrorHelp, $PersonMissing);
            }

            return self::pipelineOpenAddBasketModal($Identifier, $Type, $Basket, $ErrorHelp);
        }

        if($tblBasket){
            if(empty($PersonMissing)){
                return new Success('Abrechnung erfolgreich angelegt').self::pipelineCloseModal($Identifier);
            } else {
                return new Success('Abrechnung erfolgreich angelegt').
                    new Warning('Folgende Zahlungszuweisungen wurden herrausgefiltert:<br/>'
                    .implode('<br/>', $PersonMissing))
                    .ApiBasket::pipelineRefreshTable();
            }
        } else {
            return new Danger('Abrechnung konnte nicht gengelegt werden');
        }
    }

    /**
     * @param string     $Identifier
     * @param string     $Type
     * @param int|string $BasketId
     * @param array      $Basket
     *
     * @return string
     */
    public function saveEditBasket(
        $Identifier = '',
        $Type = '',
        $BasketId = '',
        $Basket = array()
    ){

        // Handle error's
        if($form = $this->checkInputBasket($Identifier, $Type, $BasketId, $Basket)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            $Global->POST['Basket']['TargetTime'] = $Basket['TargetTime'];
            $Global->POST['Basket']['BillTime'] = $Basket['BillTime'];
            $Global->POST['Basket']['Creditor'] = $Basket['Creditor'];
            if($Type == TblBasketType::IDENT_GUTSCHRIFT){
                $Global->POST['Basket']['FibuAccount'] = $Basket['FibuAccount'];
                $Global->POST['Basket']['FibuToAccount'] = $Basket['FibuToAccount'];
            }
            $Global->savePost();
            return $form;
        }
        $FibuAccount = $FibuToAccount = '';
        if( $Type == TblBasketType::IDENT_GUTSCHRIFT){
            // Fibu Daten durch eingaben (wenn getätigt) ersetzen
            if(isset($Basket['FibuAccount']) && $Basket['FibuAccount']) {
                $FibuAccount = $Basket['FibuAccount'];
            }
            if(isset($Basket['FibuToAccount']) && $Basket['FibuToAccount']) {
                $FibuToAccount = $Basket['FibuToAccount'];
            }
        }

        $IsChange = false;
        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            $IsChange = Basket::useService()->changeBasket($tblBasket, $Basket['Name'], $Basket['Description']
                , $Basket['TargetTime'], $Basket['BillTime'], $Basket['Creditor'], $FibuAccount, $FibuToAccount);
        }

        return ($IsChange
            ? new Success('Abrechnung erfolgreich geändert').self::pipelineCloseModal($Identifier)
            : new Danger('Abrechnung konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param string     $Type
     * @param int|string $BasketId
     *
     * @return string
     */
    public function showEditBasket($Identifier = '', $Type = '', $BasketId = '')
    {

        if('' !== $BasketId && ($tblBasket = Basket::useService()->getBasketById($BasketId))){
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $tblBasket->getName();
            $Global->POST['Basket']['Description'] = $tblBasket->getDescription();
            $Global->POST['Basket']['Year'] = $tblBasket->getYear();
            $Global->POST['Basket']['Month'] = $tblBasket->getMonth();
            $Global->POST['Basket']['TargetTime'] = $tblBasket->getTargetTime();
            $Global->POST['Basket']['BillTime'] = $tblBasket->getBillTime();
            $Global->POST['Basket']['Creditor'] = ($tblBasket->getServiceTblCreditor() ? $tblBasket->getServiceTblCreditor()->getId() : '');
            if($Type == TblBasketType::IDENT_GUTSCHRIFT){
                $Global->POST['Basket']['FibuAccount'] = $tblBasket->getFibuAccount(false);
                $Global->POST['Basket']['FibuToAccount'] = $tblBasket->getFibuToAccount(false);
            }
            $Global->savePost();
        }

        return new Well($this->formBasket($Identifier, $Type, $BasketId));
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return string
     */
    public function showDeleteBasket($Identifier = '', $BasketId = '')
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        if($tblBasket){

            $BasketVericationCount = 0;
            if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))){
                $BasketVericationCount = count($tblBasketVerificationList);
            }
            $ItemList = array();
            if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
                foreach($tblBasketItemList as $tblBasketItem) {
                    if(($tblItem = $tblBasketItem->getServiceTblItem())){
                        $ItemList[] = $tblItem->getName();
                    }
                }
            }
            $ItemString = implode(', ', $ItemList);

            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Anzahl der zu Fakturierende Beiträge: ', 4),
                new LayoutColumn(new Bold($BasketVericationCount), 8),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('zu Fakturierende Beitragsarten: ', 4),
                new LayoutColumn(new Bold($ItemString), 8),
            ))));

            $LayoutColumnWarning = new LayoutColumn('');
            if($tblBasket->getIsDone()){
                $LayoutColumnWarning = new LayoutColumn(new Danger(
                    new Container(new Bold('Achtung! ').'Eventuelle Korrekturen für SEPA oder DATEV müssen externen übernommen werden.')
                    .new Container(new Bold('Das Löschen ist unwiderruflich!'))));
            }


            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Abrechnung '.new Bold($tblBasket->getName()).' wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        $LayoutColumnWarning,
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteBasket($Identifier, $BasketId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Abrechnung wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return string
     */
    public function deleteBasket($Identifier = '', $BasketId = '')
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            Basket::useService()->destroyBasket($tblBasket);

            return new Success('Abrechnung wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier);
        }
        return new Danger('Abrechnung konnte nicht entfernt werden');
    }

    /**
     * @param string $BasketId
     * @param bool   $IsArchive
     *
     * @return string
     */
    public function setArchiveBasket($BasketId = '', $IsArchive = false)
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            // Wert kommt als String an
            if('false' == $IsArchive){
                $IsArchiveOposite = true;
            } else {
                $IsArchiveOposite = false;
            }
            Basket::useService()->updateBasketArchive($tblBasket, $IsArchiveOposite);

            // Variable Archiv ist ein String, deswegen gleich das gegenteil vom ermittelten boolean
            return self::pipelineRefreshTable($IsArchive);
        }
        return '';
    }

    /**
     * @param array $Basket
     *
     * @return string
     */
    public function reloadPersonFilterSelect(array $Basket = array())
    {

        $tblYearList = array();
        if (isset($Basket['SchoolYear']) && 0 != $Basket['SchoolYear']) {
            if(($tblYear = Term::useService()->getYearById($Basket['SchoolYear']))){
                $tblYearList[] = $tblYear;
            }
        } else {
            if(($tblYearListTmp = Term::useService()->getYearByNow())){
                $tblYearList = $tblYearListTmp;
            }
        }
        $tblDivisionCourseList = array(0 => new TblDivisionCourse());
        if(!empty($tblYearList)){
            foreach($tblYearList as $tblYear){
                if(($tblDivisionCourseTempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, 'Klasse'))){
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseTempList);
                }
                if(($tblDivisionCourseTempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, 'Stammgruppe'))){
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseTempList);
                }
                // Filterung dazu würde ganz anders funktionieren, erstmal deaktiviert.
//                if(($tblDivisionCourseTempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, 'BASIC_COURSE'))){
//                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseTempList);
//                }
//                if(($tblDivisionCourseTempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, 'ADVANCED_COURSE'))){
//                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseTempList);
//                }
            }
        }
        return new SelectBox('Basket[DivisionCourse]', 'Klasse / Stammgruppe / Kurs', array('{{ DisplayName }}' => $tblDivisionCourseList));
    }

    public function showDeleteBasketVerification($Identifier, $BasketVerificationId)
    {

        if(!($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return new Warning('Zahlungszuweisung wurde nicht gefunden');
        }
        if(!($tblBasket = $tblBasketVerification->getTblBasket())){
            return new Warning('Abrechnung wurde nicht gefunden');
        }

        $LayoutColumnWarning = new LayoutColumn(new Danger(
            new Container(new Bold('Achtung! ').'Eventuelle Korrekturen für SEPA oder DATEV müssen externen übernommen werden.')
            .new Container(new Bold('Das Löschen ist unwiderruflich!'))));

        $Causer = $tblBasketVerification->getServiceTblPersonCauser()->getLastFirstName();
        $Item = $tblBasketVerification->getServiceTblItem()->getName();
        $Price = $tblBasketVerification->getSummaryPrice();

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('Soll die Rechnung '.new Bold($Causer.' "'.$Item.'": '.$Price).' wirklich entfernt werden?', '', Panel::PANEL_TYPE_DANGER)
            ),
            $LayoutColumnWarning,
            new LayoutColumn(
                (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                    ->ajaxPipelineOnClick(self::pipelineDeleteBasketVerification($Identifier, $BasketVerificationId))
                .new Close('Nein', new Disable())
            )
        ))));
    }

    /**
     * @param $Identifier
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function deleteBasketVerification($Identifier, $BasketVerificationId)
    {

        if(!($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return new Warning('Zahlungszuweisung wurde nicht gefunden');
        }
        if(!($tblBasket = $tblBasketVerification->getTblBasket())){
            return new Warning('Abrechnung wurde nicht gefunden');
        }
        $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
        $tblItem = $tblBasketVerification->getServiceTblItem();
        $Year = $tblBasket->getYear();
        $Month = $tblBasket->getMonth();
        $tblInvoice = Invoice::useService()
            ->getInvoiceObjectByPersonCauserAndItemAndYearAndMonth($tblBasket, $tblPersonCauser, $tblItem, $Year, $Month);
        if($tblInvoice){
            Invoice::useService()->destroyInvoiceByInvoiceAndItem($tblInvoice, $tblItem);
        }
        Basket::useService()->destroyBasketVerification($tblBasketVerification);
        // table reload not as easy as expected -> reload whole page
        if(Basket::useService()->getBasketVerificationAllByBasket($tblBasket)){
            return new Success('Rechnung entfernt')
                .new Redirect('/Billing/Bookkeeping/Basket/View', Redirect::TIMEOUT_SUCCESS, array('BasketId' => $tblBasket->getId()));
        }
        // else Basket is empty -> remove Basket
        Basket::useService()->destroyBasket($tblBasket);
        return new Success('Rechnung inclusive Abrechnung entfernt').new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_SUCCESS);
    }
}
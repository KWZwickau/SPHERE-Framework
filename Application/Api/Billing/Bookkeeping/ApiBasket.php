<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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

    /**
     * @param string $Identifier
     * @param array  $Basket
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddBasketModal($Identifier = '', $Basket = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Basket'     => $Basket
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddBasket($Identifier = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();

        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     * @param array      $Basket
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditBasketModal(
        $Identifier = '',
        $BasketId = '',
        $Basket = array()
    ) {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
            'Basket'     => $Basket
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
    public static function pipelineSaveEditBasket($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
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
     * @return string
     */
    public function getBasketTable()
    {

        //ToDO
        return Basket::useFrontend()->getBasketTable();
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return IFormInterface $Form
     */
    public function formBasket($Identifier = '', $BasketId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if ('' !== $BasketId) {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditBasket($Identifier,
                $BasketId));
            $FormRowArray = array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Basket[Name]', 'Name der Abrechnug', 'Name'))->setRequired()
                        , 6),
                    new FormColumn(
                        new TextField('Basket[Description]', 'Beschreibung', 'Beschreibung')
                        , 6),
                ))
            );
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddBasket($Identifier));
            $CheckboxList = array();
            if (($tblItemList = Item::useService()->getItemAll())) {
                foreach ($tblItemList as $tblItem) {
                    $CheckboxList[] = new CheckBox('Basket[Item]['.$tblItem->getId().']', $tblItem->getName(),
                        $tblItem->getId());
                }
            }
            $FormRowArray = array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Basket[Name]', 'Name der Abrechnug', 'Name'))->setRequired()
                        , 6),
                    new FormColumn(
                        new TextField('Basket[Description]', 'Beschreibung', 'Beschreibung')
                        , 6),
                )),
                new FormRow(
                    new FormColumn(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Bold('Beitragsarten:')
                        ))))
                    )
                ),
                new FormRow(
                    new FormColumn(
                        $CheckboxList
                    )
                ),
                new FormRow(
                    new FormColumn(
                        $SaveButton
                    )
                )
            );
        }

        //ToDO Form
        return (new Form(
            new FormGroup($FormRowArray)
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     * @param array  $Basket
     *
     * @return bool|Well
     */
    private function checkInputBasket(
        $Identifier = '',
        $BasketId = '',
        $Basket = array()
    ) {

        $Error = false;
        $Warning = '';
        $form = $this->formBasket($Identifier, $BasketId);
        if (isset($Basket['Name']) && empty($Basket['Name'])) {
            $form->setError('Basket[Name]', 'Bitte geben Sie einen Namen der Abrechnung an');
            $Error = true;
        } else {
            if (($tblBasket = Basket::useService()->getBasketByName($Basket['Name']))) {
                if ($BasketId !== $tblBasket->getId()) {
                    $form->setError('Basket[Name]',
                        'Bitte geben sie eine noch nicht vergebenen Name für die Abrechnung an');
                    $Error = true;
                }
            }
        }
        if (!isset($Basket['Item'])) {
            $Warning = new Warning('Es wird mindestens eine Beitragsart benötigt');
            $Error = true;
        }


        if ($Error) {
            return new Well($Warning.$form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     *
     * @return string
     */
    public function showAddBasket($Identifier = '')
    {

        return new Well($this->formBasket($Identifier));
    }

    /**
     * @param string $Identifier
     * @param array  $Basket
     *
     * @return string
     */
    public function saveAddBasket($Identifier = '', $Basket = array())
    {

        // Handle error's
        if ($form = $this->checkInputBasket($Identifier, '', $Basket)) {

            // display Errors on form
            $Global = $this->getGlobal();
            //ToDO Erweitern des POST's
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            if (isset($Basket['Description']) && !empty($Basket['Description'])) {
                foreach ($Basket['Item'] as $ItemId) {
                    $Global->POST['Basket']['Item'][$ItemId] = $ItemId;
                }
            }
            $Global->savePost();
            return $form;
        }

        $tblBasket = Basket::useService()->createBasket($Basket['Name'], $Basket['Description']);
        $tblItemList = array();
        foreach ($Basket['Item'] as $ItemId) {
            if (($tblItem = Item::useService()->getItemById($ItemId))) {
                $tblItemList[] = $tblItem;
                $tblBasketItemList[] = Basket::useService()->createBasketItem($tblBasket, $tblItem);
            }
        }
        if (!empty($tblItemList)) {
            /** @var TblItem $tblItem */
            foreach ($tblItemList as $tblItem) {
                Basket::useService()->createBasketPersonBulk($tblBasket, $tblItem);
//                Basket::useService()->createBasketVerificationBulk($tblBasket, $tblItem);
            }
        }

        if ($tblBasket) {
            return new Success('Abrechnung erfolgreich angelegt').self::pipelineCloseModal($Identifier);
        } else {
            return new Danger('Abrechnung konnte nicht gengelegt werden');
        }
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     * @param array      $Basket
     *
     * @return string
     */
    public function saveEditBasket(
        $Identifier = '',
        $BasketId = '',
        $Basket = array()
    ) {

        // Handle error's
        if ($form = $this->checkInputBasket($Identifier, $BasketId, $Basket)) {
            // display Errors on form
            $Global = $this->getGlobal();
            //ToDO Erweitern des POST's (Item's)
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            $Global->savePost();
            return $form;
        }

        $IsChange = false;
        if (($tblBasket = Basket::useService()->getBasketById($BasketId))) {
            $IsChange = Basket::useService()->changeBasket($tblBasket, $Basket['Name'], $Basket['Description']);
        }

        return ($IsChange
            ? new Success('Abrechnung erfolgreich geändert').self::pipelineCloseModal($Identifier)
            : new Danger('Abrechnung konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return string
     */
    public function showEditBasket($Identifier = '', $BasketId = '')
    {

        if ('' !== $BasketId && ($tblBasket = Basket::useService()->getBasketById($BasketId))) {
            $Global = $this->getGlobal();
            // ToDO
            $Global->POST['Basket']['Name'] = $tblBasket->getName();
            $Global->POST['Basket']['Description'] = $tblBasket->getDescription();
            $Global->savePost();
        }

        return new Well(self::formBasket($Identifier, $BasketId));
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
        if ($tblBasket) {
            // ToDO
//            $PersonString = 'Person nicht gefunden!';
//            if(($tblPerson = $tblBasket->getServiceTblPerson())) {
//                $PersonString = $tblPerson->getFullName();
//            }
//            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
//                new LayoutColumn('Person: ', 2),
//                new LayoutColumn(new Bold($PersonString), 10),
//            ))));
//            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
//                new LayoutColumn('Abrechnung: ', 2),
//                new LayoutColumn(new Bold($tblBasket->getBasket()), 10),
//            ))));
            $Content[] = 'leer';

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Abrechnung wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
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

        if (($tblBasket = Basket::useService()->getBasketById($BasketId))) {
            Basket::useService()->destroyBasket($tblBasket);

            return new Success('Abrechnung wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier);
        }
        return new Danger('Abrechnung konnte nicht entfernt werden');
    }
}
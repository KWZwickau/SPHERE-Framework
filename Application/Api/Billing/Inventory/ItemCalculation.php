<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ItemCalculation extends Extension
{

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @return Pipeline
     */
    public static function pipelineOpenAddCalculationModal($Identifier, $VariantId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId' => $VariantId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddCalculation($Identifier, $VariantId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveAddCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId' => $VariantId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditCalculationModal($Identifier, $VariantId, $CalculationId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showEditCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId' => $VariantId,
            'CalculationId' => $CalculationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditCalculation($Identifier, $VariantId, $CalculationId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveEditCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId'     => $VariantId,
            'CalculationId'  => $CalculationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteCalculationModal($Identifier = '', $CalculationId = '')
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showDeleteCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CalculationId'     => $CalculationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteCalculation($Identifier = '', $CalculationId = '')
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'deleteCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'CalculationId'     => $CalculationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return Form
     */
    public function formCalculation($Identifier, $VariantId, $CalculationId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', ApiItem::getEndpoint(), new Save());
        if('' !== $CalculationId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditCalculation($Identifier, $VariantId, $CalculationId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddCalculation($Identifier, $VariantId));
        }

        return (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Calculation[Value]', '0,00', 'Preis'))->setRequired()
                        , 4),
                    new FormColumn(
                        (new DatePicker('Calculation[DateFrom]', 'z.B.(01.01.2019)', 'Gültig ab', new Clock()))->setRequired()
                        , 4),
                    new FormColumn(
                        new DatePicker('Calculation[DateTo]', 'z.B.(01.01.2020)', 'Gültig bis', new Clock())
                        , 4),
                    new FormColumn(
                        $SaveButton
                    )
                ))
            )
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $VariantId
     * @param string $CalculationId
     * @param array $Calculation
     *
     * @return false|string|Form
     */
    private function checkInputCalculation($Identifier, $VariantId, $CalculationId, $Calculation = array())
    {
        $Error = false;
        $form = $this->formCalculation($Identifier, $VariantId, $CalculationId);

        $Warning = '';
        if(!($tblItemVariant = Item::useService()->getItemVariantById($VariantId))){
            $Warning = new Danger('Beitrags-Variante ist nicht mehr vorhanden!');
            $Error = true;
        } else {
            if (isset($Calculation['Value']) && empty($Calculation['Value'])) {
                $form->setError('Calculation[Value]', 'Bitte geben Sie einen Preis an');
                $Error = true;
            }
            if (isset($Calculation['DateFrom']) && empty($Calculation['DateFrom'])) {
                $form->setError('Calculation[DateFrom]', 'Bitte geben Sie einen Begin der Gültigkeit an');
                $Error = true;
            }
        }

        if ($Error) {
            if($Warning){
                return $Warning.$form;
            }
            return $form;
        }

        return $Error;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @return string
     */
    public function showAddCalculation($Identifier, $VariantId)
    {

        return self::formCalculation($Identifier, $VariantId);
    }

    /**
     * @param $Identifier
     * @param $VariantId
     * @param array $Calculation
     *
     * @return string
     */
    public function saveAddCalculation($Identifier, $VariantId, $Calculation = array())
    {

        // Handle error's
        if ($form = $this->checkInputCalculation($Identifier, $VariantId, '', $Calculation)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Calculation']['Value'] = $Calculation['Value'];
            $Global->POST['Calculation']['DateFrom'] = $Calculation['DateFrom'];
            $Global->POST['Calculation']['DateTo'] = $Calculation['DateTo'];
            $Global->savePost();
            return $form;
        }

        $tblCalculation = false;
        if(($tblItemVariant = Item::useService()->getItemVariantById($VariantId))){
            $tblCalculation = Item::useService()->createItemCalculation($tblItemVariant, $Calculation['Value'], $Calculation['DateFrom'], $Calculation['DateTo']);
        }

        return ($tblCalculation
            ? new Success('Preis erfolgreich angelegt'). ApiItem::pipelineCloseModal($Identifier)
            : new Danger('Preis konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return string
     */
    public function showEditCalculation($Identifier, $VariantId, $CalculationId)
    {

        if('' !== $CalculationId && ($tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId))){
            $Global = $this->getGlobal();
            $Global->POST['Calculation']['Value'] = $tblItemCalculation->getValue(true);
            $Global->POST['Calculation']['DateFrom'] = $tblItemCalculation->getDateFrom();
            $Global->POST['Calculation']['DateTo'] = $tblItemCalculation->getDateTo();
            $Global->savePost();
        }

        return self::formCalculation($Identifier, $VariantId, $CalculationId);
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     * @param array      $Calculation
     *
     * @return string
     */
    public function saveEditCalculation($Identifier, $VariantId,$CalculationId , $Calculation = array())
    {

        // Handle error's
        if ($form = $this->checkInputCalculation($Identifier, $VariantId, $CalculationId, $Calculation)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Calculation']['Value'] = $Calculation['Value'];
            $Global->POST['Calculation']['DateFrom'] = $Calculation['DateFrom'];
            $Global->POST['Calculation']['DateTo'] = $Calculation['DateTo'];
            $Global->savePost();
            return $form;
        }

        $Success = false;
        if(($tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId))){
            if((Item::useService()->changeItemCalculation($tblItemCalculation, $Calculation['Value']
                , $Calculation['DateFrom'], $Calculation['DateTo']))){
                $Success = true;
            }
        }

        return ($Success
            ? new Success('Preis erfolgreich angelegt') . ApiItem::pipelineCloseModal($Identifier)
            : new Danger('Preis konnte nicht gengelegt werden'));
    }

    /**
     * @param string $Identifier
     * @param string $CalculationId
     *
     * @return string
     */
    public function showDeleteCalculation($Identifier = '', $CalculationId = '')
    {

        $tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId);
        if($tblItemCalculation){
            $VariantName = '';
            $ItemName = '';
            if(($tblItemVariant = $tblItemCalculation->getTblItemVariant())){
                $VariantName = $tblItemVariant->getName();
                if(($tblItem = $tblItemVariant->getTblItem())){
                    $ItemName = new Bold($tblItem->getName());
                }
            }
            $Content[] ='Preis: '.$tblItemCalculation->getPriceString();
            $Content[] ='Zeitraum: '.$tblItemCalculation->getDateFrom().' - '.$tblItemCalculation->getDateTo();

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Preis von Beitrags-Variante '.new Bold($VariantName).' der Beitragsart '.$ItemName.' wirklich entfernen?'
                                , new Listing($Content), Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', ApiItem::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteCalculation($Identifier, $CalculationId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Preis wurde nicht gefunden');
        }
    }

    /**
     * @param string     $Identifier
     * @param int|string $CalculationId
     * @return string
     */
    public function deleteCalculation($Identifier = '', $CalculationId = '')
    {

        if(($tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId))){
            Item::useService()->removeItemCalculation($tblItemCalculation);
            return new Success('Preis wurde erfolgreich entfernt'). ApiItem::pipelineCloseModal($Identifier);
        }
        return new Danger('Preis konnte nicht entfernt werden');
    }
}
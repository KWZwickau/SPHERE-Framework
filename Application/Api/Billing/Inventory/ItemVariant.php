<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
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

/**
 * Class ItemVariant
 * @package SPHERE\Application\Api\Billing\Inventory
 *
 * ApiItem -> ItemVariant -> ItemCalculation
 */
class ItemVariant extends ItemCalculation
{

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddVariantModal($Identifier, $ItemId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddVariant($Identifier, $ItemId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveAddVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param int|string $VariantId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditVariantModal($Identifier, $ItemId, $VariantId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showEditVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
            'VariantId'  => $VariantId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param int|string $VariantId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditVariant($Identifier, $ItemId, $VariantId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveEditVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
            'VariantId'  => $VariantId
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
    public static function pipelineOpenDeleteVariantModal($Identifier = '', $VariantId = '')
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showDeleteVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId'  => $VariantId,
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
    public static function pipelineDeleteVariant($Identifier = '', $VariantId = '')
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'deleteVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId'  => $VariantId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param int|string $VariantId
     *
     * @return Form
     */
    public function formVariant($Identifier, $ItemId, $VariantId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', ApiItem::getEndpoint(), new Save());
        if('' !== $VariantId) {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditVariant($Identifier, $ItemId, $VariantId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddVariant($Identifier, $ItemId));
        }

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Variant[Name]', 'Beitrags-Variante', 'Beitrags-Variante'))->setRequired()
                        , 6),
                    new FormColumn(
                        new TextArea('Variant[Description]', 'Beschreibung', 'Beschreibung')
                        , 6),
                )),
            )),
            ('' === $VariantId
                ?
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            (new TextField('Calculation[Value]', '0,00', 'Preis'))->setRequired()
                            , 4),
                        new FormColumn(
                            (new DatePicker('Calculation[DateFrom]', 'z.B.(01.01.2019)', 'Gültig ab',
                                new Clock()))->setRequired()
                            , 4),
                        new FormColumn(
                            new DatePicker('Calculation[DateTo]', 'z.B.(01.01.2020)', 'Gültig bis', new Clock())
                            , 4),
                    )),
                    new FormRow(
                        new FormColumn(
                            $SaveButton
                        )
                    )
                ))
                : new FormGroup(new FormRow(
                    new FormColumn(
                        $SaveButton
                    )
                ))
            )
        )))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $ItemId
     * @param string $VariantId
     * @param array  $Variant
     * @param array  $Calculation
     *
     * @return false|string|Form
     */
    private function checkInputVariant($Identifier, $ItemId, $VariantId, $Variant = array(), $Calculation = array())
    {
        $Error = false;
        $form = $this->formVariant($Identifier, $ItemId, $VariantId);

        $Warning = '';
        if(!($tblItem = Item::useService()->getItemById($ItemId))) {
            $Warning = new Danger('Beitragsart ist nicht mehr vorhanden!');
            $Error = true;
        } else {
            if(isset($Variant['Name']) && empty($Variant['Name'])) {
                $form->setError('Variant[Name]', 'Bitte geben Sie den Namen der Bezahl-Variante an');
                $Error = true;
                // disable save for duplicated names
            } elseif(isset($Variant['Name']) && ($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))) {
                // look vor same Variant name in Item range
                foreach ($tblItemVariantList as $tblItemVariant) {
                    // ignore own Name
                    if($tblItemVariant->getName() == $Variant['Name'] && $tblItemVariant->getId() != $VariantId) {
                        $form->setError('Variant[Name]', 'Der Name der Variante exisitiert bereits');
                        $Error = true;
                    }
                }
            }
            if(!$VariantId) {
                if(isset($Calculation['Value']) && empty($Calculation['Value'])) {
                    $form->setError('Calculation[Value]', 'Bitte geben Sie einen Preis an');
                    $Error = true;
                }
                if(isset($Calculation['DateFrom']) && empty($Calculation['DateFrom'])) {
                    $form->setError('Calculation[DateFrom]', 'Bitte geben Sie einen Beginn der Gültigkeit an');
                    $Error = true;
                }
            }
        }

        if($Error) {
            if($Warning) {
                return $Warning . new Well($form);
            }
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return string
     */
    public function showAddVariant($Identifier, $ItemId)
    {

        return new Well(self::formVariant($Identifier, $ItemId));
    }

    /**
     * @param       $Identifier
     * @param       $ItemId
     * @param array $Variant
     * @param array $Calculation
     *
     * @return string
     */
    public function saveAddVariant($Identifier, $ItemId, $Variant = array(), $Calculation = array())
    {

        // Handle error's
        if($form = $this->checkInputVariant($Identifier, $ItemId, '', $Variant, $Calculation)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Variant']['Name'] = $Variant['Name'];
            $Global->POST['Variant']['Description'] = $Variant['Description'];
            $Global->POST['Calculation']['Value'] = $Calculation['Value'];
            $Global->POST['Calculation']['DateFrom'] = $Calculation['DateFrom'];
            $Global->POST['Calculation']['DateTo'] = $Calculation['DateTo'];
            $Global->savePost();
            return $form;
        }

        $tblVariant = false;
        if(($tblItem = Item::useService()->getItemById($ItemId))) {
            //ignore create if already exist
            if(!(Item::useService()->getItemVariantByItemAndName($tblItem, $Variant['Name']))) {
                $tblVariant = Item::useService()->createItemVariant($tblItem, $Variant['Name'],
                    $Variant['Description']);
                Item::useService()->createItemCalculation($tblVariant, $Calculation['Value'], $Calculation['DateFrom'],
                    $Calculation['DateTo']);
            }
        }

        return ($tblVariant
            ? new Success('Beitrags-Variante erfolgreich angelegt') . ApiItem::pipelineCloseModal($Identifier)
            : new Danger('Beitrags-Variante konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param int|string $VariantId
     *
     * @return string
     */
    public function showEditVariant($Identifier, $ItemId, $VariantId)
    {

        if('' !== $VariantId && ($tblItemVariant = Item::useService()->getItemVariantById($VariantId))) {
            $Global = $this->getGlobal();
            $Global->POST['Variant']['Name'] = $tblItemVariant->getName();
            $Global->POST['Variant']['Description'] = $tblItemVariant->getDescription(false);
            $Global->savePost();
        }

        return new Well(self::formVariant($Identifier, $ItemId, $VariantId));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param int|string $VariantId
     * @param array      $Variant
     *
     * @return string
     */
    public function saveEditVariant($Identifier, $ItemId, $VariantId, $Variant = array())
    {

        // Handle error's
        if($form = $this->checkInputVariant($Identifier, $ItemId, $VariantId, $Variant)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Variant']['Name'] = $Variant['Name'];
            $Global->POST['Variant']['Description'] = $Variant['Description'];
            $Global->savePost();
            return $form;
        }

        $Success = false;
        if(($tblItemVariant = Item::useService()->getItemVariantById($VariantId))) {
            if((Item::useService()->changeItemVariant($tblItemVariant, $Variant['Name'], $Variant['Description']))) {
                $Success = true;
            }
        }

        return ($Success
            ? new Success('Beitrags-Variante erfolgreich angelegt') . ApiItem::pipelineCloseModal($Identifier)
            : new Danger('Beitrags-Variante konnte nicht gengelegt werden'));
    }

    /**
     * @param string $Identifier
     * @param string $VariantId
     *
     * @return string
     */
    public function showDeleteVariant($Identifier = '', $VariantId = '')
    {

        $tblItemVariant = Item::useService()->getItemVariantById($VariantId);
        if($tblItemVariant) {
            $ItemName = '';
            if(($tblItem = $tblItemVariant->getTblItem())) {
                $ItemName = new Bold($tblItem->getName());
            }
            $Content[] = 'Beschreibung: ' . $tblItemVariant->getDescription();

            if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))) {
                foreach ($tblItemCalculationList as $tblItemCalculation) {
                    $Content[] = 'Zeitraum: ' . $tblItemCalculation->getDateFrom() . ' - ' . $tblItemCalculation->getDateTo()
                        . ' Preis: ' . $tblItemCalculation->getPriceString();
                }
            }

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Beitrags-Variante ' . new Bold($tblItemVariant->getName()) . ' der Beitragsart ' . $ItemName . ' wirklich entfernt werden?'
                                , new Listing($Content), Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', ApiItem::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteVariant($Identifier, $VariantId))
                            . new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Beitrags-Variante wurde nicht gefunden');
        }
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     *
     * @return string
     */
    public function deleteVariant($Identifier = '', $VariantId = '')
    {

        if(($tblItemVariant = Item::useService()->getItemVariantById($VariantId))) {
            Item::useService()->removeItemVariant($tblItemVariant);
            return new Success('Beitrags-Variante wurde erfolgreich entfernt') . ApiItem::pipelineCloseModal($Identifier);
        }
        return new Danger('Beitrags-Variante konnte nicht entfernt werden');
    }
}
<?php
namespace SPHERE\Application\Billing\Inventory\Setting;

use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use \SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Setting
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function FrontendSetting()
    {

        $Stage = new Stage('Einstellungen', 'für die Fakturierung');

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        ApiSetting::receiverPersonGroup($this->displayPersonGroup())
                    )
                ),
                new LayoutRow(
                    new LayoutColumn(
                        ApiSetting::receiverSetting($this->displaySetting())
                    )
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @return Layout|string
     */
    public function displayPersonGroup()
    {
        if(($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())){
            $tblGroupList = array();
            foreach($tblSettingGroupPersonList as $tblSettingGroupPerson) {
                $tblGroupList[] = $tblSettingGroupPerson->getServiceTblGroupPerson();
            }
            $NameListLeft = array();
            $NameListRight = array();
            /** @var TblGroup $tblGroup */
            $tblGroupList = $this->getSorter($tblGroupList)->sortObjectBy('Name');
            foreach($tblGroupList as $tblGroup) {
                if($tblGroup->getMetaTable()){
                    $NameListLeft[] = $tblGroup->getName();
                } else {
                    $NameListRight[] = $tblGroup->getName();
                }
            }
            return new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('Personengruppen, die für Beitragsarten zur Auswahl stehen: '
                                ,(new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineShowFormPersonGroup()))
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(new Well(new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Title('Standard Personengruppen')
                                    , 6),
                                new LayoutColumn(
                                    new Title('Individuelle Personengruppen')
                                    , 6),
                                new LayoutColumn(
                                    new Listing($NameListLeft)
                                    , 6),
                                new LayoutColumn(
                                    new Listing($NameListRight)
                                    , 6),
                            ))
                        ))))
                    )
                ))
            );
        }
        return 'Personengruppen konnten nicht geladen werden.';
    }

    /**
     * @return Danger|string
     */
    public function formPersonGroup()
    {
        if(($tblSettingGroupList = Setting::useService()->getSettingGroupPersonAll())){
            $Global = $this->getGlobal();
            foreach($tblSettingGroupList as $tblSettingGroup) {
                if(($tblGroup = $tblSettingGroup->getServiceTblGroupPerson())){
                    $Global->POST['PersonGroup'][$tblGroup->getId()] = $tblGroup->getId();
                }
            }
            $Global->savePost();
        }
        $LeftList = $RightList = array();
        if(($tblGroupAll = Group::useService()->getGroupAll())){
            foreach($tblGroupAll as &$tblGroup) {
                if($tblGroup->getMetaTable() === 'COMMON'){
                    $tblGroup = false;
                }
            }
            $tblGroupAll = array_filter($tblGroupAll);

            $tblGroupAll = $this->getSorter($tblGroupAll)->sortObjectBy('Name');
            // sort left Standard, right Individual
            array_walk($tblGroupAll, function(TblGroup $tblGroup) use (&$LeftList, &$RightList){
                if($tblGroup->getMetaTable()){
                    $LeftList[] = new CheckBox('PersonGroup['.$tblGroup->getId().']', $tblGroup->getName(),
                        $tblGroup->getId());
                } else {
                    $RightList[] = new CheckBox('PersonGroup['.$tblGroup->getId().']', $tblGroup->getName(),
                        $tblGroup->getId());
                }
            });

            return new Title('Personengruppen, die für Beitragsarten zur Auswahl stehen:')
                .new Well((new Form(
                    new FormGroup(
                        new FormRow(array(
                            new FormColumn(new FormTitle('Standard Personengruppen'), 6),
                            new FormColumn(new FormTitle('Individuelle Personengruppen'), 6),
                            new FormColumn($LeftList, 6),
                            new FormColumn($RightList, 6),
                            new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                (new Primary('Speichern', ApiSetting::getEndpoint(), new Save()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineSavePersonGroup())
                                .(new Primary('Abbrechen', ApiSetting::getEndpoint(), new Disable()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineShowPersonGroup())
                            )))))
                        ))
                    )
                ))->disableSubmitAction());
        } else {
            return new Danger('Es stehen keine Gruppen zur Verfügung!');
        }
    }

    /**
     * @return Layout
     */
    public function displaySetting()
    {

        $tblSettingList = Setting::useService()->getSettingAll();

        $Listing = array();
        foreach($tblSettingList as &$tblSetting){
            switch($tblSetting->getIdentifier()){
                case TblSetting::IDENT_DEBTOR_NUMBER_COUNT:
                    $Listing[] = '&nbsp;Länge der Debitoren-Nr.: &nbsp;'
                        .new Bold(($tblSetting->getValue()
                        ? new SuccessText($tblSetting->getValue())
                        : new DangerText('Nicht hinterlegt!')));
                break;
                case TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED:
                    $Listing[] = '&nbsp;Debitoren-Nr. ist eine Pflichtangabe: &nbsp;'
                        .new Bold(($tblSetting->getValue()
                        ? new SuccessText(new Check())
                        : new DangerText(new Unchecked())));
                break;
                case TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED:
                    $Listing[] ='&nbsp;Bankverbindung für SEPA-Lastschrift ist eine Pflichtangabe: &nbsp;'
                        .new Bold(($tblSetting->getValue()
                        ? new SuccessText(new Check())
                        : new DangerText(new Unchecked())));
                break;
            }
        }
        return new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn(
                    new Title('Allgemeine Einstellungen: ',
                        (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiSetting::pipelineShowFormSetting()))
                )
            ),
            new LayoutRow(new LayoutColumn(new Well(new Listing($Listing)), 6))
        )));
    }

    /**
     * @return Layout
     */
    public function formSetting()
    {

        $elementList = array();
        $tblSettingList = Setting::useService()->getSettingAll();
        foreach($tblSettingList as &$tblSetting){
            switch($tblSetting->getIdentifier()){
                case TblSetting::IDENT_DEBTOR_NUMBER_COUNT:
                    $_POST['Setting'][TblSetting::IDENT_DEBTOR_NUMBER_COUNT] = $tblSetting->getValue();
                    $elementList[] = new NumberField('Setting['.TblSetting::IDENT_DEBTOR_NUMBER_COUNT.']', '', 'Länge der Debitoren-Nr.');
                    break;
                case TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED:
                    $_POST['Setting'][TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED] = $tblSetting->getValue();
                    $elementList[] = new CheckBox('Setting['.TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED.']', 'Debitoren-Nr. ist eine Pflichtangabe', true);
                    break;
                case TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED:
                    $_POST['Setting'][TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED] = $tblSetting->getValue();
                    $elementList[] = new CheckBox('Setting['.TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED.']', 'Bankverbindung für SEPA-Lastschrift ist eine Pflichtangabe', true);
                    break;
            }
        }
        return new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn(
                    new Title('Allgemeine Einstellungen:')
                )
            ),
            new LayoutRow(
                new LayoutColumn(new Well(
                    (new Form(new FormGroup(array(
                        new FormRow(new FormColumn($elementList, 12)),
                        new FormRow(new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            (new Primary('Speichern', ApiSetting::getEndpoint(), new Save()))
                                ->ajaxPipelineOnClick(ApiSetting::pipelineSaveSetting())
                            .(new Primary('Abbrechen', ApiSetting::getEndpoint(), new Disable()))
                                ->ajaxPipelineOnClick(ApiSetting::pipelineShowSetting())
                        ))))))
                    ))))->disableSubmitAction()
                ), 6)
            )
        )));
    }
}
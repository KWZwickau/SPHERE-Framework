<?php

namespace SPHERE\Application\Setting\Consumer\Setting;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Consumer\Setting
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendSettings($Data = null)
    {
        $stage = new Stage('Mandant' , 'Einstellungen');

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblIdentification = $tblAccount->getServiceTblIdentification())
            && $tblIdentification->getName() == 'System'
        ) {
            $isSystem = true;
        } else {
            $isSystem = false;
        }

//        $isSystem = false;
        if (($tblSettingList = Consumer::useService()->getSettingAll($isSystem))) {
            if ($Data == null) {
                $global = $this->getGlobal();
                foreach ($tblSettingList as $tblSetting) {
                    $global->POST['Data'][$tblSetting->getId()] = $tblSetting->getValue();
                }
                $global->savePost();
            }

            $selectBoxContent[] = new SelectBoxItem(TblAddress::VALUE_PLZ_ORT_OT_STR_NR, 'PLZ_ORT_OT_STR_NR');
            $selectBoxContent[] = new SelectBoxItem(TblAddress::VALUE_OT_STR_NR_PLZ_ORT, 'OT_STR_NR_PLZ_ORT');

            $fields = array();
            foreach ($tblSettingList as $tblSetting) {

                // werden automatisch vom System gesetzt
                if ($tblSetting->getIdentifier() == 'InterfaceFilterMessageDate'
                    || $tblSetting->getIdentifier() == 'InterfaceFilterMessageCount'
                ) {
                    continue;
                }

                $description = $tblSetting->getDescription()
                    ? $tblSetting->getDescription()
                    : $tblSetting->getIdentifier() . ' (keine Beschreibung verfügbar)';

                if ($tblSetting->getIdentifier() == 'Format_GuiString') {
                    $fields[$tblSetting->getCategory()][] = new SelectBox('Data[' . $tblSetting->getId() . ']', $description, array('{{ Name }}' => $selectBoxContent));
                } elseif ($tblSetting->getType() == TblSetting::TYPE_BOOLEAN) {
                    $fields[$tblSetting->getCategory()][] = new CheckBox('Data[' . $tblSetting->getId() . ']', $description, 1);
                } elseif ($tblSetting->getType() == TblSetting::TYPE_STRING) {
                    $fields[$tblSetting->getCategory()][] = new TextField('Data[' . $tblSetting->getId() . ']', '', $description, new Comment());
                } elseif ($tblSetting->getType() == TblSetting::TYPE_INTEGER) {
                    $fields[$tblSetting->getCategory()][] = new NumberField('Data[' . $tblSetting->getId() . ']', '', $description, new Quantity());
                }
            }

            ksort($fields);
            $formColumns = array();
            foreach ($fields as $category => $content) {
                $formColumns[] = new FormColumn(new Panel(
                    $category,
                    $content,
                    Panel::PANEL_TYPE_INFO
                ));
            }

            $form = new Form(new FormGroup(new FormRow($formColumns)));

            $form->appendFormButton(new Primary('Speichern', new Save()));

            $stage->setContent(new Well(
                Consumer::useService()->updateSettingList($form, $Data, $isSystem)
            ));
        }

        return $stage;
    }
}
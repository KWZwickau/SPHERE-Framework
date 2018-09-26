<?php

namespace SPHERE\Application\Setting\Consumer\Setting;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
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

            $fields = array();
            foreach ($tblSettingList as $tblSetting) {
                // werden automatisch vom System gesetzt
                if ($tblSetting->getIdentifier() == 'InterfaceFilterMessageDate'
                    || $tblSetting->getIdentifier() == 'InterfaceFilterMessageCount'
                ) {
                    continue;
                }

                $description = $tblSetting->getDescription() ? $tblSetting->getDescription() : 'Keine Beschreibung verfügbar.';
                if ($tblSetting->getType() == TblSetting::TYPE_BOOLEAN) {
                    $fields[$tblSetting->getApplication()][] =
                        new CheckBox('Data[' . $tblSetting->getId() . ']', $description, 1)
                    ;
                } elseif ($tblSetting->getType() == TblSetting::TYPE_STRING) {
                    $fields[$tblSetting->getApplication()][] =
                        new TextField('Data[' . $tblSetting->getId() . ']', '', $description, new Comment())
                    ;
                } elseif ($tblSetting->getType() == TblSetting::TYPE_INTEGER) {
                    $fields[$tblSetting->getApplication()][] =
                        new NumberField('Data[' . $tblSetting->getId() . ']', '', $description, new Quantity())
                    ;
                }
            }

            ksort($fields);
            $formColumns = array();
            foreach ($fields as $application => $content) {

                switch ($application) {
                    case 'Address': $title = 'Adressen'; break;
                    case 'Certificate': $title = 'Zeugnisse'; break;
                    case 'ClassRegister': $title = 'Klassenbücher'; break;
                    case 'Consumer': $title = 'Alphabetische Sortierung'; break;
                    case 'Document': $title = 'Dokumente'; break;
                    case 'Education': $title = 'Bildung'; break;
                    case 'Graduation': $title = 'Notenbücher/Leistungsüberprüfungen'; break;
                    case 'KamenzReport': $title = 'Validierung'; break;
                    case 'Meta': $title = 'Metadaten'; break;

                    default: $title = $application;
                }

                $formColumns[] = new FormColumn(new Panel(
                    $title,
                    $content,
                    Panel::PANEL_TYPE_INFO
                ));
            }

            $form = new Form(new FormGroup(new FormRow($formColumns)));

            $form->appendFormButton(new Primary('Speichern', new Save()));

            $stage->setContent(new Well(
                $form
            ));
        }

        return $stage;
    }
}
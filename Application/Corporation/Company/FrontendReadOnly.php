<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.12.2018
 * Time: 16:07
 */

namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Api\Contact\ApiAddressToCompany;
use SPHERE\Application\Api\Contact\ApiMailToCompany;
use SPHERE\Application\Api\Contact\ApiPhoneToCompany;
use SPHERE\Application\Api\Contact\ApiWebToCompany;
use SPHERE\Application\Api\Corporation\Company\ApiCompanyEdit;
use SPHERE\Application\Api\Corporation\Company\ApiCompanyReadOnly;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\FrontendReadOnly as PersonFrontendReadOnly;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class FrontendReadOnly
 *
 * @package SPHERE\Application\Corporation\Company
 */
class FrontendReadOnly extends Extension implements IFrontendInterface
{

    const TITLE = 'Grunddaten';

    /**
     * @return Stage
     */
    public function frontendCompanyCreate()
    {
        $stage = new Stage('Institution', 'Datenblatt anlegen');
        if (Access::useService()->hasAuthorization('/Api/Corporation/Company/ApiCompanyEdit')) {
            $createCompanyContent = ApiCompanyEdit::receiverBlock(
                $this->getCreateCompanyContent(), 'CompanyContent'
            );
        } else {
            $createCompanyContent = new Danger('Sie haben nicht das Recht neue Institutionen anzulegen', new Exclamation());
        }

        $stage->setContent(
            $createCompanyContent
        );

        return $stage;
    }

    /**
     * @param null|int $Id
     * @param null|int $Group
     *
     * @return Stage
     */
    public function frontendCompanyReadOnly($Id = null, $Group = null)
    {

        $stage = new Stage('Institution', 'Datenblatt ' . ($Id ? 'bearbeiten' : 'anlegen'));
        $stage->addButton(new Standard('Zurück', '/Corporation/Search/Group', new ChevronLeft(), array('Id' => $Group)));

        // Institution bearbeiten
        if ($Id != null && ($tblCompany = Company::useService()->getCompanyById($Id))) {
            $stage->addButton(new Standard('Ansprechpartner hinzufügen', '/Corporation/Company/Contact/Create', new PlusSign(), array(
                'Id' => $tblCompany->getId(),
                'Group' => $Group
            )));

            $basicContent = ApiCompanyReadOnly::receiverBlock(
                self::getBasicContent($Id), 'BasicContent'
            );

            $addressToCompanyContent = ApiAddressToCompany::receiverBlock(Address::useFrontend()->frontendLayoutCompanyNew($tblCompany),
                'AddressToCompanyContent');
            $addressContent = ApiAddressToCompany::receiverModal()
                . TemplateReadOnly::getContent(
                    'Adressdaten',
                    $addressToCompanyContent,
                    array(
                        (new Link(
                            new Plus() . ' Adresse hinzufügen',
                            ApiAddressToCompany::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiAddressToCompany::pipelineOpenCreateAddressToCompanyModal($tblCompany->getId()))
                    ),
                    'der Institution ' . new Bold(new Success($tblCompany->getDisplayName())),
                    new MapMarker()
                );

            $phoneReceiver = ApiPhoneToCompany::receiverBlock(Phone::useFrontend()->frontendLayoutCompanyNew($tblCompany),
                'PhoneToCompanyContent');
            $phoneContent = ApiPhoneToCompany::receiverModal()
                . TemplateReadOnly::getContent(
                    'Telefonnummern',
                    $phoneReceiver,
                    array(
                        (new Link(
                            new Plus() . ' Telefonnummer hinzufügen',
                            ApiPhoneToCompany::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiPhoneToCompany::pipelineOpenCreatePhoneToCompanyModal($tblCompany->getId())),
                    ),
                    'der Institution ' . new Bold(new Success($tblCompany->getDisplayName())),
                    new \SPHERE\Common\Frontend\Icon\Repository\Phone()
                );

            $mailReceiver = ApiMailToCompany::receiverBlock(Mail::useFrontend()->frontendLayoutCompanyNew($tblCompany),
                'MailToCompanyContent');
            $mailContent = ApiMailToCompany::receiverModal()
                . TemplateReadOnly::getContent(
                    'E-Mail Adressen',
                    $mailReceiver,
                    array(
                        (new Link(
                            new Plus() . '  E-Mail Adresse hinzufügen',
                            ApiMailToCompany::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiMailToCompany::pipelineOpenCreateMailToCompanyModal($tblCompany->getId())),
                    ),
                    'der Institution ' . new Bold(new Success($tblCompany->getDisplayName())),
                    new \SPHERE\Common\Frontend\Icon\Repository\Mail()
                );

            $webReceiver = ApiWebToCompany::receiverBlock(Web::useFrontend()->frontendLayoutCompanyNew($tblCompany),
                'WebToCompanyContent');
            $webContent = ApiWebToCompany::receiverModal()
                . TemplateReadOnly::getContent(
                    'Internet Adressen',
                    $webReceiver,
                    array(
                        (new Link(
                            new Plus() . '  Internet Adresse hinzufügen',
                            ApiWebToCompany::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiWebToCompany::pipelineOpenCreateWebToCompanyModal($tblCompany->getId())),
                    ),
                    'der Institution ' . new Bold(new Success($tblCompany->getDisplayName())),
                    new Globe()
                );

            $relationshipContent = TemplateReadOnly::getContent(
                'Beziehungen',
                Relationship::useFrontend()->frontendLayoutCompanyNew($tblCompany),
                array(),
                'der Institution ' . new Bold(new Success($tblCompany->getDisplayName())),
                new \SPHERE\Common\Frontend\Icon\Repository\Link()
            );

            $stage->setContent(
                $basicContent
                . $addressContent
                . $phoneContent
                . $mailContent
                . $webContent
                . $relationshipContent
            );
            // neue Institution anlegen
        } else {
            if (Access::useService()->hasAuthorization('/Api/Corporation/Company/ApiCompanyEdit')) {
                $createCompanyContent = ApiCompanyEdit::receiverBlock(
                    $this->getCreateCompanyContent(), 'CompanyContent'
                );
            } else {
                $createCompanyContent = new Danger('Sie haben nicht das Recht neue Institutionen anzulegen', new Exclamation());
            }

            $stage->setContent(
                $createCompanyContent
            );
        }

        return $stage;
    }

    /**
     * @return string
     */
    public function getCreateCompanyContent()
    {

        return new Well($this->getCreateCompanyForm());
    }

    /**
     * @return Form
     */
    private function getCreateCompanyForm()
    {

        $form = (new Form(array(
            new FormGroup(array(
                new FormRow(
                    new FormColumn($this->getEditBasicTitle(null, true)
                    )),
                $this->getBasicFormRow()
            )),
            new FormGroup(array(
                new FormRow(new FormColumn(
                    (new Primary(new Save() . ' Speichern', ApiCompanyEdit::getEndpoint()))
                        ->ajaxPipelineOnClick(ApiCompanyEdit::pipelineSaveCreateCompanyContent())
                ))
            )),
        )))->disableSubmitAction();

        return $form;
    }

    /**
     * @param null $CompanyId
     *
     * @return string
     */
    public static function getBasicContent($CompanyId = null)
    {
        if (($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            $groups = array();
            if (($tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany))) {
                foreach ($tblGroupList as $tblGroup) {
                    $groups[] = $tblGroup->getName();
                }
            }

            $layoutRow = new LayoutRow(new LayoutColumn(''));
            if(Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)){
                $layoutRow =
                new LayoutRow(array(
                    PersonFrontendReadOnly::getLayoutColumnLabel('Kontakt Nummer'),
                    PersonFrontendReadOnly::getLayoutColumnValue($tblCompany->getContactNumber(), 10),
                ));
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    PersonFrontendReadOnly::getLayoutColumnLabel('Name'),
                    PersonFrontendReadOnly::getLayoutColumnValue($tblCompany->getName(), 10),
                )),
                new LayoutRow(array(
                    PersonFrontendReadOnly::getLayoutColumnLabel('Zusatz'),
                    PersonFrontendReadOnly::getLayoutColumnValue($tblCompany->getExtendedName(), 10),
                )),
                new LayoutRow(array(
                    PersonFrontendReadOnly::getLayoutColumnLabel('Beschreibung'),
                    PersonFrontendReadOnly::getLayoutColumnValue($tblCompany->getDescription(), 10),
                )),
                new LayoutRow(array(
                    PersonFrontendReadOnly::getLayoutColumnLabel('Gruppen'),
                    PersonFrontendReadOnly::getLayoutColumnValue(implode(', ', $groups), 10),
                )),
                $layoutRow
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiCompanyEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiCompanyEdit::pipelineEditBasicContent($CompanyId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Institution ' . new Bold(new Success($tblCompany->getDisplayName())),
                new Building()
            );
        }

        return '';
    }

    /**
     * @param null $CompanyId
     *
     * @return string
     */
    public function getEditBasicContent($CompanyId = null)
    {

        $tblCompany = false;
        if ($CompanyId && ($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            $Global = $this->getGlobal();

            $Global->POST['Company']['Name'] = $tblCompany->getName();
            $Global->POST['Company']['ExtendedName'] = $tblCompany->getExtendedName();
            $Global->POST['Company']['Description'] = $tblCompany->getDescription();
            if (($tblGroupAll = Group::useService()->getGroupAllByCompany($tblCompany))) {
                foreach ((array)$tblGroupAll as $tblGroup) {
                    $Global->POST['Company']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                }
            }

            $Global->savePost();
        }

        return $this->getEditBasicTitle($tblCompany ? $tblCompany : null)
            . new Well($this->getEditBasicForm($tblCompany ? $tblCompany : null));
    }

    /**
     * @param TblCompany|null $tblCompany
     * @param bool $isCreateCompany
     *
     * @return Title
     */
    private function getEditBasicTitle(TblCompany $tblCompany = null, $isCreateCompany = false)
    {
        return new Title(new Building() . ' ' . self::TITLE, 'der Institution'
            . ($tblCompany ? new Bold(new Success($tblCompany->getDisplayName())) : '')
            . ($isCreateCompany ? ' anlegen' : ' bearbeiten'));
    }

    /**
     * @return FormRow
     */
    private function getBasicFormRow()
    {
        $tblGroupList = Group::useService()->getGroupAll();
        if ($tblGroupList) {
            // Sort by Name
            usort($tblGroupList, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                return strnatcmp($ObjectA->getName(), $ObjectB->getName());
            });
            // Create CheckBoxes
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupList, function (TblGroup &$tblGroup) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Company']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Company[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = new CheckBox(
                            'Company[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        return new FormRow(array(
            new FormColumn(
                new Panel('Name der Institution', array(
                    (new TextField('Company[Name]', 'Name', 'Name'))->setRequired(),
                    new TextField('Company[ExtendedName]', 'Zusatz', 'Zusatz'),
                    new TextField('Company[Description]', 'Beschreibung', 'Beschreibung'),
                ), Panel::PANEL_TYPE_INFO), 8),
            new FormColumn(
                new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO), 4),
        ));
    }

    /**
     * @param TblCompany|null $tblCompany
     *
     * @return Form
     */
    private function getEditBasicForm(TblCompany $tblCompany = null)
    {

        return new Form(
            new FormGroup(array(
                $this->getBasicFormRow(),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiCompanyEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiCompanyEdit::pipelineSaveBasicContent($tblCompany ? $tblCompany->getId() : 0)),
                        (new Primary('Abbrechen', ApiCompanyEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiCompanyEdit::pipelineCancelBasicContent($tblCompany ? $tblCompany->getId() : 0))
                    ))
                ))
            ))
        );
    }

    /**
     * @param TblCompany|null $tblCompany
     * @param $Company
     *
     * @return bool|string
     */
    public function checkInputBasicContent(TblCompany $tblCompany = null, $Company)
    {
        $error = false;
        $form = $this->getEditBasicForm($tblCompany ? $tblCompany : null);
        if (isset($Company['Name']) && empty($Company['Name'])) {
            $form->setError('Company[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        }

        // company name with extend have to be unique
        if (isset($Company['Name']) && !empty($Company['Name'])) {
            $tblCompanyCatch = Company::useService()->getCompanyByName($Company['Name'], $Company['ExtendedName']);
            if ($tblCompanyCatch && $tblCompanyCatch->getId() != $tblCompany->getId()) {
                $form->setError('Company[Name]', 'Name der Firma (mit Zusatz) bereits vorhanden!');
                $form->setError('Company[ExtendedName]', 'Name der Firma (mit Zusatz) bereits vorhanden!');
                $error = true;
            }
        }

        if ($error) {
            return $this->getEditBasicTitle($tblCompany ? $tblCompany : null)
                . new Well($form);
        }

        return $error;
    }

    /**
     * @param $Company
     *
     * @return bool|Well
     */
    public function checkInputCreateCompanyContent($Company)
    {
        $error = false;
        $form = $this->getCreateCompanyForm();
        if (isset($Company['Name']) && empty($Company['Name'])) {
            $form->setError('Company[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        }

        // company name with extend have to be unique
        if (isset($Company['Name']) && !empty($Company['Name'])) {
            $tblCompanyCatch = Company::useService()->getCompanyByName($Company['Name'], $Company['ExtendedName']);
            if ($tblCompanyCatch) {
                $form->setError('Company[Name]', 'Name der Firma (mit Zusatz) bereits vorhanden!');
                $form->setError('Company[ExtendedName]', 'Name der Firma (mit Zusatz) bereits vorhanden!');
                $error = true;
            }
        }

        if ($error) {
            return new Well($form);
        }

        return $error;
    }
}
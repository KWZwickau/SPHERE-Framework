<?php

namespace SPHERE\Application\People\ContactDetails;

use SPHERE\Application\Api\Contact\ApiAddressToPerson;
use SPHERE\Application\Api\Contact\ApiContactDetails;
use SPHERE\Application\Api\Contact\ApiMailToPerson;
use SPHERE\Application\Api\Contact\ApiPhoneToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblAddressToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblMailToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblPhoneToPerson;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendContactDetails(): Stage
    {
        $stage = new Stage('Kontakt-Daten', 'Übersicht');
        if (($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'OnlineContactDetailsAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            $names = array();
            foreach ($tblSchoolTypeAllowedList as $tblSchoolType) {
                $names[] = $tblSchoolType->getName();
            }
            $stage->setMessage('Die Online Kontakt-Daten Änderungswünsche für Eltern/Schüler sind für die folgenden Schularten freigeschaltet: ' . implode(', ', $names));

            $stage->setContent(ApiContactDetails::receiverBlock($this->loadContactDetailsStageContent(), 'ContactDetailsStageContent'));
        } else {
            $stage->setContent((new Warning('Die Online Kontakt-Daten Änderungswünsche für Eltern/Schüler sind für keine Schulart freigeschaltet!', new Exclamation())));
        }

        return $stage;
    }

    /**
     * @return string
     */
    public function loadContactDetailsStageContent(): string
    {
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAll())) {
            $dataList = array();
            foreach ($tblOnlineContactList as $tblOnlineContact) {
                if (!($tblPerson = $tblOnlineContact->getServiceTblPerson())) {
                    continue;
                }

                $link = '';
                switch ($tblOnlineContact->getContactType()) {
                    case TblOnlineContact::VALUE_TYPE_ADDRESS:
                        /** @var TblAddressToPerson $tblToPerson */
                        $link = ($tblToPerson = $tblOnlineContact->getServiceTblToPerson())
                            ? (new Standard('', ApiAddressToPerson::getEndpoint(), new Edit()))
                                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenEditAddressToPersonModal($tblPerson->getId(), $tblToPerson->getId(), $tblOnlineContact->getId()))
                            : (new Standard('', ApiAddressToPerson::getEndpoint(), new Plus()))
                                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenCreateAddressToPersonModal($tblPerson->getId(), $tblOnlineContact->getId()));
                        break;
                    case TblOnlineContact::VALUE_TYPE_PHONE:
                        /** @var TblPhoneToPerson $tblToPerson */
                        $link = ($tblToPerson = $tblOnlineContact->getServiceTblToPerson())
                            ? (new Standard('', ApiPhoneToPerson::getEndpoint(), new Edit()))
                                ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineOpenEditPhoneToPersonModal($tblPerson->getId(), $tblToPerson->getId(), $tblOnlineContact->getId()))
                            : (new Standard('', ApiPhoneToPerson::getEndpoint(), new Plus()))
                                ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineOpenCreatePhoneToPersonModal($tblPerson->getId(), $tblOnlineContact->getId()));
                        break;
                    case TblOnlineContact::VALUE_TYPE_MAIL:
                        /** @var TblMailToPerson $tblToPerson */
                        $link = ($tblToPerson = $tblOnlineContact->getServiceTblToPerson())
                            ? (new Standard('', ApiMailToPerson::getEndpoint(), new Edit()))
                                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenEditMailToPersonModal($tblPerson->getId(), $tblToPerson->getId(), $tblOnlineContact->getId()))
                            : (new Standard('', ApiMailToPerson::getEndpoint(), new Plus()))
                                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenCreateMailToPersonModal($tblPerson->getId(), $tblOnlineContact->getId()));
                        break;
                }

                $schoolType = '';
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                ) {
                    $schoolType = $tblSchoolType->getShortName() ?: $tblSchoolType->getName();
                }

                $dataList[] = array(
                    'CreateDate' => $tblOnlineContact->getEntityCreate()->format('d.m.Y H:i:s'),
                    'Creator' => ($tblPersonCreator = $tblOnlineContact->getServiceTblPersonCreator()) ? $tblPersonCreator->getFullName() : '',
                    'Category' => $tblOnlineContact->getContactTypeIcon() . ' ' . $tblOnlineContact->getContactTypeName(),
                    'Person' => $tblOnlineContact->getServiceTblPerson() ? $tblOnlineContact->getServiceTblPerson()->getLastFirstName() : '',
                    'SchoolType' => $schoolType,
                    'Original' => $tblOnlineContact->getOriginalContent(),
                    'Content' => $tblOnlineContact->getContactContent(),
                    'Remark' => $tblOnlineContact->getRemark(),
                    'Options' => $link
                        . (new Standard('', ApiContactDetails::getEndpoint(), new Remove()))
                            ->ajaxPipelineOnClick(ApiContactDetails::pipelineOpenDeleteContactDetailModal($tblPerson->getId(), $tblOnlineContact->getId()))
                );
            }

            $columns = array(
                'CreateDate' => 'Datum',
                'Creator' => 'Ersteller',
                'Category' => 'Kategorie',
                'Person' => 'Person',
                'SchoolType' => 'Schul&shy;art',
                'Original' => 'Alter Kontakt',
                'Content' => 'Neuer Kontakt',
                'Remark' => 'Änderungsbemerkung',
                'Options' => ''
            );

            return ApiContactDetails::receiverModal() . ApiPhoneToPerson::receiverModal() . ApiAddressToPerson::receiverModal() . ApiMailToPerson::receiverModal()
                . new TableData($dataList, null, $columns,
                    array(
                        'order' => array(
                            array(0, 'desc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0),
                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                );
        }

        return new Success('Es gibt aktuell keine Änderungswünsche für Kontakt-Daten durch Eltern/Schüler.', new Check());
    }

    /**
     * @return string
     */
    public function getWelcome(): string
    {
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAll())) {
            return new Panel(
                'Es sind ' . count($tblOnlineContactList) . ' unbearbeitete Änderungswünsche für Kontakt-Daten vorhanden!',
                '',
                Panel::PANEL_TYPE_WARNING,
                new Standard(
                    '',
                    '/People/ContactDetails',
                    new Extern(),
                    array(),
                    'Zur Kontakt-Daten Übersicht wechseln'
                )
            );
        }

        return '';
    }
}
<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendStudentGroup
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentGroup  extends FrontendReadOnly
{
    const TITLE = 'Schülerakte';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentGroupTitle($PersonId = null)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))){
            return '';
        }

        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_STUDENT;
        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_ARCHIVE;
        $hasBlock = false;
        $AllowEdit = 0;
        foreach ($AuthorizedToCollectGroups as $group) {
            if (($tblGroup = Group::useService()->getGroupByMetaTable($group))
                && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
            ) {
                $hasBlock = true;
                // nur Schüler dürfen in der Schülerakte bearbeitet werden
                if($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT){
                    $AllowEdit = 1;
                }
//                break;
            }
        }
        if(!$hasBlock){
            return '';
        }

        $showLink = (new Link(new EyeOpen() . ' Anzeigen', ApiPersonReadOnly::getEndpoint()))
            ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStudentGroupContent($PersonId, $AllowEdit));
        $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

        return TemplateReadOnly::getContent(
            self::TITLE,
            new Info('Die Schülerakte ist ausgeblendet. Bitte klicken Sie auf Anzeigen.'),
            array($showLink),
            'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
            new Tag(),
            true
        );
    }

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentGroupContent($PersonId = null, $AllowEdit = 1)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')) {
                $routeEnrollmentDocument = '/Document/Custom/Hoga/EnrollmentDocument/Fill';
            } else {
                $routeEnrollmentDocument = '/Document/Standard/EnrollmentDocument/Fill';
            }

            $hasApiRight = Access::useService()->hasAuthorization('/Api/Document/Standard/StudentCard/Create');
            if ($hasApiRight && $tblPerson != null) {
                $listingContent[] =
                    new External(
                        'Herunterladen der Schülerkartei', '/Api/Document/Standard/StudentCardNew/Create',
                        new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei herunterladen')
                    .new External(
                        'Schülerkartei (alt)', '/Api/Document/Standard/StudentCard/Create',
                        new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei herunterladen')
                    .new External(
                        'Erstellen der Schulbescheinigung', $routeEnrollmentDocument,
                        new Download(), array('PersonId' => $tblPerson->getId()),
                        'Erstellen und Herunterladen einer Schulbescheinigung')
                    .new External(
                        'Erstellen der Schülerüberweisung', '/Document/Standard/StudentTransfer/Fill',
                        new Download(), array('PersonId' => $tblPerson->getId()),
                        'Erstellen und Herunterladen einer Schülerüberweisung ')
                    . new External(
                        'Erstellen der Abmeldebescheinigung', '/Document/Standard/SignOutCertificate/Fill',
                        new Download(), array('PersonId' => $tblPerson->getId()),
                        'Erstellen und Herunterladen einer Abmeldebescheinigung '
                    );
            }

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentBasic::getStudentBasicContent($PersonId, $AllowEdit), 'StudentBasicContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentTransfer::getStudentTransferContent($PersonId, $AllowEdit), 'StudentTransferContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentProcess::getStudentProcessContent($PersonId, $AllowEdit), 'StudentProcessContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentMedicalRecord::getStudentMedicalRecordContent($PersonId, $AllowEdit), 'StudentMedicalRecordContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentGeneral::getStudentGeneralContent($PersonId, $AllowEdit), 'StudentGeneralContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentAgreement::getStudentAgreementContent($PersonId, $AllowEdit), 'StudentAgreementContent'
            );

            if (School::useService()->hasConsumerSupportSchool()) {
//            if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'WVSZ')) {
                $listingContent[] = ApiPersonReadOnly::receiverBlock(
                    FrontendStudentSpecialNeeds::getStudentSpecialNeedsContent($PersonId, $AllowEdit), 'StudentSpecialNeedsContent'
                );
            }

            if (School::useService()->hasConsumerTechnicalSchool()) {
                $listingContent[] = ApiPersonReadOnly::receiverBlock(
                    FrontendStudentTechnicalSchool::getStudentTechnicalSchoolContent($PersonId, $AllowEdit),
                    'StudentTechnicalSchoolContent'
                );
            }

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentSubject::getStudentSubjectContent($PersonId, $AllowEdit), 'StudentSubjectContent'
            );

            $content = new Listing($listingContent);

            $hideLink = (new Link(new EyeMinus() . ' Ausblenden', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStudentGroupTitle($PersonId));
            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($hideLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                new Tag()
                , true
            );
        }

        return '';
    }
}
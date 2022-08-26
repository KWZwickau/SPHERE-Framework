<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendStaffGroup
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStaffGroup  extends FrontendReadOnly
{
    const TITLE = 'Mitarbeiter';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStaffGroupTitle($PersonId = null)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))){
            return '';
        }

        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_STAFF;
        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_TEACHER;
        $hasBlock = false;
        foreach ($AuthorizedToCollectGroups as $group) {
            if (($tblGroup = Group::useService()->getGroupByMetaTable($group))
                && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
            ) {
                $hasBlock = true;
                break;
            }
        }
        if(!$hasBlock){
            return '';
        }

//        $AllowEdit = 1;
        $showLink = (new Link(new EyeOpen() . ' Anzeigen', ApiPersonReadOnly::getEndpoint()))
            ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStaffGroupContent($PersonId)); // $AllowEdit
        $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

        return TemplateReadOnly::getContent(
            self::TITLE,
            new Info('Die Mitarbeiter - Daten sind ausgeblendet. Bitte klicken Sie auf Anzeigen.'),
            array($showLink),
            'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
            new Tag(),
            true
        );

    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStaffGroupContent($PersonId = null)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return '';
        }

        $listingContent[] = ApiPersonReadOnly::receiverBlock(FrontendTeacher::getTeacherContent($PersonId), 'TeacherContent');
        $listingContent[] = ApiPersonReadOnly::receiverBlock(FrontendPersonMasern::getPersonMasernContent($PersonId), 'PersonMasernContent');
        $listingContent[] = ApiPersonReadOnly::receiverBlock(FrontendPersonAgreement::getPersonAgreementContent($PersonId), 'PersonAgreementContent');

        $listingContent = array_filter($listingContent);
        $content = new Listing($listingContent);

        $hideLink = (new Link(new EyeMinus() . ' Ausblenden', ApiPersonReadOnly::getEndpoint()))
            ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStaffGroupTitle($PersonId));
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
}
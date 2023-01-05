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
 * Class FrontendProspectGroup
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendProspectGroup  extends FrontendReadOnly
{
    const TITLE = 'Interessent';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getProspectGroupTitle($PersonId = null)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))){
            return '';
        }

        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_STUDENT;
        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_PROSPECT;
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
            ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadProspectGroupContent($PersonId)); // $AllowEdit
        $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

        return TemplateReadOnly::getContent(
            self::TITLE,
            new Info('Die Interessenten - Daten sind ausgeblendet. Bitte klicken Sie auf Anzeigen.'),
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
    public static function getProspectGroupContent($PersonId = null)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return '';
        }
        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_STUDENT;
        $AuthorizedToCollectGroups[] = TblGroup::META_TABLE_PROSPECT;
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
        $listingContent[] = ApiPersonReadOnly::receiverBlock(FrontendProspect::getProspectContent($PersonId), 'ProspectContent');
        $listingContent[] = ApiPersonReadOnly::receiverBlock(FrontendProspectTransfer::getProspectTransferContent($PersonId), 'ProspectTransferContent');
        $content = new Listing($listingContent);

        $hideLink = (new Link(new EyeMinus() . ' Ausblenden', ApiPersonReadOnly::getEndpoint()))
            ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadProspectGroupTitle($PersonId));
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
<?php
namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\Response;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class ClassRegister
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ClassRegister extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Reorder', __CLASS__ . '::reorderDivision'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param array|null $Reorder
     * @param array|null $Additional
     *
     * @return Response
     */
    public function reorderDivision(array $Reorder = null, array $Additional = null): Response
    {

        if ($Additional
            && $Reorder
            && isset($Additional['DivisionCourseId'])
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Additional['DivisionCourseId']))
            && ($tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier($Additional['MemberTypeIdentifier']))
        ) {
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, $Additional['MemberTypeIdentifier'], true, false))) {
                $count = 1;
                $updateList = array();
                foreach ($tblMemberList as $tblMember) {
                    $tblMember->setSortOrder($count);
                    $updateList[$count++] = $tblMember;
                }

                foreach ($Reorder as $item) {
                    if (isset($item['pre']) && isset($item['post'])) {
                        $pre = $item['pre'];
                        $post = $item['post'];

                        if (isset($updateList[$pre])) {
                            $updateList[$pre]->setSortOrder($post);
                        }
                    }
                }

                DivisionCourse::useService()->updateDivisionCourseMemberBulkSortOrder($updateList, $Additional['MemberTypeIdentifier'], $tblDivisionCourse->getType() ?: null);
            }

            return (new Response())->addData( new Success().' Die Sortierung der ' . $tblMemberType->getName() . ' wurde erfolgreich aktualisiert.');
        }

        return (new Response())->addError( 'Fehler!', new HazardSign().' Die Sortierung der Mitglieder konnte nicht aktualisiert werden.', 0);
    }
}

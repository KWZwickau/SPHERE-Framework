<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\System\Extension\Extension;

abstract class FrontendBasic extends Extension implements IFrontendInterface
{
    const VIEW_GRADE_BOOK_SELECT = "VIEW_GRADE_BOOK_SELECT";
    const VIEW_GRADE_BOOK_CONTENT = "VIEW_GRADE_BOOK_CONTENT";
    const VIEW_TEACHER_GROUP = "VIEW_TEACHER_GROUP";

    /**
     * @param string $View
     *
     * @return string
     */
    public function getHeader(string $View): string
    {
        $role = Grade::useService()->getRole();

        $textGradeBook = $View == self::VIEW_GRADE_BOOK_SELECT || $View == self::VIEW_GRADE_BOOK_CONTENT
            ? new Info(new Edit() . new Bold(" Notenbuch"))
            : "Notenbuch";

        $textTeacherGroup = $View == self::VIEW_TEACHER_GROUP
            ? new Info(new Edit() . new Bold(" Lerngruppen"))
            : "Lerngruppen";

        return
            (new Standard($textGradeBook, ApiGradeBook::getEndpoint()))
                ->ajaxPipelineOnClick(array(
                    ApiGradeBook::pipelineLoadHeader(self::VIEW_GRADE_BOOK_SELECT),
                    ApiGradeBook::pipelineLoadViewGradeBookSelect()
                ))
            . ($role == "Teacher"
                ? (new Standard($textTeacherGroup, ApiTeacherGroup::getEndpoint()))
                    ->ajaxPipelineOnClick(array(
                        ApiGradeBook::pipelineLoadHeader(self::VIEW_TEACHER_GROUP),
                        ApiTeacherGroup::pipelineLoadViewTeacherGroups()
                    ))
                : "")
            ;
    }
}
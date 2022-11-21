<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\System\Extension\Extension;

abstract class FrontendBasic extends Extension implements IFrontendInterface
{
    const VIEW_GRADE_BOOK_SELECT = "VIEW_GRADE_BOOK_SELECT";
    const VIEW_GRADE_BOOK_CONTENT = "VIEW_GRADE_BOOK_CONTENT";
    const VIEW_TEACHER_GROUP = "VIEW_TEACHER_GROUP";

    const BACKGROUND_COLOR = '#E0F0FF';
    const MIN_HEIGHT_HEADER = '46px';
    const MIN_HEIGHT_BODY = '30px';
    const PADDING = '3px';

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

    /**
     * @param string $content
     * @param bool $isBold
     *
     * @return TableColumn
     */
    public function getTableColumnHead(string $content, bool $isBold = true): TableColumn
    {
        return (new TableColumn(new Center($isBold ? new Bold($content) : $content)))
            ->setBackgroundColor(self::BACKGROUND_COLOR)
            ->setMinHeight(self::MIN_HEIGHT_HEADER)
            ->setVerticalAlign('middle')
            ->setPadding(self::PADDING);
    }

    /**
     * @param string $content
     * @param string|null $backgroundColor
     *
     * @return TableColumn
     */
    public function getTableColumnBody(string $content, ?string $backgroundColor = null): TableColumn
    {
        return (new TableColumn(new Center($content)))
            ->setBackgroundColor($backgroundColor)
            ->setMinHeight(self::MIN_HEIGHT_BODY)
            ->setVerticalAlign('middle')
            ->setPadding(self::PADDING);
    }
}
<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

abstract class FrontendBasic extends Extension implements IFrontendInterface
{
    const VIEW_GRADE_BOOK_SELECT = "VIEW_GRADE_BOOK_SELECT";
    const VIEW_GRADE_BOOK_CONTENT = "VIEW_GRADE_BOOK_CONTENT";
    const VIEW_TEACHER_GROUP = "VIEW_TEACHER_GROUP";

    const BACKGROUND_COLOR = '#E0F0FF';
    const BACKGROUND_COLOR_TASK_HEADER = '#E7E7E7';
    const BACKGROUND_COLOR_TASK_BODY = '#E7E7E7';
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
     * @param int $size
     * @param string|null $backgroundColor
     *
     * @return TableColumn
     */
    public function getTableColumnHead(string $content, bool $isBold = true, int $size = 1, ?string $backgroundColor = null): TableColumn
    {
        return (new TableColumn(new Center($isBold ? new Bold($content) : $content), $size))
            ->setBackgroundColor($backgroundColor ?: self::BACKGROUND_COLOR)
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

    /**
     * @param bool $hasPicture
     * @param bool $hasIntegration
     * @param bool $hasCourse
     * @param bool $isCustomHeader
     *
     * @return array
     */
    protected function getGradeBookPreHeaderList(bool $hasPicture, bool $hasIntegration, bool $hasCourse, bool $isCustomHeader = false): array
    {
        $headerList['Number'] = $isCustomHeader ? $this->getTableColumnHead('#') : '#';
        $headerList['Person'] = $isCustomHeader ? $this->getTableColumnHead('Schüler') : 'Schüler';
        if ($hasPicture) {
            $headerList['Picture'] = $isCustomHeader ? $this->getTableColumnHead('Fo&shy;to') : 'Fo&shy;to';
        }
        if ($hasIntegration) {
            $headerList['Integration'] = $isCustomHeader ? $this->getTableColumnHead('Inte&shy;gra&shy;tion') : 'Inte&shy;gra&shy;tion';
        }
        if ($hasCourse) {
            $headerList['Course'] = $isCustomHeader ? $this->getTableColumnHead(new ToolTip('BG', 'Bildungsgang')) : new ToolTip('BG', 'Bildungsgang');
        }

        return $headerList;
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return false|string
     */
    public function getGradeTypeTooltip(TblGradeType $tblGradeType)
    {
        switch ($tblGradeType->getName()) {
            case 'Betragen': $tooltip = 'Betragen umfasst Aufmerksamkeit, Hilfsbereitschaft, Zivilcourage und
                        angemessenen Umgang mit Konflikten, Rücksichtnahme, Toleranz und Gemeinsinn sowie Selbsteinschätzung.';
                break;
            case 'Fleiß': $tooltip = 'Fleiß umfasst Lernbereitschaft, Zielstrebigkeit, Ausdauer und Regelmäßigkeit
                        beim Erfüllen von Aufgaben.';
                break;
            case 'Mitarbeit': $tooltip = 'Mitarbeit umfasst Initiative, Kooperationsbereitschaft und Teamfähigkeit,
                        Beteiligung am Unterricht, Selbstständigkeit, Kreativität sowie Verantwortungsbereitschaft.';
                break;
            case 'Ordnung': $tooltip = 'Ordnung umfasst Sorgfalt, Pünktlichkeit, Zuverlässigkeit, Einhalten von
                        Regeln und Absprachen sowie Bereithalten notwendiger Unterrichtsmaterialien';
                break;
            default: $tooltip = false;
        }

        return $tooltip;
    }
}
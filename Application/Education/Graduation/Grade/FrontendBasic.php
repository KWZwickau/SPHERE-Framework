<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiStudentOverview;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

abstract class FrontendBasic extends Extension implements IFrontendInterface
{
    const VIEW_GRADE_BOOK_SELECT = "VIEW_GRADE_BOOK_SELECT";
    const VIEW_GRADE_BOOK_CONTENT = "VIEW_GRADE_BOOK_CONTENT";
    const VIEW_TEACHER_GROUP = "VIEW_TEACHER_GROUP";
    const VIEW_STUDENT_OVERVIEW_COURSE_SELECT = 'VIEW_STUDENT_OVERVIEW_COURSE_SELECT';
    const VIEW_STUDENT_OVERVIEW_STUDENT_SELECT = 'VIEW_STUDENT_OVERVIEW_STUDENT_SELECT';
    const VIEW_STUDENT_OVERVIEW_STUDENT_CONTENT = 'VIEW_STUDENT_OVERVIEW_STUDENT_CONTENT';
    const VIEW_MINIMUM_GRADE_COUNT_REPORTING = 'VIEW_MINIMUM_GRADE_COUNT_REPORTING';
    const VIEW_TEST_PLANNING = 'VIEW_TEST_PLANNING';

    const BACKGROUND_COLOR = '#D8EDF7';
//    const BACKGROUND_COLOR_TASK_HEADER = '#EEEEEE';
//    const BACKGROUND_COLOR_TASK_BODY = '#EEEEEE';
    const BACKGROUND_COLOR_TASK_HEADER = '#FFFFFF';
    const BACKGROUND_COLOR_TASK_BODY = '#FFFFFF';
    const BACKGROUND_COLOR_PERIOD = '#EEEEEE';
    const MIN_HEIGHT_HEADER = '46px';
    const MIN_HEIGHT_BODY = '30px';
    const PADDING = '5px';

    const SCORE_RULE = 0;
    const SCORE_CONDITION = 1;
    const GRADE_GROUP = 2;

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

        $textStudentOverview = $View == self::VIEW_STUDENT_OVERVIEW_COURSE_SELECT
            || $View == self::VIEW_STUDENT_OVERVIEW_STUDENT_SELECT
            || $View == self::VIEW_STUDENT_OVERVIEW_STUDENT_CONTENT
                ? new Info(new Edit() . new Bold(" Schülerübersicht"))
                : "Schülerübersicht";

        $textTeacherGroup = $View == self::VIEW_TEACHER_GROUP
            ? new Info(new Edit() . new Bold(" Lerngruppen"))
            : "Lerngruppen";

        $textMinimumGradeCountReporting = $View == self::VIEW_MINIMUM_GRADE_COUNT_REPORTING
            ? new Info(new Edit() . new Bold(" Mindestnotenauswertung"))
            : "Mindestnotenauswertung";

        $textTestPlanning = $View == self::VIEW_TEST_PLANNING
            ? new Info(new Edit() . new Bold(" Planungsübersicht"))
            : "Planungsübersicht";

        $hasMinimumGradeCountReporting = $role !== 'Teacher';
        if (!$hasMinimumGradeCountReporting
            && ($tblPersonLogin = Account::useService()->getPersonByLogin())
            && ($tblYearList = Term::useService()->getYearByNow())
        ) {
            foreach ($tblYearList as $tblYear) {
                if (!$hasMinimumGradeCountReporting
                    && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonLogin, $tblYear))
                ) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if ($tblDivisionCourse->getIsDivisionOrCoreGroup()) {
                            $hasMinimumGradeCountReporting = true;
                            break;
                        }
                    }
                }
            }
        }

        return
            (new Standard($textGradeBook, ApiGradeBook::getEndpoint()))
                ->ajaxPipelineOnClick(array(
                    ApiGradeBook::pipelineLoadHeader(self::VIEW_GRADE_BOOK_SELECT),
                    ApiGradeBook::pipelineLoadViewGradeBookSelect()
                ))
            . (new Standard($textStudentOverview, ApiGradeBook::getEndpoint()))
                ->ajaxPipelineOnClick(array(
                    ApiGradeBook::pipelineLoadHeader(self::VIEW_STUDENT_OVERVIEW_COURSE_SELECT),
                    ApiStudentOverview::pipelineLoadViewStudentOverviewCourseSelect()
                ))
            . ($role == "Teacher"
                ? (new Standard($textTeacherGroup, ApiTeacherGroup::getEndpoint()))
                    ->ajaxPipelineOnClick(array(
                        ApiGradeBook::pipelineLoadHeader(self::VIEW_TEACHER_GROUP),
                        ApiTeacherGroup::pipelineLoadViewTeacherGroups()
                    ))
                : "")
            . ($hasMinimumGradeCountReporting
                ? (new Standard($textMinimumGradeCountReporting, ApiGradeBook::getEndpoint()))
                    ->ajaxPipelineOnClick(array(
                        ApiGradeBook::pipelineLoadHeader(self::VIEW_MINIMUM_GRADE_COUNT_REPORTING),
                        ApiGradeBook::pipelineLoadViewMinimumGradeCountReportingContent()
                    ))
                : "")
            . ($hasMinimumGradeCountReporting
                ? (new Standard($textTestPlanning, ApiGradeBook::getEndpoint()))
                    ->ajaxPipelineOnClick(array(
                        ApiGradeBook::pipelineLoadHeader(self::VIEW_TEST_PLANNING),
                        ApiGradeBook::pipelineLoadViewTestPlanningContent()
                    ))
                : "")
            ;
    }

    /**
     * @param string $content
     * @param bool $isBold
     * @param string|null $backgroundColor
     * @param int $size
     * @param string $width
     *
     * @return TableColumn
     */
    public function getTableColumnHead(string $content, bool $isBold = true, ?string $backgroundColor = null, int $size = 1, string $width = 'auto'): TableColumn
    {
        return (new TableColumn(new Center($isBold ? new Bold($content) : $content), $size, $width))
            ->setBackgroundColor($backgroundColor ?: self::BACKGROUND_COLOR)
            ->setMinHeight(self::MIN_HEIGHT_HEADER)
            ->setVerticalAlign('middle')
            ->setPadding(self::PADDING);
    }

    /**
     * @param string $content
     * @param string|null $backgroundColor
     * @param string $width
     *
     * @return TableColumn
     */
    public function getTableColumnBody(string $content, ?string $backgroundColor = null, string $width = 'auto'): TableColumn
    {
        return (new TableColumn(new Center($content), 1, $width))
            ->setBackgroundColor($backgroundColor)
            ->setMinHeight(self::MIN_HEIGHT_BODY)
            ->setVerticalAlign('middle')
            ->setPadding(self::PADDING);
    }

    /**
     * @param array $headerList
     * @param array $bodyList
     *
     * @return Table
     */
    public function getTableCustom(array $headerList, array $bodyList): Table
    {
        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $columnList) {
            $rows[] = new TableRow($columnList);
        }
        $tableBody = new TableBody($rows);

        return new Table($tableHead, $tableBody, null, false, null, 'TableCustom');
    }

    /**
     * @param bool $hasPicture
     * @param bool $hasIntegration
     * @param bool $hasCourse
     *
     * @return array
     */
    protected function getGradeBookPreHeaderList(bool $hasPicture, bool $hasIntegration, bool $hasCourse): array
    {
        $headerList['Number'] = $this->getTableColumnHead('#');
        $headerList['Person'] = $this->getTableColumnHead('Schüler');
        if ($hasPicture) {
            $headerList['Picture'] = $this->getTableColumnHead('Fo&shy;to');
        }
        if ($hasIntegration) {
            $headerList['Integration'] = $this->getTableColumnHead('Inte&shy;gra&shy;tion');
        }
        if ($hasCourse) {
            $headerList['Course'] = $this->getTableColumnHead(new ToolTip('BG', 'Bildungsgang'));
        }

        return $headerList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $count
     * @param bool $hasPicture
     * @param bool $hasIntegration
     * @param bool $hasCourse
     * @param array $pictureList
     * @param array $integrationList
     * @param array $courseList
     *
     * @return array
     */
    protected function getGradeBookPreBodyList(TblPerson $tblPerson, $count, bool $hasPicture, bool $hasIntegration, bool $hasCourse,
        array $pictureList, array $integrationList, array $courseList): array
    {
        $result['Number'] = $this->getTableColumnBody($count);
        $result['Person'] = $this->getTableColumnBody($tblPerson->getLastFirstNameWithCallNameUnderline());

        if ($hasPicture) {
            $result['Picture'] = $this->getTableColumnBody($pictureList[$tblPerson->getId()] ?? '&nbsp;');
        }
        if ($hasIntegration) {
            $result['Integration'] = $this->getTableColumnBody($integrationList[$tblPerson->getId()] ?? '&nbsp;');
        }
        if ($hasCourse) {
            $result['Course'] = $this->getTableColumnBody($courseList[$tblPerson->getId()] ?? '&nbsp;');
        }

        return $result;
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

    /**
     * @param Stage $Stage
     * @param int $view
     */
    protected function setScoreStageMenuButtons(Stage $Stage, int $view)
    {
        $text = ' Berechnungsvorschriften';
        $Stage->addButton(
            new Standard($view == self::SCORE_RULE ? new Edit() . new Info ($text) : $text,
                '/Education/Graduation/Grade/ScoreRule', null, null, 'Erstellen/Berarbeiten')
        );

        $text = ' Berechnungsvarianten';
        $Stage->addButton(
            new Standard($view == self::SCORE_CONDITION ? new Edit() . new Info ($text) : $text,
                '/Education/Graduation/Grade/ScoreRule/Condition', null, null, 'Erstellen/Berarbeiten')
        );

        $text = ' Zensuren-Gruppen';
        $Stage->addButton(
            new Standard($view == self::GRADE_GROUP ? new Edit() . new Info ($text) : $text,
                '/Education/Graduation/Grade/ScoreRule/Group', null, null, 'Erstellen/Berarbeiten')
        );
    }
}
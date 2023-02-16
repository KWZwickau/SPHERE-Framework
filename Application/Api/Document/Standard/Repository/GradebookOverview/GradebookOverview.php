<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;

use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GradebookOverview
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview
 */
class GradebookOverview extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Notenübersicht';
    }

    /**
     * @param TblPerson   $tblPerson
     * @param TblYear $tblYear
     *
     * @return Page
     */
    public function buildPage(TblPerson $tblPerson, TblYear $tblYear): Page
    {
        return (new Page())
            ->addSlice($this->getPageHeaderSlice($tblPerson, $tblYear))
            ->addSlice($this->getGradebookOverviewSlice($tblPerson, $tblYear));
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0')
    {
        $document = new Document();

        foreach ($pageList as $subjectPages) {
            if (is_array($subjectPages)) {
                foreach ($subjectPages as $page) {
                    $document->addPage($page);
                }
            } else {
                $document->addPage($subjectPages);
            }
        }

        return (new Frame())->addDocument($document);
    }

    /**
     * @param TblPerson   $tblPerson
     * @param TblYear $tblYear
     *
     * @return Slice $PageHeader
     */
    public function getPageHeaderSlice(TblPerson $tblPerson, TblYear $tblYear): Slice
    {
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            // Anzeige Klasse + Stammgruppe
            $textCourse = DivisionCourse::useService()->getCurrentMainCoursesByStudentEducation($tblStudentEducation);
        } else {
            $textCourse = '';
        }

        return (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler: ' . $tblPerson->getLastFirstName())
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($textCourse)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Stand: ' . (new DateTime())->format('d.m.Y'))
                        )
                    )
                    , '33%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Schülerübersicht')
                    ->styleAlignCenter()
                    ->styleTextSize('30px')
                    ->styleTextUnderline(), '34%'
                )
                ->addElementColumn((new Element())
                    ->setContent(''), '33%'
                )
            )->stylePaddingBottom('25px');
    }

    /**
     * @param TblPerson   $tblPerson
     * @param TblYear     $tblYear
     *
     * @return Slice
     */
    public function getGradebookOverviewSlice(TblPerson $tblPerson, TblYear $tblYear): Slice
    {
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            return Grade::useService()->getStudentOverviewDataByPerson($tblPerson, $tblYear, $tblStudentEducation, false, true);
        }

        return new Slice();
    }

    /**
     * @param string $content
     * @param bool $hasLeftBorder
     *
     * @return Element
     */
    public static function getHeaderElement(string $content, bool $hasLeftBorder = false): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderRight()
            ->styleBorderLeft($hasLeftBorder ? '1px' : '0px')
            ->styleTextBold()
            ->stylePaddingTop('9.7px')
            ->stylePaddingBottom('9.8px')
            ->styleBackgroundColor('lightgrey');
    }

    /**
     * @param string $content
     * @param bool $isBold
     * @param bool $isBackground
     *
     * @return Element
     */
    public static function getBodyElement(string $content, bool $isBold = false, bool $isBackground = false): Element
    {
        $element = (new Element())
            ->setContent($content)
            ->styleTextSize('10px')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderRight();

        if ($isBold) {
            $element->styleTextBold();
        }

        if ($isBackground) {
            $element->styleBackgroundColor('lightgrey');
        }

        return $element;
    }
}
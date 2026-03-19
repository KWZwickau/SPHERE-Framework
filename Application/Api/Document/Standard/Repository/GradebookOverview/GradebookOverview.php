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
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param string $View
     *
     * @return Page[]
     */
    public function buildPage(TblPerson $tblPerson, TblYear $tblYear, string $View = 'Parent'): array
    {
        $pageList = [];
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYearAndDateWithLeaved($tblPerson, $tblYear))) {
            $dataPdf = Grade::useService()->getStudentOverviewDataByPerson($tblPerson, $tblYear, $tblStudentEducation, $View == 'Parent', true);
            $isTwoPage = $dataPdf['isTwoPage'];
            $headerPdfList = $dataPdf['headerPdfList'];
            $bodyPdfList = $dataPdf['bodyPdfList'];

            if ($isTwoPage) {
                $pageList[] = (new Page())
                    ->addSlice($this->getPageHeaderSlice($tblPerson, $tblYear))
                    ->addSlice($this->getSliceBody([$headerPdfList[1]], false))
                    ->addSlice($this->getSliceBody($bodyPdfList[1]));

                $pageList[] = (new Page())
                    ->addSlice($this->getPageHeaderSlice($tblPerson, $tblYear))
                    ->addSlice($this->getSliceBody([$headerPdfList[2]], false))
                    ->addSlice($this->getSliceBody($bodyPdfList[2]));
            } else {
                $pageList[] = (new Page())
                    ->addSlice($this->getPageHeaderSlice($tblPerson, $tblYear))
                    ->addSlice($this->getSliceBody([$headerPdfList], false))
                    ->addSlice($this->getSliceBody($bodyPdfList));
            }
        }

        return $pageList;
    }

    private function getSliceBody(array $dataList, bool $hasBorderBottom = true): Slice
    {
        $slice = new Slice();
        foreach ($dataList as $row) {
            $section = new Section();
            foreach ($row as $item) {
                $section->addElementColumn($item['Content'], $item['Width']);
            }
            $slice->addSection($section);
        }

        if ($hasBorderBottom) {
            $slice->styleBorderBottom();
        }

        return $slice;
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
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

        // damit 16 Fächer auf die Schülerübersicht passen
        $InjectStyle = 'body { margin-bottom: -1.5cm !important; }';

        return (new Frame($InjectStyle))->addDocument($document);
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
            )->stylePaddingBottom('2px');
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
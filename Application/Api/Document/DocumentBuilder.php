<?php

namespace SPHERE\Application\Api\Document;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;

class DocumentBuilder extends AbstractDocument
{
    private string $name;

    /**
     * @param string $name
     */
    function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param $pageList
     * @param $part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $part = '0'): Frame
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
     * @param array $headerNameList
     * @param array $headerWidthList
     * @param array $dataList
     * @param array|null $preTextList
     *
     * @return Page
     */
    public function getPageList(array $headerNameList, array $headerWidthList, array $dataList, ?array $preTextList = null): Page
    {
        $slice = new Slice();
        if ($preTextList) {
            foreach ($preTextList as $text) {
                $slice->addElement((new Element())->setContent($text));
            }
            $slice->addElement((new Element())->setContent('&nbsp;'));
        }

        $sectionHeader = new Section();
        foreach ($headerNameList as $key => $header) {
            $sectionHeader->addElementColumn((new Element())
                ->setContent($header ?: '&nbsp;')
                ->styleAlignCenter()
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleTextBold()
                , $headerWidthList[$key] ?? '10%');
        }
        $sectionList[] = $sectionHeader;

        foreach ($dataList as $item) {
            $section = new Section();
            foreach ($headerNameList as $key => $header) {
                $content = isset($item[$key]) ? strip_tags($item[$key]) : '&nbsp;';
                $section->addElementColumn((new Element())
                    ->setContent($content ?: '&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderLeft()
                    ->styleBorderTop()
                    , $headerWidthList[$key] ?? '10%');
            }
            $sectionList[] = $section;
        }

        return (new Page())
            ->addSlice($slice)
            ->addSlice((new Slice())
                ->addSectionList($sectionList)
                ->styleBorderRight()
                ->styleBorderBottom()
            );
    }

    /**
     * @param Layout|Layout[] $layouts
     *
     * @return Page
     */
    public function getPageListByLayout(Layout|array $layouts): Page
    {
        if (!is_array($layouts)) {
            $layouts = [$layouts];
        }

        $page = new Page();
        foreach ($layouts as $layout) {
            $page->addSliceArray($this->getSliceListFromLayout($layout));
        }

        return $page;
    }

    /**
     * @param Layout $layout
     *
     * @return Slice[]
     */
    private function getSliceListFromLayout(Layout $layout): array
    {
        $sliceList = [];
        foreach ($layout->getLayoutGroups() as $layoutGroup) {
            $slice = new Slice();
            if (($title = $layoutGroup->getLayoutTitle())) {
                $slice->addElement((new Element())->setContent($title));
            }
            foreach ($layoutGroup->getLayoutRow() as $layoutRow) {
                $section = new Section();
                $sizeSum = 0;
                $checkSum = true;
                foreach ($layoutRow->getLayoutColumn() as $layoutColumn) {
                    if ($layoutColumn->getSize() > 0) {
                        $sizeSum += $layoutColumn->getSize();
                        $size = (($layoutColumn->getSize() / 12) * 100) . '%';
                    } else {
                        $checkSum = false;
                        $size = 'auto';
                    }
                    $sliceColumn = new Slice();
                    foreach ($layoutColumn->getFrontend() as $frontend) {
                        // rekursion, layout kann in einem layout sein
                        if ($frontend instanceof Layout) {
                            $subSection = new Section();
                            foreach ($this->getSliceListFromLayout($frontend) as $subSlice) {
                                $subSection->addSliceColumn($subSlice);
                            }
                            $sliceColumn->addSection($subSection);
                        } else {
                            $item = $this->getElement($frontend);
                            if ($item instanceof Element) {
                                $sliceColumn->addElement($item);
                            } elseif ($item instanceof Slice) {
                                $sliceColumn->addSection((new Section())->addSliceColumn($item));
                            }
                        }
                    }

                    $section->addSliceColumn($sliceColumn, $size);
                }

                // leere Spalte anfügen, falls erforderlich
                if ($checkSum && ($sizeSum < 12)) {
                    $section->addElementColumn((new Element())->setContent('&nbsp;'));
                }

                $slice->addSection($section);
            }

            $sliceList[] = $slice;
        }

        return $sliceList;
    }

    /**
     * @param $frontend
     *
     * @return Element|Slice
     */
    private function getElement($frontend): Element|Slice
    {
        $padding = '4px';
        $paddingLeft = '10px';
        $margin = '4px';

        if ($frontend instanceof Panel) {
            $slice = new Slice();
            $borderColor = $frontend->getBorderColor();
            if ($frontend->getTitle()) {
                $slice->addElement((new Element())
                    ->setContent($frontend->getTitle())
                    ->styleBackgroundColor($frontend->getHeaderBackgroundColor())
                    ->styleTextColor($frontend->getHeaderTextColor())
                    ->stylePaddingTop($padding)
                    ->stylePaddingBottom($padding)
                    ->stylePaddingLeft($paddingLeft)
                    ->styleTextBold()
                    ->styleBorderTop('1px', $borderColor, 'solid', '5px 5px 0px 0px')
                    ->styleBorderLeft('1px', $borderColor)
                    ->styleBorderRight('1px', $borderColor)
                );
            }
            if (($panelElements = $frontend->getElementList())) {
                $count = 0;
                foreach ($panelElements as $panelElement) {
                    $count++;
                    $element = (new Element())
                        ->setContent($panelElement)
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderTop('1px', $borderColor)
                        ->styleBorderLeft('1px', $borderColor)
                        ->styleBorderRight('1px', $borderColor);
                    if (count($panelElements) == $count) {
                        $element->styleBorderBottom('1px', $borderColor, 'solid', '0px 0px 5px 5px');
                    }
                    $slice->addElement($element);
                }
            }

            return $slice
                ->styleMarginTop($margin)
                ->styleMarginBottom($margin)
                ->styleMarginLeft($margin)
                ->styleMarginRight($margin);
        } elseif ($frontend instanceof Title) {
            return (new Element())
                ->setContent($frontend->getTitle())
                ->styleMarginTop('10px')
                ->styleMarginBottom($margin)
                ->stylePaddingLeft($padding)
                ->styleTextSize('15px')
                ->styleTextBold()
                ->styleBorderBottom();
        } else {
            return (new Element())
                ->setContent($frontend)
                ->styleMarginTop($margin)
                ->styleMarginBottom($margin)
                ->styleMarginLeft($margin)
                ->styleMarginRight($margin);
        }
    }
}
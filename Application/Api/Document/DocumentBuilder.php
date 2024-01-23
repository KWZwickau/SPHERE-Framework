<?php

namespace SPHERE\Application\Api\Document;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

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
                ->setContent($header)
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
}
<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\ClassRegister;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class ClassRegister extends AbstractDocument
{
    private ?TblDivision $tblDivision = null;
    private ?TblGroup $tblGroup = null;
    private ?TblCompany $tblCompany = null;
    private ?TblYear $tblYear = null;
    private string $name = '&nbsp;';
    private string $displayName = '&nbsp;';
    private string $typeName = '&nbsp;';
    private string $tudors = '&nbsp;';
    private $tblPersonList = array();

    public function __construct(?TblDivision $tblDivision, ?TblGroup $tblGroup)
    {
        if ($tblDivision) {
            $this->tblDivision = $tblDivision;
            $this->name = 'Klassentagebuch';
            $this->typeName = 'Klasse';
            $this->displayName = $tblDivision->getDisplayName();
            $this->tblCompany = ($tblDivision->getServiceTblCompany()) ?: null;
            $this->tblYear = ($tblDivision->getServiceTblYear()) ?: null;
            if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))) {
                $teachers = array();
                foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                    if ($tblPerson = $tblDivisionTeacher->getServiceTblPerson()) {
                        $teachers[] = $tblPerson->getFullName();
                    }
                }
                if (!empty($teachers)) {
                    $this->tudors = implode(', ', $teachers);
                }
            }
            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                $this->tblPersonList = $tblPersonList;
            }
        } elseif ($tblGroup) {
            $this->tblGroup = $tblGroup;
            $this->name = 'Stammgruppentagebuch';
            $this->typeName = 'Gruppe';
            $this->displayName = $tblGroup->getName();
            $this->tblCompany = ($tblGroup->getCurrentCompanySingle()) ?: null;
            $this->tblYear = ($tblGroup->getCurrentYear()) ?: null;
            $this->tudors = $tblGroup->getTudorsString(false);
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                $this->tblPersonList = (new Extension)->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringNaturalOrderSorter());
            }
        }
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name . ' ' . $this->displayName;
    }

    /**
     * @param array  $pageList
     * @param string $part
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
     * @return Page[]
     */
    public function getPageList(): array
    {
        $pageList[] = $this->getCoverSheet();
        $pageList[] = new Page();
        $pageList[] = $this->getFirstPage();
        $pageList[] = $this->getStudentPage(true);
        $pageList[] = $this->getStudentPage(false);
        if ($this->tblDivision) {
            $pageList[] = $this->getRepresentativeHolidayPage();
        }

        return $pageList;
    }

    /**
     * @return Page
     */
    public function getCoverSheet(): Page
    {
        $textSize = '16px';
        $borderPercentage = '15%';

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($this->name)
                    ->styleTextSize('30px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20%')
                    ->styleTextBold()
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element()), $borderPercentage)
                    ->addElementColumn((new Element())
                        ->setContent('Schule')
                        ->styleMarginTop('20px')
                        ->styleBorderBottom()
                        ->styleTextSize($textSize)
                        , '13%')
                    ->addElementColumn((new Element())
                        ->setContent($this->tblCompany ? $this->tblCompany->getDisplayName() : '&nbsp;')
                        ->styleMarginTop('20px')
                        ->styleBorderBottom()
                        ->styleTextSize($textSize)
                    )
                    ->addElementColumn((new Element()), $borderPercentage)
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element()), $borderPercentage)
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr')
                        ->styleMarginTop('20px')
                        ->styleBorderBottom()
                        ->styleTextSize($textSize)
                        , '13%')
                    ->addElementColumn((new Element())
                        ->setContent($this->tblYear ? $this->tblYear->getName() : '&nbsp;')
                        ->styleMarginTop('20px')
                        ->styleBorderBottom()
                        ->styleTextSize($textSize)
                    )
                    ->addElementColumn((new Element()), $borderPercentage)
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element()), $borderPercentage)
                    ->addElementColumn((new Element())
                        ->setContent($this->typeName)
                        ->styleMarginTop('20px')
                        ->styleBorderBottom()
                        ->styleTextSize($textSize)
                        , '13%')
                    ->addElementColumn((new Element())
                        ->setContent($this->displayName)
                        ->styleMarginTop('20px')
                        ->styleBorderBottom()
                        ->styleTextSize($textSize)
                    )
                    ->addElementColumn((new Element()), $borderPercentage)
                )
            );
    }

    /**
     * @return Page
     */
    private function getFirstPage(): Page
    {
        if ($this->tblCompany && ($tblAddress = $this->tblCompany->fetchMainAddress())) {
            $address = $tblAddress->getGuiTwoRowString();
        } else {
            $address = '&nbsp;';
        }

        $textSize = '10px';
        $paddingLeft = '5px';
        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->styleHeight('122px')
                        ->styleBorderTop()
                        ->styleBorderBottom()
                        ->styleBorderLeft()
                        ->addElement((new Element())
                            ->setContent('Name und Ort der Schule')
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->tblCompany ? $this->tblCompany->getDisplayName() : '&nbsp;')
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($address)
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                        )
                    , '50%')
                    ->addSliceColumn((new Slice())
                        ->styleHeight('122px')
                        ->styleBorderAll()
                        ->addElement((new Element())
                            ->setContent($this->typeName)
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->displayName)
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                            ->styleBorderBottom()
                        )
                        ->addElement((new Element())
                            ->setContent('Schuljahr')
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->tblYear ? $this->tblYear->getName() : '&nbsp;')
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                            ->styleBorderBottom()
                        )
                        ->addElement((new Element())
                            ->setContent($this->tblDivision ? 'Klassenlehrer/in' : 'Tudor/in')
                            ->styleTextSize($textSize)
                            ->stylePaddingLeft($paddingLeft)
                        )
                        ->addElement((new Element())
                            ->setContent($this->tudors)
                            ->styleMarginTop('5px')
                            ->stylePaddingLeft($paddingLeft)
                        )
                        , '50%')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($this->name)
                    ->styleMarginTop('25px')
                    ->styleTextSize('30px')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Unterrichtete Fächer / Unterrichtende Lehrer/innen')
                    ->styleMarginTop('25px')
                    ->styleTextSize('18px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getLectureshipSlice())
            ;
    }

    /**
     * @return Slice
     */
    private function getLectureshipSlice(): Slice
    {
        if ($this->tblDivision) {
            $dataList = Digital::useService()->getSubjectsAndLectureshipByDivisionForDownload($this->tblDivision);
        } elseif ($this->tblGroup) {
            $dataList = array();
            if (($tblDivisionList = $this->tblGroup->getCurrentDivisionList())) {
                foreach ($tblDivisionList as $tblDivision) {
                    foreach (Digital::useService()->getSubjectsAndLectureshipByDivisionForDownload($tblDivision) as $acronymId => $item) {
                        if (isset($dataList[$acronymId])) {
                            foreach ($item['TeacherArray'] as $personId => $name) {
                                if (!isset($dataList[$acronymId]['TeacherArray'][$personId])) {
                                    $dataList[$acronymId]['TeacherArray'][$personId] = $name;
                                }
                            }
                        } else {
                            $dataList[$acronymId] = $item;
                        }
                    }
                }
            }
            ksort($dataList);
        } else {
            $dataList = array();
        }

        $slice = (new Slice())
            ->styleMarginTop('5px')
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Unterrichtsfach')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , '50%')
                ->addElementColumn((new Element())
                    ->setContent('Lehrer/in')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , '50%')
//                ->addElementColumn((new Element())
//                    ->setContent('Fach-Gruppe')
//                    ->stylePaddingLeft('5px')
//                    ->stylePaddingTop('10px')
//                    ->stylePaddingBottom('10px')
//                    ->styleBackgroundColor('#CCC')
//                    ->styleBorderTop()
//                    ->styleBorderBottom()
//                    , '25%')
//                ->addElementColumn((new Element())
//                    ->setContent('Fach-Gruppen-Lehrer')
//                    ->styleAlignRight()
//                    ->stylePaddingRight('5px')
//                    ->stylePaddingTop('10px')
//                    ->stylePaddingBottom('10px')
//                    ->styleBackgroundColor('#CCC')
//                    ->styleBorderTop()
//                    ->styleBorderBottom()
//                    ->styleBorderRight()
//                    , '25%')
            );

        foreach ($dataList as $data) {
            if (count($data['TeacherArray']) > 2) {
                $height = '34px';
            } else {
                $height = 'auto';
            }
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($data['Subject'])
                    ->styleHeight($height)
                    ->stylePaddingLeft('5px')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , '50%')
                ->addElementColumn((new Element())
                    ->setContent(empty($data['TeacherArray']) ? '&nbsp;' : implode(', ', $data['TeacherArray']))
                    ->styleHeight($height)
                    ->stylePaddingLeft('5px')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , '50%')
            );
        }

        return $slice;
    }

    /**
     * @param bool $IsAddress
     *
     * @return Page
     */
    public function getStudentPage(bool $IsAddress): Page
    {
        $width[1] = '6%';
        $width[2] = '30%';
        $width[3] = '64%';

        $slice = (new Slice())
            ->styleMarginTop('5px')
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Lfd.' . new Container('Nr.'))
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('Familienname, Vorname')
                    ->styleAlignCenter()
                    ->stylePaddingTop('18.5px')
                    ->stylePaddingBottom('18.5px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[2])
                ->addElementColumn((new Element())
                    ->setContent($IsAddress
                        ? 'Wohnanschrift'
                        : 'Telefonnummer, unter der die Eltern / Personensorgeberechtigten im Notfall zu erreichen sind'
                    )
                    ->styleAlignCenter()
                    ->stylePaddingTop($IsAddress ? '18.5px' : '10px')
                    ->stylePaddingBottom($IsAddress ? '18.5px' : '10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[3])
            );

        $count = 0;
        /** @var TblPerson $tblPerson */
        foreach ($this->tblPersonList as $tblPerson) {
            if ($IsAddress) {
                $content = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : '&nbsp;';
                $textSize = '14px';
                $padding = '5px';
                $height = 'auto';
            } else {
                // Kontakt-Daten
                $contacts = array();
                $contacts = Person::useService()->getContactDataFromPerson($tblPerson, $contacts);
                $content = $contacts['Phone'] ? str_replace('<br>', '; ', $contacts['Phone']) : '&nbsp;';
                $textSize = '9px';
                $padding = '1px';
                $height = '21px';
            }

            $count++;
            $backgroundColor = $count % 2 ? '#FFF' : '#CCC';
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($count)
                    ->styleBackgroundColor($backgroundColor)
                    ->styleAlignCenter()
                    ->stylePaddingLeft('5px')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent($tblPerson->getLastFirstName())
                    ->styleBackgroundColor($backgroundColor)
                    ->stylePaddingLeft('5px')
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[2])
                ->addElementColumn((new Element())
                    ->setContent($content)
                    ->styleTextSize($textSize)
                    ->styleHeight($height)
                    ->styleBackgroundColor($backgroundColor)
                    ->stylePaddingLeft($padding)
                    ->stylePaddingTop($padding)
                    ->stylePaddingBottom('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[3])
            );
        }

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Schülerverzeichnis')
                    ->styleTextBold()
                    ->styleTextSize('18px')
                )
            )
            ->addSlice($slice);
    }

    /**
     * @return Page
     */
    public function getRepresentativeHolidayPage(): Page
    {
        $width[1] = '20%';
        $width[2] = '80%';

        $padding = '5px';

        $slice = (new Slice())
            ->styleMarginTop('5px')
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('Amtsinhaber/in')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[2])
            );

        // Elternvertreter
        if ($this->tblDivision && ($tblCustodyList = Division::useService()->getCustodyAllByDivision($this->tblDivision))) {
            $CustodyList = array();
            $count = 0;
            foreach ($tblCustodyList as $tblPerson) {
                $Description = Division::useService()->getDivisionCustodyByDivisionAndPerson($this->tblDivision, $tblPerson)->getDescription();
                $CustodyList[$count++] = $tblPerson->getFullName() . ($Description ? ' (' . $Description . ')' : '');
            }
        }
        // Klassensprecher
        if ($this->tblDivision && ($tblDivisionRepresentativeList = Division::useService()->getDivisionRepresentativeByDivision($this->tblDivision))) {
            $RepresentativeList = array();
            $count = 0;
            foreach($tblDivisionRepresentativeList as $tblDivisionRepresentative){
                $tblPersonRepresentative = $tblDivisionRepresentative->getServiceTblPerson();
                $Description = $tblDivisionRepresentative->getDescription();
                $RepresentativeList[$count++] = $tblPersonRepresentative->getFirstSecondName() . ' ' . $tblPersonRepresentative->getLastName()
                    . ($Description ? ' (' . $Description . ')' : '');
            }
        }

        $subSliceCustody = new Slice();
        $subSliceRepresentative = new Slice();
        for ($i = 0; $i < 5; $i++) {
            $subSliceCustody
                ->addElement((new Element())
                    ->setContent($CustodyList[$i] ?? '&nbsp;')
                    ->styleHeight('30px')
                    ->stylePaddingLeft($padding)
                    ->stylePaddingTop($padding)
                    ->styleBorderBottom()
                    ->styleBorderRight()
                );
            $subSliceRepresentative
                ->addElement((new Element())
                    ->setContent($RepresentativeList[$i] ?? '&nbsp;')
                    ->styleHeight('30px')
                    ->stylePaddingLeft($padding)
                    ->stylePaddingTop($padding)
                    ->styleBorderBottom()
                    ->styleBorderRight()
                );
        }

        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Elternvertreter/in')
                    ->stylePaddingLeft('5px')
                    ->stylePaddingTop('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    ->styleHeight('174px')
                , $width[1])
                ->addSliceColumn($subSliceCustody, $width[2])
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Klassensprecher/in')
                    ->stylePaddingLeft('5px')
                    ->stylePaddingTop('5px')
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    ->styleHeight('174px')
                    , $width[1])
                ->addSliceColumn($subSliceRepresentative, $width[2])
            );

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Elternvertreter / Klassensprecher')
                    ->styleTextBold()
                    ->styleTextSize('18px')
                )
            )
            ->addSlice($slice)
            ->addSliceArray($this->getHolidaySliceList());
    }

    /**
     * @return Slice[]
     */
    private function getHolidaySliceList(): array
    {
        $width[1] = '60%';
        $width[2] = '40%';

        $slice = (new Slice())
            ->styleMarginTop('5px')
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zeit')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[1])
                ->addElementColumn((new Element())
                    ->setContent('vom - bis')
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->stylePaddingBottom('10px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[2])
            );

        if ($this->tblYear && $this->tblCompany) {
            $list = array();
            if (($tblYearHolidayAllByYearAndCompany = Term::useService()->getYearHolidayAllByYear($this->tblYear, $this->tblCompany))) {
                $list = $tblYearHolidayAllByYearAndCompany;
            }
            if (($tblYearHolidayAllByYear = Term::useService()->getYearHolidayAllByYear($this->tblYear))) {
                $list = array_merge($list, $tblYearHolidayAllByYear);
            }

            $tblHolidayList = array();
            foreach ($list as $tblYearHoliday) {
                if (($item = $tblYearHoliday->getTblHoliday())) {
                    $tblHolidayList[$item->getId()] = $item;
                }
            }
            // sort
            $tblHolidayList = (new Extension())->getSorter($tblHolidayList)->sortObjectBy('FromDate', new DateTimeSorter());
            foreach ($tblHolidayList as $tblHoliday) {
                $slice
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($tblHoliday->getName())
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingBottom('5px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , $width[1])
                        ->addElementColumn((new Element())
                            ->setContent($tblHoliday->getFromDate() . ($tblHoliday->getToDate() ? ' - ' . $tblHoliday->getToDate() : ' '))
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingBottom('5px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , $width[2])
                    );
            }
        }

        $sliceList[] = ((new Slice())
            ->styleMarginTop('30px')
            ->addElement((new Element())
                ->setContent('Ferien')
                ->styleTextBold()
                ->styleTextSize('18px')
            )
        );

        $sliceList[] = $slice;

        return $sliceList;
    }
}
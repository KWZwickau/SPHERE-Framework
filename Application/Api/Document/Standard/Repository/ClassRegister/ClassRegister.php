<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\ClassRegister;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
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
    protected ?TblDivision $tblDivision = null;
    private ?TblGroup $tblGroup = null;
    private ?TblYear $tblYear = null;
    protected ?TblCompany $tblCompany = null;
    private array $tblCompanyList = array();
    protected string $name = '&nbsp;';
    protected string $displayName = '&nbsp;';
    private string $typeName = '&nbsp;';
    private string $tudors = '&nbsp;';
    protected array $tblPersonList = array();
    protected array $personNumberAbsenceList = array();

    private array $dayName = array(
        '0' => 'Sonntag',
        '1' => 'Montag',
        '2' => 'Dienstag',
        '3' => 'Mittwoch',
        '4' => 'Donnerstag',
        '5' => 'Freitag',
        '6' => 'Samstag',
    );

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     */
    public function __construct(?TblDivision $tblDivision, ?TblGroup $tblGroup)
    {
        if ($tblDivision) {
            $this->tblDivision = $tblDivision;
            $this->name = 'Klassentagebuch';
            $this->typeName = 'Klasse';
            $this->displayName = $tblDivision->getDisplayName();
            $this->tblYear = ($tblDivision->getServiceTblYear()) ?: null;
            if (($this->tblCompany = ($tblDivision->getServiceTblCompany()) ?: null)) {
                $this->tblCompanyList[] = $this->tblCompany;
            }

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
            $this->tblYear = ($tblGroup->getCurrentYear()) ?: null;
            $this->tblCompany = ($tblGroup->getCurrentCompanySingle()) ?: null;
            $this->tblCompanyList = ($tblGroup->getCurrentCompanyList()) ?: array();
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
        // ist erforderlich für Anzeige der Fehlzeiten Schülernummer
        $pageList[] = $this->getStudentPage(true);
        $pageList[] = $this->getStudentPage(false);
        $pageList[] = $this->getRepresentativeHolidayPage();
        $pageList[] = new Page();
        $this->setLessonContentPageList($pageList);
        $this->setInstructionPageList($pageList);

        return $pageList;
    }

    /**
     * @return Page
     */
    private function getCoverSheet(): Page
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
    protected function getStudentPage(bool $IsAddress): Page
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
            $count++;

            if ($IsAddress) {
                $content = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : '&nbsp;';
                $textSize = '14px';
                $padding = '5px';
                $height = 'auto';

                // für Fehlzeitenanzeige erforderlich
                $this->personNumberAbsenceList[$tblPerson->getId()] = $count;
            } else {
                // Kontakt-Daten
                $contacts = array();
                $contacts = Person::useService()->getContactDataFromPerson($tblPerson, $contacts);
                $content = $contacts['Phone'] ? str_replace('<br>', '; ', $contacts['Phone']) : '&nbsp;';
                $textSize = '9px';
                $padding = '1px';
                $height = '21px';
            }

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
    private function getRepresentativeHolidayPage(): Page
    {
        $page = (new Page());
        if ($this->tblDivision) {
            $page->addSliceArray($this->getRepresentativeSliceList());
        }
        $page->addSliceArray($this->getHolidaySliceList());

        return $page;
    }

    /**
     * @return array
     */
    private function getRepresentativeSliceList(): array
    {
        $width[1] = '20%';
        $width[2] = '80%';

        $padding = '5px';

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent('Elternvertreter / Klassensprecher')
                ->styleTextBold()
                ->styleTextSize('18px')
            );

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

        $sliceList[] = $slice;

        return $sliceList;
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

        if ($this->tblYear) {
            $list = array();
            if ($this->tblCompany && ($tblYearHolidayAllByYearAndCompany = Term::useService()->getYearHolidayAllByYear($this->tblYear, $this->tblCompany))) {
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

    /**
     * @param array $pageList
     */
    private function setLessonContentPageList(array &$pageList)
    {
        if ($this->tblYear) {
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($this->tblYear);
            if ($startDate && $endDate) {
                $dayOfWeek = $startDate->format('w');

                // wenn Schuljahresbeginn ein Samstag oder Sonntag dann beginne mit der nächsten Woche
                if ($dayOfWeek == 6 || $dayOfWeek == 0) {
                    $startDate->add(new DateInterval('P7D'));
                }
                $startDate = Timetable::useService()->getStartDateOfWeek($startDate);

                $sliceList = array();
                while ($startDate <= $endDate) {
                    $dateString = $startDate->format('d.m.Y');
                    $dayOfWeek = $startDate->format('w');

                    // Samstag und Sonntag überspringen
                    if ($dayOfWeek == 6 || $dayOfWeek == 0) {
                        $startDate->add(new DateInterval('P1D'));
                        continue;
                    }

                    // Prüfung, ob die gesamte Woche Ferien sind, dann diese überspringen
                    if ($dayOfWeek == 1 && Term::useService()->getIsSchoolWeekHoliday($dateString, $this->tblYear, $this->tblCompanyList)) {
                        $startDate->add(new DateInterval('P7D'));
                        continue;
                    }

                    // Montag und Donnerstag
                    if ($dayOfWeek == 1 || $dayOfWeek == 4) {
                        if (!empty($sliceList)) {
                            $pageList[] = (new Page())->addSliceArray($sliceList);
                            $sliceList = array();
                        }
                        // Montag
                        if ($dayOfWeek == 1) {
                            $sliceList[] = $this->getHeaderWeekSlice($dateString);
                        }
                        $sliceList[] = $this->getLessonContentHeaderSlice();
                    }

                    $sliceList[] = $this->getLessonContentDaySlice($startDate, $dayOfWeek);

                    // Freitag
                    if ($dayOfWeek == 5) {
                        $sliceList[] = $this->getWeekSummarySlice($startDate);
                    }

                    $startDate->add(new DateInterval('P1D'));
                }

                // letzte Seite hinzufügen
                if (!empty($sliceList)) {
                    $pageList[] = (new Page())->addSliceArray($sliceList);
                }
            }
        }
    }

    /**
     * @param DateTime $dateTime
     * @param int $dayOfWeek
     *
     * @return Slice
     */
    private function getLessonContentDaySlice(DateTime $dateTime, int $dayOfWeek): Slice
    {
        $width[1] = '4%';
        $width[2] = '4%';
        $width[3] = '10%';
        $width[4] = '32%';
        $width[5] = '32%';
        $width[6] = '10%';
        $width[7] = '8%';

        // unterrichtsfreier Tag
        if ($this->tblYear && $this->tblCompany
            && ($tblHoliday = Term::useService()->getHolidayByDay($this->tblYear, $dateTime, $this->tblCompany))
        ) {
            $count = 10;
            $isHoliday = true;

            $slice = (new Slice())
                ->styleBorderAll()
                ->styleHeight(($count * 28) . 'px')
                ->styleBackgroundColor('#EEE')
                ->addElement((new Element())
                    ->setContent($tblHoliday->getName() . ' (' . $tblHoliday->getTblHolidayType()->getName() . ')')
                    ->stylePaddingTop('120px')
                    ->styleAlignCenter()
                );
        } else {
            $isHoliday = false;

            // Fehlzeiten für den Tag ermitteln
            $divisionList = $this->tblDivision ? array('0' => $this->tblDivision) : array();
            $groupList = $this->tblGroup ? array('0' => $this->tblGroup) : array();
            $absenceContent = array();
            if (($AbsenceList = Absence::useService()->getAbsenceAllByDay($dateTime, null, null, $divisionList, $groupList,
                $hasTypeOption, null)
            )) {
                foreach ($AbsenceList as $Absence) {
                    if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
                        if (($tblPerson = $tblAbsence->getServiceTblPerson()) && isset($this->personNumberAbsenceList[$tblPerson->getId()])) {
                            $item = $this->personNumberAbsenceList[$tblPerson->getId()];
                            if (($tblAbsenceLessonList = Absence::useService()->getAbsenceLessonAllByAbsence($tblAbsence))) {
                                foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                                    if (!isset($absenceContent[$tblAbsenceLesson->getLesson()])) {
                                        $absenceContent[$tblAbsenceLesson->getLesson()] = array('0' => $item);
                                    } else {
                                        $absenceContent[$tblAbsenceLesson->getLesson()][] = $item;
                                    }
                                }
                            } else {
                                if (!isset($absenceContent['Day'])) {
                                    $absenceContent['Day'] = array('0' => $item);
                                } else {
                                    $absenceContent['Day'][] = $item;
                                }
                            }
                        }
                    }
                }
            }

            $count = 0;
            $slice = (new Slice())
                ->styleBorderTop()
                ->styleBorderLeft();
            for ($i = 1; $i < 11; $i++) {
                // mehrere UEs zur selben Zeit sind möglich
                if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDateAndLesson($dateTime, $i, $this->tblDivision, $this->tblGroup))) {
                    foreach ($tblLessonContentList as $tblLessonContent) {
                        $absence = '';
                        if (isset($absenceContent['Day'])) {
                            $absence .= implode(', ', $absenceContent['Day']);
                        }
                        if (isset($absenceContent[$i])) {
                            $absence .= ($absence ? ', ' : '') . implode(', ', $absenceContent[$i]);
                        }

                        $slice->addSection((new Section())
                            ->addElementColumn($this->getElement($i . '.')->styleAlignCenter(), $width[2])
                            ->addElementColumn($this->getElement($tblLessonContent->getDisplaySubject(true)), $width[3])
                            ->addElementColumn($this->getElement($tblLessonContent->getContent(), 32, 80), $width[4])
                            ->addElementColumn($this->getElement($tblLessonContent->getHomework(), 32, 80), $width[5])
                            ->addElementColumn($this->getElement($absence, 11), $width[6])
                            ->addElementColumn($this->getElement($tblLessonContent->getTeacherString()), $width[7])
                        );
                        $count++;
                    }
                } else {
                    $slice->addSection((new Section())
                        ->addElementColumn($this->getElement($i . '.')->styleAlignCenter(), $width[2])
                        ->addElementColumn($this->getElement('&nbsp;'), $width[3])
                        ->addElementColumn($this->getElement('&nbsp;'), $width[4])
                        ->addElementColumn($this->getElement('&nbsp;'), $width[5])
                        ->addElementColumn($this->getElement('&nbsp;'), $width[6])
                        ->addElementColumn($this->getElement('&nbsp;'), $width[7])
                    );
                    $count++;
                }
            }
        }

        return (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->styleBorderLeft()
                    ->styleBorderTop()
                    ->addElement((new Element())
                        ->setContent($this->setRotatedContent($this->dayName[$dayOfWeek]))
                        ->styleHeight(($count * 28) . 'px')
                        ->styleBorderBottom()
                        ->styleBackgroundColor($isHoliday ? '#EEE' : '#FFF')
                    )
                    , $width[1])
                ->addSliceColumn($slice)
            );
    }

    /**
     * @param DateTime $dateTime
     *
     * @return Slice
     */
    private function getWeekSummarySlice(DateTime $dateTime): Slice
    {
        list($fromDate, $canceledSubjectList, $additionalSubjectList, $subjectList)
            = Digital::useService()->getCanceledSubjectList($dateTime, $this->tblDivision, $this->tblGroup);

        $slice = (new Slice())->styleBorderLeft();
        if ($subjectList) {
            ksort($subjectList);

            $width = 18;
            $widthString = $width . '%';
            $widthItemString  = ((100.0 - $width) / count($subjectList)) . '%';

            $sectionHeader = (new Section())->addElementColumn($this->getHeaderElement('Fach'), $widthString);
            $sectionCanceled = (new Section())->addElementColumn($this->getElement('Anzahl ausgefallene Stunden', 10), $widthString);
            $sectionAdditional = (new Section())->addElementColumn($this->getElement('Anzahl zusätzlich erteilte Stunden', 10), $widthString);

            foreach ($subjectList as $acronym => $subject) {
                $sectionHeader->addElementColumn($this->getHeaderElement($acronym), $widthItemString);
                $sectionCanceled->addElementColumn($this->getElement($canceledSubjectList[$acronym] ?? 0)->styleAlignCenter(), $widthItemString);
                $sectionAdditional->addElementColumn($this->getElement($additionalSubjectList[$acronym] ?? 0)->styleAlignCenter(), $widthItemString);
            }

            $slice
                ->addSection($sectionHeader)
                ->addSection($sectionCanceled)
                ->addSection($sectionAdditional);
        }

        $remark = '';
        $descriptionDivisionTeacher = 'Klassenlehrerin/Klassenlehrer';
        $descriptionHeadmaster = 'Schulleiterin/Schulleiter';
        $divisionTeacher = '&nbsp;';
        $headmaster = '&nbsp;';
        if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($this->tblDivision, $this->tblGroup, $fromDate))) {
            $remark = str_replace("\n", '<br/>', $tblLessonWeek->getRemark());
            if ($tblLessonWeek->getDateDivisionTeacher()) {
                $divisionTeacher = $tblLessonWeek->getDateDivisionTeacher();
                if (($tblPersonDivisionTeacher = $tblLessonWeek->getServiceTblPersonDivisionTeacher())) {
                    $divisionTeacher .= ' ' . $tblPersonDivisionTeacher->getLastName();
                    if (($salutation = $tblPersonDivisionTeacher->getSalutation())) {
                        if($salutation == 'Frau') {
                            $descriptionDivisionTeacher = 'Klassenlehrerin';
                        } elseif ($salutation == 'Herr') {
                            $descriptionDivisionTeacher = 'Klassenlehrer';
                        }
                    }
                }
            }
            if ($tblLessonWeek->getDateHeadmaster()) {
                $headmaster = $tblLessonWeek->getDateHeadmaster();
                if (($tblPersonHeadmaster = $tblLessonWeek->getServiceTblPersonHeadmaster())) {
                    $headmaster .= ' ' . $tblPersonHeadmaster->getLastName();
                    if (($salutation = $tblPersonHeadmaster->getSalutation())) {
                        if($salutation == 'Frau') {
                            $descriptionHeadmaster = 'Schulleiterin';
                        } elseif ($salutation == 'Herr') {
                            $descriptionHeadmaster = 'Schulleiter';
                        }
                    }
                }
            }
        }

        // Wochenbemerkung
        $slice->addElement((new Element())
            ->setContent($remark ?: '&nbsp;')
            ->stylePaddingTop('5px')
            ->stylePaddingLeft('5px')
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleHeight('200px')
        );

        // Bestätigung KL und SL
        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Für die Vollständigkeit der Angaben:')
                    ->stylePaddingTop('2px')
                    ->stylePaddingLeft('8px')
                    ->stylePaddingBottom('15px')
                    ->styleBorderRight()
                    , '50%')
                ->addElementColumn((new Element())
                    ->setContent('Kenntnis genommen:')
                    ->stylePaddingTop('2px')
                    ->stylePaddingLeft('8px')
                    ->stylePaddingBottom('15px')
                    ->styleBorderRight()
                    , '50%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())->setContent('&nbsp;'), '1%')
                ->addElementColumn((new Element())
                    ->setContent($divisionTeacher)
                    ->styleBorderBottom('0.5px')
                    ->stylePaddingLeft('5px')
                    , '47%')
                ->addElementColumn((new Element())->setContent('&nbsp;')->styleBorderRight(), '2%')

                ->addElementColumn((new Element())->setContent('&nbsp;'), '1%')
                ->addElementColumn((new Element())
                    ->setContent($headmaster)
                    ->styleBorderBottom('0.5px')
                    ->stylePaddingLeft('5px')
                    , '47%')
                ->addElementColumn((new Element())->setContent('&nbsp;')->styleBorderRight(), '2%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($descriptionDivisionTeacher)
                    ->styleTextSize('10px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingLeft('8px')
                    ->stylePaddingBottom('5px')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '50%')
                ->addElementColumn((new Element())
                    ->setContent($descriptionHeadmaster)
                    ->styleTextSize('10px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingLeft('8px')
                    ->stylePaddingBottom('5px')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    , '50%')
            );


        return $slice;
    }

    /**
     * @param string $name
     * @param int $maxStrLengthLine
     * @param int $cutLength
     *
     * @return Element
     */
    private function getElement(string $name, int $maxStrLengthLine = 0, int $cutLength = 0): Element
    {
        if ($maxStrLengthLine && strlen($name) > $maxStrLengthLine) {
            // Zulange Texte abschneiden
            if ($cutLength && strlen($name) > $cutLength) {
                $name = substr($name, 0, $cutLength);
            }
            return (new Element())
                ->setContent($name !== '' ? $name : '&nbsp;')
                ->styleTextSize('10px')
                ->styleHeight('27px')
                ->styleBorderBottom()
                ->styleBorderRight()
                ->stylePaddingLeft('3px');
        }

        return (new Element())
            ->setContent($name !== '' ? $name : '&nbsp;')
            ->styleBorderBottom()
            ->styleBorderRight()
            ->stylePaddingLeft('3px')
            ->stylePaddingTop('5px')
            ->stylePaddingBottom('5px');
    }

    /**
     * @param string $text
     * @param string $paddingTop
     * @param string $paddingLeft
     * @param string $paddingRight
     *
     * @return string
     */
    protected function setRotatedContent(string $text = '&nbsp;', string $paddingTop = '-45px', string $paddingLeft = '-90px', string $paddingRight = ''): string
    {
        return
            '<div style="padding-top: ' . $paddingTop . '!important;'
            . 'padding-left: ' . $paddingLeft . '!important;'
            . ($paddingRight ? 'padding-right: ' . $paddingRight . '!important;' : '')
            . 'white-space: nowrap;'
            . 'transform: rotate(-90deg)!important;'
            . '">'
            . $text
            . '</div>';
    }

    /**
     * @return Slice
     */
    private function getLessonContentHeaderSlice(): Slice
    {
        $width[1] = '4%';
        $width[2] = '4%';
        $width[3] = '10%';
        $width[4] = '32%';
        $width[5] = '32%';
        $width[6] = '10%';
        $width[7] = '8%';

        return (new Slice())
            ->styleMarginTop('5px')
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn($this->getHeaderElement('&nbsp;'), $width[1])
                ->addElementColumn($this->getHeaderElement('Std.'), $width[2])
                ->addElementColumn($this->getHeaderElement('Fach'), $width[3])
                ->addElementColumn($this->getHeaderElement('Unterrichtsgegenstand'), $width[4])
                ->addElementColumn($this->getHeaderElement('Hausaufgaben'), $width[5])
                ->addElementColumn((new Element())
                    ->setContent('Fehlende' . new Container('SuS (Nr.)'))
                    ->styleAlignCenter()
                    ->stylePaddingTop('1.5px')
                    ->stylePaddingBottom('1.5px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , $width[6])
                ->addElementColumn($this->getHeaderElement('Signum'), $width[7])
            );
    }

    /**
     * @param string $name
     *
     * @return Element
     */
    private function getHeaderElement(string $name): Element
    {
        return (new Element())
            ->setContent($name)
            ->styleAlignCenter()
            ->stylePaddingTop('10px')
            ->stylePaddingBottom('10px')
            ->styleBackgroundColor('#CCC')
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderRight();
    }

    /**
     * @param string $fromDateString
     *
     * @return Slice
     */
    private function getHeaderWeekSlice(string $fromDateString): Slice
    {
        $toDate = new DateTime($fromDateString);
        $toDate = $toDate->add(new DateInterval('P4D'));

        return (new Slice())->addElement((new Element())
            ->setContent('Wochenbericht im Klassenbuch vom ' . $fromDateString . ' bis ' . $toDate->format('d.m.Y'))
            ->styleTextBold()
        );
    }

    /**
     * @param array $pageList
     */
    private function setInstructionPageList(array &$pageList)
    {
        $count = 0;
        $sliceList = array();
        if (($tblInstructionList = Instruction::useService()->getInstructionAll())) {
            foreach ($tblInstructionList as $tblInstruction) {
                $count++;
                $sliceList[] = $this->getInstructionSlice($tblInstruction);

                // Neue Seite
                if ($count == 6) {
                    $count = 0;
                    $pageList[] = (new Page())
                        ->addSlice($this->getInstructionHeaderSlice())
                        ->addSliceArray($sliceList);
                    $sliceList = array();
                }
            }
        }

        // Letzte Seite
        if (!empty($sliceList)) {
            $pageList[] = (new Page())
                ->addSlice($this->getInstructionHeaderSlice())
                ->addSliceArray($sliceList);
        }
    }

    /**
     * @return Slice
     */
    private function getInstructionHeaderSlice(): Slice
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent('Belehrungen')
                ->styleMarginBottom('5px')
                ->styleTextSize('18px')
                ->styleTextBold()
            )
            ->addSection((new Section())
                ->addElementColumn($this->getHeaderElement('Thema')->styleBorderLeft(), '30%')
                ->addElementColumn($this->getHeaderElement('Inhalt'), '35%')
                ->addElementColumn((new Element())
                    ->setContent('Datum/Signum' . new Container('Fehlende SuS (Nr.)'))
                    ->styleAlignCenter()
                    ->stylePaddingTop('1.5px')
                    ->stylePaddingBottom('1.5px')
                    ->styleBackgroundColor('#CCC')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    , '35%')
            );
    }

    /**
     * @param TblInstruction $tblInstruction
     *
     * @return Slice
     */
    private function getInstructionSlice(TblInstruction $tblInstruction): Slice
    {
        $height = '140px';
        $subject = $tblInstruction->getSubject();
        $content = $tblInstruction->getContent();
        $count = 0;

        if (($tblInstructionItemList = Instruction::useService()->getInstructionItemAllByInstruction($tblInstruction, $this->tblDivision, $this->tblGroup))) {
            $subSlice = (new Slice())
                // keine Ahnung warum diese Höhe 10 Pixel mehr sein muss
                ->styleHeight('150px')
                ->styleBorderBottom()
                ->styleBorderRight();
            $itemCount = count($tblInstructionItemList) - 1;
            foreach ($tblInstructionItemList as $tblInstructionItem) {
                if ($tblInstructionItem->getIsMain()) {
                    $content = $tblInstructionItem->getContent();
                    if ($tblInstructionItem->getSubject()) {
                        $subject = $tblInstructionItem->getSubject();
                    }

                } else {
                    $count++;
                }

                $personNumberList = array();
                if (($missingStudents = Instruction::useService()->getMissingPersonNameListByInstructionItem($tblInstructionItem))) {
                    foreach ($missingStudents as $personId => $name) {
                        if (isset($this->personNumberAbsenceList[$personId])) {
                            $personNumberList[] = $this->personNumberAbsenceList[$personId];
                        }
                    }
                };

                $subSlice->addElement((new Element())
                    ->setContent(
                        ($count == 0 ? 'Belehrung' : $count . '. Nachbelehrung')
                            . ' ' . $tblInstructionItem->getDate()  . ' ' . $tblInstructionItem->getTeacherString(false)
                            . ($personNumberList ? new Container(implode(', ', $personNumberList)) : '')
                    )
                    ->styleBorderBottom($count == $itemCount ? '0px' : '1px')
                    ->stylePaddingLeft('3px')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('2px')
                );
            }
        } else {
            $subSlice = (new Slice())->addElement($this->getElement('')->styleHeight($height));
        }

        return (new Slice())
            ->styleBorderLeft()
            ->addSection((new Section())
                ->addElementColumn($this->getElement($subject)->styleHeight($height), '30%')
                ->addElementColumn($this->getElement($content)->styleHeight($height), '35%')
                ->addSliceColumn($subSlice, '35%')
            );
    }
}
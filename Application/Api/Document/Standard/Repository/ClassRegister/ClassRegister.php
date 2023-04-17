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
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

class ClassRegister extends AbstractDocument
{
    protected TblDivisionCourse $tblDivisionCourse;
    protected ?TblYear $tblYear = null;
    protected ?TblCompany $tblCompany = null;
    protected string $name = '&nbsp;';
    protected string $displayName = '&nbsp;';

    private array $tblCompanyList = array();
    private array $tblSchoolTypeList = array();
    private string $typeName;
    private string $tudors;
    protected array $tblPersonList = array();
    protected array $personNumberAbsenceList = array();
    private array $totalCanceledSubjectList = array();
    private array $totalAdditionalSubjectList = array();
    private bool $hasSaturdayLessons = false;
    private bool $hasTypeOption = false;

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
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function __construct(TblDivisionCourse $tblDivisionCourse)
    {
        $this->tblDivisionCourse = $tblDivisionCourse;
        $this->name = $tblDivisionCourse->getTypeName() . 'ntagebuch';
        $this->tblYear = ($tblYear = $tblDivisionCourse->getServiceTblYear()) ?: null;
        $this->typeName = $tblDivisionCourse->getTypeName();
        $this->displayName = $tblDivisionCourse->getName();
        $this->tudors = $tblDivisionCourse->getDivisionTeacherNameListString(', ');
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            $this->tblPersonList = $tblPersonList;
            if ($tblYear) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                        if (($tblCompany = $tblStudentEducation->getServiceTblCompany())
                            && !isset($this->tblCompanyList[$tblCompany->getId()])
                        ) {
                            $this->tblCompanyList[$tblCompany->getId()] = $tblCompany;
                        }
                        if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            && !isset($this->tblSchoolTypeList[$tblSchoolType->getId()])
                        ) {
                            $this->tblSchoolTypeList[$tblSchoolType->getId()] = $tblSchoolType;
                            if (!$this->hasTypeOption && $tblSchoolType->isTechnical()) {
                                $this->hasTypeOption = true;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tblSchoolTypeList)) {
            $this->hasSaturdayLessons = Digital::useService()->getHasSaturdayLessonsBySchoolTypeList($tblSchoolTypeList);
        }

        if (!empty($this->tblCompanyList)) {
            $this->tblCompany = current($this->tblCompanyList);
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
                            ->setContent($this->tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION ? 'Klassenlehrer/in' : 'Tudor/in')
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
            ->addSlice($this->getLectureshipSlice());
    }

    /**
     * @return Slice
     */
    private function getLectureshipSlice(): Slice
    {
        $dataList = Digital::useService()->getSubjectsAndLectureshipByDivisionForDownload($this->tblDivisionCourse);

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
        $page->addSliceArray($this->getRepresentativeSliceList());
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
        $custodyList = array();
        if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $this->tblDivisionCourse, TblDivisionCourseMemberType::TYPE_CUSTODY, false, false
        ))) {
            $count = 0;
            /** @var TblDivisionCourseMember $tblCustody */
            foreach ($tblCustodyMemberList as $tblCustody) {
                if (($tblPersonCustody = $tblCustody->getServiceTblPerson())) {
                    $custodyList[$count++] = $tblPersonCustody->getFullName() . ($tblCustody->getDescription() ? ' (' . $tblCustody->getDescription() . ')': '');
                }
            }
        }
        // Klassensprecher
        $representativeList = array();
        if (($tblRepresentativeMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $this->tblDivisionCourse, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false
        ))) {
            $count = 0;
            /** @var TblDivisionCourseMember $tblRepresentative */
            foreach ($tblRepresentativeMemberList as $tblRepresentative) {
                if (($tblPersonRepresentative = $tblRepresentative->getServiceTblPerson())) {
                    $representativeList[$count++] = $tblPersonRepresentative->getFirstSecondName() . ' ' . $tblPersonRepresentative->getLastName()
                        . ($tblRepresentative->getDescription() ? ' (' . $tblRepresentative->getDescription() . ')' : '');
                }
            }
        }

        $subSliceCustody = new Slice();
        $subSliceRepresentative = new Slice();
        for ($i = 0; $i < 5; $i++) {
            $subSliceCustody
                ->addElement((new Element())
                    ->setContent($custodyList[$i] ?? '&nbsp;')
                    ->styleHeight('30px')
                    ->stylePaddingLeft($padding)
                    ->stylePaddingTop($padding)
                    ->styleBorderBottom()
                    ->styleBorderRight()
                );
            $subSliceRepresentative
                ->addElement((new Element())
                    ->setContent($representativeList[$i] ?? '&nbsp;')
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
            if ($this->tblCompanyList) {
                foreach ($this->tblCompanyList as $tblCompany) {
                    if (($tblYearHolidayAllByYearAndCompany = Term::useService()->getYearHolidayAllByYear($this->tblYear, $tblCompany))) {
                        $list = array_merge($list, $tblYearHolidayAllByYearAndCompany);
                    }
                }
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
                ->setContent('Ferien / Unterrichtsfreie Tage')
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
//            $startDate = new DateTime('10.11.2022');
//            $endDate = new DateTime('20.11.2022');
            if ($startDate && $endDate) {
                $dayOfWeek = $startDate->format('w');

                // wenn Schuljahresbeginn ein Sonntag ist, dann beginne mit der nächsten Woche
                if ($this->hasSaturdayLessons) {
                    if ($dayOfWeek == 0) {
                        $startDate->add(new DateInterval('P7D'));
                    }
                // wenn Schuljahresbeginn ein Samstag oder ein Sonntag ist, dann beginne mit der nächsten Woche
                } else {
                    if ($dayOfWeek == 6 || $dayOfWeek == 0) {
                        $startDate->add(new DateInterval('P7D'));
                    }
                }
                $startDate = Timetable::useService()->getStartDateOfWeek($startDate);

                $sliceList = array();
                while ($startDate <= $endDate) {
                    $dateString = $startDate->format('d.m.Y');
                    $dayOfWeek = $startDate->format('w');

                    // nur Sonntag überspringen
                    if ($this->hasSaturdayLessons) {
                        if ($dayOfWeek == 0) {
                            $startDate->add(new DateInterval('P1D'));
                            continue;
                        }
                    // Samstag und Sonntag überspringen
                    } else {
                        if ($dayOfWeek == 6 || $dayOfWeek == 0) {
                            $startDate->add(new DateInterval('P1D'));
                            continue;
                        }
                    }

                    // Prüfung, ob die gesamte Woche Ferien sind, dann diese überspringen
                    if ($dayOfWeek == 1 && Term::useService()->getIsSchoolWeekHoliday($dateString, $this->tblYear, $this->tblCompanyList, $this->hasSaturdayLessons)) {
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

                    if ($this->hasSaturdayLessons) {
                        if ($dayOfWeek == 6) {
                            $pageList[] = (new Page())->addSliceArray($sliceList);
                            $sliceList = array();
                            $sliceList[] = $this->getWeekSummarySlice($startDate);
                            $pageList[] = (new Page())->addSliceArray($sliceList);
                            $sliceList = array();
                        }
                    } else {
                        // Freitag
                        if ($dayOfWeek == 5) {
                            $sliceList[] = $this->getWeekSummarySlice($startDate);
                        }
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
            && ($tblHoliday = Term::useService()->getHolidayByDayAndCompanyList($this->tblYear, $dateTime, $this->tblCompanyList))
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
            $divisionCourseList = array('0' => $this->tblDivisionCourse);
            $absenceContent = array();
            if (($AbsenceList = Absence::useService()->getAbsenceAllByDay(
                $dateTime, null, null, $divisionCourseList, $this->hasTypeOption, null
            ))) {
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
            if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
                && $tblSetting->getValue()
            ) {
                $minLesson = 0;
            } else {
                $minLesson = 1;
            }
            for ($i = $minLesson; $i < 13; $i++) {
                // mehrere UEs zur selben Zeit sind möglich
                if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDateAndLesson($dateTime, $i, $this->tblDivisionCourse))) {
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
                    if ($i > 0 && $i < 9) {
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
        list($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList)
            = Digital::useService()->getCanceledSubjectList($dateTime, $this->tblDivisionCourse);

        $slice = (new Slice())->styleBorderLeft();
        if ($subjectList) {
            ksort($subjectList);

            $width = 18;
            $widthString = $width . '%';
            $widthItemString  = ((100.0 - $width) / count($subjectList)) . '%';

            $sectionHeader = (new Section())->addElementColumn($this->getHeaderElement('Fach'), $widthString);
            $sectionCanceled = (new Section())->addElementColumn($this->getElement('Anzahl ausgefallene Stunden', 10), $widthString);
            $sectionAdditional = (new Section())->addElementColumn($this->getElement('Anzahl zusätzlich erteilte Stunden', 10), $widthString);
            $sectionTotalCanceled = (new Section())->addElementColumn($this->getElement('absoluter Ausfall', 10), $widthString);
            $sectionTotalAdditional = (new Section())->addElementColumn($this->getElement('abs. zus. erteilte Stunden', 10), $widthString);

            foreach ($subjectList as $acronym => $subject) {

                // absoluter Ausfall aufsummieren
                if (isset($this->totalCanceledSubjectList[$acronym]) && isset($canceledSubjectList[$acronym])) {
                    $this->totalCanceledSubjectList[$acronym] += $canceledSubjectList[$acronym];
                } elseif (isset($canceledSubjectList[$acronym])) {
                    $this->totalCanceledSubjectList[$acronym] = $canceledSubjectList[$acronym];
                }

                // absolute zusätzlich erteilte Stunden aufsummieren
                if (isset($this->totalAdditionalSubjectList[$acronym]) && isset($additionalSubjectList[$acronym])) {
                    $this->totalAdditionalSubjectList[$acronym] += $additionalSubjectList[$acronym];
                } elseif (isset($additionalSubjectList[$acronym])) {
                    $this->totalAdditionalSubjectList[$acronym] = $additionalSubjectList[$acronym];
                }

                $sectionHeader->addElementColumn($this->getHeaderElement($acronym), $widthItemString);
                $sectionCanceled->addElementColumn($this->getElement($canceledSubjectList[$acronym] ?? 0)->styleAlignCenter(), $widthItemString);
                $sectionAdditional->addElementColumn($this->getElement($additionalSubjectList[$acronym] ?? 0)->styleAlignCenter(), $widthItemString);
                $sectionTotalCanceled->addElementColumn($this->getElement($this->totalCanceledSubjectList[$acronym] ?? 0)->styleAlignCenter(), $widthItemString);
                $sectionTotalAdditional->addElementColumn($this->getElement($this->totalAdditionalSubjectList[$acronym] ?? 0)->styleAlignCenter(), $widthItemString);
            }

            $slice
                ->addSection($sectionHeader)
                ->addSection($sectionCanceled)
                ->addSection($sectionAdditional)
                ->addSection($sectionTotalCanceled)
                ->addSection($sectionTotalAdditional);
        }

        $remark = '';
        $descriptionDivisionTeacher = 'Klassenlehrerin/Klassenlehrer';
        $descriptionHeadmaster = 'Schulleiterin/Schulleiter';
        $divisionTeacher = '&nbsp;';
        $headmaster = '&nbsp;';
        if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($this->tblDivisionCourse, $fromDate))) {
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
            ->styleHeight('120px')
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
        $toDate = $toDate->add(new DateInterval($this->hasSaturdayLessons ? 'P5D' : 'P4D'));

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

        if (($tblInstructionItemList = Instruction::useService()->getInstructionItemAllByInstruction($tblInstruction, $this->tblDivisionCourse))) {
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
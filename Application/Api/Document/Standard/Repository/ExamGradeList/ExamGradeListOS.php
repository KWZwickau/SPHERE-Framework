<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\ExamGradeList;

use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Sup;

class ExamGradeListOS extends AbstractDocument
{
    const NUMBER_MAX = 30;

    const TEXT_SIZE_H1 = '14pt';
    const TEXT_SIZE_H2 = '12pt';
    const TEXT_SIZE = '9pt';
    const TEXT_SIZE_SMALL = '7pt';
    const TEXT_SIZE_TINY = '6pt';

    const BACKGROUND_COLOR = '#EAEBEC';
    const BORDER = '1px';

    const HEIGHT_HEADER = '40px';

    private TblDivisionCourse $tblDivisionCourse;
    private ?TblCompany $tblCompany = null;
    private bool $isMainCourse = false;
    private array $personList = array();
    private array $gradeList = array();
    private array $identifierList = array();

    function __construct(TblPrepareCertificate $tblPrepareCertificate, TblDivisionCourse $tblDivisionCourse)
    {
        $this->tblDivisionCourse = $tblDivisionCourse;
        $tblYear = $tblDivisionCourse->getServiceTblYear();
        if (($levelList = $tblDivisionCourse->getLevelListFromStudents()) && isset($levelList[9])) {
            $this->isMainCourse = true;
        }
        if (($tblCompanyList = $tblDivisionCourse->getCompanyListFromStudents())) {
            $this->tblCompany = reset($tblCompanyList);
        }
        $number = 1;
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                if (!$this->isMainCourse
                    || ($tblYear
                        && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                        && ($tblCourse = $tblStudentEducation->getServiceTblCourse())
                        && $tblCourse->getName() == 'Hauptschule')
                ) {
                    $this->personList[$number] = $tblPerson;

                    // Prüfungsnoten
                    if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareCertificate, $tblPerson))) {
                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                            if (($tblSubjectDiploma = $tblPrepareAdditionalGrade->getServiceTblSubject()) && $tblPrepareAdditionalGrade->getGrade()) {
                                $identifier = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier();
                                $this->gradeList[$number][$tblSubjectDiploma->getId()][$identifier] = $tblPrepareAdditionalGrade->getDisplayGrade();
                            }
                        }
                    }

                    // Jahresnoten
                    if (($tblTask = $tblPrepareCertificate->getServiceTblAppointedDateTask())
                        && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
                    ) {
                        foreach ($tblTaskGradeList as $tblTaskGrade) {
                            if (($tblSubjectYear = $tblTaskGrade->getServiceTblSubject())) {
                                $this->gradeList[$number][$tblSubjectYear->getId()]['JN'] = $tblTaskGrade->getDisplayGrade();
                            }
                        }
                    }

                    $number++;
                }
            }
        }

        $this->identifierList['JN'] = 'Jn';
        $this->identifierList['PS'] = 'Ps';
        $this->identifierList['PM'] = 'Pm' ;
        $this->identifierList['PZ'] = 'Pz';
        $this->identifierList['EN'] = 'En';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Notenliste Abschlussprüfungen Oberschule';
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

    public function getPageList(): array
    {
        $pageNumber = 1;

        $pageList[] = (new Page())
            ->addSlice($this->getHeaderSlice($pageNumber++))
            ->addSlice($this->getFirstTableSlice())
            ->addSlice($this->getInfoSlice())
            ->addSlice($this->getSignSlice());

        $pageList[] = (new Page())
            ->addSlice($this->getHeaderSlice($pageNumber))
            ->addSlice($this->getSecondTableSlice())
            ->addSlice($this->getInfoSlice())
            ->addSlice($this->getSignSlice());

        return  $pageList;
    }

    /**
     * @param int $pageNumber
     *
     * @return Slice
     */
    private function getHeaderSlice(int $pageNumber): Slice
    {
        $marginTopSchool = '25px';

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Seite ' . $pageNumber . '/2')
                ->styleTextSize(self::TEXT_SIZE_SMALL)
                ->styleAlignRight()
                ->styleHeight('5px')
            )
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Abschlussprüfung an der Oberschulen, Abendoberschulen und Gemeinschaftsschulen')
                        ->styleTextSize(self::TEXT_SIZE_H1)
                        ->styleTextBold()
                    )
                    ->addElement((new Element())
                        ->setContent('Notenliste zum '
                            . ($this->isMainCourse
                                ? 'Hauptschulabschluss und qualifizierenden Hauptschulabschluss'
                                : 'Realschulabschluss'
                            )
                        )
                        ->styleTextSize(self::TEXT_SIZE_H2)
                        ->styleTextBold()
                    )
                , '55%')
                ->addElementColumn((new Element())
                    ->setContent('Name und Anschrift der Schule:')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleMarginTop($marginTopSchool)
                , '12%')
                ->addElementColumn((new Element())
                    ->setContent(
                        ($this->tblCompany ? $this->tblCompany->getDisplayName() : '&nbsp;') . ' '
                        . ($this->tblCompany && ($tblAddress = $this->tblCompany->fetchMainAddress())
                            ? $tblAddress->getGuiString()
                            : '&nbsp;'
                        )

                    )
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleMarginTop($marginTopSchool)
                )
            )
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize('2px')
                ->styleBorderBottom('2px')
            );
    }

    private function getFirstTableSlice(): Slice
    {
        $countSubjects = 10;

        $widthLeft = 18;
        $widthLanguage = 9;
        $widthSubject = (100 - $widthLeft - $widthLanguage) / $countSubjects;

        $slice = new Slice();
        $slice->styleMarginTop('10px');

        $section = new Section();
        $section->addSliceColumn($this->getNameSlice(), $widthLeft . '%');
        $section->addSliceColumn($this->getSubjectSlice('DE', 'DE'), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('SOR', 'SOR'), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('MA', 'MA'), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('EN', 'EN'), $widthSubject . '%');

        $section->addSliceColumn($this->getLanguageSlice(), $widthLanguage . '%');

        $section->addSliceColumn($this->getSubjectSlice('BIO', 'BIO'), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('CH', 'CH'), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('PH', 'PH'), $widthSubject . '%');

        if ($this->isMainCourse) {
            $section->addSliceColumn($this->getSubjectSlice('WTH', 'WTH'), $widthSubject . '%');
            $section->addSliceColumn($this->getSubjectSlice('INF', 'INF'), $widthSubject . '%');
        } else {
            $section->addSliceColumn($this->getSubjectSlice('INF', 'INF'), $widthSubject . '%');
            $section->addSliceColumn($this->getSubjectSlice('GK', 'GK' . new Sup('4')), $widthSubject . '%');
        }

        $section->addSliceColumn($this->getSubjectSlice('SPO', 'SPO')->styleBorderRight(self::BORDER), $widthSubject . '%');
        $slice->addSection($section);

        return $slice;
    }

    private function getSecondTableSlice(): Slice
    {
        $widthLeft = 18;
        if ($this->isMainCourse) {
            $widthDroppedSubjects = 0;
            $widthDiploma = 14;
            $countSubjects = 10;
        } else {
            $widthDroppedSubjects = 9;
            $widthDiploma = 9;
            $countSubjects = 9;
        }
        $widthSubject = (100 - $widthLeft - $widthDroppedSubjects - $widthDiploma) / $countSubjects;

        $slice = new Slice();
        $slice->styleMarginTop('10px');

        $section = new Section();
        $section->addSliceColumn($this->getNameSlice(), $widthLeft . '%');
        $section->addSliceColumn($this->getSubjectSlice('KU', 'KU' . ($this->isMainCourse ? '' : new Sup('4'))), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('MU', 'MU' . ($this->isMainCourse ? '' : new Sup('4'))), $widthSubject . '%');
        if ($this->isMainCourse) {
            $section->addSliceColumn($this->getSubjectSlice('GK', 'GK'), $widthSubject . '%');
        }
        $section->addSliceColumn($this->getSubjectSlice('GE', 'GE' . ($this->isMainCourse ? '' : new Sup('4'))), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('GEO', 'GEO' . ($this->isMainCourse ? '' : new Sup('4'))), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('RE/e', 'RE/e' . new Sup('4')), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('RE/k', 'RE/k' . new Sup('4')), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('RE/j', 'RE/j' . new Sup('4')), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('ETH', 'ETH' . new Sup('4')), $widthSubject . '%');
        $section->addSliceColumn($this->getSubjectSlice('FS2', '2. FS' . new Sup('4')), $widthSubject . '%');

        if ($this->isMainCourse) {
            $section->addSliceColumn($this->getDiplomaHsSlice()->styleBorderRight(self::BORDER), $widthDiploma . '%');
        } else {
            $section->addSliceColumn($this->getDroppedSubjectsSlice(), $widthDroppedSubjects . '%');
            $section->addSliceColumn($this->getDiplomaRsSlice()->styleBorderRight(self::BORDER), $widthDiploma . '%');
        }

        $slice->addSection($section);

        return $slice;
    }

    private function getNameSlice(): Slice
    {
        $widthName = '45%';

        $slice = new Slice();
        $slice->addElement((new Element())
            ->setContent('&nbsp;')
            ->styleTextSize(self::TEXT_SIZE)
            ->styleHeight(self::HEIGHT_HEADER)
            ->stylePaddingTop('6px')
        );
        $slice->addSection((new Section())
            ->addElementColumn($this->getBodyElement('Lfd.' . new Container('Nr.'), self::TEXT_SIZE_SMALL, '1px'))
            ->addElementColumn($this->getBodyElement('Name')->styleTextBold(), $widthName)
            ->addElementColumn($this->getBodyElement('Vorname')->styleTextBold(), $widthName)
        );

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            /** @var TblPerson $tblPerson */
            $tblPerson = $this->personList[$i] ?? null;
            $textSize = self::TEXT_SIZE;
            if ($tblPerson) {
                $firstSecondName = $tblPerson->getFirstSecondName();
                if (strlen($firstSecondName) > 18) {
                    $textSize = self::TEXT_SIZE_SMALL;
                }
            }
            $slice->addSection((new Section())
                ->addElementColumn($this->getBodyElement($i))
                ->addElementColumn($this->getBodyElement($tblPerson ? $tblPerson->getLastName() : '&nbsp;')
                    ->styleAlignLeft()
                    ->stylePaddingLeft('5px')
                    , $widthName)
                ->addElementColumn($this->getBodyElement($tblPerson ? $tblPerson->getFirstSecondName() : '&nbsp;', $textSize)
                    ->styleAlignLeft()
                    ->stylePaddingLeft('5px')
                    , $widthName)
            );
        }

        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getSubjectSlice(string $acronym, string $display): Slice
    {
        $width = '20%';

        $tblSubject = Subject::useService()->getSubjectByVariantAcronym($acronym);

        $slice = new Slice();
        $slice->addElement((new Element())
            ->setContent($display)
            ->styleTextSize(self::TEXT_SIZE)
            ->styleHeight(self::HEIGHT_HEADER)
            ->styleTextBold()
            ->styleAlignCenter()
            ->stylePaddingTop('5px')
            ->styleBorderTop(self::BORDER)
            ->styleBorderLeft(self::BORDER)
        );

        $section = new Section();
        foreach ($this->identifierList as $name) {
            if ($acronym == 'SOR') {
                $content = $name . '¹';
                $textSize = '8pt';
                $padding = '5.75px';
            } else {
                $content = $name;
                $textSize = self::TEXT_SIZE;
                $padding = '5px';
            }

            $element = $this->getBodyElement($content, $textSize, $padding);
            if ($name == 'En') {
                $element->styleBackgroundColor(self::BACKGROUND_COLOR);
                $element->styleTextBold();
            }
            $section->addElementColumn($element, $width);
        }
        $slice->addSection($section);

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            if ($acronym == 'FS2') {
                /** @var TblPerson $tblPerson */
                if (($tblPerson = $this->personList[$i] ?? null) && ($tblYear = $this->tblDivisionCourse->getServiceTblYear())) {
                    $tblSubject = DivisionCourse::useService()->getForeignLanguageSubjectByPersonAndYear($tblPerson, $tblYear, 2);
                }
            }

            $section = new Section();
            foreach ($this->identifierList as $key => $item) {
                $element = $this->getBodyElement($tblSubject ? $this->gradeList[$i][$tblSubject->getId()][$key] ?? '&nbsp;' : '&nbsp;');
                if ($key == 'EN') {
                    $element->styleBackgroundColor(self::BACKGROUND_COLOR);
                    $element->styleTextBold();
                }
                $section->addElementColumn($element, $width);
            }
            $slice->addSection($section);
        }

        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getDroppedSubjectsSlice(): Slice
    {
        $width = '16.666%';

        $subjectList[] = 'MU';
        $subjectList[] = 'KU';
        $subjectList[] = 'GK';
        $subjectList[] = 'GE';
        $subjectList[] = 'GEO';
        $subjectList[] = 'WTH';

        $sectionJn = new Section();
        $sectionSubject = new Section();
        foreach ($subjectList as $acronym) {
            $sectionJn->addElementColumn($this->getBodyElement('Jn')->styleTextBold(), $width);
            $sectionSubject->addElementColumn($this->getBodyElement($acronym, self::TEXT_SIZE_SMALL, '1.6px'), $width);
        }

        $slice = (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->styleBorderTop(self::BORDER)
                    ->addElement((new Element())
                        ->setContent('Leistungen in den Fächer, die in')
                        ->styleTextSize(self::TEXT_SIZE_TINY)
                        ->styleAlignCenter()
                        ->styleTextBold()
                        ->stylePaddingTop('-2px')
                        ->styleBorderLeft(self::BORDER)
                    )
                    ->addElement((new Element())
                        ->setContent('Klassenstufe 9')
                        ->styleTextSize(self::TEXT_SIZE_TINY)
                        ->styleAlignCenter()
                        ->styleTextBold()
                        ->stylePaddingTop('-2px')
                        ->styleBorderLeft(self::BORDER)
                    )
                    ->addElement((new Element())
                        ->setContent('abgeschlossen wurden')
                        ->styleTextSize(self::TEXT_SIZE_TINY)
                        ->styleAlignCenter()
                        ->styleTextBold()
                        ->stylePaddingTop('-2px')
                        ->styleBorderLeft(self::BORDER)
                    )
                    ->addSection($sectionSubject)
                )
            );
        $slice->addSection($sectionJn);

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            $section = new Section();
            foreach ($subjectList as $acronym) {
                $tblSubject = Subject::useService()->getSubjectByVariantAcronym($acronym);
                $element = $this->getBodyElement($tblSubject ? $this->gradeList[$i][$tblSubject->getId()]['PRIOR_YEAR_GRADE'] ?? '&nbsp;' : '&nbsp;');
                $section->addElementColumn($element, $width);
            }
            $slice->addSection($section);
        }

        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getLanguageSlice(): Slice
    {
        $width = '20%';

        $slice = new Slice();
        $slice->addElement((new Element())
            ->setContent('Herkunftssprache²')
            ->styleTextSize(self::TEXT_SIZE)
            ->styleHeight(self::HEIGHT_HEADER)
            ->styleTextBold()
            ->styleAlignCenter()
            ->stylePaddingTop('5px')
            ->styleBorderTop(self::BORDER)
            ->styleBorderLeft(self::BORDER)
        );

        $slice->addSection((new Section())
            ->addElementColumn($this->getBodyElement('Ps'), $width)
            ->addElementColumn($this->getBodyElement('Sprache'))
        );

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            $slice->addSection((new Section())
                ->addElementColumn($this->getBodyElement()->styleAlignLeft(), $width)
                ->addElementColumn($this->getBodyElement()->stylePaddingLeft('5px'))
            );
        }
        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getDiplomaRsSlice(): Slice
    {
        $slice = (new Slice())
            ->styleBorderTop(self::BORDER)
            ->addElement((new Element())
                ->setContent('Abschluss')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleTextBold()
                ->styleAlignCenter()
                ->stylePaddingTop('5px')
                ->styleBorderLeft(self::BORDER)
            )
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->styleHeight('51px')
                    ->styleBorderLeft(self::BORDER)
                    ->addElement($this->getDiplomaHeaderElement('RSA (Realschulabschluss)'))
                    ->addElement($this->getDiplomaHeaderElement('Abgang (Abgangszeugnis)'))
                    ->addElement($this->getDiplomaHeaderElement('Wn (Wiederholung, nicht bestanden)'))
                    ->addElement($this->getDiplomaHeaderElement('Wf (Wiederholung, freiwillig)'))
                )
            );

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            $slice->addSection((new Section())
                ->addElementColumn($this->getBodyElement()->styleAlignLeft())
            );
        }
        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getDiplomaHsSlice(): Slice
    {
        $slice = (new Slice())
            ->styleBorderTop(self::BORDER)
            ->addElement((new Element())
                ->setContent('Abschluss')
                ->styleTextSize(self::TEXT_SIZE)
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleMarginTop('-1px')
                ->styleMarginBottom('-1px')
                ->styleBorderLeft(self::BORDER)
            )
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->styleHeight('58px')
                    ->styleBorderLeft(self::BORDER)
                    ->addSection($this->getDiplomaSection('qHSA', '(qualifizierender Hauptschulabschluss)'))
                    ->addSection($this->getDiplomaSection('HSA', '(Hauptschulabschluss)'))
                    ->addSection($this->getDiplomaSection('HSA(g)', '(dem Hauptschulabschluss gleichgestellter Abschluss)'))
                    ->addSection($this->getDiplomaSection('AFL', '(Abschluss im Förderschwerpunkt Lernen)'))
                    ->addSection($this->getDiplomaSection('Abgang', '(Abgangszeugnis)'))
                    ->addSection($this->getDiplomaSection('Wn', '(Wiederholung, nicht bestanden)'))
                    ->addSection($this->getDiplomaSection('Wf', '(Wiederholung, freiwillig)'))
                )
            );

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            $slice->addSection((new Section())
                ->addElementColumn($this->getBodyElement()->styleAlignLeft())
            );
        }
        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getInfoSlice(): Slice
    {
        return (new Slice())
            ->styleMarginTop('10px')
            ->addSection((new Section())
                ->addElementColumn($this->getInfoElement('Jn: Jahresnote'))
                ->addElementColumn($this->getInfoElement('Ps: Prüfungsnote (schriftlich)'))
                ->addElementColumn($this->getInfoElement('Pm: Prüfungsnote (mündlich)'))
                ->addElementColumn($this->getInfoElement('Pz: Prüfungsnote (zusätzlich mündlich)'))
                ->addElementColumn($this->getInfoElement('En: Endnote'))
                ->addElementColumn($this->getInfoElement('1) nur Schüler/innen mit Sorbisch'))
                ->addElementColumn($this->getInfoElement('2) siehe § 39 Absatz 3 SOOSA, auch soweit auf diesen verwiesen wird'))
                ->addElementColumn($this->getInfoElement('3) nur Schüler/innen mit vertiefter sportlicher Ausbildung'))
                ->addElementColumn($this->getInfoElement('4) sofern in der Klassenstufe 10 belegt'))
            );
    }

    private function getSignSlice(): Slice
    {
        $count = 0;
        $width[$count++] = '1%';
        $width[$count++] = '8%';
        $width[$count++] = '5%';
        $width[$count++] = '8%';
        $width[$count++] = '5%';
        $width[$count++] = '12%';
        $width[$count++] = 'auto';
        $width[$count++] = '8%';
        $width[$count++] = '5%';
        $width[$count++] = '12%';
        $width[$count] = '10%';

        $count = 0;
        $sectionContent = (new Section())
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn((new Element())
                ->setContent($this->tblDivisionCourse->getYearName())
                ->styleTextSize(self::TEXT_SIZE_SMALL)
                ->styleAlignCenter()
                , $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn((new Element())
                ->setContent($this->tblDivisionCourse->getName())
                ->styleTextSize(self::TEXT_SIZE_SMALL)
                ->styleAlignCenter()
                , $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn((new Element())
                ->setContent((new DateTime('Today'))->format('d.m.Y'))
                ->styleTextSize(self::TEXT_SIZE_SMALL)
                ->styleAlignCenter()
                , $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count]);

        $count = 0;
        $sectionDescription = (new Section())
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSignElement('Schuljahr'), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSignElement('Klasse'), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSignElement('Klassenlehrer/in'), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSignElement('Datum'), $width[$count++])
            ->addElementColumn($this->getSpaceElement(), $width[$count++])
            ->addElementColumn($this->getSignElement('Schulleiter/in'), $width[$count++])
            ->addElementColumn((new Element())
                ->setContent('Dienstsiegel der Schule')
                ->styleTextSize(self::TEXT_SIZE_SMALL)
                ->styleAlignRight()
                ->styleMarginTop('-10px')
                , $width[$count]);

        return (new Slice())
            ->styleMarginTop('30px')
            ->addSection($sectionContent)
            ->addSection($sectionDescription);
    }

    private function getBodyElement(string $content = '&nbsp;', string $textSize = self::TEXT_SIZE, string $padding = '5px'): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleTextSize($textSize)
            ->stylePaddingTop($padding)
            ->stylePaddingBottom($padding)
            ->styleAlignCenter()
            ->styleBorderTop(self::BORDER)
            ->styleBorderLeft(self::BORDER);
    }

    private function getDiplomaHeaderElement(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleTextSize(self::TEXT_SIZE_TINY)
            ->stylePaddingLeft('7px');
    }

    private function getDiplomaSection(string $short, string $name): Section
    {
        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($short)
                ->stylePaddingLeft('7px')
                ->styleTextSize('5pt')
                ->styleMarginTop('0px')
            , '18%')
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleTextSize('5pt')
                ->styleMarginTop('0px')
            );
    }

    private function getInfoElement(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleTextSize(self::TEXT_SIZE_TINY)
            ->styleAlignCenter();
    }

    private function getSignElement(string $content): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleTextSize(self::TEXT_SIZE_SMALL)
            ->styleAlignCenter()
            ->styleMarginTop('-4px')
            ->styleBorderTop();
    }

    private function getSpaceElement(): Element
    {
        return (new Element())->setContent('&nbsp;');
    }
}
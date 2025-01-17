<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\ExamGradeList;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Sup;

class ExamGradeListTechnical extends AbstractDocument
{
    const NUMBER_MAX = 30;

    const TEXT_SIZE = '9pt';

    const BORDER = '1px';
    private array $personList = array();
    private array $gradeList = array();
    private array $examList = array();

    function __construct(TblPrepareCertificate $tblPrepareCertificate, TblDivisionCourse $tblDivisionCourse)
    {
        $number = 1;
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                $this->personList[$number] = $tblPerson;
                $count = 2;

                // Prüfungsnoten
                if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareCertificate, $tblPerson))) {
                    foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                        if (($tblSubjectDiploma = $tblPrepareAdditionalGrade->getServiceTblSubject()) && $tblPrepareAdditionalGrade->getGrade()) {
                            $identifier = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier();
                            $this->gradeList[$number][$tblSubjectDiploma->getId()][$identifier] = $tblPrepareAdditionalGrade->getDisplayGrade();

                            if ($tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier() == 'PS')
                            {
                                if (($tblSubjectCompare = Subject::useService()->getSubjectByVariantAcronym('DE'))
                                    && $tblSubjectCompare->getId() == $tblSubjectDiploma->getId()
                                ) {
                                    $this->examList[$tblPerson->getId()]['WrittenExam'][1] = $tblSubjectDiploma;
                                } elseif (($tblSubjectCompare = Subject::useService()->getSubjectByVariantAcronym('EN'))
                                    && $tblSubjectCompare->getId() == $tblSubjectDiploma->getId()
                                ) {
                                    $this->examList[$tblPerson->getId()]['WrittenExam'][2] = $tblSubjectDiploma;
                                } elseif (($tblSubjectCompare = Subject::useService()->getSubjectByVariantAcronym('MA'))
                                    && $tblSubjectCompare->getId() == $tblSubjectDiploma->getId()
                                ) {
                                    $this->examList[$tblPerson->getId()]['WrittenExam'][3] = $tblSubjectDiploma;
                                } else {
                                    $this->examList[$tblPerson->getId()]['WrittenExam'][4] = $tblSubjectDiploma ;
                                }
                            }

                            if ($tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier() == 'PM')
                            {
                                if (($tblSubjectCompare = Subject::useService()->getSubjectByVariantAcronym('EN'))
                                    && $tblSubjectCompare->getId() == $tblSubjectDiploma->getId()
                                ) {
                                    $this->examList[$tblPerson->getId()]['VerbalExam'][1] = $tblSubjectDiploma;
                                } else {
                                    $this->examList[$tblPerson->getId()]['VerbalExam'][$count++] = $tblSubjectDiploma;
                                }
                            }
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Notenliste Abschlussprüfungen Berufsbildende Schule';
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

        $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.35cm !important; margin-right: 0.35cm !important; }';

        return (new Frame($InjectStyle))->addDocument($document);
    }

    /**
     * @return array
     */
    public function getPageList(): array
    {
        $pageList[] = (new Page())
            ->addSlice($this->getTableSlice())
            ->addSlice($this->getInfoSlice());

        return  $pageList;
    }

    private function getTableSlice(): Slice
    {
        $widthLeft = 32;
        $widthWrittenExam = 32;
        $widthVerbalExam = 24;
        $widthPracticalExtraVerbalExam = 12;

        $slice = new Slice();
        $slice->styleMarginTop('10px');

        $section = new Section();
        $section->addSliceColumn($this->getNameSlice(), $widthLeft . '%');
        $section->addSliceColumn($this->getExamSlice('schriftliche Prüfung' . new Sup('*)')), $widthWrittenExam . '%');
        $section->addSliceColumn($this->getExamSlice('mündliche Prüfung' . new Sup('*)')), $widthVerbalExam . '%');
        $section->addSliceColumn($this->getExamSlice('praktische Prüfung' . new Sup('*)'))
            ->styleBorderRight(self::BORDER)
            , $widthPracticalExtraVerbalExam . '%');

        $slice->addSection($section);

        return $slice;
    }

    private function getNameSlice(): Slice
    {
        $widthName = '90%';

        $slice = new Slice();
        $slice->addSection((new Section())
            ->addElementColumn($this->getElement('Lfd.' . new Container('Nr.'), '10px'))
            ->addElementColumn($this->getElement('Name, Vorname', '17.25px')->styleTextBold(), $widthName)
        );

        for ($i = 1; $i <= self::NUMBER_MAX; $i++)
        {
            /** @var TblPerson $tblPerson */
            $tblPerson = $this->personList[$i] ?? null;
            $slice->addSection((new Section())
                ->addElementColumn($this->getElement($i))
                ->addElementColumn($this->getElement($tblPerson ? $tblPerson->getLastFirstName() : '&nbsp;')
                    ->styleAlignLeft()
                    ->stylePaddingLeft('5px')
                    , $widthName)
            );
        }

        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getExamSlice(string $name): Slice
    {
        $slice = new Slice();
        $slice->addElement($this->getElement($name, '4.5px')->styleTextBold());

        $identifierList = array();
        $subjectRankingList = array();
        if (str_contains($name, 'schriftliche')) {
            $count = 4;
            $type = 'WrittenExam';
            $identifierList['JN'] = 'V';
            $identifierList['PS'] = 'P';
            $identifierList['PM'] = 'M';
            $identifierList['EN'] = 'Z';

            $subjectRankingList[1] = 'DE';
            $subjectRankingList[2] = 'EN';
            $subjectRankingList[3] = 'MA';
        } elseif (str_contains($name, 'mündliche')) {
            $count = 4;
            $type = 'VerbalExam';
            $identifierList['JN'] = 'V';
            $identifierList['PM'] = 'P';
            $identifierList['EN'] = 'Z';

            $subjectRankingList[1] = 'EN';
        } else {
            $count = 2;
            $type = '';
            $identifierList['JN'] = 'V';
            $identifierList['PS'] = 'P';
            $identifierList['EN'] = 'Z';
        }

        $width = (100 / ($count + count($identifierList))) . '%';

        $section = new Section();
        for ($column = 1; $column <= $count; $column++) {
            foreach ($identifierList as $name) {
                $section->addElementColumn($this->getElement($name, '5px'), $width);
            }
        }
        $slice->addSection($section);

        for ($row = 1; $row <= self::NUMBER_MAX; $row++)
        {
            $section = new Section();
            for ($column = 1; $column <= $count; $column++) {
                /** @var TblSubject $tblSubject */
                $tblSubject = false;
                if (($tblPerson = $this->personList[$row] ?? null)) {
                    if (isset($this->examList[$tblPerson->getId()][$type][$column])) {
                        $tblSubject = $this->examList[$tblPerson->getId()][$type][$column];
                    } elseif (isset($subjectRankingList[$column])) {
                        $tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectRankingList[$column]);
                    }
                }

                foreach ($identifierList as $key => $item) {
                    if ($tblSubject && isset($this->gradeList[$row][$tblSubject->getId()][$key])) {
                        $subjectAcronym = strlen($tblSubject->getAcronym()) > 3 ? substr($tblSubject->getAcronym(), 0, 3) : $tblSubject->getAcronym();
                        $section->addElementColumn(
                            $this->getElement($subjectAcronym . new Container($this->gradeList[$row][$tblSubject->getId()][$key]), '0px')
                        , $width);
                    } else {
                        $section->addElementColumn($this->getElement(), $width);
                    }
                }
            }
            $slice->addSection($section);
        }

        return $slice->styleBorderBottom(self::BORDER);
    }

    private function getInfoSlice(): Slice
    {
//        return (new Slice())
//            ->addElement($this->getInfoElement(new Sup('*)')  . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Nicht Zutreffendes streichen', '20px'))
//            ->addElement($this->getInfoElement('Abkürzungen:', '15px')->styleTextUnderline())
//            ->addElement($this->getInfoElement('V&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vornote', '10px'))
//            ->addElement($this->getInfoElement('M&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;mündliche Prüfung'))
//            ->addElement($this->getInfoElement('P&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prüfungsnote'))
//            ->addElement($this->getInfoElement('Z&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Zeugnisnote'));
        $marginTop = '10px';
        return (new Slice())
            ->addElement($this->getInfoElement(new Sup('*)')  . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Nicht Zutreffendes streichen', '10px'))
            ->addSection((new Section())
                ->addElementColumn($this->getInfoElement('Abkürzungen:', $marginTop)->styleTextUnderline(), '8%')
                ->addElementColumn($this->getInfoElement('V&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vornote', $marginTop), '10%')
                ->addElementColumn($this->getInfoElement('M&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;mündliche Prüfung', $marginTop), '14%')
                ->addElementColumn($this->getInfoElement('P&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prüfungsnote', $marginTop), '12%')
                ->addElementColumn($this->getInfoElement('Z&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Zeugnisnote', $marginTop))
            );
    }


    // '6px'
    private function getElement(string $content = '&nbsp;', string $padding = '7.5px'): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleTextSize(self::TEXT_SIZE)
            ->stylePaddingTop($padding)
            ->stylePaddingBottom($padding)
            ->styleAlignCenter()
            ->styleBorderTop(self::BORDER)
            ->styleBorderLeft(self::BORDER);
    }

    private function getInfoElement(string $content, string $marginTop = '2px'): Element
    {
        return (new Element())
            ->setContent($content)
            ->styleMarginTop($marginTop)
            ->styleTextSize(self::TEXT_SIZE);
    }
}
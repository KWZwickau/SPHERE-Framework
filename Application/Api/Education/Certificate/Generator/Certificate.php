<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

abstract class Certificate extends Extension
{

    /** @var null|Frame $Certificate */
    private $Certificate = null;

    /**
     * @var bool
     */
    private $IsSample;

    /**
     * @var array|false
     */
    private $Grade;

    /**
     * @var TblDivision|null
     */
    private $tblDivision = null;

    /**
     * @param TblDivision $tblDivision
     * @param bool|true $IsSample
     * @param array $pageList
     */
    public function __construct(TblDivision $tblDivision = null, $IsSample = true, $pageList = array())
    {

        $this->getCache(new TwigHandler())->clearCache();

        $this->setGrade(false);
        $this->tblDivision = $tblDivision;
        $this->IsSample = (bool)$IsSample;
        $this->Certificate = $this->buildCertificate($pageList);
        $this->tblDivision = $tblDivision;
    }

    /**
     * @param TblPerson|null $tblPerson
     * @return Page|Page[]
     * @internal param bool $IsSample
     *
     */
    abstract public function buildPages(TblPerson $tblPerson = null);

    /**
     * @param array $Data
     * @param array $PageList
     *
     * @return IBridgeInterface
     */
    public function createCertificate($Data = array(), $PageList = array())
    {

        foreach ($PageList as $personId => $page) {
            // ToDo Prüfung ob noch benötigt
            if (isset($Data['P' . $personId]['Grade'])) {
                // für Befreiung
                $this->setGrade($Data['P' . $personId]['Grade']);
                $Data['P' . $personId]['Grade'] = $this->getGrade();
            }
        }

        $this->Certificate = $this->buildCertificate($PageList);

        if (!empty($Data)) {
            $this->Certificate->setData($Data);
        }

        return $this->Certificate->getTemplate();
    }

    /**
     * @param array $PageList
     *
     * @return Frame
     */
    public function buildCertificate($PageList = array())
    {

        $document = new Document();

        foreach ($PageList as $personPages) {
            if (is_array($personPages)) {
                foreach ($personPages as $page) {
                    $document->addPage($page);
                }
            } else {
                $document->addPage($personPages);
            }
        }

        return (new Frame())->addDocument($document);
    }


    /**
     * @param $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

    /**
     * @return array|false
     */
    public function getGrade()
    {

        return $this->Grade;
    }

    /**
     * @return false|TblDivision
     */
    public function getTblDivision()
    {
        if (null === $this->tblDivision) {
            return false;
        } else {
            return $this->tblDivision;
        }
    }

    /**
     * @return string Certificate-Name from Database-Settings
     * @throws \Exception
     */
    public function getCertificateName()
    {

        $Certificate = trim(str_replace(
            'SPHERE\Application\Api\Education\Certificate\Generator\Repository', '', get_class($this)
        ), '\\');

        $tblCertificate = Generator::useService()->getCertificateByCertificateClassName($Certificate);
        if ($tblCertificate) {
            return $tblCertificate->getName() . ($tblCertificate->getDescription()
                    ? ' (' . $tblCertificate->getDescription() . ')'
                    : ''
                );
        }
        throw new \Exception('Certificate Missing: ' . $Certificate);
    }

    /**
     * @return bool
     */
    public function isSample()
    {
        return $this->IsSample;
    }

    /**
     * @return bool|TblCertificate
     * @throws \Exception
     */
    public function getCertificateEntity()
    {

        $Certificate = trim(str_replace(
            'SPHERE\Application\Api\Education\Certificate\Generator\Repository', '', get_class($this)
        ), '\\');

        $tblCertificate = Generator::useService()->getCertificateByCertificateClassName($Certificate);
        if ($tblCertificate) {
            return $tblCertificate;
        }
        throw new \Exception('Certificate Missing: ' . $Certificate);
    }

    /**
     * @return int Certificate-Id from Database-Settings
     * @throws \Exception
     */
    public function getCertificateId()
    {

        $Certificate = trim(str_replace(
            'SPHERE\Application\Api\Education\Certificate\Generator\Repository', '', get_class($this)
        ), '\\');

        $tblCertificate = Generator::useService()->getCertificateByCertificateClassName($Certificate);
        if ($tblCertificate) {
            return $tblCertificate->getId();
        }
        throw new \Exception('Certificate Missing: ' . $Certificate);
    }

    /**
     * @return null|Frame
     */
    public function getCertificate()
    {

        return $this->Certificate;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSchoolName($personId, $MarginTop = '20px')
    {

        $SchoolSlice = (new Slice());
        $SchoolSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Name der Schule:')
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name) %}
                                        {{ Content.P' . $personId . '.Company.Data.Name }}
                                    {% else %}
                                          &nbsp;
                                    {% endif %}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '64%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '18%')
        )->styleMarginTop($MarginTop);

        return $SchoolSlice;
    }

    /**
     * @param string $HeadLine
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCertificateHead($HeadLine = '', $MarginTop = '15px')
    {
        $CertificateSlice = (new Slice());
        $CertificateSlice->addElement((new Element())
            ->setContent($HeadLine)
            ->styleTextSize('18px')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleMarginTop($MarginTop)
        );
        return $CertificateSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     * @param string $YearString
     *
     * @return Slice
     */
    protected function getDivisionAndYear($personId, $MarginTop = '20px', $YearString = 'Schuljahr')
    {
        $YearDivisionSlice = (new Slice());
        $YearDivisionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klasse:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '7%')
            ->addElementColumn((new Element())
                , '55%')
            ->addElementColumn((new Element())
                ->setContent($YearString . ':')
                ->styleAlignRight()
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%')
        )->styleMarginTop($MarginTop);
        return $YearDivisionSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getStudentName($personId, $MarginTop = '5px')
    {
        $StudentSlice = (new Slice());
        $StudentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Vorname und Name:')
                , '21%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                              {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                ->styleBorderBottom()
                , '79%')
        )->styleMarginTop($MarginTop);
        return $StudentSlice;
    }

    /**
     * @param $personId
     * @param bool|true $isSlice
     * @param array $languagesWithStartLevel
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @return Section[]|Slice
     */
    protected function getSubjectLanes(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $tblPerson = Person::useService()->getPersonById($personId);

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if ($tblPerson
                        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                    ) {
                        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                    && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                                ) {
                                    $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->stylePaddingBottom('0px')
                            ->styleMarginBottom('0px')
                            ->styleBorderBottom('1px', '#000')
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '37%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , '39%');
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent(new Container('Gemeinschaftskunde/')
                                . new Container('Rechtserziehung/Wirtschaft'))
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    }

                    $TextSizeSmall = '8px';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 4px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 5px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
                        ->styleTextSize(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                    $isShrinkMarginTop = false;
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;

                if ($hasAdditionalLine) {
                    $SubjectSection = (new Section());

                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] is not empty) %}
                                     {{ Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] }}
                                 {% else %}
                                    &nbsp;
                                 {% endif %}'
                            . ')')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , '39%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $hasAdditionalLine = false;

                    // es wird abstand gelassen, einkommentieren für keinen extra Abstand der nächsten Zeile
//                    $isShrinkMarginTop = true;

                    $SubjectSlice->addSection($SubjectSection);
                    $SectionList[] = $SubjectSection;
                }

            }
        }

        if ($isSlice) {
            return $SubjectSlice;
        } else {
            return $SectionList;
        }
    }

    /**
     * @param $personId
     * @param bool|true $isSlice
     * @param array $languagesWithStartLevel
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Section[]|Slice
     */
    protected function getSubjectLanesCoswig(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $tblPerson = Person::useService()->getPersonById($personId);

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                    }
                }
            }

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if ($tblPerson
                        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                    ) {
                        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                    && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                                ) {
                                    $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->stylePaddingBottom('0px')
                            ->styleMarginBottom('0px')
                            ->styleBorderBottom('1px', '#000')
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '37%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , '39%');
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent(new Container('Gemeinschaftskunde/')
                                . new Container('Rechtserziehung/Wirtschaft'))
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    }

                    $TextSizeSmall = '8px';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 4px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 5px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
                        ->styleTextSize(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                    $isShrinkMarginTop = false;
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;

                if ($hasAdditionalLine) {
                    $SubjectSection = (new Section());

                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] is not empty) %}
                                     {{ Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] }}
                                 {% else %}
                                    &nbsp;
                                 {% endif %}'
                            . ')')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , '39%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $hasAdditionalLine = false;

                    // es wird abstand gelassen, einkommentieren für keinen extra Abstand der nächsten Zeile
//                    $isShrinkMarginTop = true;

                    $SubjectSlice->addSection($SubjectSection);
                    $SectionList[] = $SubjectSection;
                }

            }
        }

        if ($isSlice) {
            return $SubjectSlice;
        } else {
            return $SectionList;
        }
    }

    /**
     * @param $personId
     * @param bool $isMissing
     *
     * @return Slice
     */
    protected function getDescriptionHead($personId, $isMissing = false)
    {
        $DescriptionSlice = (new Slice());
        if ($isMissing) {
            $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:')
                    , '16%')
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
//                    ->styleBorderBottom('1px')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
//                    ->styleBorderBottom('1px')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '4%')
            )
                ->styleMarginTop('15px');
        } else {
            $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:'))
            )->styleMarginTop('15px');
        }
        return $DescriptionSlice;
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $MarginTop
     * @return Slice
     */
    public function getDescriptionContent($personId, $Height = '150px', $MarginTop = '0px')
    {
        $DescriptionSlice = (new Slice());
        $DescriptionSlice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                        {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop($MarginTop)
        );
        return $DescriptionSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getTransfer($personId, $MarginTop = '5px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Versetzungsvermerk:')
                , '22%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                                        {{ Content.P' . $personId . '.Input.Transfer }}
                                    {% else %}
                                          &nbsp;
                                    {% endif %}')
                ->styleBorderBottom('1px')
                , '58%')
            ->addElementColumn((new Element())
                , '20%')
        )
            ->styleMarginTop($MarginTop);
        return $TransferSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getDateLine($personId, $MarginTop = '25px')
    {
        $DateSlice = (new Slice());
        $DateSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Datum:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                ->styleBorderBottom('1px', '#000')
                ->styleAlignCenter()
                , '23%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        return $DateSlice;
    }

    /**
     * @param $personId
     * @param bool $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSignPart($personId, $isExtended = true, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                );
        } else {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    , '70%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                );
        }
        return $SignSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getParentSign($MarginTop = '25px')
    {
        $ParentSlice = (new Slice());
        $ParentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '40%')
            ->addElementColumn((new Element())
                , '30%')
        )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Eltern')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            )
            ->styleMarginTop($MarginTop);
        return $ParentSlice;
    }

    /**
     * @param string $MarginTop
     * @param string $LineOne
     * @param string $LineTwo
     * @param string $LineThree
     * @param string $LineFour
     * @param string $LineFive
     *
     * @return Slice
     */
    protected function getInfo(
        $MarginTop = '10px',
        $LineOne = '',
        $LineTwo = '',
        $LineThree = '',
        $LineFour = '',
        $LineFive = ''
    ) {
        $InfoSlice = (new Slice());
        $InfoSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->styleBorderBottom()
                , '30%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        if ($LineOne !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineOne)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineTwo !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineTwo)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineThree !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineThree)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineFour !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineFour)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineFive !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineFive)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }

        return $InfoSlice;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getGradeLanes($personId, $TextSize = '14px', $IsGradeUnderlined = false, $MarginTop = '15px')
    {

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , '4%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     * @return Slice
     */
    protected function getGradeLanesCoswig(
        $personId,
        $TextSize = '14px',
        $IsGradeUnderlined = false,
        $MarginTop = '15px'
    )
    {

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , '4%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'])
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @return Slice
     */
    public function getProfileStandard($personId, $TextSize = '14px', $IsGradeUnderlined = false)
    {

        $tblPerson = Person::useService()->getPersonById($personId);

        $slice = new Slice();
        $sectionList = array();

        $tblSubject = false;

        $profileAppendText = 'Profil';

        // Profil
        if ($tblPerson
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);
            if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                $tblSubject = $tblSubjectProfile;

                if (strpos(strtolower($tblSubject->getName()), 'naturwissen') !== false
                    && $this->getTblDivision()
                    && $this->getTblDivision()->getTblLevel()
                    && !preg_match('!(0?(8))!is', $this->getTblDivision()->getTblLevel()->getName())
                ) {
                    $profileAppendText = 'Profil mit informatischer Bildung';
                }
            }
        }

        $foreignLanguageName = '---';
        // 3. Fremdsprache
        if ($tblPerson
            && ($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if ($tblStudentSubject->getTblStudentSubjectRanking()
                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '3'
                    && ($tblSubjectForeign = $tblStudentSubject->getServiceTblSubject())
                ) {
                    $foreignLanguageName = $tblSubjectForeign->getName();
                }
            }
        }

        if ($tblSubject) {
            // Todo noch richtig Klären erstmal fest für Chemnitz
            // $SubjectAcronym = str_replace(' ', '', $tblSubject-getAcronym());
            $SubjectAcronym = 'PRO';

            $elementName = (new Element())
                // Profilname aus der Schülerakte
                // bei einem Leerzeichen im Acronymn stürzt das TWIG ab
                ->setContent('
                   {% if(Content.P' . $personId . '.Student.Profile.' . str_replace(' ', '', $tblSubject->getAcronym()) . ' is not empty) %}
                       {{ Content.P' . $personId . '.Student.Profile.' . str_replace(' ', '', $tblSubject->getAcronym()) . '.Name' . ' }}
                   {% else %}
                        &nbsp;
                   {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.Grade.Data.' . $SubjectAcronym . ' is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data.' . $SubjectAcronym . ' }}
                    {% else %}
                        &ndash;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);
        } else {
            $elementName = (new Element())
                ->setContent('---')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('&ndash;')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);
        }

        $marginTop = '20px';

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Wahlpflichtbereich:')
                ->styleTextBold()
                ->styleMarginTop($marginTop)
                ->styleTextSize($TextSize)
                , '20%')
            ->addElementColumn($elementName
                ->styleMarginTop($marginTop)
                , '42%')
            ->addElementColumn((new Element())
                ->setContent($profileAppendText)
                ->styleMarginTop($marginTop)
            );
        $sectionList[] = $section;
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('besuchtes Profil')
                ->styleAlignCenter()
                ->styleTextSize('11px')
                , '42%')
            ->addElementColumn((new Element()));
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Profil')
                ->styleTextSize($TextSize)
                ->styleMarginTop('5px')
                , '39%')
            ->addElementColumn($elementGrade
                ->styleMarginTop('5px')
                , '9%')
            ->addElementColumn((new Element())
                ->styleMarginTop('5px')
                , '4%')
            ->addElementColumn((new Element())
                ->styleMarginTop('5px')
                ->setContent($foreignLanguageName)
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '48%');
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                , '52%')
            ->addElementColumn((new Element())
                ->setContent('Fremdsprache (ab Klassenstufe 8) im sprachlichen Profil')
                ->styleTextSize('11px')
                ->styleAlignCenter()
                , '48%');
        $sectionList[] = $section;


        return $slice->addSectionList($sectionList);
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @return Slice
     */
    public function getOrientationStandard($personId, $TextSize = '14px', $IsGradeUnderlined = false)
    {

        $tblPerson = Person::useService()->getPersonById($personId);

        $marginTop = '5px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;
        if ($tblPerson
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {

                    // Todo noch richtig Klären erstmal fest für Chemnitz
                    // $SubjectAcronym = $tblSubject-getAcronym();
                    $SubjectAcronym = 'NK';

                    $elementOrientationName = new Element();
                    $elementOrientationName
                        ->setContent('
                            {% if(Content.P' . $personId . '.Student.Orientation.' . str_replace(' ', '', $tblSubject->getAcronym()) . ' is not empty) %}
                                 {{ Content.P' . $personId . '.Student.Orientation.' . str_replace(' ', '',
                                $tblSubject->getAcronym()) . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);

                    $elementOrientationGrade = new Element();
                    $elementOrientationGrade
                        ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data.' . $SubjectAcronym . ' is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data.' . $SubjectAcronym . ' }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);
                }
            }

            // 2. Fremdsprache
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if ($tblStudentSubject->getTblStudentSubjectRanking()
                        && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                        && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    ) {
                        $elementForeignLanguageName = new Element();
                        $elementForeignLanguageName
                            ->setContent('
                            {% if(Content.P' . $personId . '.Student.ForeignLanguage.' . $tblSubject->getAcronym() . ' is not empty) %}
                                 {{ Content.P' . $personId . '.Student.ForeignLanguage.' . $tblSubject->getAcronym() . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);

                        $elementForeignLanguageGrade = new Element();
                        $elementForeignLanguageGrade
                            ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data.' . $tblSubject->getAcronym() . ' is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data.' . $tblSubject->getAcronym() . ' }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);
                    }
                }
            }

            // aktuell immer anzeigen
//            if ($elementOrientationName || $elementForeignLanguageName) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('Wahlpflichtbereich:')
                    ->styleTextBold()
                    ->styleMarginTop('10px')
                    ->styleTextSize($TextSize)
                );
            $sectionList[] = $section;
//            }

            if ($elementOrientationName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, '91%')
                    ->addElementColumn($elementOrientationGrade, '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('<u>Neigungskurs (Neigungskursbereich)</u> / 2. Fremdsprache (abschlussorientiert)')
                        ->styleBorderTop()
                        ->styleMarginTop('0px')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        , '89%')
                    ->addElementColumn((new Element()), '11%');
                $sectionList[] = $section;
            } elseif ($elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementForeignLanguageName, '91%')
                    ->addElementColumn($elementForeignLanguageGrade, '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich) / <u>2. Fremdsprache (abschlussorientiert)</u>')
                        ->styleBorderTop()
                        ->styleMarginTop('0px')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        , '89%')
                    ->addElementColumn((new Element()), '11%');
                $sectionList[] = $section;
            } else {
                $elementName = (new Element())
                    ->setContent('---')
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize);

                $elementGrade = (new Element())
                    ->setContent('&ndash;')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                    ->stylePaddingTop('0px')
                    ->stylePaddingBottom('0px')
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize);

                $section = new Section();
                $section
                    ->addElementColumn($elementName
                        , '90%')
                    ->addElementColumn((new Element())
                        , '1%')
                    ->addElementColumn($elementGrade
                        , '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich)/2. Fremdsprache (abschlussorientiert)')
                        ->styleTextSize('11px')
                        , '50%');
                $sectionList[] = $section;
            }
        }

        return empty($sectionList) ? (new Slice())->styleHeight('60px') : $slice->addSectionList($sectionList);
    }

    /**
     * @param $personId
     * @param string $TextColor
     * @param string $TextSize
     * @param string $GradeFieldBackgroundColor
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     * @param int $GradeFieldWidth
     * @param string $fontFamily
     * @return Slice
     */
    protected function getGradeLanesForRadebeul(
        $personId,
        $TextColor = 'black',
        $TextSize = '13px',
        $GradeFieldBackgroundColor = 'rgb(224,226,231)',
        $IsGradeUnderlined = false,
        $MarginTop = '20px',
        $GradeFieldWidth = 28,
        $fontFamily = 'MetaPro'
    ) {

        $widthText = (50 - $GradeFieldWidth - 4) . '%';
        $widthGrade = $GradeFieldWidth . '%';

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , '8%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'] . ':')
                        ->styleTextColor($TextColor)
                        ->stylePaddingTop()
                        ->styleMarginTop('4px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthText);
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleTextColor($TextColor)
                        ->styleAlignCenter()
                        ->styleBackgroundColor($GradeFieldBackgroundColor)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', $TextColor)
                        ->stylePaddingTop('-4px')
                        ->stylePaddingBottom('2px')
                        ->styleMarginTop('8px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthGrade);
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '54%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param $personId
     * @param string $TextColor
     * @param string $TextSize
     * @param string $GradeFieldBackgroundColor
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     * @param int $GradeFieldWidth
     * @param string $fontFamily
     *
     * @return Slice
     */
    protected function getSubjectLanesForRadebeul(
        $personId,
        $TextColor = 'black',
        $TextSize = '13px',
        $GradeFieldBackgroundColor = 'rgb(224,226,231)',
        $IsGradeUnderlined = false,
        $MarginTop = '8px',
        $GradeFieldWidth = 28,
        $fontFamily = 'MetaPro'
    ) {

        $tblPerson = Person::useService()->getPersonById($personId);

        $widthText = (50 - $GradeFieldWidth - 4) . '%';
        $widthGrade = $GradeFieldWidth . '%';

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                        // Liberation?
                        if (
                            $tblPerson
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                            && ($tblStudentLiberationCategory = $tblCertificateSubject->getServiceTblStudentLiberationCategory())
                        ) {
                            $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
                            if ($tblStudentLiberationAll) {
                                foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
                                    if (($tblStudentLiberationType = $tblStudentLiberation->getTblStudentLiberationType())) {
                                        $tblStudentLiberationType->getTblStudentLiberationCategory();
                                        if ($tblStudentLiberationCategory->getId() == $tblStudentLiberationType->getTblStudentLiberationCategory()->getId()) {
                                            $this->Grade['Data'][$tblSubject->getAcronym()] = $tblStudentLiberationType->getName();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                $count++;

                foreach ($SubjectList as $Lane => $Subject) {
                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '8%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'] . ':')
                        ->styleTextColor($TextColor)
                        ->stylePaddingTop()
                        ->styleMarginTop($count == 1 ? $MarginTop : '4px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthText);

                    if (strlen($Subject['SubjectName']) > 20 && preg_match('!\s!', $Subject['SubjectName'])) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                            ->styleTextColor($TextColor)
                            ->styleAlignCenter()
                            ->styleBackgroundColor($GradeFieldBackgroundColor)
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', $TextColor)
                            ->stylePaddingTop('-4px')
                            ->stylePaddingBottom('2px')
                            ->styleMarginTop($count == 1 ? '25px' : '19px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily($fontFamily)
                            , $widthGrade);
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                            ->styleTextColor($TextColor)
                            ->styleAlignCenter()
                            ->styleBackgroundColor($GradeFieldBackgroundColor)
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', $TextColor)
                            ->stylePaddingTop('-4px')
                            ->stylePaddingBottom('2px')
                            ->styleMarginTop($count == 1 ? '14px' : '8px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily($fontFamily)
                            , $widthGrade);
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '54%');
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;
            }
        }

        return $SubjectSlice;
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     * @return Slice
     */
    protected function getGradeLanesCustomForChemnitz(
        $personId,
        $TextSize = '14px',
        $IsGradeUnderlined = false,
        $MarginTop = '15px'
    ) {

        $GradeFieldWidth = 16;
        $space = 7;
        $marginTop = '6px';

        $widthText = (50 - $GradeFieldWidth - $space) . '%';
        $widthGrade = $GradeFieldWidth . '%';
        $spaceText = $space . '%';

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , $spaceText);
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'])
                        ->stylePaddingTop()
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize)
                        , $widthText);
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize)
                        , $widthGrade);
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), (50 + $space) . '%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param $personId
     * @param bool $isSlice
     * @param array $languagesWithStartLevel
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return array|Slice
     */
    protected function getSubjectLanesCustomForChemnitz(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $tblPerson = Person::useService()->getPersonById($personId);

        $GradeFieldWidth = 16;
        $space = 7;
        $marginTop = '6px';

        $widthText = (50 - $GradeFieldWidth - $space) . '%';
        $widthGrade = $GradeFieldWidth . '%';
        $spaceText = $space . '%';

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        $marginTopSection = new Section();
        $marginTopSection->addElementColumn((new Element())
            ->setContent('&nbsp;')
            ->styleHeight('15px')
        );
        $SubjectSlice->addSection($marginTopSection);
        $SectionList[] = $marginTopSection;

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if ($tblPerson
                        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                    ) {
                        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                    && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                                ) {
                                    $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , $spaceText);
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->stylePaddingBottom('0px')
                            ->styleMarginBottom('0px')
                            ->styleBorderBottom('1px', '#000')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize)
                            , $widthText);
                        $SubjectSection->addElementColumn((new Element()), $spaceText);
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , $widthText);
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent(new Container('Gemeinschaftskunde/')
                                . new Container('Rechtserziehung/Wirtschaft'))
                            ->stylePaddingTop()
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize)
                            , $widthText);
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize)
                            , $widthText);
                    }

                    $TextSizeSmall = '8px';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 4px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 5px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : $marginTop)
                        ->styleTextSize(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , $widthGrade);

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), (50) . '%');
                    $isShrinkMarginTop = false;
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;

                if ($hasAdditionalLine) {
                    $SubjectSection = (new Section());

                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn((new Element()), (50) . '%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] is not empty) %}
                                     {{ Content.P' . $personId . '.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] }}
                                 {% else %}
                                    &nbsp;
                                 {% endif %}'
                            . ')')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , '39%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), (50) . '%');
                    }

                    $hasAdditionalLine = false;

                    // es wird abstand gelassen, einkommentieren für keinen extra Abstand der nächsten Zeile
//                    $isShrinkMarginTop = true;

                    $SubjectSlice->addSection($SubjectSection);
                    $SectionList[] = $SubjectSection;
                }

            }
        }

        if ($isSlice) {
            return $SubjectSlice;
        } else {
            return $SectionList;
        }
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    protected function getObligationToVotePartCustomForCoswig($personId, $TextSize = '14px', $IsGradeUnderlined = false)
    {

        $tblPerson = Person::useService()->getPersonById($personId);

        $marginTop = '5px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;
        if ($tblPerson
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    $subjectAcronym = 'NK';

                    $elementOrientationName = new Element();
                    $elementOrientationName
                        ->setContent('
                            {% if(Content.P' . $personId . '.Student.Orientation.' . $tblSubject->getAcronym() . ' is not empty) %}
                                 {{ Content.P' . $personId . '.Student.Orientation.' . $tblSubject->getAcronym() . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);

                    $elementOrientationGrade = new Element();
                    $elementOrientationGrade
                        ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data.' . $subjectAcronym . ' is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data.' . $subjectAcronym . ' }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);
                }
            }

            // 2. Fremdsprache
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if ($tblStudentSubject->getTblStudentSubjectRanking()
                        && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                        && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    ) {
                        $elementForeignLanguageName = new Element();
                        $elementForeignLanguageName
                            ->setContent('
                            {% if(Content.P' . $personId . '.Student.ForeignLanguage.' . $tblSubject->getAcronym() . ' is not empty) %}
                                 {{ Content.P' . $personId . '.Student.ForeignLanguage.' . $tblSubject->getAcronym() . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);

                        $elementForeignLanguageGrade = new Element();
                        $elementForeignLanguageGrade
                            ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data.' . $tblSubject->getAcronym() . ' is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data.' . $tblSubject->getAcronym() . ' }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#E9E9E9')
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);
                    }
                }
            }

            if ($elementOrientationName || $elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Wahlpflichtbereich:')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleTextItalic()
                        ->styleTextBold()
                        ->styleMarginTop('20px')
                        ->styleTextSize($TextSize)
                    );
                $sectionList[] = $section;
            }

            if ($elementOrientationName && $elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, '39%')
                    ->addElementColumn($elementOrientationGrade, '9%')
                    ->addElementColumn((new Element()), '4%')
                    ->addElementColumn($elementForeignLanguageName, '39%')
                    ->addElementColumn($elementForeignLanguageGrade, '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                        , '48%')
                    ->addElementColumn((new Element()), '4%')
                    ->addElementColumn((new Element())
                        ->setContent('2. Fremdsprache (abschlussorientiert)')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                        , '48%'
                    );
                $sectionList[] = $section;
            } elseif ($elementOrientationName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, '39%')
                    ->addElementColumn($elementOrientationGrade, '9%')
                    ->addElementColumn((new Element()), '52%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                    );
                $sectionList[] = $section;
            } elseif ($elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementForeignLanguageName, '39%')
                    ->addElementColumn($elementForeignLanguageGrade, '9%')
                    ->addElementColumn((new Element()), '52%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('2. Fremdsprache (abschlussorientiert)')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                    );
                $sectionList[] = $section;
            }
        }

        return empty($sectionList)
            ? $slice->addElement((new Element())
                ->setContent('&nbsp;')
            )->styleHeight('76px')
            : $slice->addSectionList($sectionList);
    }
}

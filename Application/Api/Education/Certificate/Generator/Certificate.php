<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\People\Meta\Student\Student;
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
     * @var array|false
     */
    private $Person;

    public function __construct($IsSample = true)
    {

        $this->getCache(new TwigHandler())->clearCache();

        $this->setGrade(false);
        $this->setPerson(false);
        $this->IsSample = (bool)$IsSample;
//        $this->Certificate = $this->buildCertificate($this->IsSample);
    }

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    abstract public function buildCertificate($IsSample = true);

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
     * @param $Person
     */
    public function setPerson($Person)
    {
        $this->Person = $Person;
    }

    /**
     * @return array|false
     */
    public function getPerson()
    {

        return $this->Person;
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
     * @param array $Data
     *
     * @return IBridgeInterface
     */
    public function createCertificate($Data = array())
    {

        if (isset($Data['Grade'])){
            $this->setGrade($Data['Grade']);
        }
        if (isset($Data['Person'])){
            $this->setPerson($Data['Person']);
        }

        $this->Certificate = $this->buildCertificate($this->IsSample);

        // für Befreiung
        if (isset($Data['Grade'])){
            $Data['Grade'] = $this->getGrade();
        }

        if (!empty($Data)) {
            $this->Certificate->setData($Data);
        }

        return $this->Certificate->getTemplate();
    }


    /**
     * @return Slice
     * @throws \Exception
     */
    protected function getSubjectLanes()
    {

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym'] = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName'] = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym'] = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName'] = $tblSubject->getName();
                        // Liberation?
                        $PersonList = $this->getPerson();
                        if(
                            isset($PersonList['Student']) && isset($PersonList['Student']['Id'])
                            && ($tblStudentLiberationCategory = $tblCertificateSubject->getServiceTblStudentLiberationCategory())
                        ) {
                            $tblStudent = Student::useService()->getStudentById( $PersonList['Student']['Id'] );
                            if( $tblStudent ) {
                                $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
                                if( $tblStudentLiberationAll ) {
                                    foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
                                        if (( $tblStudentLiberationType = $tblStudentLiberation->getTblStudentLiberationType() )) {
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

            foreach ($SubjectStructure as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('5px')
                        , '39%');
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Grade.Data.' . $Subject['SubjectAcronym'] . ' is not empty) %}
                                             {{ Content.Grade.Data.' . $Subject['SubjectAcronym'] . ' }}
                                         {% else %}
                                             ---
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom('1px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('5px')
                        , '9%');

                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $SubjectSlice->addSection($SubjectSection);
            }
        }

        return $SubjectSlice;
    }

    /**
     * @return Slice
     * @throws \Exception
     */
    protected function getGradeLanes()
    {

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym'] = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName'] = $tblGradeType->getName();

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
                        ->styleMarginTop('5px')
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Input.' . $Grade['GradeAcronym'] . ' is not empty) %}
                                         {{ Content.Input.' . $Grade['GradeAcronym'] . ' }}
                                     {% else %}
                                         ---
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom('1px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('5px')
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop('15px');
            }
        }

        return $GradeSlice;
    }
}

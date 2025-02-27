<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator;

use DateTime;
use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\System\Extension\Extension;

abstract class Certificate extends Extension
{
    const BACKGROUND_GRADE_FIELD = '#CCC';

    /** @var null|Frame $Certificate */
    private ?Frame $Certificate = null;

    /**
     * @var bool
     */
    private bool $IsSample;

    /**
     * @var array|false
     */
    private $Grade;

    /**
     * @var array|false
     */
    private $AdditionalGrade;

    /**
     * @var ?TblStudentEducation
     */
    private ?TblStudentEducation $tblStudentEducation;

    /**
     * @var ?TblPrepareCertificate
     */
    private ?TblPrepareCertificate $tblPrepareCertificate;

    /**
     * @param TblStudentEducation|null $tblStudentEducation
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     * @param bool $IsSample
     * @param array $pageList
     */
    public function __construct(TblStudentEducation $tblStudentEducation = null, TblPrepareCertificate $tblPrepareCertificate = null, bool $IsSample = true,
        array $pageList = array())
    {

        // todo find usage
        $this->setGrade(false);
        $this->setAdditionalGrade(false);
        $this->tblStudentEducation = $tblStudentEducation;
        $this->tblPrepareCertificate = $tblPrepareCertificate;
        $this->IsSample = $IsSample;

        // need for Preview frontend (getTemplateInformationForPreview)
        $this->Certificate = $this->buildCertificate($pageList);
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
     * @param array $certificateList
     *
     * @return IBridgeInterface
     */
    public function createCertificate($Data = array(), $PageList = array(), $certificateList = array())
    {

        $this->Certificate = $this->buildCertificate($PageList, $certificateList);

        if (!empty($Data)) {
            $this->Certificate->setData($Data);
        }

        return $this->Certificate->getTemplate();
    }

    /**
     * @param array $PageList
     * @param array $certificateList
     *
     * @return Frame
     */
    public function buildCertificate($PageList = array(), $certificateList = array())
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

        $tblConsumer = \SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer::useService()->getConsumerBySession();

        $isWidth = false;
        $InjectStyle = '';

        // Herausforderung bei multi download ist das Certificate jetzt ein MultiCertificate und nicht mehr das eigentliche Certificate
        // Zwischenlösung das häufigste Certificate für den Style verwenden
        // ansonsten prüfen ob die Pdfs wieder gemerged werden können
        if (!empty($certificateList)) {
            arsort($certificateList);
            reset($certificateList);
            $certificate = 'SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'. key($certificateList);
        } else {
            $certificate = get_class($this);
        }

        // für Lernentwicklungsbericht von Radebeul 2cm Rand (1,4 cm scheint Standard zu seien)
        if (strpos($certificate, 'RadebeulLernentwicklungsbericht') !== false) {
            $InjectStyle = 'body { margin-left: 1.0cm !important; margin-right: 1.0cm !important; margin-top: 0.9cm !important; margin-bottom: 0.9cm !important; }';
        // für Kinderbrief von Radebeul 2,5cm Rand
        } elseif (strpos($certificate, 'RadebeulKinderbrief') !== false) {
            $InjectStyle = 'body { margin-left: 1.0cm !important; margin-right: 1.0cm !important; margin-top: 0.9cm !important; margin-bottom: 0.9cm !important; }';
        } elseif (strpos($certificate, 'EmspGsJ') !== false) {
            $InjectStyle = 'body { margin-left: 0.18cm !important; margin-right: 0.18cm !important; margin-top: 0.18cm !important;
             padding-bottom: 0.18cm !important; border: 1px solid black; padding: 40px}';
        } elseif (strpos($certificate, 'EmspGsHj') !== false) {
            $InjectStyle = 'body { margin-left: 0.18cm !important; margin-right: 0.18cm !important; margin-top: 0.18cm !important;
             padding-bottom: 0.18cm !important; border: 1px solid black; padding: 40px}';
        } elseif (strpos($certificate, 'RadebeulHalbjahresinformation') !== false) {
            $InjectStyle = 'body { margin-left: 1.2cm !important; margin-right: 1.2cm !important; }';
        } elseif (strpos($certificate, 'RadebeulJahreszeugnis') !== false) {
            $InjectStyle = 'body { margin-left: 1.2cm !important; margin-right: 1.2cm !important; }';
        } elseif (strpos($certificate, 'RadebeulOs') !== false) {
            $InjectStyle = 'body { margin-left: 1.2cm !important; margin-right: 1.2cm !important; }';
        } elseif (strpos($certificate, 'EzshKurshalbjahreszeugnis') !== false) {
            $InjectStyle = 'body { margin-left: 0.9cm !important; margin-right: 1.0cm !important; }';
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EVGSM') && (strpos($certificate, 'GsHjInfo') !== false || strpos($certificate, 'GsJ') !== false)) {
            $isWidth = true;
//            $InjectStyle = 'body { margin-bottom: -0.7cm !important; margin-left: 0.75cm !important; margin-right: 0.75cm !important; }';
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'CSW')) {
            $InjectStyle = 'body { margin-bottom: -0.7cm !important; margin-left: 0.8cm !important; margin-right: 0.8cm !important; }';
        }
        // Mandanten, deren individuelle Zeugnisse ebenfalls mit den Mandanteneinstellungen die Rahmenbreite verändern können
        elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'ESZC')) {
            $InjectStyle = 'body { margin-bottom: -0.7cm !important; margin-left: 0.75cm !important; margin-right: 0.75cm !important; }';
            $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generate', 'DocumentBorder');
            if($tblSetting && $tblSetting->getValue() == 1){
                // normal
                $InjectStyle = 'body { margin-bottom: -0.7cm !important; margin-left: 0.35cm !important; margin-right: 0.35cm !important; }';
            }
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'ESBD')) {
            $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.35cm !important; margin-right: 0.35cm !important; }';
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'LWSZ')) {
            // erforderlich für die Fußzeile auf der 2. Seite
            $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.75cm !important; margin-right: 0.75cm !important; }';
            $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generate', 'DocumentBorder');
            if ($tblSetting && $tblSetting->getValue() == 1) {
                // normal
                $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.35cm !important; margin-right: 0.35cm !important; }';
            }
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')) {
            $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.75cm !important; margin-right: 0.75cm !important; }';
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'MLS')) {
            $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.75cm !important; margin-right: 0.75cm !important; }';
        } elseif ($tblConsumer && $tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HGGT')) {
            $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 1.25cm !important; margin-right: 1.25cm !important; }';
        }

        // Standardzeugnisse mit Breiteneinstellung
        $tblCertificateList = \SPHERE\Application\Education\Certificate\Generator\Generator::useService()->getCertificateAllByConsumer();

        // Vorbereitung auf Vergleich
        if ($tblCertificateList) {
            foreach ($tblCertificateList as &$tblCertificate) {
                $tblCertificate = 'SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            }

            $tblCertificateList = array_filter($tblCertificateList);
            if (in_array($certificate, $tblCertificateList) || $isWidth) {
                // breiter (Standard)
                $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.75cm !important; margin-right: 0.75cm !important; }';
                $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generate',
                    'DocumentBorder');
                if ($tblSetting && $tblSetting->getValue() == 1) {
                    // normal
                    $InjectStyle = 'body { margin-bottom: -1.5cm !important; margin-left: 0.35cm !important; margin-right: 0.35cm !important; }';
                }
            }
        }

        // SSW-1026 schmaler Zeugnisrand und SSW-1037
        if ((strpos($certificate, 'GymAbitur') !== false
            || strpos($certificate, 'GymAbgSekI') !== false
            || strpos($certificate, 'GymAbgSekII') !== false
            || strpos($certificate, 'MsAbs') !== false
            || strpos($certificate, 'MsAbg') !== false)
            && $tblConsumer
            && !$tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')
            && !$tblConsumer->isConsumer(TblConsumer::TYPE_SACHSEN, 'EVOSG')
        ) {
            $InjectStyle = '';
        }

        return (new Frame($InjectStyle))->addDocument($document);
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
     * @return false|TblStudentEducation
     */
    public function getTblStudentEducation()
    {
        if (null === $this->tblStudentEducation) {
            return false;
        } else {
            return $this->tblStudentEducation;
        }
    }

    /**
     * @return false|TblYear
     */
    public function getYear()
    {
        return $this->getTblStudentEducation() ? $this->getTblStudentEducation()->getServiceTblYear() : false;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->getTblStudentEducation() ? $this->getTblStudentEducation()->getLevel() : null;
    }

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->getLevel() ? (string) $this->getLevel() : '';
    }

    /**
     * @return false|TblCompany
     */
    public function getTblCompany()
    {
        return $this->getTblStudentEducation() ? $this->getTblStudentEducation()->getServiceTblCompany() : false;
    }

    /**
     * @return false|TblCourse
     */
    public function getTblCourse()
    {
        return $this->getTblStudentEducation() ? $this->getTblStudentEducation()->getServiceTblCourse() : false;
    }

    /**
     * @return false|TblPrepareCertificate
     */
    public function getTblPrepareCertificate()
    {
        if (null === $this->tblPrepareCertificate) {
            return false;
        } else {
            return $this->tblPrepareCertificate;
        }
    }

    /**
     * @return false|TblLeaveStudent
     */
    public function getTblLeaveStudent()
    {
        if (($tblStudentEducation = $this->getTblStudentEducation())
            && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
            && ($tblYear = $this->getYear())
        ) {
            return Prepare::useService()->getLeaveStudentBy($tblPerson, $tblYear);
        }

        return false;
    }

    /**
     * @return DateTime|null
     */
    public function getCertificateDateTime(): ?DateTime
    {
        if (($tblPrepareCertificate = $this->getTblPrepareCertificate())) {
            return $tblPrepareCertificate->getDateTime();
        } elseif (($tblLeaveStudent = $this->getTblLeaveStudent())
            && ($tblLeaveInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))
            && ($tblLeaveInformation->getValue())
        ) {
            return new DateTime($tblLeaveInformation->getValue());
        }

        return null;
    }

    /**
     * @return false|TblSubject
     */
    public function getProfilSubject()
    {
        if (($tblStudentEducation = $this->getTblStudentEducation())
            && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
            && ($tblYear = $tblStudentEducation->getServiceTblYear())
        ) {
            // Profil aus dem gespeicherten Fach am Schüler
            if (($level = $tblStudentEducation->getLevel())
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && ($tblSubjectTable = DivisionCourse::useService()->getSubjectTableByStudentMetaIdentifier($tblSchoolType, $level, 'PROFILE'))
                && ($tblStudentSubject = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubjectTable($tblPerson, $tblYear, $tblSubjectTable))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                return $tblSubject;
            // Profil aus der Schülerakte
            } elseif (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
            ) {
                $tblStudentSubject = current($tblStudentSubjectList);
                return $tblStudentSubject->getServiceTblSubject();
            }
        }

        return false;
    }

    /**
     * @return false|TblSubject
     */
    public function getOrientationSubject()
    {
        if (($tblStudentEducation = $this->getTblStudentEducation())
            && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
            && ($tblYear = $tblStudentEducation->getServiceTblYear())
        ) {
            // Wahlbereich aus dem gespeicherten Fach am Schüler
            if (($level = $tblStudentEducation->getLevel())
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && ($tblSubjectTable = DivisionCourse::useService()->getSubjectTableByStudentMetaIdentifier($tblSchoolType, $level, 'ORIENTATION'))
                && ($tblStudentSubject = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubjectTable($tblPerson, $tblYear, $tblSubjectTable))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                return $tblSubject;
            // Wahlbereich aus der Schülerakte
            } elseif (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
            ) {
                $tblStudentSubject = current($tblStudentSubjectList);
                return $tblStudentSubject->getServiceTblSubject();
            }
        }

        return false;
    }

    /**
     * @param $ranking
     *
     * @return bool|TblSubject
     */
    public function getForeignLanguageSubject($ranking)
    {
        if (($tblStudentEducation = $this->getTblStudentEducation())
            && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
            && ($tblYear = $tblStudentEducation->getServiceTblYear())
        ) {
            return DivisionCourse::useService()->getForeignLanguageSubjectByPersonAndYear($tblPerson, $tblYear, $ranking);
        }

        return false;
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
     * @return array|false
     */
    public function getAdditionalGrade()
    {
        return $this->AdditionalGrade;
    }

    /**
     * @param array|false $AdditionalGrade
     */
    public function setAdditionalGrade($AdditionalGrade)
    {
        $this->AdditionalGrade = $AdditionalGrade;
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
     * get Certificate Content
     * @param TblPerson                  $tblPerson
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     *
     * @return array $Data <br/>
     * ['Remark'] => Beschreibung
     */
    protected function getCertificateData(TblPerson $tblPerson, $tblPrepareCertificate = null)
    {

        //ToDO add necessary Data (at the moment Editor content)
        $Data['Remark'] = '';
        if($tblPrepareCertificate && ($tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepareCertificate,
                $tblPerson))){
            foreach($tblPrepareInformationList as $tblPrepareInformation)
            {
                if($tblPrepareInformation->getField() == 'Remark'){
                    $Data['Remark'] = $tblPrepareInformation->getValue();
                }
            }
        }

        return $Data;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSchoolName($personId, $MarginTop = '20px')
    {

        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
            'Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed'))
            && $tblSetting->getValue()
        ) {
            $isSchoolExtendedNameDisplayed = true;
        } else {
            $isSchoolExtendedNameDisplayed = false;
        }
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'SchoolExtendedNameSeparator'))
            && $tblSetting->getValue()
        ) {
            $separator = $tblSetting->getValue();
        } else {
            $separator = false;
        }
        $isLargeCompanyName = false;
        $name = '';
        $empty = '&nbsp;';
        // get company name
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblCompany = $this->getTblCompany())
        ) {
           $name = $isSchoolExtendedNameDisplayed ? $tblCompany->getName() .
               ($separator ? ' ' . $separator . ' ' : ' ') . $tblCompany->getExtendedName() : $tblCompany->getName();
           if (strlen($name) > 60) {
               $isLargeCompanyName = true;
           }

            if (strlen($name) > 75 && $isSchoolExtendedNameDisplayed) {
                $isLargeCompanyName = strlen($tblCompany->getName()) > 60 || strlen($tblCompany->getExtendedName()) > 60;
                $name = $tblCompany->getName() . new Container($tblCompany->getExtendedName());
                $empty .= new Container('&nbsp;');
            }
        }

        $SchoolSlice = (new Slice());
        if ($isLargeCompanyName) {
            $SchoolSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name der Schule:')
                    , '18%')
                ->addElementColumn((new Element())
                    ->setContent($name ? $name : '&nbsp;')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    , '82%')
            )->styleMarginTop($MarginTop);
        } else {
            $SchoolSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name der Schule:')
                    , '18%')
                ->addElementColumn((new Element())
                    ->setContent($name ? $name : '&nbsp;')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    , '64%')
                ->addElementColumn((new Element())
                    ->setContent($empty)
                    ->styleBorderBottom()
                    , '18%')
            )->styleMarginTop($MarginTop);
        }

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
     * @param $IsSample
     *
     * @return Section
     */
    protected function getIndividuallyLogo($IsSample)
    {

        $isOS = false;
        if (($tblCertificate = $this->getCertificateEntity())
            && ($tblSchoolType = $tblCertificate->getServiceTblSchoolType())
            && $tblSchoolType->getShortName() == 'OS'
        ) {
            $isOS = true;
        }

        $picturePath = $this->getUsedPicture($isOS);
        $IndividuallyLogoHeight = $this->getPictureHeight($isOS);

//        $Head = new Slice();
        $Section = new Section();

        // Sample
        if($IsSample){
            $Section->addElementColumn((new Element\Sample())
                ->styleTextSize('30px')
                ->styleHeight('0px')
                , '27%');
        } else {
            $Section->addElementColumn((new Element()), '27%');
        }

        $Section->addElementColumn((new Element()), '51%');

        // Individually Logo
        if ($picturePath != '') {
            $Section->addElementColumn((new Element\Image($picturePath, 'auto', $IndividuallyLogoHeight))
                ->styleAlignCenter()
                ->styleHeight('0px')
                , '22%');
        } else {
            $Section->addElementColumn((new Element()), '22%');
        }

        return $Section;
    }

    /**
     * @param bool   $IsSample
     * @param bool   $isBigLogo
     *
     * @return Slice
     */
    protected function getHead($IsSample, $isBigLogo = true, $showIndividualLogo = true)
    {

        $isOS = false;
        if (($tblCertificate = $this->getCertificateEntity())
            && ($tblSchoolType = $tblCertificate->getServiceTblSchoolType())
            && $tblSchoolType->getShortName() == 'OS'
        ) {
            $isOS = true;
        }

        if ($showIndividualLogo) {
            $picturePath = $this->getUsedPicture($isOS);
            $IndividuallyLogoHeight = $this->getPictureHeight($isOS);
        } else {
            $picturePath = '';
            $IndividuallyLogoHeight = '66px';
        }

        $StandardLogoHeight = '50px';
        $StandardLogoWidth = '165px';
        if($isBigLogo){
            $StandardLogoHeight = '66px';
            $StandardLogoWidth = '214px';
        }

        $Head = new Slice();
        $Section = new Section();
        // Individually Logo
        if ($picturePath != '') {
            $Section->addElementColumn((new Element\Image($picturePath, 'auto', $IndividuallyLogoHeight)), '39%');
        } else {
            $Section->addElementColumn((new Element()), '39%');
        }
        // Sample
        if($IsSample){
            $Section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $Section->addElementColumn((new Element()), '22%');
        }

        // Standard Logo
        $Section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
            $StandardLogoWidth, $StandardLogoHeight))
            ->styleAlignRight()
            , '39%');

        // Maximale Bildgröße für das Individuelle Logo 100px (Höhe)
        // Wird für das Standardlogo als "Mindestrand" (unten) benötigt!
        if($isBigLogo){
            $Head->stylePaddingTop('24px');
            $Head->styleHeight('100px');
        }
        return $Head->addSection($Section);
    }

    /**
     * @param bool $IsSample
     * @param bool $showPicture
     *
     * @return Slice
     */
    public function getHeadForLeave(bool $IsSample, bool $showPicture = true): Slice
    {
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting('Education', 'Certificate', 'Diploma', 'DoNotShowSaxonyLogo'))
            && $tblSetting->getValue()
        ) {
            $elementSaxonyLogo = (new Element())->setContent('&nbsp;');
        } else {
            $elementSaxonyLogo = (new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '214px', '66px'))->styleAlignRight();
        }

        $pictureAddress = '';
        $pictureHeight = '66px';
        if ($showPicture) {
            if (($tblSettingAddress = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Generate', 'PictureAddressForLeaveCertificate'))
            ) {
                $pictureAddress = trim($tblSettingAddress->getValue());
            }
            if (($tblSettingHeight = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                    'Education', 'Certificate', 'Generate', 'PictureHeightForLeaveCertificate'))
                && ($value = trim($tblSettingHeight->getValue()))
            ) {
                $pictureHeight = $value;
            }
        }
        if ($pictureAddress) {
            $elementSchoolLogo = new Element\Image($pictureAddress, 'auto', $pictureHeight);
        } else {
            $elementSchoolLogo = (new Element())->setContent('&nbsp;');
        }

        $Header = (new Slice())
            ->addSection((new Section())
                ->addElementColumn($elementSchoolLogo, '61%')
                ->addElementColumn($elementSaxonyLogo, '39%')
            );
        if ($IsSample) {
            $Header->addSection((new Section())
                ->addElementColumn((new Element\Sample())
                    ->styleMarginTop('80px')
                    ->styleTextSize('30px')
                    ->styleAlignCenter()
                    ->styleHeight('0px')
                )
            );
        }

        $Header->stylePaddingTop('24px');
        $Header->styleHeight('100px');

        return $Header;
    }

    /**
     * @param bool $IsSample
     * @param bool $showPicture
     *
     * @return Slice
     */
    public function getHeadForDiploma(bool $IsSample, bool $showPicture = true): Slice
    {
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting('Education', 'Certificate', 'Diploma', 'DoNotShowSaxonyLogo'))
            && $tblSetting->getValue()
        ) {
            $elementSaxonyLogo = (new Element())->setContent('&nbsp;');
        } else {
            $elementSaxonyLogo = (new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '214px', '66px'))->styleAlignRight();
        }

        $pictureAddress = '';
        $pictureHeight = '66px';
        if ($showPicture) {
            if (($tblSettingAddress = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Generate', 'PictureAddressForDiplomaCertificate'))
            ) {
                $pictureAddress = trim($tblSettingAddress->getValue());
            }
            if (($tblSettingHeight = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                    'Education', 'Certificate', 'Generate', 'PictureHeightForDiplomaCertificate'))
                && ($value = trim($tblSettingHeight->getValue()))
            ) {
                $pictureHeight = $value;
            }
        }
        if ($pictureAddress) {
            $elementSchoolLogo = new Element\Image($pictureAddress, 'auto', $pictureHeight);
        } else {
            $elementSchoolLogo = (new Element())->setContent('&nbsp;');
        }

        $Header = (new Slice())
            ->addSection((new Section())
                ->addElementColumn($elementSchoolLogo, '61%')
                ->addElementColumn($elementSaxonyLogo, '39%')
            );
        if ($IsSample) {
            $Header->addSection((new Section())
                ->addElementColumn((new Element\Sample())
                    ->styleMarginTop('80px')
                    ->styleTextSize('30px')
                    ->styleAlignCenter()
                    ->styleHeight('0px')
                )
            );
        }

        $Header->stylePaddingTop('24px');
        $Header->styleHeight('100px');

        return $Header;
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
                , '8%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '10%')
            ->addElementColumn((new Element())
                , '51%')
            ->addElementColumn((new Element())
                ->setContent($YearString . ':&nbsp;&nbsp;')
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
     * @param string $YearString
     *
     * @return Slice
     */
    protected function getFoesLevelAndYear($personId, $MarginTop = '20px', $YearString = 'Schuljahr')
    {
        $YearDivisionSlice = (new Slice());
        $YearDivisionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Student.StudentSpecialNeeds.LevelName is not empty) %}
                    {{ Content.P' . $personId . '.Student.StudentSpecialNeeds.LevelName }}
                {% else %}
                    ________ Stufe
                {% endif %}')
//                ->styleBorderBottom()
                , '33%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Input.SchoolVisitYear }}. Schulbesuchsjahr')
                    ->styleAlignCenter()
                , '33%')
            ->addElementColumn((new Element())
                ->setContent($YearString . ':&nbsp;&nbsp;{{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleAlignRight()
                , '34%')
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
                ->styleAlignCenter()
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '21%')
        )->styleMarginTop($MarginTop);
        return $StudentSlice;
    }

    /**
     * @param bool|true $isSlice
     * @param array $languagesWithStartLevel
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @param bool $hasSecondLanguageDiploma
     * @param bool $hasSecondLanguageSecondarySchool
     * @param bool $hasSecondLanguageFoteNote
     *
     * @return Section[]|Slice
     */
    protected function getSubjectLanes(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false,
        $hasSecondLanguageDiploma = false,
        $hasSecondLanguageSecondarySchool = false,
        $hasSecondLanguageFoteNote = false
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
                if ($tblSubject) {
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
            }

            $tblSecondForeignLanguageDiploma = false;
            $tblSecondForeignLanguageSecondarySchool = false;

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if (($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))) {
                        $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                    }
                }
            } else {
                if (($hasSecondLanguageDiploma || $hasSecondLanguageSecondarySchool)
                    && ($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))
                ) {
                    if ($hasSecondLanguageDiploma) {
                        $tblSecondForeignLanguageDiploma = $tblSubjectForeignLanguage;
                    }

                    // Mittelschulzeugnisse
                    if ($hasSecondLanguageSecondarySchool)  {
                        $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
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

            $hasAdditionalLine = array();
            $isShrinkMarginTop = false;

            // Abschlusszeugnis 2. Fremdsprache anfügen
            if ($hasSecondLanguageDiploma) {
                // Zeiger auf letztes Element
                end($SubjectStructure);
                $lastItem = &$SubjectStructure[key($SubjectStructure)];
                //
                if (isset($lastItem[1])) {
                    $SubjectStructure[][1] = $this->addSecondForeignLanguageDiploma($tblSecondForeignLanguageDiploma
                        ? $tblSecondForeignLanguageDiploma : null);
                } else {
                    $lastItem[1] = $this->addSecondForeignLanguageDiploma($tblSecondForeignLanguageDiploma
                        ? $tblSecondForeignLanguageDiploma : null);
                }
            }

            // Mittelschulzeugnisse 2. Fremdsprache anfügen
            if ($hasSecondLanguageSecondarySchool) {
                // Zeiger auf letztes Element
                end($SubjectStructure);
                $lastItem = &$SubjectStructure[key($SubjectStructure)];

                $column = array(
                    'SubjectAcronym' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'SECONDLANGUAGE',
                    'SubjectName' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getName()
                        : '&ndash;'
                );
                //
                if (isset($lastItem[1])) {
                    $SubjectStructure[][1] = $column;
                } else {
                    $lastItem[1] = $column;
                }
            }

            // Zeugnisnoten im Wortlaut auf Abschlusszeugnissen --> breiter Zensurenfelder
            if (($tblCertificate = $this->getCertificateEntity())
                && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
                && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                    'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
                && $tblSetting->getValue()
            ) {
                $subjectWidth = 37;
                $gradeWidth = 11;
                $TextSizeSmall = '13px';
                $paddingTopShrinking = '4px';
                $paddingBottomShrinking = '4px';
            } else {
                $subjectWidth = 39;
                $gradeWidth = 9;
                $TextSizeSmall = '8px';
                $paddingTopShrinking = '5px';
                $paddingBottomShrinking = '6px';
            }

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
                    } elseif ($hasSecondLanguageSecondarySchool
                        && ($Subject['SubjectAcronym'] == 'SECONDLANGUAGE'
                            || ($tblSecondForeignLanguageSecondarySchool && $Subject['SubjectAcronym'] == $tblSecondForeignLanguageSecondarySchool->getAcronym())
                        )
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguageSecondarySchool
                            ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'Empty';
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
                            , (string)($subjectWidth - 2) . '%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , (string)$subjectWidth . '%');
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent(new Container('Gemeinschaftskunde/')
                                . new Container('Rechtserziehung/Wirtschaft'))
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , (string)$subjectWidth . '%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , (string)$subjectWidth . '%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                ' . $paddingTopShrinking . ' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                               ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , (string)$gradeWidth . '%');

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

                    $content = $hasSecondLanguageSecondarySchool
                        ? $hasAdditionalLine['Ranking'] . '. Fremdsprache (abschlussorientiert)' . ($hasSecondLanguageFoteNote ? '¹' : '')
                        : $hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.P' . $personId . '.Subject.Level.2 is not empty) %}
                                {{ Content.P' . $personId . '.Subject.Level.2 }})
                            {% else %}
                                &ndash;)
                            {% endif %}';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($content)
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , (string)$subjectWidth . '%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $hasAdditionalLine = array();

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
     * @param bool $isSlice
     * @param array $languagesWithStartLevel
     * @param string $TextSize
     * @param false $IsGradeUnderlined
     * @param false $hasSecondLanguageDiploma
     * @param false $hasSecondLanguageSecondarySchool
     * @param string $backgroundColor
     *
     * @return Section[]|Slice
     */
    protected function getSubjectLanesSmall(
        $personId,
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false,
        $hasSecondLanguageDiploma = false,
        $hasSecondLanguageSecondarySchool = false,
        $backgroundColor = self::BACKGROUND_GRADE_FIELD,
        &$subjectRowCount = 0
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
                if ($tblSubject) {
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
            }

            $tblSecondForeignLanguageDiploma = false;
            $tblSecondForeignLanguageSecondarySchool = false;

            // add SecondLanguageField
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if (($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))) {
                        $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                    }
                }
            } else {
                if (($hasSecondLanguageDiploma || $hasSecondLanguageSecondarySchool)
                    && ($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))
                ) {
                    if ($hasSecondLanguageDiploma) {
                        $tblSecondForeignLanguageDiploma = $tblSubjectForeignLanguage;
                    }
                    // Mittelschulzeugnisse
                    if ($hasSecondLanguageSecondarySchool)  {
                        $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
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

            $hasAdditionalLine = array();
            $isShrinkMarginTop = false;

            // Abschlusszeugnis 2. Fremdsprache anfügen
            if ($hasSecondLanguageDiploma) {
                // Zeiger auf letztes Element
                end($SubjectStructure);
                $lastItem = &$SubjectStructure[key($SubjectStructure)];
                //
                if (isset($lastItem[1])) {
                    $SubjectStructure[][1] = $this->addSecondForeignLanguageDiploma($tblSecondForeignLanguageDiploma
                        ? $tblSecondForeignLanguageDiploma : null);
                } else {
                    $lastItem[1] = $this->addSecondForeignLanguageDiploma($tblSecondForeignLanguageDiploma
                        ? $tblSecondForeignLanguageDiploma : null);
                }
            }

            // Mittelschulzeugnisse 2. Fremdsprache anfügen
            if ($hasSecondLanguageSecondarySchool) {
                // Zeiger auf letztes Element
                end($SubjectStructure);
                $lastItem = &$SubjectStructure[key($SubjectStructure)];

                $column = array(
                    'SubjectAcronym' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'SECONDLANGUAGE',
                    'SubjectName' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getName()
                        : '&ndash;'
                );
                //
                if (isset($lastItem[1])) {
                    $SubjectStructure[][1] = $column;
                } else {
                    $lastItem[1] = $column;
                }
            }

            // Zeugnisnoten im Wortlaut auf Abschlusszeugnissen --> breiter Zensurenfelder
            if (($tblCertificate = $this->getCertificateEntity())
                && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
                && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                    'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
                && $tblSetting->getValue()
            ) {
                $subjectWidth = 37;
                $gradeWidth = 11;
                $TextSizeSmall = '13px';
                $paddingTopShrinking = '4px';
                $paddingBottomShrinking = '4px';
            } else {
                $subjectWidth = 39;
                $gradeWidth = 9;
                $TextSizeSmall = '8px';
                $paddingTopShrinking = '4.5px';
                $paddingBottomShrinking = '5px';
            }

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                $subjectRowCount++;
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
                    } elseif ($hasSecondLanguageSecondarySchool
                        && ($Subject['SubjectAcronym'] == 'SECONDLANGUAGE'
                            || ($tblSecondForeignLanguageSecondarySchool && $Subject['SubjectAcronym'] == $tblSecondForeignLanguageSecondarySchool->getAcronym())
                        )
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguageSecondarySchool
                            ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'Empty';
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
                            ->styleMarginTop('4px')
                            ->styleTextSize($TextSize)
                            , (string)($subjectWidth - 2) . '%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , (string)$subjectWidth . '%');
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent(new Container('Gemeinschaftskunde/')
                                . new Container('Rechtserziehung/Wirtschaft'))
                            ->stylePaddingTop()
                            ->styleMarginTop('4px')
                            ->styleTextSize($TextSize)
                            , (string)$subjectWidth . '%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('4px')
                            ->styleTextSize($TextSize)
                            , (string)$subjectWidth . '%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($backgroundColor)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                ' . $paddingTopShrinking . ' 
                             {% else %}
                                 1px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                               ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 1px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '4px')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , (string)$gradeWidth . '%');

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

                    $content = $hasSecondLanguageSecondarySchool
                        ? $hasAdditionalLine['Ranking'] . '. Fremdsprache (abschlussorientiert)'
                        : $hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.P' . $personId . '.Subject.Level.2 is not empty) %}
                                {{ Content.P' . $personId . '.Subject.Level.2 }})
                            {% else %}
                                &ndash;)
                            {% endif %}';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($content)
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , (string)$subjectWidth . '%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $hasAdditionalLine = array();

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
     * @param TblSubject|null $tblSecondForeignLanguageDiploma
     *
     * @return array
     */
    private function addSecondForeignLanguageDiploma(TblSubject $tblSecondForeignLanguageDiploma = null)
    {
        return array(
            'SubjectAcronym' => $tblSecondForeignLanguageDiploma ? $tblSecondForeignLanguageDiploma->getAcronym() : 'SECONDLANGUAGE',
            'SubjectName' => $tblSecondForeignLanguageDiploma
                ? $tblSecondForeignLanguageDiploma->getName() . ' (abschlussorientiert)'
                :'2. Fremdsprache (abschlussorientiert)'
        );
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
                if($tblSubject){
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
            }

            // add SecondLanguageField
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if (($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))) {
                        $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
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

            $hasAdditionalLine = array();
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
                                 1px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 3px
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
                            '{% if(Content.P' . $personId . '.Subject.Level.2 is not empty) %}
                                {{ Content.P' . $personId . '.Subject.Level.2 }})
                            {% else %}
                                &ndash;)
                            {% endif %}'
                        )
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

                    $hasAdditionalLine = array();

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
     * @param string $PreRemark
     * @param string|bool $TextSize
     * @param string $Remark
     * @return Slice
     */
    public function getDescriptionContent($personId, $Height = '150px', $MarginTop = '0px', $PreRemark = '', $TextSize = false, $Remark = '')
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        if($Remark != ''){
            $Element->setContent($PreRemark.nl2br($Remark));
        } else {
            $Element->setContent($PreRemark.
                '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                        {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}');
        }
        $Element->styleHeight($Height);
        $Element->styleMarginTop($MarginTop);

        if($tblSetting && $tblSetting->getValue()){
            $Element->styleAlignJustify();
        }
        if($TextSize){
            $Element->styleTextSize($TextSize);
        }

        return (new Slice())->addElement($Element);
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $MarginTop
     * @param string $PreRemark
     * @return Slice
     */
    public function getSupportContent($personId, $Height = '150px', $MarginTop = '0px', $PreRemark = '')
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        $Element->setContent($PreRemark.
                    '{% if(Content.P' . $personId . '.Input.Support is not empty) %}
                        {{ Content.P' . $personId . '.Input.Support|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop($MarginTop);

        if($tblSetting && $tblSetting->getValue()){
            $Element->styleAlignJustify();
        }

        return (new Slice())->addElement($Element);
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $MarginTop
     * @param string $PreRemark
     * @param string|bool $TextSize
     * @param string $Remark
     * @return Slice
     */
    public function getDescriptionWithoutTeamContent($personId, $Height = '150px', $MarginTop = '0px', $PreRemark = '', $TextSize = false, $Remark = '')
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        if($Remark != ''){
            $Element->setContent($PreRemark.nl2br($Remark));
        } else {
            $Element->setContent($PreRemark.
                '{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                        {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}');
        }
        $Element->styleHeight($Height);
        $Element->styleMarginTop($MarginTop);

        if($tblSetting && $tblSetting->getValue()){
            $Element->styleAlignJustify();
        }
        if($TextSize){
            $Element->styleTextSize($TextSize);
        }

        return (new Slice())->addElement($Element);
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $MarginTop
     * @param string $PreRemark
     * @return Slice
     */
    public function getSupportSubjectContent($personId, $Height = '150px', $MarginTop = '0px', $PreRemark = '')
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        $Element->setContent($PreRemark.
            '{% if(Content.P' . $personId . '.Input.SupportSubject is not empty) %}
                        {{ Content.P' . $personId . '.Input.SupportSubject|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop($MarginTop);

        if($tblSetting && $tblSetting->getValue()){
            $Element->styleAlignJustify();
        }

        return (new Slice())->addElement($Element);
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $MarginTop
     * @param string $PreRemark
     * @return Slice
     */
    public function getRatingContent($personId, $Height = '50px', $MarginTop = '15px', $PreRemark = 'Einschätzung: ')
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        $Element->setContent($PreRemark.'
                {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                    {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                {% else %}
                    &nbsp;
                {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop($MarginTop);

        if($tblSetting && $tblSetting->getValue()){
            $Element->styleAlignJustify();
        }

        return (new Slice())->addElement($Element);
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
                        {{ Content.P' . $personId . '.Input.Transfer }}.
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

    public function getTransferWithNoTransferOption($personId, $MarginTop = '5px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent(
                    '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        Versetzungsvermerk:
                    {% endif %}')
                , '22%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                          &nbsp;
                    {% endif %}')
                ->styleBorderBottom(
                    '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        1px
                    {% else %}
                        0px
                    {% endif %}'
                )
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
    public function getSignPart($personId, $isExtended = true, $MarginTop = '25px')
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
                        ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
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
     * @param string $backgroundColor
     *
     * @return Slice
     */
    protected function getGradeLanesSmall(
        $personId,
        $TextSize = '14px',
        $IsGradeUnderlined = false,
        $MarginTop = '10px',
        $backgroundColor = self::BACKGROUND_GRADE_FIELD
    ) {

        $TextSizeSmall = '8px';
        $paddingTopShrinking = '4.5px';
        $paddingBottomShrinking = '5px';

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
                        ->styleMarginTop('4px')
                        ->styleTextSize($TextSize)
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.P' . $personId . '.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($backgroundColor)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                ' . $paddingTopShrinking . ' 
                             {% else %}
                                 1px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                               ' . $paddingBottomShrinking . ' 
                             {% else %}
                                 1px
                             {% endif %}'
                        )
                        ->styleMarginTop('4px')
                        ->styleTextSize(
                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
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
     *
     * @deprecated ehemaliger Wahlpflichbereich Profil
     *
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
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
        if (($tblSubjectProfile = $this->getProfilSubject())) {
            $tblSubject = $tblSubjectProfile;
            if (strpos(strtolower($tblSubject->getName()), 'wissen') !== false
                && !preg_match('!(0?(8))!is', $this->getLevelName())
            ) {
                $profileAppendText = 'Profil mit informatischer Bildung';
            }
        }

        $foreignLanguageName = '---';
        // 3. Fremdsprache
        if (($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(3))) {
            $foreignLanguageName = $tblSubjectForeignLanguage->getName();
        }

        if ($tblSubject) {
            if (($tblSetting = Consumer::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
                && ($value = $tblSetting->getValue())
            ) {
                $subjectAcronymForGrade = $value;
            } else {
                $subjectAcronymForGrade = $tblSubject->getAcronym();
            }

            $elementName = (new Element())
                // Profilname aus der Schülerakte
                // bei einem Leerzeichen im Acronymn stürzt das TWIG ab
                ->setContent('
                   {% if(Content.P' . $personId . '.Student.Profile["' . $tblSubject->getAcronym() . '"] is not empty) %}
                       {{ Content.P' . $personId . '.Student.Profile["' . $tblSubject->getAcronym() . '"].Name' . ' }}
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
                    {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
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
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
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
     * @param bool $IsSmall
     * @param bool $IsFootnoteShowed
     *
     * @return Slice
     */
    public function getProfileStandardNew($personId, $TextSize = '14px', $IsGradeUnderlined = false, $IsSmall = false, $IsFootnoteShowed = true)
    {

        $tblPerson = Person::useService()->getPersonById($personId);

        $slice = new Slice();
        $sectionList = array();

        $TextSizeSmall = '8px';

        $paddingTop = '2px';
        $paddingBottom = '2px';
        $paddingTopShrinking = '4.5px';
        $paddingBottomShrinking = '5px';
        if($IsSmall){
            $paddingTop = '1px';
            $paddingBottom = '1px';
        }

        // Profil
        $tblSubjectProfile = $this->getProfilSubject();
        // 3. Fremdsprache
        $tblSubjectForeign = $this->getForeignLanguageSubject(3);

        if ($tblSubjectProfile && !$tblSubjectForeign) {
            $subjectAcronymForGrade = $tblSubjectProfile->getAcronym();
            $elementName = (new Element())
                ->setContent($tblSubjectProfile->getName())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->stylePaddingTop($paddingTop)
                ->stylePaddingBottom($paddingBottom)
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                         ' . $paddingTopShrinking . ' 
                    {% else %}
                        '.$paddingTop.'
                    {% endif %}'
                )
                ->stylePaddingBottom(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                         ' . $paddingBottomShrinking . ' 
                    {% else %}
                        '.$paddingBottom.'
                    {% endif %}'
                )
                ->styleTextSize(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                        ' . $TextSizeSmall . '
                    {% else %}
                        ' . $TextSize . '
                    {% endif %}'
                );
        } else {
            $elementName = (new Element())
                ->setContent('---')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->stylePaddingTop($paddingTop)
                ->stylePaddingBottom($paddingBottom)
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('&ndash;')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop($paddingTop)
                ->stylePaddingBottom($paddingBottom)
                ->styleTextSize($TextSize);
        }

        if ($tblSubjectForeign) {
            $elementForeignName = (new Element())
                // Profilname aus der Schülerakte
                // bei einem Leerzeichen im Acronymn stürzt das TWIG ab
                ->setContent($tblSubjectForeign->getName())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->stylePaddingTop($paddingTop)
                ->stylePaddingBottom($paddingBottom)
                ->styleTextSize($TextSize);

            if ($tblSubjectProfile) {
                $subjectAcronymForGrade = $tblSubjectProfile->getAcronym();
                // SSW-493 Profil vs. 3.FS
                $contentForeignGrade = '
                    {% if(Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] }}
                    {% else %}
                        {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}
                    {% endif %}
                ';

                $paddingTopGrade =
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                        ' . $paddingTopShrinking . ' 
                    {% else %}
                        {% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                            ' . $paddingTopShrinking . '
                        {% else %}
                            '.$paddingTop.' 
                        {% endif %}
                    {% endif %}';
                $paddingBottomGrade =
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                        ' . $paddingBottomShrinking . ' 
                    {% else %}
                        {% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                            ' . $paddingBottomShrinking . '
                        {% else %}
                            '.$paddingBottom.' 
                        {% endif %}
                    {% endif %}';
                $textSizeGrade =
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                        ' . $TextSizeSmall . '
                    {% else %}
                        {% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                            ' . $TextSizeSmall . '
                        {% else %}
                            ' . $TextSize . '
                        {% endif %}
                    {% endif %}';
            } else {
                $contentForeignGrade = '
                    {% if(Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $tblSubjectForeign->getAcronym() . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}
                ';

                $paddingTopGrade =
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                         ' . $paddingTopShrinking . ' 
                    {% else %}
                        '.$paddingTop.'
                    {% endif %}';
                $paddingBottomGrade = '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                         ' . $paddingBottomShrinking . ' 
                    {% else %}
                        '.$paddingBottom.'
                    {% endif %}';
                $textSizeGrade =
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubjectForeign->getAcronym() . '"] is not empty) %}
                        ' . $TextSizeSmall . '
                    {% else %}
                        ' . $TextSize . '
                    {% endif %}';
            }

            $elementForeignGrade = (new Element())
                ->setContent($contentForeignGrade)
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop($paddingTopGrade)
                ->stylePaddingBottom($paddingBottomGrade)
                ->styleTextSize($textSizeGrade);
        } else {
            $elementForeignName = (new Element())
                ->setContent('---')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->stylePaddingTop($paddingTop)
                ->stylePaddingBottom($paddingBottom)
                ->styleTextSize($TextSize);

            $elementForeignGrade = (new Element())
                ->setContent('&ndash;')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop($paddingTop)
                ->stylePaddingBottom($paddingBottom)
                ->styleTextSize($TextSize);
        }

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Wahlpflichtbereich:')
                ->styleTextBold()
                ->styleMarginTop('15px')
                ->styleMarginBottom('5px')
                ->styleTextSize($TextSize)
            );

        $sectionList[] = $section;
        $section = new Section();
        $section
            ->addElementColumn($elementName
                , '38%')
            ->addElementColumn((new Element()), '1%')
//            ->addElementColumn((new Element())
//                ->setContent('Profil')
//                ->stylePaddingTop($paddingTop)
//                ->stylePaddingBottom($paddingBottom)
//                ->styleTextSize($TextSize)
//                ->styleAlignCenter()
//                , '7%')
            ->addElementColumn($elementGrade, '9%')
            ->addElementColumn((new Element()), '4%')
            ->addElementColumn($elementForeignName, '38%')
            ->addElementColumn((new Element()), '1%')
            ->addElementColumn($elementForeignGrade, '9%');
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('besuchtes schulspezifisches Profil' . ($IsFootnoteShowed ? '¹' : ''))
                ->styleTextSize('11px')
                , '52%')
            ->addElementColumn((new Element())
                ->setContent('3. Fremdsprache (ab Klassenstufe 8)' . ($IsFootnoteShowed ? '¹' : ''))
                ->styleTextSize('11px')
            );
        $sectionList[] = $section;

        return $slice->addSectionList($sectionList);
    }

    /**
     *@deprecated
     *
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @return Slice
     */
    public function getOrientationStandard($personId, $TextSize = '14px', $IsGradeUnderlined = false)
    {
        $marginTop = '3px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;

        // Zeugnisnoten im Wortlaut auf Abschlusszeugnissen --> breiter Zensurenfelder
        if (($tblCertificate = $this->getCertificateEntity())
            && ($tblCertificateType = $tblCertificate->getTblCertificateType())
            && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
            && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
            && $tblSetting->getValue()
        ) {
            $subjectWidth = 89;
            $gradeWidth = 11;
            $TextSizeSmall = '13px';
            $paddingTopShrinking = '4px';
            $paddingBottomShrinking = '4px';
        } else {
            $subjectWidth = 91;
            $gradeWidth = 9;
            $TextSizeSmall = '8.5px';
            $paddingTopShrinking = '5px';
            $paddingBottomShrinking = '6px';
        }

        // Neigungskurs
        if (($tblSubject = $this->getOrientationSubject())) {
            $subjectAcronymForGrade = $tblSubject->getAcronym();
            $elementOrientationName = new Element();
            $elementOrientationName
                ->setContent($tblSubject->getName())
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('7px')
                ->styleTextSize($TextSize);

            $elementOrientationGrade = new Element();
            $elementOrientationGrade
                ->setContent('
                    {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                         ' . $paddingTopShrinking . ' 
                     {% else %}
                         2px
                     {% endif %}'
                )
                ->stylePaddingBottom(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                          ' . $paddingBottomShrinking . ' 
                     {% else %}
                         2px
                     {% endif %}'
                )
                ->styleTextSize(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                         ' . $TextSizeSmall . '
                     {% else %}
                         ' . $TextSize . '
                     {% endif %}'
                )
                ->styleMarginTop($marginTop);
        }

        // 2. Fremdsprache
        if (($tblSubject = $this->getForeignLanguageSubject(2))) {
            $elementForeignLanguageName = new Element();
            $elementForeignLanguageName
                ->setContent('
                {% if(Content.P' . $personId . '.Student.ForeignLanguage["' . $tblSubject->getAcronym() . '"] is not empty) %}
                     {{ Content.P' . $personId . '.Student.ForeignLanguage["' . $tblSubject->getAcronym() . '"].Name' . ' }}
                {% else %}
                     &nbsp;
                {% endif %}')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('7px')
                ->styleTextSize($TextSize);

            $elementForeignLanguageGrade = new Element();
            $elementForeignLanguageGrade
                ->setContent('
                {% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                    {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                {% else %}
                    &ndash;
                {% endif %}')
                ->styleAlignCenter()
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                     ' . $paddingTopShrinking . ' 
                 {% else %}
                     2px
                 {% endif %}'
                )
                ->stylePaddingBottom(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                      ' . $paddingBottomShrinking . ' 
                 {% else %}
                     2px
                 {% endif %}'
                )
                ->styleTextSize(
                    '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                     ' . $TextSizeSmall . '
                 {% else %}
                     ' . $TextSize . '
                 {% endif %}'
                )
                ->styleMarginTop($marginTop);
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
                ->addElementColumn($elementOrientationName, (string)$subjectWidth . '%')
                ->addElementColumn($elementOrientationGrade, (string)$gradeWidth . '%');
            $sectionList[] = $section;

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('<u>Neigungskurs (Neigungskursbereich)</u> / 2. Fremdsprache (abschlussorientiert)')
                    ->styleBorderTop()
                    ->styleMarginTop('0px')
                    ->stylePaddingTop()
                    ->styleTextSize('13px')
                    , (string)($subjectWidth - 2) . '%')
                ->addElementColumn((new Element()), (string)($gradeWidth + 2) . '%');
            $sectionList[] = $section;
        } elseif ($elementForeignLanguageName) {
            $section = new Section();
            $section
                ->addElementColumn($elementForeignLanguageName, (string)$subjectWidth . '%')
                ->addElementColumn($elementForeignLanguageGrade, (string)$gradeWidth . '%');
            $sectionList[] = $section;

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('Neigungskurs (Neigungskursbereich) / <u>2. Fremdsprache (abschlussorientiert)</u>')
                    ->styleBorderTop()
                    ->styleMarginTop('0px')
                    ->stylePaddingTop()
                    ->styleTextSize('13px')
                    , (string)($subjectWidth - 2) . '%')
                ->addElementColumn((new Element()), (string)($gradeWidth + 2) . '%');
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
                ->styleBackgroundColor(self::BACKGROUND_GRADE_FIELD)
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
     * @param bool|string $height
     * @param bool $hasSecondLanguageSecondarySchool
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
        $fontFamily = 'MetaPro',
        $height = false,
        $hasSecondLanguageSecondarySchool = false
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
                if($tblSubject){
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
            }

            $tblSecondForeignLanguageSecondarySchool = false;
            if ($hasSecondLanguageSecondarySchool
                && ($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))
            ) {
                $tblSecondForeignLanguageSecondarySchool = $tblSubjectForeignLanguage;
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

            // Mittelschulzeugnisse 2. Fremdsprache anfügen
            if ($hasSecondLanguageSecondarySchool) {
                // Zeiger auf letztes Element
                end($SubjectStructure);
                $lastItem = &$SubjectStructure[key($SubjectStructure)];

                $column = array(
                    'SubjectAcronym' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getAcronym() : 'SECONDLANGUAGE',
                    'SubjectName' => $tblSecondForeignLanguageSecondarySchool
                        ? $tblSecondForeignLanguageSecondarySchool->getName()
                        : '&ndash;'
                );

                if (isset($lastItem[1]) && isset($lastItem[2])) {
                    $SubjectStructure[][1] = $column;
                } elseif (isset($lastItem[1])) {
                    $lastItem[2] = $column;
                } else {
                    $lastItem[1] = $column;
                }
            }

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

                    if ($hasSecondLanguageSecondarySchool
                        && (($tblSecondForeignLanguageSecondarySchool && $Subject['SubjectAcronym'] == $tblSecondForeignLanguageSecondarySchool->getAcronym())
                            || ($Subject['SubjectAcronym'] == 'SECONDLANGUAGE'))
                    ) {
                        $SubjectSection->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent($Subject['SubjectName'] . ($Subject['SubjectName'] == '&ndash;' ? '' : ':'))
                                    ->styleTextColor($TextColor)
                                    ->stylePaddingTop()
                                    ->styleMarginTop($count == 1 ? $MarginTop : '4px')
                                    ->styleBorderBottom('0.5px', $TextColor)
                                    ->styleTextSize($TextSize)
                                    ->styleFontFamily($fontFamily)
                                    , $widthText)
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('2. Fremdsprache (abschlussorientiert)')
                                    ->stylePaddingTop('-4px')
                                    ->stylePaddingBottom('0px')
                                    ->styleMarginTop('0px')
                                    ->styleMarginBottom('0px')
                                    ->styleTextSize('9px')
                                    ->styleTextColor($TextColor)
                                    ->styleFontFamily($fontFamily)
                                    , $widthText)
                                )
                        );
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'] . ($Subject['SubjectName'] == '&ndash;' ? '' : ':'))
                            ->styleTextColor($TextColor)
                            ->stylePaddingTop()
                            ->styleMarginTop($count == 1 ? $MarginTop : '4px')
                            ->styleTextSize($TextSize)
                            ->styleFontFamily($fontFamily)
                            , $widthText);
                    }

                    if ($GradeFieldWidth > 24 && strlen($Subject['SubjectName']) > 20 && preg_match('!\s!', $Subject['SubjectName'])) {
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

        return $height ? $SubjectSlice->styleHeight($height) : $SubjectSlice;
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
            ->styleHeight('5px')
        );
        $SubjectSlice->addSection($marginTopSection);
        $SectionList[] = $marginTopSection;

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if($tblSubject){
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
            }

            // add SecondLanguageField
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if (($tblSubjectForeignLanguage = $this->getForeignLanguageSubject(2))) {
                        $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                        $SubjectStructure[$languagesWithStartLevel['Rank']]
                        [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
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

            $hasAdditionalLine = array();
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

                    // Zeugnistext soll nicht verkleinert werden SSW-2331
//                    $TextSizeSmall = '8px';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
//                        ->stylePaddingTop(
//                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
//                                 4px
//                             {% else %}
//                                 2px
//                             {% endif %}'
//                        )
//                        ->stylePaddingBottom(
//                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
//                                 5px
//                             {% else %}
//                                 2px
//                             {% endif %}'
//                        )
                        ->stylePaddingTop('2px')
                        ->stylePaddingBottom('2px')
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : $marginTop)
                        ->styleTextSize($TextSize)
//                        ->styleTextSize(
//                            '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
//                                 ' . $TextSizeSmall . '
//                             {% else %}
//                                 ' . $TextSize . '
//                             {% endif %}'
//                        )
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
                            '{% if(Content.P' . $personId . '.Subject.Level.2 is not empty) %}
                                {{ Content.P' . $personId . '.Subject.Level.2 }})
                            {% else %}
                                &ndash;)
                            {% endif %}')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , '39%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), (50) . '%');
                    }

                    $hasAdditionalLine = array();

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
        $TextSizeSmall = '8px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;
        if ($tblPerson) {
            // Neigungskurs
            if (($tblSubject = $this->getOrientationSubject())) {
                $subjectAcronymForGrade = $tblSubject->getAcronym();
                $elementOrientationName = new Element();
                $elementOrientationName
                    ->setContent($tblSubject->getName())
                    ->styleFontFamily('Trebuchet MS')
                    ->styleLineHeight('85%')
                    ->stylePaddingTop('0px')
                    ->stylePaddingBottom('0px')
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize);

                $elementOrientationGrade = new Element();
                $elementOrientationGrade
                    ->setContent('
                        {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                        {% else %}
                            &ndash;
                        {% endif %}')
                    ->styleFontFamily('Trebuchet MS')
                    ->styleLineHeight('85%')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#E9E9E9')
                    ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                    ->stylePaddingTop(
                        '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                             1px
                         {% else %}
                             2px
                         {% endif %}'
                    )
                    ->stylePaddingBottom(
                        '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                             3px
                         {% else %}
                             2px
                         {% endif %}'
                    )
                    ->styleMarginTop($marginTop)
                    ->styleTextSize(
                        '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $subjectAcronymForGrade . '"] is not empty) %}
                             ' . $TextSizeSmall . '
                         {% else %}
                             ' . $TextSize . '
                         {% endif %}'
                    );
            }

            // 2. Fremdsprache
            if (($tblSubject = $this->getForeignLanguageSubject(2))) {
                $elementForeignLanguageName = new Element();
                $elementForeignLanguageName
                    ->setContent($tblSubject->getName())
                    ->styleFontFamily('Trebuchet MS')
                    ->styleLineHeight('85%')
                    ->stylePaddingTop('2px')
                    ->stylePaddingBottom('0px')
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize);

                $elementForeignLanguageGrade = new Element();
                $elementForeignLanguageGrade
                    ->setContent('
                    {% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                        {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                    {% else %}
                        &ndash;
                    {% endif %}')
                    ->styleFontFamily('Trebuchet MS')
                    ->styleLineHeight('85%')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#E9E9E9')
                    ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                    ->stylePaddingTop(
                        '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                             1px
                         {% else %}
                             2px
                         {% endif %}'
                    )
                    ->stylePaddingBottom(
                        '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                             3px
                         {% else %}
                             2px
                         {% endif %}'
                    )
                    ->styleMarginTop($marginTop)
                    ->styleTextSize(
                        '{% if(Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $tblSubject->getAcronym() . '"] is not empty) %}
                         ' . $TextSizeSmall . '
                     {% else %}
                         ' . $TextSize . '
                     {% endif %}'
                    );
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

    /**
     * @param bool $isOS
     *
     * @return string
     */
    public function getUsedPicture(bool $isOS = false) : string
    {
        if ($isOS
            && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Generate', 'PictureAddressForOS'))
            && $tblSetting->getValue() != ''
        ) {
            return (string)$tblSetting->getValue();
        }

        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
            'Education', 'Certificate', 'Generate', 'PictureAddress'))
        ) {
            return (string)$tblSetting->getValue();
        }

        return '';
    }

    /**
     * @param bool $isOS
     *
     * @return string
     */
    private function getPictureHeight(bool $isOS = false) : string
    {
        $StandardHeight = '66px';
        $value = '';

        if ($isOS
            && ($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Generate', 'PictureHeightForOS'))
        ) {
            $value = $tblSetting->getValue();
        }

        if ($value == '' && ($tblSetting = Consumer::useService()->getSetting(
            'Education', 'Certificate', 'Generate', 'PictureHeight'))
        ) {
            $value = $tblSetting->getValue();
        }

        return $value ? $value : $StandardHeight;
    }

    /**
     * @param string $content
     * @param string $thicknessInnerLines
     *
     * @return Slice
     */
    protected function setCheckBox($content = '&nbsp;', $thicknessInnerLines = '0.5px')
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('7px')
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('10px')
                    , '1.2%')
                ->addElementColumn((new Element())
                    ->setContent($content)
                    ->styleHeight('14px')
                    ->styleTextSize('8.5')
                    ->stylePaddingLeft('1.2px')
                    ->stylePaddingTop('-2px')
                    ->stylePaddingBottom('-2px')
                    ->styleBorderAll($thicknessInnerLines)
                    , '1.6%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('10px')
                    , '1.2%')
            )
            ->styleHeight('24px');
    }

    /**
     * @param        $personId
     * @param string $MarginTop
     * @param string $FontSize
     *
     * @return Slice $Slice
     */
    public function getCourse($personId, $MarginTop = '15px', $FontSize = '14px')
    {

        $Slice = (new Slice())
            ->addElement((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                        nahm am Unterricht mit dem Ziel des {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                    {% else %}
                        &nbsp;
                    {% endif %}'
                )
                ->styleMarginTop($MarginTop)
                ->styleTextSize($FontSize)
            );
        return $Slice;
    }

    /**
     * @param $content
     * @param string $size
     *
     * @return string
     */
    public function setSup($content, $size = '60%')
    {
        return '<sup style="font-size: ' . $size . ' !important;">' . $content . '</sup>';
    }
}

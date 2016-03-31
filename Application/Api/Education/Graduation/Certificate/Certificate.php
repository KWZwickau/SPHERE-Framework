<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

abstract class Certificate extends Extension
{

    /** @var null|Frame $Certificate */
    private $Certificate = null;

    /** @var null|TblPerson $tblPerson */
    private $tblPerson = null;
    /** @var null|TblStudent $tblStudent */
    private $tblStudent = null;
    /** @var null|TblCompany $tblCompany */
    private $tblCompany = null;
    /** @var null|TblDivision $tblDivision */
    private $tblDivision = null;

    private $Person = array('Data' => array());
    private $Company = array('Data' => array());
    private $Division = array('Data' => array());
    private $Grade = array('Data' => array());

    public function __construct(TblPerson $tblPerson, TblDivision $tblDivision)
    {

        $this->getCache(new TwigHandler())->clearCache();

        $this->tblPerson = $tblPerson;
        $this->tblDivision = $tblDivision;
        $this->tblStudent = $this->fetchStudentByPerson();
        $this->tblCompany = $this->fetchCompanyByStudent();

        $this->Certificate = $this->buildCertificate();
    }

    /**
     * @return bool|TblStudent
     */
    final private function fetchStudentByPerson()
    {

        return Student::useService()->getStudentByPerson($this->tblPerson);
    }

    /**
     * @return bool|TblCompany
     */
    final private function fetchCompanyByStudent()
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
        if ($tblStudentTransferType && $this->tblStudent) {
            $tblStudentTransfer = Student::useService()->getStudentTransferByType($this->tblStudent,
                $tblStudentTransferType);
            return $tblStudentTransfer->getServiceTblCompany();
        }
        return false;
    }

    /**
     * @return Frame
     */
    abstract public function buildCertificate();

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

        $this->prepareData();

        /**
         * Assign
         */
        $this->Certificate->setData(array('Person' => $this->Person));
        $this->Certificate->setData(array('Company' => $this->Company));
        $this->Certificate->setData(array('Division' => $this->Division));
        $this->Certificate->setData(array('Grade' => $this->Grade));
        if (!empty( $Data )) {
            $this->Certificate->setData($Data);
        }
        return $this->Certificate->getTemplate();
    }

    /**
     * @return $this
     */
    private function prepareData()
    {

        /**
         * Allocate Person
         */
        $this->allocatePersonData();
        // Set Person-Address
        if ($this->tblPerson) {
            $Address = $this->tblPerson->fetchMainAddress();
            if ($Address) {
                $this->allocatePersonAddress($Address);
            }
        }
        // Set Person-Common
        if ($this->tblPerson) {
            $Common = Common::useService()->getCommonByPerson($this->tblPerson);
            if ($Common) {
                $this->allocatePersonCommon($Common);
            }
        }
        if ($this->tblStudent) {
            $this->allocatePersonStudent($this->tblStudent);
        }
        /**
         * Allocate Company
         */
        $this->allocateCompanyData();
        // Set School-Address
        if ($this->tblCompany) {
            $Address = $this->tblCompany->fetchMainAddress();
            if ($Address) {
                $this->allocateCompanyAddress($Address);
            }
        }

        /**
         * Allocate Division
         */
        $this->allocateDivisionData();

        /**
         * Allocate Grade
         */
        $this->allocateGradeData();

        return $this;
    }

    /**
     * @return $this
     */
    private function allocatePersonData()
    {

        $this->Person['Data'] = $this->tblPerson->__toArray();
        $this->Person['Data']['Name']['Salutation'] = $this->tblPerson->getSalutation();
        $this->Person['Data']['Name']['First'] = $this->tblPerson->getFirstName();
        $this->Person['Data']['Name']['Last'] = $this->tblPerson->getLastName();

        return $this;
    }

    /**
     * @param TblAddress $tblAddress
     *
     * @return $this
     */
    private function allocatePersonAddress(TblAddress $tblAddress)
    {

        $this->Person['Address'] = array_merge($tblAddress->__toArray(),
            array('City' => $tblAddress->getTblCity()->__toArray()));
        if ($tblAddress->getTblState()) {
            $this->Person['Address'] = array_merge($this->Person['Address'],
                array('State' => $tblAddress->getTblState()->__toArray()));
        }
        $this->Person['Address']['Street']['Name'] = $tblAddress->getStreetName();
        $this->Person['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
        return $this;
    }

    /**
     * @param TblCommon $tblCommon
     *
     * @return $this
     */
    private function allocatePersonCommon(TblCommon $tblCommon)
    {

        $this->Person['Common'] = $tblCommon->__toArray();

        $BirthDates = $tblCommon->getTblCommonBirthDates();
        if ($BirthDates) {
            $this->Person['Common']['BirthDates'] = $BirthDates->__toArray();
            $this->Person['Common']['BirthDates']['Birthplace'] = $BirthDates->getBirthplace() ? $BirthDates->getBirthplace() : '&nbsp;';
        }
        return $this;
    }

    private function allocatePersonStudent(TblStudent $tblStudent)
    {

        $this->Person['Student'] = $tblStudent->__toArray();

        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
        $tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType);
        if ($tblTransfer) {
            if ($tblTransfer->getServiceTblCourse()) {
                $this->Person['Student']['Course'] = $tblTransfer->getServiceTblCourse()->getName();
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function allocateCompanyData()
    {

        if ($this->tblCompany) {
            $this->Company['Data'] = $this->tblCompany->__toArray();
        }
        return $this;
    }

    /**
     * @param TblAddress $tblAddress
     *
     * @return $this
     */
    private function allocateCompanyAddress(TblAddress $tblAddress)
    {

        $this->Company['Address'] = array_merge($tblAddress->__toArray(),
            array('City' => $tblAddress->getTblCity()->__toArray()));
        $this->Company['Address'] = array_merge($this->Company['Address'],
            array('State' => $tblAddress->getTblState()->__toArray()));
        $this->Company['Address']['Street']['Name'] = $tblAddress->getStreetName();
        $this->Company['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
        return $this;
    }

    /**
     * @return $this
     */
    private function allocateDivisionData()
    {

        $this->Division['Data'] = $this->tblDivision->__toArray();

        $Level = $this->tblDivision->getTblLevel();
        if ($Level) {
            $this->Division['Data']['Level'] = $this->tblDivision->getTblLevel()->__toArray();
        }

        $Term = $this->tblDivision->getServiceTblYear();
        if ($Term) {
            // TODO: Schuljahr- / Halbjahr-Übergabe
//            $Term = $Term->getTblPeriodAll();
//            var_dump( $Term );
//            $this->Division['Data']['Year'] = $Term->getTblLevel()->__toArray();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function allocateGradeData()
    {

        $this->Grade['Data'] = array();

        if ($this->tblStudent) {
            $tblStudentSubjectAll = Student::useService()->getStudentSubjectAllByStudent($this->tblStudent);
            if ($tblStudentSubjectAll) {
                $this->Grade['Data'] = array_merge(
                    $this->Grade['Data'], $this->fetchStudentSubjectGrades($tblStudentSubjectAll)
                );
            }
        }

        if ($this->tblDivision) {
            $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($this->tblDivision);
            if ($tblDivisionSubjectAll) {
                $this->Grade['Data'] = array_merge(
                    $this->Grade['Data'], $this->fetchDivisionSubjectGrades($tblDivisionSubjectAll)
                );
            }
        }

        return $this;
    }

    /**
     * @param TblStudentSubject[] $tblStudentSubjectAll
     *
     * @return array
     */
    private function fetchStudentSubjectGrades($tblStudentSubjectAll)
    {

        $Result = array();
        array_walk($tblStudentSubjectAll, function (TblStudentSubject $tblStudentSubject) use (&$Result) {

            $tblSubject = $tblStudentSubject->getServiceTblSubject();
            $tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking();
            $tblStudentSubjectType = $tblStudentSubject->getTblStudentSubjectType();

            if ($tblSubject && $tblStudentSubjectRanking && $tblStudentSubjectType) {
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
                if ($tblTestType) {

                    $tblGradeAll = Gradebook::useService()->getGradesByStudent(
                        $this->tblPerson, $this->tblDivision, $tblSubject, $tblTestType
                    );
                    if ($tblGradeAll) {
                        // TODO: Nach Zeit, Durchschnitt, etc... ??
                        /** @var TblGrade $tblGrade */
                        $tblGrade = end($tblGradeAll);

                        $Result
                        [$tblStudentSubjectType->getIdentifier()]
                        [$tblSubject->getAcronym()]
                        [$tblStudentSubjectRanking->getIdentifier()]
                            = $tblGrade->getGrade();
                    }
                }
            }
        });
        return $Result;
    }

    /**
     * @param TblDivisionSubject[] $tblDivisionSubjectAll
     *
     * @return array
     */
    private function fetchDivisionSubjectGrades($tblDivisionSubjectAll)
    {

        $Result = array();
        $tblSubjectAll = array();
        array_walk($tblDivisionSubjectAll, function (TblDivisionSubject $tblDivisionSubject) use (&$tblSubjectAll) {

            $tblSubjectAll[] = $tblDivisionSubject->getServiceTblSubject();
        });

        if (!empty( $tblSubjectAll )) {
            array_walk($tblSubjectAll, function (TblSubject $tblSubject) use (&$Result) {

                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
                if ($tblTestType) {
                    $tblGradeAll = Gradebook::useService()->getGradesByStudent(
                        $this->tblPerson, $this->tblDivision, $tblSubject, $tblTestType
                    );
                    if ($tblGradeAll) {
                        // TODO: Nach Zeit, Durchschnitt, etc... ??
                        /** @var TblGrade $tblGrade */
                        $tblGrade = end($tblGradeAll);

                        $Result[$tblSubject->getAcronym()] = $tblGrade->getGrade();
                    }
                }
            });
        }
        return $Result;
    }

    /**
     * @return array
     */
    public function getPerson()
    {

        $this->prepareData();
        return $this->Person;
    }

    /**
     * @return array
     */
    public function getCompany()
    {

        $this->prepareData();
        return $this->Company;
    }

    /**
     * @return array
     */
    public function getDivision()
    {

        $this->prepareData();
        return $this->Division;
    }

    /**
     * @return array
     */
    public function getGrade()
    {

        $this->prepareData();
        return $this->Grade;
    }
}

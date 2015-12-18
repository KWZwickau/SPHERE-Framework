<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return (new Data($this->getBinding()))->getLevelAll();
    }

    /**
     * @return bool|TblDivision[]
     */
    public function getDivisionAll()
    {

        return (new Data($this->getBinding()))->getDivisionAll();
    }

    /**
     * @param IFormInterface $Form
     * @param                $Level
     * @param                $Division
     *
     * @return IFormInterface|string
     */
    public function createLevelDivision(
        IFormInterface $Form,
        $Level, $Division
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Level && null === $Division) {
            return $Form;
        }

        $Error = false;

        if (isset( $Division['Name'] ) && empty( $Division['Name'] )) {
            $Form->setError('Division[Name]', 'Bitte geben Sie eine Klassengruppe an');
            $Error = true;
        }
        if (!isset( $Division['Year'] ) || empty( $Division['Year'] )) {
            $Form->setError('Division[Year]', 'Jahr erforderlich! Bitte zuerst einpflegen');
            $Error = true;
        }
        if (isset( $Division['Name'] )
            && !empty( $Division['Name'] )
            && !isset( $Level['Name'] )
            && !$Error
        ) {

            if (Division::useService()->getDivisionByGroupAndLevelAndYear($Division['Name'], null, $Division['Year'])) {
                $Form->setError('Division[Name]', 'Name in diesem Schuljahr schon vergeben');
                $Error = true;
            }

            if (isset($Division['Year'])) {
                $tblYear = Term::useService()->getYearById($Division['Year']);
                if (empty($tblYear)) {
                    $Form->setError('Division[Year]', 'Schuljahr nicht gefunden');
                    $Error = true;
                }
            } else {
                $Form->setError('Division[Year]', 'Schuljahr benötigt');
                $Error = true;
            }
            if (isset($Division['Year'])) {

                $tblYear = Term::useService()->getYearById($Division['Year']);
                if (empty($tblYear)) {
                    $Form->setError('Division[Year]', 'Schuljahr nicht gefunden');
                    $Error = true;
                }
            }
            if (!$Error) {

                $tblYear = Term::useService()->getYearById($Division['Year']);
                (new Data($this->getBinding()))->createDivision($tblYear, null, $Division['Name'],
                    $Division['Description']);
                return new Success('Die KlassenGruppe wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), 1);
            }
        }

        if (isset($Level['Name']) && empty($Level['Name'])) {
            $Form->setError('Level[Name]', 'Bitte geben Sie eine Klassenstufe für die Schulart an <br/>');
            $Error = true;
        }

        if (!$Error) {
            $tblType = Type::useService()->getTypeById($Level['Type']);
            $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, $Level['Name']);

            $Error = false;
            if (isset($Division['Year'])) {
                $tblYear = Term::useService()->getYearById($Division['Year']);
                if (empty($tblYear)) {
                    $Form->setError('Division[Year]', 'Schuljahr nicht gefunden');
                    $Error = true;
                }
            } else {
                $Form->setError('Division[Year]', 'Schuljahr benötigt');
                $Error = true;
            }
            if ($tblLevel) {
                if (empty($tblLevel)) {
                    $Form->setError('Level[Name]', 'Klassenstufe nicht gefunden');
                    $Error = true;
                }
            } else {
                $Form->setError('Level[Name]', 'Klassenstufe benötigt');
                $Error = true;
            }

            if (!$Error) {
                if (isset($Division['Name']) && empty($Division['Name'])) {
                    $Form->setError('Division[Name]', 'Bitte geben Sie eine Klassengruppe an');
                    $Error = true;
                } else {
                    if ($this->getDivisionByGroupAndLevelAndYear($Division['Name'], $tblLevel->getId(),
                        $Division['Year'])
                    ) {
                        $Form->setError('Division[Name]', 'Name wird in der Klassenstufe/Jahrgang bereits verwendet');
                        $Error = true;
                    }
                }
            }

            if (!$Error) {
                $tblYear = Term::useService()->getYearById($Division['Year']);
                (new Data($this->getBinding()))->createDivision($tblYear, $tblLevel, $Division['Name'],
                    $Division['Description']);
                return new Success('Die KlassenGruppe wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), 1);
            }
        }
        return $Form;
    }

    public function selectYear(IFormInterface $Form, $Year)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Year) {
            return $Form;
        }

        $Error = false;

        if (isset( $Year ) && empty( $Year )) {
            $Form->setError('Year', 'Schuljahr benötigt!');
            $Error = true;
        }
        if (!$Error) {
            return new Redirect('/Education/Lesson/Division', 1, array('Year' => $Year));
        }


        return $Form;
    }

    /**
     * @param TblType $tblType
     * @param string  $Name
     *
     * @return bool|TblLevel
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        return (new Data($this->getBinding()))->checkLevelExists($tblType, $Name);
    }

    /**
     * @param TblType $tblType
     * @param string  $Name
     * @param string  $Description
     *
     * @return bool|TblLevel
     */
    public function insertLevel(TblType $tblType, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createLevel($tblType, $Name, $Description);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return (new Data($this->getBinding()))->getLevelById($Id);
    }

    /**
     * @param      $Name
     * @param null $Level
     * @param      $Year
     *
     * @return bool|false|\SPHERE\System\Database\Fitting\Element
     */
    public function getDivisionByGroupAndLevelAndYear($Name, $Level = null, $Year)
    {

        if ($Level !== null) {
            $tblLevel = $this->getLevelById($Level);
        } else {
            $tblLevel = null;
        }
        $tblYear = Term::useService()->getYearById($Year);
        return (new Data($this->getBinding()))->getDivisionByGroupAndLevelAndYear($Name, $tblLevel, $tblYear);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return bool|TblDivisionTeacher
     */
    public function getDivisionTeacherByDivisionAndTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson);
    }

    /**
     * @param TblYear  $tblYear
     * @param TblLevel $tblLevel
     * @param string   $Name
     * @param string   $Description
     *
     * @return null|TblDivision
     */
    public function insertDivision(TblYear $tblYear, TblLevel $tblLevel, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createDivision($tblYear, $tblLevel, $Name, $Description);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return bool
     */
    public function removeStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $tblStudentSubjectList = (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
        if ($tblStudentSubjectList) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                (new Data($this->getBinding()))->removeSubjectStudent($tblStudentSubject);
            }
        }

        return (new Data($this->getBinding()))->removeStudentToDivision($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return TblDivisionTeacher
     */
    public function insertDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addDivisionTeacher($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return TblDivisionStudent
     */
    public function addStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return bool
     */
    public function removeTeacherToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->removeTeacherToDivision($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     *
     * @return string
     */
    public function removeSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        $tblDivisionSubjectList = $this->getDivisionSubjectByDivision($tblDivision);
        if ($tblDivisionSubjectList) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if ($tblDivisionSubject->getServiceTblSubject()->getId() === $tblSubject->getId()) {
                    (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
                    (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        (new Data($this->getBinding()))->removeSubjectGroup($tblDivisionSubject->getTblSubjectGroup());
                    }
                }
            }
        }
        return (new Data($this->getBinding()))->removeSubjectToDivision($tblDivision, $tblSubject);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return mixed
     */
    public function removeDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->removeDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblSubjectStudent $tblSubjectStudent
     *
     * @return string
     */
    public function removeSubjectStudent(TblSubjectStudent $tblSubjectStudent)
    {

        return (new Data($this->getBinding()))->removeSubjectStudent($tblSubjectStudent);
    }

    /**
     * @param TblSubjectTeacher $tblSubjectTeacher
     *
     * @return bool
     */
    public function removeSubjectTeacher(TblSubjectTeacher $tblSubjectTeacher)
    {

        return (new Data($this->getBinding()))->removeSubjectTeacher($tblSubjectTeacher);
    }

    /**
     * @param TblSubjectGroup    $tblSubjectGroup
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectGroup(TblSubjectGroup $tblSubjectGroup, TblDivisionSubject $tblDivisionSubject)
    {

        if ($tblDivisionSubject->getTblSubjectGroup()->getId() === $tblSubjectGroup->getId()) {
            (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
            (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
        }

        return (new Data($this->getBinding()))->removeSubjectGroup($tblSubjectGroup);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     * @param             $Description
     *
     * @return null|object|TblDivisionTeacher
     */
    public function addDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson, $Description)
    {

        return (new Data($this->getBinding()))->addDivisionTeacher($tblDivision, $tblPerson, $Description);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     *
     * @return null|object|TblDivisionSubject
     */
    public function addSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject);
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param TblSubject     $tblSubject
     * @param array          $Group
     * @param int            $DivisionSubjectId
     *
     * @return null|object|TblDivisionSubject|IFormInterface
     */
    public function addSubjectToDivisionWithGroup(IFormInterface $Form, TblDivision $tblDivision, TblSubject $tblSubject, $Group, $DivisionSubjectId)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }
        $Error = false;
        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an');
            $Error = true;
        }

        if (!$Error) {
            $tblGroup = (new Data($this->getBinding()))->createSubjectGroup($Group['Name'], $Group['Description']);
            if ($tblGroup) {
                if ((new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject, $tblGroup)) {
                    return new Success('Die Gruppe '.new Bold($Group['Name']).' wurde erfolgreich angelegt')
                    .new Redirect('/Education/Lesson/Division/SubjectGroup/Add', 1, array('Id'                => $tblDivision->getId(),
                                                                                          'DivisionSubjectId' => $DivisionSubjectId));
                } else {
                    return new Danger('Die Gruppe '.new Bold($Group['Name']).' wurde nicht angelegt')
                    .new Redirect('/Education/Lesson/Division/SubjectGroup/Add', 1, array('Id'                => $tblDivision->getId(),
                                                                                          'DivisionSubjectId' => $DivisionSubjectId));
                }

            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param int            $DivisionSubject
     * @param array          $Student
     * @param int            $DivisionId
     *
     * @return IFormInterface|string
     */
    public function addSubjectStudent(IFormInterface $Form, $DivisionSubject, $Student, $DivisionId)
    {

        $Global = $this->getGlobal();

        /**
         * Skip to Frontend
         */
        if (!isset( $Global->POST['Button']['Submit'] )) {
            return $Form;
        }

        $Error = false;
        if (empty( $DivisionSubject )) {
            $Form .= new Warning('Keine Zuordnung ohne Fach möglich');
            $Error = true;
        }

        if (!$Error) {

            // Remove old Link
            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubject);
            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if (is_array($tblSubjectStudentList)) {
                array_walk($tblSubjectStudentList, function ($tblSubjectStudentList) {

                    if (!$this->removeSubjectStudent($tblSubjectStudentList)) {
                    }
                });
            }

            // Add new Link
            if (is_array($Student)) {
                array_walk($Student, function ($Student) use ($tblDivisionSubject, &$Error) {

                    if (!(new Data($this->getBinding()))->addSubjectStudent(Person::useService()->getPersonById($Student), $tblDivisionSubject)) {
                        $Error = false;
                    }
                });
            }

            if (!$Error) {
                return new Success('Die Gruppe mit Personen wurden erfolgreich angelegt')
                .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
            } else {
                return new Danger('Einige Personen konnte nicht in der Gruppe angelegt werden')
                .new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $tblDivision->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param array          $SubjectTeacher
     * @param int            $DivisionId
     * @param int            $DivisionSubjectId
     *
     * @return IFormInterface|string
     */
    public function addSubjectTeacher(IFormInterface $Form, $SubjectTeacher, $DivisionId, $DivisionSubjectId)
    {

        $Global = $this->getGlobal();

        /**
         * Skip to Frontend
         */
        if (!isset( $Global->POST['Button']['Submit'] )) {
            return $Form;
        }

        $Error = false;

        if (!$Error) {

            // Remove old Link
            $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
            $tblSubjectTeacherAll = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
            if (is_array($tblSubjectTeacherAll)) {
                array_walk($tblSubjectTeacherAll, function (TblSubjectTeacher $tblSubjectTeacher) {

                    if (!$this->removeSubjectTeacher($tblSubjectTeacher)) {
                    }
                });
            }

            // Add new Link
            if (is_array($SubjectTeacher)) {
                array_walk($SubjectTeacher, function ($SubjectTeacher) use ($tblDivisionSubject, &$Error) {

                    if ($Person = Person::useService()->getPersonById($SubjectTeacher)) {
                        if (!(new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubject, $Person)
                        ) {
                            $Error = true;
                        }
                    } else {
                        $Error = true;
                    }
                });
            }


            if (!$Error) {
                return new Success('Fachlehrerzuweisung erfolgreich ausgewählt')
                . new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $DivisionId));
            } else {
                return new Danger('Einige Fachlehrer konnten für das Fach nicht ausgewählt werden')
                . new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $DivisionId));
            }
        }
        return $Form;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return TblDivisionStudent
     */
    public function insertDivisionStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Division
     * @param int            $Id
     *
     * @return IFormInterface|string
     */
    public function changeDivision(IFormInterface $Form, $Division, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Division) {
            return $Form;
        }

        $Error = false;

//        if (isset( $Division['Name'] ) && empty( $Division['Name'] )) {
//            $Form->setError('Division[Name]', 'Bitte geben sie einen Namen an');
//            $Error = true;
//        } else {
//            $tblDivisionTest =
//                Division::useService()->getDivisionByGroupAndLevelAndYear($Division['Name'], $Division['Level'], $Division['Year']);
//            if ($tblDivisionTest) {
//                $Form->setError('Division[Name]', 'Name schon vergeben');
//                $Error = true;
//            }
//        }

        if (!$Error) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            if ($tblDivision) {
//                $tblYear = Term::useService()->getYearById($Division['Year']);
//                $tblLevel = $this->getLevelById($Division['Level']);
                if ((new Data($this->getBinding()))->updateDivision(
                    $tblDivision, $Division['Description']
                )
                ) {
                    return new Success('Die Beschreibung wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Division', 1);
                } else {
                    return new Danger('Die Beschreibung konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Division');
                }
            } else {
                return new Danger('Die Klassen wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Division');
            }
        }
        return $Form;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivision
     */
    public function getDivisionById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivisionSubject
     */
    public function getDivisionSubjectById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectByDivision($tblDivision);
    }

    /**
     * @param TblSubject  $tblSubject
     * @param TblDivision $tblDivision
     *
     * @return array|bool
     */
    public function getDivisionSubjectBySubjectAndDivision(TblSubject $tblSubject, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectStudent
     */
    public function getSubjectStudentById($Id)
    {

        return (new Data($this->getBinding()))->getSubjectStudentById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTeacher
     */
    public function getSubjectTeacherById($Id)
    {

        return (new Data($this->getBinding()))->getSubjectTeacherById($Id);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getSubjectStudentByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
    }

    /**
     * @param IFormInterface $Form
     * @param                $Group
     * @param                $Id
     * @param                $DivisionId
     * @param                $DivisionSubjectId
     *
     * @return IFormInterface|string
     */
    public function changeSubjectGroup(IFormInterface $Form, $Group, $Id, $DivisionId, $DivisionSubjectId)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        $tblSubjectGroup = Division::useService()->getSubjectGroupById($Id);

        if (isset($SubjectGroup['Name']) && empty($SubjectGroup['Name'])) {
            $Form->setError('Group[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
//            $SubjectGroupTest = Division::useService()->checkSubjectGroupExists($Group['Name'], $Group['Description']);   // Test auf doppelte Namen sinnvoll?
//            if ($SubjectGroupTest) {
//                if ($SubjectGroupTest->getId() !== $tblSubjectGroup->getId()) {
//                    $Form->setError('Group[Name]', 'Kombination schon vergeben');
//                    $Form->setError('Group[Description]', 'Beschreibung oder Gruppenname ändern');
//                    $Error = true;
//                }
//            }

        }

        if (!$Error) {

            if ($tblSubjectGroup) {
                if ((new Data($this->getBinding()))->updateSubjectGroup(
                    $tblSubjectGroup, $Group['Name'], $Group['Description']
                )
                ) {
                    return new Success('Die Gruppe wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Division/SubjectGroup/Add', 1, array('Id'                => $DivisionId,
                                                                                          'DivisionSubjectId' => $DivisionSubjectId));
                } else {
                    return new Danger('Die Gruppe konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Division/SubjectGroup/Add', 15, array('Id'                => $DivisionId,
                                                                                           'DivisionSubjectId' => $DivisionSubjectId));
                }
            } else {
                return new Danger('Die Gruppe wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Division/SubjectGroup/Add', 15, array('Id'                => $DivisionId,
                                                                                       'DivisionSubjectId' => $DivisionSubjectId));
            }
        }
        return $Form;
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectGroup
     */
    public function getSubjectGroupById($Id)
    {

        return (new Data($this->getBinding()))->getSubjectGroupById($Id);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getSubjectTeacherByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    public function destroyDivision(TblDivision $tblDivision)
    {

        if (null === $tblDivision) {
            return '';
        }
        $Error = false;
        $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
        if (!empty( $tblStudentList )) {
            $Error = true;
        }
        if (!empty( $tblTeacherList )) {
            $Error = true;
        }

        if (!$Error) {

            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            if (!empty( $tblDivisionSubjectList )) {
                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                    (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
                    (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
                }
            }

            $tblSubjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
            if (!empty( $tblSubjectList )) {
                foreach ($tblSubjectList as $tblSubject) {

                    (new Data($this->getBinding()))->removeSubjectToDivision($tblDivision, $tblSubject);
                }
            }


            if ((new Data($this->getBinding()))->destroyDivision($tblDivision)) {
                return new Success('Die Klassengruppe wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Division', 1);
            } else {
                return new Danger('Die Klassengruppe konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Division');
            }
        }
        return new Danger('Die Klassengruppe konnte nicht gelöscht werden, da Personen zugeordnet sind')
        .new Redirect('/Education/Lesson/Division');
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getTeacherAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTeacherAllByDivision($tblDivision);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getTeacherAllByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getTeacherAllByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getSubjectAllByDivision($tblDivision);
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return string
     */
    public function destroyLevel(TblLevel $tblLevel)
    {

        if (null === $tblLevel) {
            return '';
        }
        $Error = false;
        if ($this->getDivisionByLevel($tblLevel)) {
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyLevel($tblLevel)) {
                return new Success('Die Klassenstufe wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Division/Create/LevelDivision', 1);
            } else {
                return new Danger('Die Klassenstufe konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Division/Create/LevelDivision');
            }
        }
        return new Danger('Die Klassenstufe enthält Klassengruppen!')
        .new Redirect('/Education/Lesson/Division/Create/LevelDivision');
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByLevel(TblLevel $tblLevel)
    {

        return (new Data($this->getBinding()))->getDivisionByLevel($tblLevel);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getDivisionByYear($tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionStudentAllByPerson($tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->countDivisionStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionTeacherAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->countDivisionTeacherAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionSubjectAllByDivision(TblDivision $tblDivision)
    {

        $Sum = (new Data($this->getBinding()))->countDivisionSubjectAllByDivision($tblDivision);
        $Sub = (new Data($this->getBinding()))->countDivisionSubjectGroupByDivision($tblDivision);
        return ( $Sum - $Sub );
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionSubjectUsedByDivision(TblDivision $tblDivision)
    {

        $DivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
        $SubjectUsedCount = 0;
        if (!$DivisionSubjectList) {
        } else {
            foreach ($DivisionSubjectList as $DivisionSubject) {

                if (!$DivisionSubject->getTblSubjectGroup()) {
                    $tblDivisionSubjectActiveList = Division::useService()
                        ->getDivisionSubjectBySubjectAndDivision($DivisionSubject->getServiceTblSubject(), $tblDivision);
                    $TeacherGroup = array();
                    if ($tblDivisionSubjectActiveList) {
                        /**@var TblDivisionSubject $tblDivisionSubjectActive */
                        foreach ($tblDivisionSubjectActiveList as $tblDivisionSubjectActive) {
                            $TempList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectActive);
                            if ($TempList) {
                                foreach ($TempList as $Temp)
                                    array_push($TeacherGroup, $Temp->getId());
                            }
                        }
                        if (empty( $TeacherGroup )) {
                            $SubjectUsedCount = $SubjectUsedCount + 1;
                        }
                    }
                }
            }
        }
        return $SubjectUsedCount;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject($tblDivision,
            $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getSubjectTeacherAllByTeacher(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getSubjectTeacherAllByTeacher($tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @return bool|TblDivisionSubject
     */
    public function getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision,
            $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblPerson $tblPerson
     * @return bool|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByTeacher(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionTeacherAllByTeacher($tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     * @return bool|TblSubjectStudent
     */
    public function getSubjectStudentByDivisionSubjectAndPerson(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubject, $tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @return int
     */
    public function countSubjectStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->countSubjectStudentByDivisionSubject($tblDivisionSubject);
    }

}

<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
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
     * @return bool|TblSubjectGroup[]
     */
    public function getSubjectGroupAll()
    {

        return (new Data($this->getBinding()))->getSubjectGroupAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Level
     *
     * @return IFormInterface|string
     */
    public function createLevel(
        IFormInterface $Form,
        $Level
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Level) {
            return $Form;
        }

        $Error = false;

        $tblType = Type::useService()->getTypeById($Level['Type']);

        if (isset( $Level['Name'] ) && empty( $Level['Name'] )) {
            $Form->setError('Level[Name]', 'Bitte geben Sie eine eindeutige Klassenstufe für die Schulart an');
            $Error = true;
        } else {
            if ($this->checkLevelExists($tblType, $Level['Name'])) {
                $Form->setError('Level[Name]', 'Diese Klassenstufe wird in <b>'.$tblType->getName().'</b> bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createLevel($tblType, $Level['Name'], $Level['Description']);
            return new Success('Die Klassenstufe wurde erfolgreich hinzugefügt')
            .new Redirect($this->getRequest()->getUrl(), 1);
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
     * @param IFormInterface $Form
     * @param null|array     $SubjectGroup
     *
     * @return IFormInterface|string
     */
    public function createSubjectGroup(IFormInterface $Form, $SubjectGroup)
    {

        /**
         * Skip to Frontend
         */
        if (null === $SubjectGroup) {
            return $Form;
        }
        $Error = false;

        if (isset( $SubjectGroup['Name'] ) && empty( $SubjectGroup['Name'] )) {
            $Form->setError('SubjectGroup[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if (Division::useService()->checkSubjectGroupExists($SubjectGroup['Name'], $SubjectGroup['Description'])) {
                $Form->setError('SubjectGroup[Name]', 'Kombination schon vergeben');
                $Form->setError('SubjectGroup[Description]', 'Beschreibung oder Gruppenname ändern');
                $Error = true;
            }
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createSubjectGroup($SubjectGroup['Name'], $SubjectGroup['Description']);
            return new Success('Die Gruppe wurde erfolgreich hinzugefügt')
            .new Redirect($this->getRequest()->getUrl(), 1);
        }

        return $Form;
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return bool|TblSubjectGroup
     */
    public function checkSubjectGroupExists($Name, $Description)
    {

        return (new Data($this->getBinding()))->checkSubjectGroupExists($Name, $Description);
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return bool|TblLevel
     */
    public function checkSubjectExists($Name, $Description = '')
    {

        return (new Data($this->getBinding()))->checkSubjectExists($Name, $Description);
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
     * @param IFormInterface $Form
     * @param null|array     $Division
     *
     * @return IFormInterface|string
     */
    public function createDivision(IFormInterface $Form, $Division)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Division) {
            return $Form;
        }

        $Error = false;
        if (isset( $Division['Year'] )) {
            $tblYear = Term::useService()->getYearById($Division['Year']);
            if (empty( $tblYear )) {
                $Form->setError('Division[Year]', 'Schuljahr nicht gefunden');
                $Error = true;
            }
        } else {
            $Form->setError('Division[Year]', 'Schuljahr benötigt');
            $Error = true;
        }
        if (isset( $Division['Level'] )) {
            $tblLevel = $this->getLevelById($Division['Level']);
            if (empty( $tblLevel )) {
                $Form->setError('Division[Level]', 'Klassenstufe nicht gefunden');
                $Error = true;
            }
        } else {
            $Form->setError('Division[Level]', 'Klassenstufe benötigt');
            $Error = true;
        }

        if (!$Error) {
            if (isset( $Division['Name'] ) && empty( $Division['Name'] )) {
                $Form->setError('Division[Name]', 'Bitte geben Sie einen eineindeutigen Namen in Bezug auf die Schulart an');
                $Error = true;
            } else {
                if ($this->getDivisionByGroupAndLevelAndYear($Division['Name'], $Division['Level'], $Division['Year'])) {
                    $Form->setError('Division[Name]', 'Name wird in der Klassenstufe/Jahrgang bereits verwendet');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblYear = Term::useService()->getYearById($Division['Year']);
            $tblLevel = $this->getLevelById($Division['Level']);
            (new Data($this->getBinding()))->createDivision($tblYear, $tblLevel, $Division['Name'], $Division['Description']);
            return new Success('Die KlassenGruppe wurde erfolgreich hinzugefügt')
            .new Redirect($this->getRequest()->getUrl(), 1);

        }
        return $Form;
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
     * @param $Name
     * @param $Level
     * @param $Year
     *
     * @return bool|TblDivision
     */
    public function getDivisionByGroupAndLevelAndYear($Name, $Level, $Year)
    {

        $tblLevel = $this->getLevelById($Level);
        $tblYear = Term::useService()->getYearById($Year);
        return (new Data($this->getBinding()))->getDivisionByGroupAndLevelAndYear($Name, $tblLevel, $tblYear);
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
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param null|array     $Student
     *
     * @return IFormInterface|string
     */
    public function addStudentToDivision(IFormInterface $Form, TblDivision $tblDivision, $Student)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Student) {
            return $Form;
        }

        $Error = false;

        if (!$Error) {
            // Add new Link
            array_walk($Student, function ($Student) use ($tblDivision, &$Error) {

                if (!(new Data($this->getBinding()))->addDivisionStudent($tblDivision, Person::useService()->getPersonById($Student))) {
                    $Error = true;
                }
            });

            if (!$Error) {
                return new Success('Die Schüler wurden der Klasse erfolgreich hinzugefügt')
                .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
            } else {
                return new Danger('Einige Schüler konnte nicht hinzugefügt werden')
                .new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $tblDivision->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return string
     */
    public function removeStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Error = false;

        $tblStudentSubjectList = (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
        if ($tblStudentSubjectList) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                (new Data($this->getBinding()))->removeSubjectStudent($tblStudentSubject);
            }
        }

        if (!(new Data($this->getBinding()))->removeStudentToDivision($tblDivision, $tblPerson)) {
            $Error = true;
        }
        if (!$Error) {
            return new Success('Der Schüler wurde erfolgreich aus der Klasse entfernt')
            .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Der Schüler konnte nicht entfernt werden')
            .new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $tblDivision->getId()));
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return string
     */
    public function removeTeacherToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Error = false;
        if (!(new Data($this->getBinding()))->removeTeacherToDivision($tblDivision, $tblPerson)) {
            $Error = true;
        }
        if (!$Error) {
            return new Success('Der Lehrer wurde erfolgreich aus der Klasse entfernt')
            .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Der Lehrer konnte nicht entfernt werden')
            .new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $tblDivision->getId()));
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     *
     * @return string
     */
    public function removeSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        $Error = false;
        if (!(new Data($this->getBinding()))->removeSubjectToDivision($tblDivision, $tblSubject)) {
            $Error = true;
        }
        if (!$Error) {
            return new Success('Die Klasse wurde erfolgreich aus der Klasse entfernt')
            .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Die Klasse konnte nicht entfernt werden')
            .new Redirect('/Education/Lesson/Division/Show', 15, array('Id' => $tblDivision->getId()));
        }
    }

    /**
     * @param TblSubjectStudent $tblSubjectStudent
     * @param TblDivision       $tblDivision
     *
     * @return string
     */
    public function removeSubjectStudent(TblSubjectStudent $tblSubjectStudent, TblDivision $tblDivision)
    {

        if ((new Data($this->getBinding()))->removeSubjectStudent($tblSubjectStudent)) {
            return new Success('Die Zuordnung wurde erfolgreich entfernt')
            .new Redirect('/Education/Lesson/Division/SubjectStudent/Show', 1, array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Die Zuordnung konnte nicht entfernt werden')
            .new Redirect('/Education/Lesson/Division/SubjectStudent/Show', 15, array('Id' => $tblDivision->getId()));
        }
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblDivision        $tblDivision
     *
     * @return string
     */
    public function removeSubjectTeacher(TblDivisionSubject $tblDivisionSubject, TblDivision $tblDivision)
    {

        $tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
        $Error = false;

        if ($tblSubjectTeacherList) {
            foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                if (!(new Data($this->getBinding()))->removeSubjectTeacher($tblSubjectTeacher)) {
                    $Error = true;
                }
            }
        }
        if (!$Error) {
            return new Success('Die Zuordnung wurde erfolgreich entfernt')
            .new Redirect('/Education/Lesson/Division/SubjectTeacher/Show', 1, array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Die Zuordnung konnte nicht entfernt werden')
            .new Redirect('/Education/Lesson/Division/SubjectTeacher/Show', 15, array('Id' => $tblDivision->getId()));
        }


    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param null|array     $Teacher
     *
     * @return IFormInterface|string
     */
    public function addTeacherToDivision(IFormInterface $Form, TblDivision $tblDivision, $Teacher)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Teacher) {
            return $Form;
        }

        $Error = false;

        if (!$Error) {
            // Add new Link
            array_walk($Teacher, function ($Teacher) use ($tblDivision, &$Error) {

                if (!(new Data($this->getBinding()))->addDivisionTeacher($tblDivision, Person::useService()->getPersonById($Teacher))) {
                    $Error = false;
                }
            });

            if (!$Error) {
                return new Success('Der Klassenlehrer wurde der Klasse erfolgreich hinzugefügt')
                .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
            } else {
                return new Danger('Einige Lehrer konnte nicht hinzugefügt werden')
                .new Redirect('/Education/Lesson/Division', 15, array('Id' => $tblDivision->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param null|array     $Subject
     *
     * @return IFormInterface|string
     */
    public function addSubjectToDivision(IFormInterface $Form, TblDivision $tblDivision, $Subject)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Subject) {
            return $Form;
        }

        $Error = false;

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $tblSubjectActivateList = array();
        foreach ($Subject as $item) {
            $tblSubjectActivateList[] = Subject::useService()->getSubjectById($item);
        }

        $tblSubjectList = array();
        if ($tblSubjectAll && $tblSubjectActivateList) {     // get Deleteable Subjects
            $tblSubjectList = array_udiff($tblSubjectAll, $tblSubjectActivateList,
                function (TblSubject $invoiceA, TblSubject $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });
        }

        if (!$Error) {

            // Remove old SubjectToDivision
            array_walk($tblSubjectList, function (TblSubject $tblSubject) use ($tblDivision, &$Error) {

                $tblDivisionSubjectList = $this->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectList) {
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        if ($tblDivisionSubject->getServiceTblSubject()->getId() === $tblSubject->getId()) {
                            (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
                            (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
                        }
                    }
                }

                $this->removeSubjectToDivision($tblDivision, $tblSubject);
            });
            // Add new SubjectToDivision
            array_walk($Subject, function ($Subject) use ($tblDivision, &$Error) {

                if (!(new Data($this->getBinding()))->addDivisionSubject($tblDivision, Subject::useService()->getSubjectById($Subject))) {
                    $Error = false;
                }
            });

            if (!$Error) {
                return new Success('Die Fächer wurden der Klasse erfolgreich hinzugefügt')
                .new Redirect('/Education/Lesson/Division/Show', 1, array('Id' => $tblDivision->getId()));
            } else {
                return new Danger('Einige Fächer konnten nicht hinzugefügt werden')
                .new Redirect('/Education/Lesson/Division', 15, array('Id' => $tblDivision->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param int            $DivisionSubject
     * @param array          $Student
     * @param int            $DivisionId
     * @param null           $Group
     *
     * @return IFormInterface|string
     */
    public function addSubjectStudent(IFormInterface $Form, $DivisionSubject, $Student, $DivisionId, $Group = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Student) {
            return $Form;
        }

        $Error = false;
        if (empty( $DivisionSubject )) {
            $Form .= new Warning('Keine Zuordnung ohne Fach möglich');
            $Error = true;
        }

        if (!$Error) {

            $tblSubjectGroup = Division::useService()->getSubjectGroupById($Group);
            if ($tblSubjectGroup === false) {
                $tblSubjectGroup = null;
            }

            $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubject);
            // Add new Link
            array_walk($Student, function ($Student) use ($tblDivisionSubject, $tblSubjectGroup, &$Error) {

                if (!(new Data($this->getBinding()))->addSubjectStudent(Person::useService()->getPersonById($Student), $tblDivisionSubject, $tblSubjectGroup)) {
                    $Error = false;
                }
            });

            if (!$Error) {
                return new Success('Die Gruppe mit Personen wurden erfolgreich angelegt')
                .new Redirect('/Education/Lesson/Division/SubjectStudent/Show', 1, array('Id' => $DivisionId));
            } else {
                return new Danger('Einige Personen konnte nicht in der Gruppe angelegt werden')
                .new Redirect('/Education/Lesson/Division/SubjectStudent/Show', 15, array('Id' => $DivisionId));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param array          $DivisionSubject
     * @param int            $Teacher
     * @param int            $DivisionId
     * @param null           $Group
     *
     * @return IFormInterface|string
     */
    public function addSubjectTeacher(IFormInterface $Form, $DivisionSubject, $Teacher, $DivisionId, $Group = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $DivisionSubject) {
            return $Form;
        }

        $Error = false;
        if (empty( $Teacher )) {
            $Form .= new Warning('Keine Zuordnung ohne Lehrer möglich');
            $Error = true;
        }

        if (!$Error) {

            $tblSubjectGroup = Division::useService()->getSubjectGroupById($Group);
            if ($tblSubjectGroup === false) {
                $tblSubjectGroup = null;
            }

            // Add new Link
            array_walk($DivisionSubject, function ($DivisionSubject) use ($Teacher, $tblSubjectGroup, &$Error) {

                if (!(new Data($this->getBinding()))->addSubjectTeacher(Division::useService()->getDivisionSubjectById($DivisionSubject), Person::useService()->getPersonById($Teacher), $tblSubjectGroup)) {
                    $Error = false;
                }
            });

            if (!$Error) {
                return new Success('Die Gruppe mit Personen wurden erfolgreich angelegt')
                .new Redirect('/Education/Lesson/Division/SubjectTeacher/Show', 1, array('Id' => $DivisionId));
            } else {
                return new Danger('Einige Personen konnte nicht in der Gruppe angelegt werden')
                .new Redirect('/Education/Lesson/Division/SubjectTeacher/Show', 15, array('Id' => $DivisionId));
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

        if (isset( $Division['Name'] ) && empty( $Division['Name'] )) {
            $Form->setError('Division[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
            $tblDivisionTest =
                Division::useService()->getDivisionByGroupAndLevelAndYear($Division['Name'], $Division['Level'], $Division['Year']);
            if ($tblDivisionTest) {
                $Form->setError('Division[Name]', 'Name schon vergeben');
                $Error = true;
            }
        }

        if (!$Error) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            if ($tblDivision) {
                $tblYear = Term::useService()->getYearById($Division['Year']);
                $tblLevel = $this->getLevelById($Division['Level']);
                if ((new Data($this->getBinding()))->updateDivision(
                    $tblDivision, $tblYear, $tblLevel, $Division['Name'], $Division['Description']
                )
                ) {
                    return new Success('Die Klassengruppe wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Division/Create/Division', 1);
                } else {
                    return new Danger('Die Klassengruppe konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Division/Create/Division');
                }
            } else {
                return new Danger('Die Klassengruppe wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Division/Create/Division');
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
     * @param IFormInterface $Form
     * @param null|array     $Level
     * @param int            $Id
     *
     * @return IFormInterface|string
     */
    public function changeLevel(IFormInterface $Form, $Level, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Level) {
            return $Form;
        }

        $Error = false;
        $tblType = Type::useService()->getTypeById($Level['Type']);
        $tblLevel = Division::useService()->getLevelById($Id);

        if (isset( $Level['Name'] ) && empty( $Level['Name'] )) {
            $Form->setError('Division[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
            $tblLevelTest = $this->checkLevelExists($tblType, $Level['Name']);
            if ($tblLevelTest->getId() !== $tblLevel->getId()) {
                $Form->setError('Level[Name]', 'Diese Klassenstufe wird in <b>'.$tblType->getName().'</b> bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {

            if ($tblLevel) {
                if ((new Data($this->getBinding()))->updateLevel(
                    $tblLevel, $tblType, $Level['Name'], $Level['Description']
                )
                ) {
                    return new Success('Die Klassenstufe wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Division/Create/Level', 1);
                } else {
                    return new Danger('Die Klassenstufe konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Division/Create/Level');
                }
            } else {
                return new Danger('Die Klassenstufe wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Division/Create/Level');
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param                $SubjectGroup
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function changeSubjectGroup(IFormInterface $Form, $SubjectGroup, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $SubjectGroup) {
            return $Form;
        }

        $Error = false;

        $tblSubjectGroup = Division::useService()->getSubjectGroupById($Id);

        if (isset( $SubjectGroup['Name'] ) && empty( $SubjectGroup['Name'] )) {
            $Form->setError('SubjectGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
            $SubjectGroupTest = Division::useService()->checkSubjectGroupExists($SubjectGroup['Name'], $SubjectGroup['Description']);
            if ($SubjectGroupTest) {
                if ($SubjectGroupTest->getId() !== $tblSubjectGroup->getId()) {
                    $Form->setError('SubjectGroup[Name]', 'Kombination schon vergeben');
                    $Form->setError('SubjectGroup[Description]', 'Beschreibung oder Gruppenname ändern');
                    $Error = true;
                }
            }

        }

        if (!$Error) {

            if ($tblSubjectGroup) {
                if ((new Data($this->getBinding()))->updateSubjectGroup(
                    $tblSubjectGroup, $SubjectGroup['Name'], $SubjectGroup['Description']
                )
                ) {
                    return new Success('Die Gruppe wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Division/Create/SubjectGroup', 1);
                } else {
                    return new Danger('Die Gruppe konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Division/Create/SubjectGroup');
                }
            } else {
                return new Danger('Die Gruppe wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Division/Create/SubjectGroup');
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
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectGroup[]
     */
    public function getSubjectGroupByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getSubjectGroupByDivisionSubject($tblDivisionSubject);
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
                .new Redirect('/Education/Lesson/Division/Create/Division', 1);
            } else {
                return new Danger('Die Klassengruppe konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Division/Create/Division');
            }
        }
        return new Danger('Die Klassengruppe konnte nicht gelöscht werden, da Personen zugeordnet sind')
        .new Redirect('/Education/Lesson/Division/Create/Division');
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
                .new Redirect('/Education/Lesson/Division/Create/Level', 1);
            } else {
                return new Danger('Die Klassenstufe konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Division/Create/Level');
            }
        }
        return new Danger('Die Klassenstufe enthält Klassengruppen!')
        .new Redirect('/Education/Lesson/Division/Create/Level');
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
}

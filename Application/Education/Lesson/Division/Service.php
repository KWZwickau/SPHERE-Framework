<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
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

            if ((new Data($this->getBinding()))->createLevel(
                $tblType, $Level['Name'], $Level['Description']
            )
            ) {
                return new Success('Die Klassenstufe wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 1);
            } else {
                return new Danger('Die Klassenstufe konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param TblType $tblType
     * @param $Name
     * @param string $Description
     * @return bool|TblLevel
     */
    public function insertLevel(TblType $tblType, $Name, $Description = '')
    {
        return (new Data($this->getBinding()))->createLevel($tblType, $Name, $Description);
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
     * @param                $Division
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
            if ((new Data($this->getBinding()))->createDivision(
                $tblYear, $tblLevel, $Division['Name'], $Division['Description']
            )
            ) {
                return new Success('Die KlassenGruppe wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 1);
            } else {
                return new Danger('Die KlassenGruppe konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param TblYear $tblYear
     * @param TblLevel $tblLevel
     * @param $Name
     * @param string $Description
     *
     * @return null|TblDivision
     */
    public function insertDivision(TblYear $tblYear, TblLevel $tblLevel, $Name, $Description = '')
    {
        return (new Data($this->getBinding()))->createDivision($tblYear, $tblLevel, $Name, $Description);
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
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param                $Student
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
                .new Redirect('/Education/Lesson/Division', 3);
            } else {
                return new Danger('Einige Schüler konnte nicht hinzugefügt werden')
                .new Redirect('/Education/Lesson/Division');
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
        if (!(new Data($this->getBinding()))->removeStudentToDivision($tblDivision, $tblPerson)) {
            $Error = true;
        }
        if (!$Error) {
            return new Success('Der Schüler wurde erfolgreich aus der Klasse entfernt')
            .new Redirect('/Education/Lesson/Division/Show', 3, array('Id' => $tblDivision->getId()));
        } else {
            return new Danger('Der Schüler konnte nicht entfernt werden')
            .new Redirect('/Education/Lesson/Division/Show', null, array('Id' => $tblDivision->getId()));
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param                $Teacher
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
                return new Success('Die Lehrer wurden der Klasse erfolgreich hinzugefügt')
                .new Redirect('/Education/Lesson/Division', 3);
            } else {
                return new Danger('Einige Lehrer konnte nicht hinzugefügt werden')
                .new Redirect('/Education/Lesson/Division');
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
     * @param                $Division
     * @param                $Id
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
                    .new Redirect('/Education/Lesson/Division/Create/Division', 3);
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
     * @param IFormInterface $Form
     * @param                $Level
     * @param                $Id
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
                    .new Redirect('/Education/Lesson/Division/Create/Level', 3);
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

        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyDivision($tblDivision)) {
                return new Success('Die Klassengruppe wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Division/Create/Division', 1);
            } else {
                return new Danger('Die Klassengruppe konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Division/Create/Division');
            }
        }
        return new Danger('Die Klassengruppe wird benutzt!')
        .new Redirect('/Education/Lesson/Division/Create/Division');
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
            var_dump('nicht gelöscht');
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

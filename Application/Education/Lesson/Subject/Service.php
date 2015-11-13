<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Data;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategorySubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroupCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Subject
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
     * @return bool|TblSubject[]
     */
    public function getSubjectAll()
    {

        return (new Data($this->getBinding()))->getSubjectAll();
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectOrientationAll()
    {

        $tblSubjectList = array();
        $tblCategory = $this->getGroupByIdentifier('ORIENTATION');
        if ($tblCategory) {
            $tblCategory = $tblCategory->getTblCategoryAll();
            if ($tblCategory) {
                array_walk($tblCategory, function (TblCategory &$tblCategory) {

                    $tblCategory = $tblCategory->getTblSubjectAll();
                });
                array_walk_recursive($tblCategory, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getGroupByIdentifier($Identifier);
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAdvancedAll()
    {

        $tblSubjectList = array();
        $tblCategory = $this->getGroupByIdentifier('ADVANCED');
        if ($tblCategory) {
            $tblCategory = $tblCategory->getTblCategoryAll();
            if ($tblCategory) {
                array_walk($tblCategory, function (TblCategory &$tblCategory) {

                    $tblCategory = $tblCategory->getTblSubjectAll();
                });
                array_walk_recursive($tblCategory, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectProfileAll()
    {

        $tblSubjectList = array();
        $tblGroup = $this->getGroupByIdentifier('STANDARD');
        if ($tblGroup) {
            $tblCategory = $tblGroup->getTblCategoryByIdentifier('PROFILE');
            if ($tblCategory) {
                $tblSubjectAll = $tblCategory->getTblSubjectAll();
                array_walk_recursive($tblSubjectAll, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectReligionAll()
    {

        $tblSubjectList = array();
        $tblGroup = $this->getGroupByIdentifier('STANDARD');
        if ($tblGroup) {
            $tblCategory = $tblGroup->getTblCategoryByIdentifier('RELIGION');
            if ($tblCategory) {
                $tblSubjectAll = $tblCategory->getTblSubjectAll();
                array_walk_recursive($tblSubjectAll, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectForeignLanguageAll()
    {

        $tblSubjectList = array();
        $tblGroup = $this->getGroupByIdentifier('STANDARD');
        if ($tblGroup) {
            $tblCategory = $tblGroup->getTblCategoryByIdentifier('FOREIGNLANGUAGE');
            if ($tblCategory) {
                $tblSubjectAll = $tblCategory->getTblSubjectAll();
                array_walk_recursive($tblSubjectAll, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectElectiveAll()
    {

        $tblSubjectList = array();
        $tblCategory = $this->getGroupByIdentifier('ELECTIVE');
        if ($tblCategory) {
            $tblCategory = $tblCategory->getTblCategoryAll();
            if ($tblCategory) {
                array_walk($tblCategory, function (TblCategory &$tblCategory) {

                    $tblCategory = $tblCategory->getTblSubjectAll();
                });
                array_walk_recursive($tblCategory, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public function destroySubject(TblSubject $tblSubject)
    {

        if (null === $tblSubject) {
            return '';
        }
        $Error = false;

        if ($this->getSubjectActiveState($tblSubject)) {
            $Error = true;
        }
        if (!$Error) {
            if ((new Data($this->getBinding()))->destroySubject($tblSubject)) {
                return new Success('Das Fach wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Subject/Create/Subject', 1);
            } else {
                return new Danger('Das Fach konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Subject/Create/Subject');
            }
        }
        return new Danger('Das Fach wird benutzt!')
        .new Redirect('/Education/Lesson/Subject/Create/Subject');
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function getSubjectActiveState(TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->getSubjectActiveState($tblSubject);
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return string
     */
    public function destroyCategory(TblCategory $tblCategory)
    {

        if (null === $tblCategory) {
            return '';
        }

        $Error = false;

        // Remove link Subject
        $tblSubjectAll = $tblCategory->getTblSubjectAll();
        array_walk($tblSubjectAll, function (TblSubject $tblSubject) use ($tblCategory, &$Error) {

            if (!$this->removeCategorySubject($tblCategory, $tblSubject)) {
                $Error = true;
            }
        });
        // Remove link Group
        $tblGroupList = Subject::useService()->getGroupByCategory($tblCategory);
        if ($tblGroupList) {
            foreach ($tblGroupList as $tblGroup)
                if (!$this->removeGroupCategory($tblGroup, $tblCategory)) {
                    $Error = true;
                }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyCategory($tblCategory)) {
                return new Success('Die Kategorie wurde erfolgreich gelöscht')
                .new Redirect('/Education/Lesson/Subject/Create/Category', 1);
            } else {
                return new Danger('Die Kategorie konnte nicht gelöscht werden')
                .new Redirect('/Education/Lesson/Subject/Create/Category');
            }
        }
        return new Danger('Die Kategorie wurde benutzt!')
        .new Redirect('/Education/Lesson/Subject/Create/Category');

    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return bool
     */
    public function removeCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->removeCategorySubject($tblCategory, $tblSubject);
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|null|TblGroup[]
     */
    public function getGroupByCategory(TblCategory $tblCategory)
    {

        return (new Data($this->getBinding()))->getGroupByCategory($tblCategory);
    }

    /**
     * @param TblGroup    $tblGroup
     * @param TblCategory $tblCategory
     *
     * @return bool
     */
    public function removeGroupCategory(TblGroup $tblGroup, TblCategory $tblCategory)
    {

        return (new Data($this->getBinding()))->removeGroupCategory($tblGroup, $tblCategory);
    }

    /**
     * int
     */
    public function countSubjectAll()
    {

        return (new Data($this->getBinding()))->countSubjectAll();
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCategory[]
     */
    public function getCategoryAllByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->getCategoryAllByGroup($tblGroup);
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByCategory(TblCategory $tblCategory)
    {

        return (new Data($this->getBinding()))->getSubjectAllByCategory($tblCategory);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return (new Data($this->getBinding()))->getGroupById($Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblCategory
     */
    public function getCategoryByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getCategoryByIdentifier($Identifier);
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return (new Data($this->getBinding()))->getGroupAll();
    }

    /**
     * @return bool|TblCategory[]
     */
    public function getCategoryAll()
    {

        return (new Data($this->getBinding()))->getCategoryAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Subject
     *
     * @return IFormInterface|string
     */
    public function createSubject(
        IFormInterface $Form,
        $Subject
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Subject) {
            return $Form;
        }

        $Error = false;

        if (isset( $Subject['Acronym'] ) && empty( $Subject['Acronym'] )) {
            $Form->setError('Subject[Acronym]', 'Bitte geben Sie ein eineindeutiges Kürzel an');
            $Error = true;
        } else {
            if ($this->getSubjectByAcronym($Subject['Acronym'])) {
                $Form->setError('Subject[Acronym]', 'Dieses Kürzel wird bereits verwendet');
                $Error = true;
            }
        }

        if (isset( $Subject['Name'] ) && empty( $Subject['Name'] )) {
            $Form->setError('Subject[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createSubject(
                $Subject['Acronym'], $Subject['Name'], $Subject['Description']
            )
            ) {
                return new Success('Das Fach wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Das Fach konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblSubject
     */
    public function getSubjectByAcronym($Acronym)
    {

        return (new Data($this->getBinding()))->getSubjectByAcronym($Acronym);
    }

    /**
     * @param IFormInterface $Form
     * @param                $Subject
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function changeSubject(IFormInterface $Form, $Subject, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Subject) {
            return $Form;
        }

        $Error = false;

        if (isset( $Subject['Acronym'] ) && empty( $Subject['Acronym'] )) {
            $Form->setError('Subject[Acronym]', 'Bitte geben Sie ein eineindeutiges Kürzel an');
            $Error = true;
        } else {
            $tblSubject = Subject::useService()->getSubjectByAcronym($Subject['Acronym']);
            if ($tblSubject) {
                if ($tblSubject->getId() !== $Id) {
                    $Form->setError('Subject[Acronym]', 'Kürzel ist schon vorhanden');
                    $Error = true;
                }
            }
        }

        if (isset( $Subject['Name'] ) && empty( $Subject['Name'] )) {
            $Form->setError('Subject[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            $tblSubject = Subject::useService()->getSubjectById($Id);
            if ($tblSubject) {
                if ((new Data($this->getBinding()))->updateSubject(
                    $tblSubject, $Subject['Acronym'], $Subject['Name'], $Subject['Description']
                )
                ) {
                    return new Success('Das Fach wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Subject/Create/Subject', 3);
                } else {
                    return new Danger('Das Fach konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Subject/Create/Subject');
                }
            } else {
                return new Danger('Das Fach wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Subject/Create/Subject');
            }
        }
        return $Form;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSubject
     */
    public function getSubjectById($Id)
    {

        return (new Data($this->getBinding()))->getSubjectById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param                $Category
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function changeCategory(IFormInterface $Form, $Category, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Category) {
            return $Form;
        }

        $Error = false;

        if (isset( $Category['Name'] ) && empty( $Category['Name'] )) {
            $Form->setError('Category[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if ($this->getCategoryByName($Category['Name']) && $Id !== $this->getCategoryByName($Category['Name'])->getId()) {
                $Form->setError('Category[Name]', 'Name schon benutzt');
                $Error = true;
            }
        }

        if (!$Error) {
            $tblCategory = Subject::useService()->getCategoryById($Id);
            if ($tblCategory) {
                if ((new Data($this->getBinding()))->updateCategory(
                    $tblCategory, $Category['Name'], $Category['Description']
                )
                ) {
                    return new Success('Die Kategorie wurde erfolgreich geändert')
                    .new Redirect('/Education/Lesson/Subject/Create/Category', 3);
                } else {
                    return new Danger('Die Kategorie konnte nicht geändert werden')
                    .new Redirect('/Education/Lesson/Subject/Create/Category');
                }
            } else {
                return new Danger('Die Kategorie wurde nicht gefunden')
                .new Redirect('/Education/Lesson/Subject/Create/Category');
            }
        }
        return $Form;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCategory
     */
    public function getCategoryByName($Name)
    {

        return (new Data($this->getBinding()))->getCategoryByName($Name);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCategory
     */
    public function getCategoryById($Id)
    {

        return (new Data($this->getBinding()))->getCategoryById($Id);
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAllHavingNoCategory()
    {

        return (new Data($this->getBinding()))->getSubjectAllHavingNoCategory();
    }

    /**
     * @return bool|TblCategory[]
     */
    public function getCategoryAllHavingNoGroup()
    {

        return (new Data($this->getBinding()))->getCategoryAllHavingNoGroup();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Category
     *
     * @return IFormInterface|string
     */
    public function createCategory(
        IFormInterface $Form,
        $Category
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Category) {
            return $Form;
        }

        $Error = false;

        if (isset( $Category['Name'] ) && empty( $Category['Name'] )) {
            $Form->setError('Category[Name]', 'Bitte geben Sie einen eineindeutigen Namen an');
            $Error = true;
        } else {
            if ($this->getCategoryByName($Category['Name'])) {
                $Form->setError('Category[Name]', 'Dieser Namen wird bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createCategory(
                $Category['Name'], $Category['Description']
            )
            ) {
                return new Success('Die Kategorie wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Die Kategorie konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblGroup       $tblGroup
     * @param null|array     $Category
     *
     * @return IFormInterface|string
     */
    public function changeGroupCategory(
        IFormInterface $Form,
        TblGroup $tblGroup,
        $Category
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Category) {
            return $Form;
        }

        $Error = false;

        if (!$Error) {

            // Remove old Link
            $tblCategoryAll = $tblGroup->getTblCategoryAll();
            array_walk($tblCategoryAll, function (TblCategory $tblCategory) use ($tblGroup, &$Error) {

                if (!$this->removeGroupCategory($tblGroup, $tblCategory)) {
                    $Error = false;
                }
            });
            // Add new Link
            array_walk($Category, function ($Category) use ($tblGroup, &$Error) {

                if (!$this->addGroupCategory($tblGroup, $this->getCategoryById($Category))) {
                    $Error = false;
                }
            });

            if (!$Error) {
                return new Success('Die Kategorien wurden erfolgreich geändert')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Einige Kategorien konnte nicht geändert werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param TblGroup    $tblGroup
     * @param TblCategory $tblCategory
     *
     * @return TblGroupCategory
     */
    public function addGroupCategory(TblGroup $tblGroup, TblCategory $tblCategory)
    {

        return (new Data($this->getBinding()))->addGroupCategory($tblGroup, $tblCategory);
    }

    /**
     * @param IFormInterface $Form
     * @param TblCategory    $tblCategory
     * @param null|array     $Subject
     *
     * @return IFormInterface|string
     */
    public function changeCategorySubject(
        IFormInterface $Form,
        TblCategory $tblCategory,
        $Subject
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Subject) {
            return $Form;
        }

        $Error = false;

        if (!$Error) {

            // Remove old Link
            $tblSubjectAll = $tblCategory->getTblSubjectAll();
            array_walk($tblSubjectAll, function (TblSubject $tblSubject) use ($tblCategory, &$Error) {

                if (!$this->removeCategorySubject($tblCategory, $tblSubject)) {
                    $Error = false;
                }
            });
            // Add new Link
            array_walk($Subject, function ($Subject) use ($tblCategory, &$Error) {

                if (!$this->addCategorySubject($tblCategory, $this->getSubjectById($Subject))) {
                    $Error = false;
                }
            });

            if (!$Error) {
                return new Success('Die Fächer wurden erfolgreich geändert')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Einige Fächer konnte nicht geändert werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return TblCategorySubject
     */
    public function addCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->addCategorySubject($tblCategory, $tblSubject);
    }
}

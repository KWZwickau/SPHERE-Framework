<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction;

use SPHERE\Application\Education\ClassRegister\Instruction\Service\Data;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstructionItem;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstructionItemStudent;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblInstruction
     */
    public function getInstructionById($Id)
    {
        return (new Data($this->getBinding()))->getInstructionById($Id);
    }

    /**
     * @return false|TblInstruction[]
     */
    public function getInstructionAll()
    {
        return (new Data($this->getBinding()))->getInstructionAll();
    }

    /**
     * @param $Data
     * @param TblInstruction|null $tblInstruction
     * @return false|Form
     */
    public function checkFormInstruction(
        $Data,
        TblInstruction $tblInstruction = null
    ) {
        $error = false;

        $form = Instruction::useFrontend()->formInstruction(
            $tblInstruction ? $tblInstruction->getId() : null
        );
        if (isset($Data['Subject']) && empty($Data['Subject'])) {
            $form->setError('Data[Subject]', 'Bitte geben Sie ein Thema an');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     *
     * @return bool
     */
    public function createInstruction($Data): bool
    {
        (new Data($this->getBinding()))->createInstruction(
            $Data['Subject'],
            $Data['Content']
        );

        return  true;
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param $Data
     *
     * @return bool
     */
    public function updateInstruction(TblInstruction $tblInstruction, $Data): bool
    {
        return (new Data($this->getBinding()))->updateInstruction(
            $tblInstruction,
            $Data['Subject'],
            $Data['Content']
        );
    }

    /**
     * @param TblInstruction $tblInstruction
     *
     * @return bool
     */
    public function destroyInstruction(TblInstruction $tblInstruction): bool
    {
        return (new Data($this->getBinding()))->destroyInstruction($tblInstruction);
    }

    /**
     * @param $Id
     *
     * @return false|TblInstructionItem
     */
    public function getInstructionItemById($Id)
    {
        return (new Data($this->getBinding()))->getInstructionItemById($Id);
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblInstructionItem[]
     */
    public function getInstructionItemAllByInstruction(TblInstruction $tblInstruction, ?TblDivision $tblDivision, ?TblGroup $tblGroup)
    {
        $tblYear = $tblGroup ? $tblGroup->getCurrentYear() : null;
        return (new Data($this->getBinding()))->getInstructionItemAllByInstruction($tblInstruction, $tblDivision, $tblGroup, $tblYear ?: null);
    }


    /**
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|TblInstructionItem
     */
    public function getMainInstructionItemBy(TblInstruction $tblInstruction, ?TblDivision $tblDivision, ?TblGroup $tblGroup)
    {
        $tblYear = $tblGroup ? $tblGroup->getCurrentYear() : null;
        return (new Data($this->getBinding()))->getMainInstructionItemBy($tblInstruction, $tblDivision, $tblGroup, $tblYear ?: null);
    }

    /**
     * @param $Data
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblInstruction $tblInstruction
     * @param TblInstructionItem|null $tblInstructionItem
     *
     * @return false|Form
     */
    public function checkFormInstructionItem(
        $Data,
        ?TblDivision $tblDivision,
        ?TblGroup $tblGroup,
        TblInstruction $tblInstruction,
        ?TblInstructionItem $tblInstructionItem
    ) {
        $error = false;

        $form = Instruction::useFrontend()->formInstructionItem(
            $tblDivision,
            $tblGroup,
            $tblInstruction,
            $tblInstructionItem ? $tblInstructionItem->getId() : null
        );

        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }
        if (isset($Data['Content']) && empty($Data['Content'])) {
            $form->setError('Data[Content]', 'Bitte geben Sie einen Inhalt an');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return bool
     */
    public function createInstructionItem($Data, TblInstruction $tblInstruction, ?TblDivision $tblDivision, ?TblGroup $tblGroup): bool
    {
        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
        } else {
            $tblYear = false;
        }

        $tblPerson = Account::useService()->getPersonByLogin();
        $tblMainInstructionItem = Instruction::useService()->getMainInstructionItemBy($tblInstruction, $tblDivision, $tblGroup);

        if (($tblInstructionItem = (new Data($this->getBinding()))->createInstructionItem(
            $tblInstruction,
            $tblDivision,
            $tblGroup,
            $tblYear ?: null,
            $tblPerson ?: null,
            $Data['Date'],
            $Data['Content'] ?? '',
            !$tblMainInstructionItem
        ))) {
            if (isset($Data['Students'])) {
                foreach($Data['Students'] as $personId => $value) {
                    if (($tblPersonItem = Person::useService()->getPersonById($personId))) {
                        (new Data($this->getBinding()))->addInstructionItemStudent($tblInstructionItem, $tblPersonItem);
                    }
                }
            }
        }

        return  true;
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     * @param $Data
     *
     * @return bool
     */
    public function updateInstructionItem(TblInstructionItem $tblInstructionItem, $Data): bool
    {
        $tblPerson = Account::useService()->getPersonByLogin();

        (new Data($this->getBinding()))->updateInstructionItem(
            $tblInstructionItem,
            $tblPerson ?: null,
            $Data['Date'],
            $Data['Content'] ?? ''
        );

        if (($tblInstructionItemStudentList = Instruction::useService()->getMissingStudentsByInstructionItem($tblInstructionItem))) {
            foreach ($tblInstructionItemStudentList as $tblInstructionItemStudent) {
                if (($tblPersonRemove = $tblInstructionItemStudent->getServiceTblPerson())
                    && !isset($Data['Students'][$tblPersonRemove->getId()])
                ) {
                    (new Data($this->getBinding()))->removeInstructionItemStudent($tblInstructionItemStudent);
                }
            }
        }

        if (isset($Data['Students'])) {
            foreach($Data['Students'] as $personId => $value) {
                if (($tblPersonAdd = Person::useService()->getPersonById($personId))) {
                    (new Data($this->getBinding()))->addInstructionItemStudent($tblInstructionItem, $tblPersonAdd);
                }
            }
        }

        return true;
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     *
     * @return bool
     */
    public function destroyInstructionItem(TblInstructionItem $tblInstructionItem): bool
    {
        return (new Data($this->getBinding()))->destroyInstructionItem($tblInstructionItem);
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     *
     * @return false|TblInstructionItemStudent[]
     */
    public function getMissingStudentsByInstructionItem(TblInstructionItem $tblInstructionItem)
    {
        return (new Data($this->getBinding()))->getMissingStudentsByInstructionItem($tblInstructionItem);
    }

    /**
     * @param TblInstructionItem $tblInstructionItem
     *
     * @return false|array
     */
    public function getMissingPersonNameListByInstructionItem(TblInstructionItem $tblInstructionItem)
    {
        $personList = array();
        if (($missingList = $this->getMissingStudentsByInstructionItem($tblInstructionItem))) {
            foreach ($missingList as $tblInstructionItemStudent) {
                if (($tblPerson = $tblInstructionItemStudent->getServiceTblPerson())) {
                    $personList[$tblPerson->getId()] = $tblPerson->getLastFirstName();
                }
            }
        }

        return empty($personList) ? false : $personList;
    }

    /**
     * @param TblInstruction $tblInstruction
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return false|array
     */
    public function getMissingStudentsByInstruction(TblInstruction $tblInstruction, ?TblDivision $tblDivision, ?TblGroup $tblGroup)
    {
        $personList = array();
        if (($tblInstructionItemList = Instruction::useService()->getInstructionItemAllByInstruction($tblInstruction, $tblDivision, $tblGroup))) {
            foreach ($tblInstructionItemList as $tblInstructionItem) {
                $missingList = $this->getMissingPersonNameListByInstructionItem($tblInstructionItem);

                // keine fehlende Schüler
                if (!$missingList) {
                    return false;
                }

                // erste Durchführung fehlende Personen setzen
                if (empty($personList)) {
                   $personList = $missingList;
                } else {
                    $tempList = array();
                    // Vergleich mit bereits fehlenden Personen
                    foreach($personList as $personId => $name) {
                        if (isset($missingList[$personId])) {
                            $tempList[$personId] = $name;
                        }
                    }
                    $personList = $tempList;
                }

                // keine fehlenden Schüler mehr für die Belehrung
                if (empty($personList)) {
                    return false;
                }
            }
        }

        return empty($personList) ? false : $personList;
    }
}
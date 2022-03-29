<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction;

use SPHERE\Application\Education\ClassRegister\Instruction\Service\Data;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstructionItem;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Setup;
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
}
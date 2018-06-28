<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Data;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Consumer\Responsibility
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
     * @return bool|TblResponsibility[]
     */
    public function getResponsibilityAll()
    {

        return (new Data($this->getBinding()))->getResponsibilityAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblResponsibility
     */
    public function getResponsibilityById($Id)
    {

        return (new Data($this->getBinding()))->getResponsibilityById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param integer        $Responsibility
     *
     * @return IFormInterface|string
     */
    public function createResponsibility(
        IFormInterface $Form,
        $Responsibility
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (empty( $Global->POST )) {
            return $Form;
        }

        $Error = false;

        if (null === $Responsibility) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schulträger aus')))));
            $Error = true;
        } else {
            $tblCompany = Company::useService()->getCompanyById($Responsibility);
            if (!$tblCompany){
                $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schulträger aus')))));
                $Error = true;
            }
        }

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($Responsibility);
            if ($tblCompany && (new Data($this->getBinding()))->addResponsibility($tblCompany)
            ) {
                return new Success('Der Schulträger wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Der Schulträger konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
    }

    /**
     * @param IFormInterface    $Form
     * @param TblResponsibility $tblResponsibility
     * @param                   $CompanyNumber
     * @param array             $Responsibility
     *
     * @return IFormInterface|string
     */
    public function updateResponsibility(IFormInterface $Form, TblResponsibility $tblResponsibility, $CompanyNumber, $Responsibility)
    {

        Debugger::screenDump($Responsibility);

        /**
         * Skip to Frontend
         */
        if (null === $Responsibility) {
            return $Form;
        }

        if ((new Data($this->getBinding()))->updateResponsibility($tblResponsibility, $CompanyNumber)) {
            return new Success('Die Unternehmensnr. des Unfallversicherungsträgers wurde erfolgreich gespeichert')
                .new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_SUCCESS);
        }
        return new Danger('Die Unternehmensnr. des Unfallversicherungsträgers konnte nicht gespeichert werden')
            .new Redirect('/Setting/Consumer/Responsibility', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param TblResponsibility $tblResponsibility
     *
     * @return bool
     */
    public function destroyResponsibility(TblResponsibility $tblResponsibility)
    {

        return (new Data($this->getBinding()))->removeResponsibility($tblResponsibility);
    }
}

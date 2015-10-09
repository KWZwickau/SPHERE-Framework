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
        if (!empty( $Global->POST ) && null === $Responsibility) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie einen Schulträger aus')))));
            return $Form;
        }

        $Error = false;

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($Responsibility);

            if ((new Data($this->getBinding()))->addResponsibility($tblCompany)
            ) {
                return new Success('Der Schulträger wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/Responsibility', 1, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Der Schulträger konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/Responsibility', 10, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
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

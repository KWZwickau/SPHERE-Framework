<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\IServiceInterface;
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
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Consumer\Responsibility
 */
class Service extends Extension implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(
        Identifier $Identifier,
        $EntityPath,
        $EntityNamespace
    ) {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @return bool|TblResponsibility[]
     */
    public function getResponsibilityAll()
    {

        return (new Data($this->Binding))->getResponsibilityAll();
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

        var_dump($Responsibility);

        if (null === $Responsibility) {
            return $Form;
        }
        if (false === $Responsibility) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie einen Schulträger aus')))));
            return $Form;
        }

        $Error = false;

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($Responsibility);

            if ((new Data($this->Binding))->addResponsibility($tblCompany)
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

    public function removeResponsibility(
        IFormInterface $Form,
        $Responsibility
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Responsibility) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie den zu entfernenden Schulträger aus')))));
            return $Form;
        }

        $tblResponsibility = (new Data($this->Binding))->getResponsibilityById($Responsibility);

        if ((new Data($this->Binding))->removeResponsibility($tblResponsibility)) {
            return new Success('Der Schulträger wurde erfolgreich entfernt')
            .new Redirect('/Setting/Consumer/Responsibility', 1);
        } else {
            return new Danger('Der Schulträger konnte nicht entfernt werden')
            .new Redirect('/Setting/Consumer/Responsibility', 10);
        }
    }
}

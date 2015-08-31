<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Corporation\Company\Service\Data;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Company\Service\Setup;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Service implements IServiceInterface
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
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

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
     * int
     */
    public function countCompanyAll()
    {

        return count($this->getCompanyAll());
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return (new Data($this->Binding))->getCompanyAll();
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countCompanyAllByGroup(TblGroup $tblGroup)
    {

        return Group::useService()->countCompanyAllByGroup($tblGroup);
    }

    /**
     * @param IFormInterface $Form
     * @param array          $Company
     *
     * @return IFormInterface|Redirect
     */
    public function createCompany(IFormInterface $Form = null, $Company)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Company) {
            return $Form;
        }

        $Error = false;

        if (isset( $Company['Name'] ) && empty( $Company['Name'] )) {
            $Form->setError('Company[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {

            if (( $tblCompany = (new Data($this->Binding))->createCompany($Company['Name']) )) {
                // Add to Group
                if (isset( $Company['Group'] )) {
                    foreach ((array)$Company['Group'] as $tblGroup) {
                        Group::useService()->addGroupCompany(
                            Group::useService()->getGroupById($tblGroup), $tblCompany
                        );
                    }
                }
                return new Success('Die Firma wurde erfolgreich erstellt')
                .new Redirect('/Corporation/Company', 3,
                    array('Id' => $tblCompany->getId())
                );
            } else {
                return new Danger('Die Firma konnte nicht erstellt werden')
                .new Redirect('/Corporation/Company', 10);
            }
        }

        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        return (new Data($this->Binding))->getCompanyById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param TblCompany     $tblCompany
     * @param array          $Company
     *
     * @return IFormInterface|Redirect
     */
    public function updateCompany(IFormInterface $Form = null, TblCompany $tblCompany, $Company)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Company) {
            return $Form;
        }

        $Error = false;

        if (isset( $Company['Name'] ) && empty( $Company['Name'] )) {
            $Form->setError('Company[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->Binding))->updateCompany($tblCompany, $Company['Name'])) {
                // Change Groups
                if (isset( $Company['Group'] )) {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany);
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                    }
                    // Add current Groups
                    foreach ((array)$Company['Group'] as $tblGroup) {
                        Group::useService()->addGroupCompany(
                            Group::useService()->getGroupById($tblGroup), $tblCompany
                        );
                    }
                } else {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany);
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                    }
                }
                return new Success('Die Firma wurde erfolgreich aktualisiert')
                .new Redirect('/Corporation/Company', 1,
                    array('Id' => $tblCompany->getId())
                );
            } else {
                return new Danger('Die Firma konnte nicht aktualisiert werden')
                .new Redirect('/Corporation/Company', 10);
            }
        }

        return $Form;
    }
}

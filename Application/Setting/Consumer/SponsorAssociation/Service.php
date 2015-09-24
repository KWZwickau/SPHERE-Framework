<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Data;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Setup;
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
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation
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
     * @return bool|TblSponsorAssociation[]
     */
    public function getSponsorAssociationAll()
    {

        return (new Data($this->Binding))->getSponsorAssociationAll();
    }

    //ToDo
    /**
     * @param IFormInterface $Form
     * @param integer        $SponsorAssociation
     *
     * @return IFormInterface|string
     */
    public function createSponsorAssociation(
        IFormInterface $Form,
        $SponsorAssociation
    ) {

        /**
         * Skip to Frontend
         */

        if (null === $SponsorAssociation) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie einen Förderverein aus')))));
            return $Form;
        }

        $Error = false;

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($SponsorAssociation);

            if ((new Data($this->Binding))->addSponsorAssociation($tblCompany)
            ) {
                return new Success('Der Förderverein wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/SponsorAssociation', 1, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Der Förderverein konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/SponsorAssociation', 10, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param                $SponsorAssociation
     *
     * @return IFormInterface|string
     */
    public function removeSponsorAssociation(
        IFormInterface $Form,
        $SponsorAssociation
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $SponsorAssociation) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie den zu entfernenden Förderverein aus')))));
            return $Form;
        }

        $tblSponsorAssociation = (new Data($this->Binding))->getSponsorAssociationById($SponsorAssociation);

        if ((new Data($this->Binding))->removeSponsorAssociation($tblSponsorAssociation)) {
            return new Success('Der Förderverein wurde erfolgreich entfernt')
            .new Redirect('/Setting/Consumer/SponsorAssociation', 1);
        } else {
            return new Danger('Der Förderverein konnte nicht entfernt werden')
            .new Redirect('/Setting/Consumer/SponsorAssociation', 10);
        }
    }
}

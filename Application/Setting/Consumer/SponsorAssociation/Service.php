<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation;

use SPHERE\Application\Corporation\Company\Company;
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
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
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
     * @return bool|TblSponsorAssociation[]
     */
    public function getSponsorAssociationAll()
    {

        return (new Data($this->getBinding()))->getSponsorAssociationAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblSponsorAssociation
     */
    public function getSponsorAssociationById($Id)
    {

        return (new Data($this->getBinding()))->getSponsorAssociationById($Id);
    }

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
        $Global = $this->getGlobal();
        if (empty( $Global->POST )) {
            return $Form;
        }

        $Error = false;
        if (null === $SponsorAssociation) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Förderverein aus')))));
            $Error = true;
        } else {
            $tblCompany = Company::useService()->getCompanyById($SponsorAssociation);
            if (!$tblCompany){
                $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Förderverein aus')))));
                $Error = true;
            }
        }

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($SponsorAssociation);
            if ($tblCompany && (new Data($this->getBinding()))->addSponsorAssociation($tblCompany)
            ) {
                return new Success('Der Förderverein wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/SponsorAssociation', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Der Förderverein konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/SponsorAssociation', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
    }

    /**
     * @param TblSponsorAssociation $tblSponsorAssociation
     *
     * @return bool
     */
    public function destroySponsorAssociation(TblSponsorAssociation $tblSponsorAssociation)
    {

        return (new Data($this->getBinding()))->removeSponsorAssociation($tblSponsorAssociation);
    }
}

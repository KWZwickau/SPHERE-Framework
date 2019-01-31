<?php
namespace SPHERE\Application\Contact\Web;

use SPHERE\Application\Contact\Web\Service\Data;
use SPHERE\Application\Contact\Web\Service\Entity\TblWeb;
use SPHERE\Application\Contact\Web\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Web\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Web\Service\Entity\TblType;
use SPHERE\Application\Contact\Web\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Web
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

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblWeb
     */
    public function getWebById($Id)
    {

        return (new Data($this->getBinding()))->getWebById($Id);
    }

    /**
     * @return bool|TblWeb[]
     */
    public function getWebAll()
    {

        return (new Data($this->getBinding()))->getWebAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getWebAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getWebAllByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getWebAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getWebAllByCompany($tblCompany);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     * @param TblToCompany|null $tblToCompany
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormWebToCompany(
        TblCompany $tblCompany,
        $Address,
        $Type,
        TblToCompany $tblToCompany = null
    ) {

        $error = false;

        $form = Web::useFrontend()->formAddressToCompany($tblCompany->getId(), $tblToCompany ? $tblToCompany->getId() : null);
        $Address = $this->validateMailAddress($Address);
        if (isset($Address) && empty($Address)) {
            $form->setError('Address', 'Bitte geben Sie eine gültige Internet Adresse an');
            $error = true;
        } else {
            $form->setSuccess('Address');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param TblType $tblType
     * @param $Remark
     *
     * @return TblToCompany
     */
    public function insertWebToCompany(
        TblCompany $tblCompany,
        $Address,
        TblType $tblType,
        $Remark
    ) {

        $tblWeb = (new Data($this->getBinding()))->createWeb($Address);
        return (new Data($this->getBinding()))->addWebToCompany($tblCompany, $tblWeb, $tblType, $Remark);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTypeById($Id);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function createWebToCompany(
        TblCompany $tblCompany,
        $Address,
        $Type
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblWeb = (new Data($this->getBinding()))->createWeb($Address);

        if (!$tblType) {
            return false;
        }
        if (!$tblWeb) {
            return false;
        }

        if ((new Data($this->getBinding()))->addWebToCompany($tblCompany, $tblWeb, $tblType, $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function updateWebToCompany(
        TblToCompany $tblToCompany,
        $Address,
        $Type
    ) {

        $tblWeb = (new Data($this->getBinding()))->createWeb($Address);
        // Remove current
        (new Data($this->getBinding()))->removeWebToCompany($tblToCompany);

        if ($tblToCompany->getServiceTblCompany()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            // Add new
            if ((new Data($this->getBinding()))->addWebToCompany($tblToCompany->getServiceTblCompany(), $tblWeb,
                $tblType, $Type['Remark'])
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getWebToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getWebToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getWebToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getWebToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeWebToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removeWebToPerson($tblToPerson, $IsSoftRemove);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeWebToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->removeWebToCompany($tblToCompany);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeSoftWebAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblWebToPersonList = $this->getWebAllByPerson($tblPerson))){
            foreach($tblWebToPersonList as $tblToPerson){
                $this->removeWebToPerson($tblToPerson, $IsSoftRemove);
            }
        }
    }
}

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
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
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

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
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
     * @param IFormInterface $Form
     * @param                $Address
     * @param                $Type
     *
     * @return bool
     */
    public function checkFormWebToPerson(
        IFormInterface &$Form,
        $Address,
        $Type
    ) {

        $Error = false;

        if (isset($Address) && empty($Address)) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige Internet Adresse an');
            $Error = true;
        } else {
            $Form->setSuccess('Address');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }
        return $Error;
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createWebToPerson(
        IFormInterface $Form,
        TblPerson $tblPerson,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = $this->checkFormWebToPerson($Form, $Address, $Type);
        $tblType = $this->getTypeById($Type['Type']);

        if (!$Error && $tblType) {
            $tblWeb = (new Data($this->getBinding()))->createWeb($Address);

            if ((new Data($this->getBinding()))->addWebToPerson($tblPerson, $tblWeb, $tblType, $Type['Remark'])
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Internet Adresse wurde erfolgreich hinzugefügt')
                    .new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblPerson->getId()));
            } else {
                return new Danger(new Ban().' Die Internet Adresse konnte nicht hinzugefügt werden')
                    .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR, array('Id' => $tblPerson->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Address
     * @param TblType   $tblType
     * @param           $Remark
     *
     * @return TblToPerson
     */
    public function insertWebToPerson(
        TblPerson $tblPerson,
        $Address,
        TblType $tblType,
        $Remark
    ) {

        $tblWeb = (new Data($this->getBinding()))->createWeb($Address);
        return (new Data($this->getBinding()))->addWebToPerson($tblPerson, $tblWeb, $tblType, $Remark);
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
     * @param IFormInterface $Form
     * @param TblCompany     $tblCompany
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createWebToCompany(
        IFormInterface $Form,
        TblCompany $tblCompany,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige Internet Adresse an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblWeb = (new Data($this->getBinding()))->createWeb($Address);

            if ((new Data($this->getBinding()))->addWebToCompany($tblCompany, $tblWeb, $tblType, $Type['Remark'])
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .  ' Die Internet Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger(new Ban() . ' Die Internet Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateWebToPerson(
        IFormInterface $Form,
        TblToPerson $tblToPerson,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige Internet Adresse an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblWeb = (new Data($this->getBinding()))->createWeb($Address);
            // Remove current
            (new Data($this->getBinding()))->removeWebToPerson($tblToPerson);

            if ($tblToPerson->getServiceTblPerson()) {
                // Add new
                if ((new Data($this->getBinding()))->addWebToPerson($tblToPerson->getServiceTblPerson(), $tblWeb,
                    $tblType, $Type['Remark'])
                ) {
                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Internet Adresse wurde erfolgreich geändert')
                    . new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
                } else {
                    return new Danger(new Ban() . ' Die Internet Adresse konnte nicht geändert werden')
                    . new Redirect('/People/Person', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
                }
            } else {
                return new Danger('Person nicht gefunden', new Ban());
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToCompany   $tblToCompany
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateWebToCompany(
        IFormInterface $Form,
        TblToCompany $tblToCompany,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige Internet Adresse an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblWeb = (new Data($this->getBinding()))->createWeb($Address);
            // Remove current
            (new Data($this->getBinding()))->removeWebToCompany($tblToCompany);

            if ($tblToCompany->getServiceTblCompany()) {
                // Add new
                if ((new Data($this->getBinding()))->addWebToCompany($tblToCompany->getServiceTblCompany(), $tblWeb,
                    $tblType, $Type['Remark'])
                ) {
                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Internet Adresse wurde erfolgreich geändert')
                    . new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
                } else {
                    return new Danger(new Ban() . ' Die Internet Adresse konnte nicht geändert werden')
                    . new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
                }
            } else {
                return new Danger('Institution nicht gefunden', new Ban());
            }
        }
        return $Form;
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

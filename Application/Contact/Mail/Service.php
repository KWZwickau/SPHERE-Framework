<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Contact\Mail\Service\Data;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType;
use SPHERE\Application\Contact\Mail\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
 * @package SPHERE\Application\Contact\Mail
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
     * @param integer $Id
     *
     * @return bool|TblMail
     */
    public function getMailById($Id)
    {

        return (new Data($this->Binding))->getMailById($Id);
    }

    /**
     * @return bool|TblMail[]
     */
    public function getMailAll()
    {

        return (new Data($this->Binding))->getMailAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->Binding))->getTypeAll();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getMailAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->Binding))->getMailAllByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getMailAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->Binding))->getMailAllByCompany($tblCompany);
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createMailToPerson(
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

        $Error = false;

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $Error = true;
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            $tblMail = (new Data($this->Binding))->createMail($Address);

            if ((new Data($this->Binding))->addMailToPerson($tblPerson, $tblMail, $tblType, $Type['Remark'])
            ) {
                return new Success('Die E-Mail Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/People/Person', 1, array('Id' => $tblPerson->getId()));
            } else {
                return new Danger('Die E-Mail Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/People/Person', 10, array('Id' => $tblPerson->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblCompany     $tblCompany
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createMailToCompany(
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

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $Error = true;
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            $tblMail = (new Data($this->Binding))->createMail($Address);

            if ((new Data($this->Binding))->addMailToCompany($tblCompany, $tblMail, $tblType, $Type['Remark'])
            ) {
                return new Success('Die E-Mail Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/Corporation/Company', 1, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Die E-Mail Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/Corporation/Company', 10, array('Id' => $tblCompany->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->Binding))->getTypeById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateMailToPerson(
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

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $Error = true;
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            $tblMail = (new Data($this->Binding))->createMail($Address);
            // Remove current
            (new Data($this->Binding))->removeMailToPerson($tblToPerson);
            // Add new
            if ((new Data($this->Binding))->addMailToPerson($tblToPerson->getServiceTblPerson(), $tblMail,
                $tblType, $Type['Remark'])
            ) {
                return new Success('Die E-Mail Adresse wurde erfolgreich geändert')
                .new Redirect('/People/Person', 1,
                    array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
            } else {
                return new Danger('Die E-Mail Adresse konnte nicht geändert werden')
                .new Redirect('/People/Person', 10,
                    array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getMailToPersonById($Id)
    {

        return (new Data($this->Binding))->getMailToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getMailToCompanyById($Id)
    {

        return (new Data($this->Binding))->getMailToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removeMailToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->Binding))->removeMailToPerson($tblToPerson);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeMailToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->Binding))->removeMailToCompany($tblToCompany);
    }
}

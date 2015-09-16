<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Data;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\People\Relationship\Service\Setup;
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
 * @package SPHERE\Application\People\Relationship
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
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->Binding))->getPersonRelationshipAllByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getCompanyRelationshipAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->Binding))->getCompanyRelationshipAllByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getCompanyRelationshipAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->Binding))->getCompanyRelationshipAllByCompany($tblCompany);
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPersonFrom
     * @param int            $tblPersonTo
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createRelationshipToPerson(
        IFormInterface $Form,
        TblPerson $tblPersonFrom,
        $tblPersonTo,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Type) {
            return $Form;
        }

        $Error = false;

        if (empty( $tblPersonTo )) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Person')))));
            $Error = true;
        } else {
            $tblPersonTo = Person::useService()->getPersonById($tblPersonTo);
            if ($tblPersonFrom->getId() == $tblPersonTo->getId()) {
                $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Eine Person kann nur mit einer anderen Person verknüpft werden')))));
                $Error = true;
            }
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);

            if ((new Data($this->Binding))->addPersonRelationshipToPerson($tblPersonFrom, $tblPersonTo, $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Beziehung wurde erfolgreich hinzugefügt')
                .new Redirect('/People/Person', 1, array('Id' => $tblPersonFrom->getId()));
            } else {
                return new Danger('Die Beziehung konnte nicht hinzugefügt werden')
                .new Redirect('/People/Person', 10, array('Id' => $tblPersonFrom->getId()));
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
     * @param TblPerson      $tblPersonFrom
     * @param int            $tblCompanyTo
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createRelationshipToCompany(
        IFormInterface $Form,
        TblPerson $tblPersonFrom,
        $tblCompanyTo,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Type) {
            return $Form;
        }

        $Error = false;

        if (empty( $tblCompanyTo )) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Firma')))));
            $Error = true;
        } else {
            $tblCompanyTo = Company::useService()->getCompanyById($tblCompanyTo);
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);

            if ((new Data($this->Binding))->addCompanyRelationshipToPerson($tblCompanyTo, $tblPersonFrom, $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Beziehung wurde erfolgreich hinzugefügt')
                .new Redirect('/People/Person', 1, array('Id' => $tblPersonFrom->getId()));
            } else {
                return new Danger('Die Beziehung konnte nicht hinzugefügt werden')
                .new Redirect('/People/Person', 10, array('Id' => $tblPersonFrom->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param TblPerson      $tblPersonFrom
     * @param int            $tblPersonTo
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateRelationshipToPerson(
        IFormInterface $Form,
        TblToPerson $tblToPerson,
        TblPerson $tblPersonFrom,
        $tblPersonTo,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Type) {
            return $Form;
        }

        $Error = false;

        if (empty( $tblPersonTo )) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Person')))));
            $Error = true;
        } else {
            $tblPersonTo = Person::useService()->getPersonById($tblPersonTo);
            if ($tblPersonFrom->getId() == $tblPersonTo->getId()) {
                $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Eine Person kann nur mit einer anderen Person verknüpft werden')))));
                $Error = true;
            }
        }

        if (!$Error) {
            $tblType = $this->getTypeById($Type['Type']);
            // Remove current
            (new Data($this->Binding))->removePersonRelationshipToPerson($tblToPerson);
            // Add new
            if ((new Data($this->Binding))->addPersonRelationshipToPerson($tblPersonFrom, $tblPersonTo, $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Beziehung wurde erfolgreich geändert')
                .new Redirect('/People/Person', 1,
                    array('Id' => $tblToPerson->getServiceTblPersonFrom()->getId()));
            } else {
                return new Danger('Die Beziehung konnte nicht geändert werden')
                .new Redirect('/People/Person', 10,
                    array('Id' => $tblToPerson->getServiceTblPersonFrom()->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToCompany   $tblToCompany
     * @param TblPerson      $tblPersonFrom
     * @param int            $tblCompanyTo
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateRelationshipToCompany(
        IFormInterface $Form,
        TblToCompany $tblToCompany,
        TblPerson $tblPersonFrom,
        $tblCompanyTo,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Type) {
            return $Form;
        }

        $Error = false;

        if (empty( $tblCompanyTo )) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Firma')))));
            $Error = true;
        } else {
            $tblCompanyTo = Company::useService()->getCompanyById($tblCompanyTo);
        }

        if (!$Error) {
            $tblType = $this->getTypeById($Type['Type']);
            // Remove current
            (new Data($this->Binding))->removeCompanyRelationshipToPerson($tblToCompany);
            // Add new
            if ((new Data($this->Binding))->addCompanyRelationshipToPerson($tblCompanyTo, $tblPersonFrom, $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Beziehung wurde erfolgreich geändert')
                .new Redirect('/People/Person', 1,
                    array('Id' => $tblToCompany->getServiceTblPerson()->getId()));
            } else {
                return new Danger('Die Beziehung konnte nicht geändert werden')
                .new Redirect('/People/Person', 10,
                    array('Id' => $tblToCompany->getServiceTblPerson()->getId()));
            }
        }
        return $Form;
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->Binding))->getTypeAll();
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removePersonRelationshipToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->Binding))->removePersonRelationshipToPerson($tblToPerson);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeCompanyRelationshipToPerson(TblToCompany $tblToCompany)
    {

        return (new Data($this->Binding))->removeCompanyRelationshipToPerson($tblToCompany);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getRelationshipToPersonById($Id)
    {

        return (new Data($this->Binding))->getRelationshipToPersonById($Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getRelationshipToCompanyById($Id)
    {

        return (new Data($this->Binding))->getRelationshipToCompanyById($Id);
    }
}

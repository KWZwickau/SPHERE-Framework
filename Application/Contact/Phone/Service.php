<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Data;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Contact\Phone\Service\Setup;
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
 * @package SPHERE\Application\Contact\Phone
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
    public function __construct( Identifier $Identifier, $EntityPath, $EntityNamespace )
    {

        $this->Binding = new Binding( $Identifier, $EntityPath, $EntityNamespace );
        $this->Structure = new Structure( $Identifier );
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService( $doSimulation, $withData )
    {

        $Protocol = ( new Setup( $this->Structure ) )->setupDatabaseSchema( $doSimulation );
        if (!$doSimulation && $withData) {
            ( new Data( $this->Binding ) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById( $Id )
    {

        return ( new Data( $this->Binding ) )->getTypeById( $Id );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPhone
     */
    public function getPhoneById( $Id )
    {

        return ( new Data( $this->Binding ) )->getPhoneById( $Id );
    }

    /**
     * @return bool|TblPhone[]
     */
    public function getPhoneAll()
    {

        return ( new Data( $this->Binding ) )->getPhoneAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return ( new Data( $this->Binding ) )->getTypeAll();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPhoneAllByPerson( TblPerson $tblPerson )
    {

        return ( new Data( $this->Binding ) )->getPhoneAllByPerson( $tblPerson );
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getPhoneAllByCompany( TblCompany $tblCompany )
    {

        return ( new Data( $this->Binding ) )->getPhoneAllByCompany( $tblCompany );
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param string         $Number
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createPhoneToPerson(
        IFormInterface $Form,
        TblPerson $tblPerson,
        $Number,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Number) {
            return $Form;
        }

        $Error = false;

        if (isset( $Number ) && empty( $Number )) {
            $Form->setError( 'Number', 'Bitte geben Sie eine gültige Telefonnummer an' );
            $Error = true;
        }

        if (!$Error) {

            $tblType = $this->getTypeById( $Type['Type'] );
            $tblPhone = ( new Data( $this->Binding ) )->createPhone( $Number );

            if (( new Data( $this->Binding ) )->addPhoneToPerson( $tblPerson, $tblPhone, $tblType, $Type['Remark'] )
            ) {
                return new Success( 'Die Telefonnummer wurde erfolgreich hinzugefügt' )
                .new Redirect( '/People/Person', 1, array( 'Id' => $tblPerson->getId() ) );
            } else {
                return new Danger( 'Die Telefonnummer konnte nicht hinzugefügt werden' )
                .new Redirect( '/People/Person', 10, array( 'Id' => $tblPerson->getId() ) );
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param string         $Number
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updatePhoneToPerson(
        IFormInterface $Form,
        TblToPerson $tblToPerson,
        $Number,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Number) {
            return $Form;
        }

        $Error = false;

        if (isset( $Number ) && empty( $Number )) {
            $Form->setError( 'Number', 'Bitte geben Sie eine gültige Telefonnummer an' );
            $Error = true;
        }

        if (!$Error) {

            $tblType = $this->getTypeById( $Type['Type'] );
            $tblPhone = ( new Data( $this->Binding ) )->createPhone( $Number );
            // Remove current
            ( new Data( $this->Binding ) )->removePhoneToPerson( $tblToPerson );
            // Add new
            if (( new Data( $this->Binding ) )->addPhoneToPerson( $tblToPerson->getServiceTblPerson(), $tblPhone,
                $tblType, $Type['Remark'] )
            ) {
                return new Success( 'Die Telefonnummer wurde erfolgreich geändert' )
                .new Redirect( '/People/Person', 1,
                    array( 'Id' => $tblToPerson->getServiceTblPerson()->getId() ) );
            } else {
                return new Danger( 'Die Telefonnummer konnte nicht geändert werden' )
                .new Redirect( '/People/Person', 10,
                    array( 'Id' => $tblToPerson->getServiceTblPerson()->getId() ) );
            }
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getPhoneToPersonById( $Id )
    {

        return ( new Data( $this->Binding ) )->getPhoneToPersonById( $Id );
    }
}

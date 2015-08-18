<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Data;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Setup;
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
 * @package SPHERE\Application\People\Person
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
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

        return ( new Data( $this->Binding ) )->getSalutationAll();
    }

    /**
     * int
     */
    public function countPersonAll()
    {

        return count( $this->getPersonAll() );
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        return ( new Data( $this->Binding ) )->getPersonAll();
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countPersonAllByGroup( TblGroup $tblGroup )
    {

        return Group::useService()->countPersonAllByGroup( $tblGroup );
    }

    /**
     * @param IFormInterface $Form
     * @param array          $Person
     *
     * @return IFormInterface|Redirect
     */
    public function createPerson( IFormInterface $Form = null, $Person )
    {

        /**
         * Skip to Frontend
         */
        if (null === $Person) {
            return $Form;
        }

        $Error = false;

        if (isset( $Person['FirstName'] ) && empty( $Person['FirstName'] )) {
            $Form->setError( 'Person[FirstName]', 'Bitte geben Sie einen Vornamen an' );
            $Error = true;
        }
        if (isset( $Person['LastName'] ) && empty( $Person['LastName'] )) {
            $Form->setError( 'Person[LastName]', 'Bitte geben Sie einen Nachnamen an' );
            $Error = true;
        }

        if (!$Error) {

            if (( $tblPerson = ( new Data( $this->Binding ) )->createPerson(
                $this->getSalutationById( $Person['Salutation'] ), $Person['Title'], $Person['FirstName'],
                $Person['SecondName'], $Person['LastName'] ) )
            ) {
                // Add to Group
                if (isset( $Person['Group'] )) {
                    foreach ((array)$Person['Group'] as $tblGroup) {
                        Group::useService()->addGroupPerson(
                            Group::useService()->getGroupById( $tblGroup ), $tblPerson
                        );
                    }
                }
                return new Success( 'Die Person wurde erfolgreich erstellt' )
                .new Redirect( '/People/Person', 3,
                    array( 'tblPerson' => $tblPerson->getId() )
                );
            } else {
                return new Danger( 'Die Person konnte nicht erstellt werden' )
                .new Redirect( '/People/Person', 10 );
            }
        }

        return $Form;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById( $Id )
    {

        return ( new Data( $this->Binding ) )->getSalutationById( $Id );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPerson
     */
    public function getPersonById( $Id )
    {

        return ( new Data( $this->Binding ) )->getPersonById( $Id );
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Person
     *
     * @return IFormInterface|Redirect
     */
    public function updatePerson( IFormInterface $Form = null, TblPerson $tblPerson, $Person )
    {

        /**
         * Skip to Frontend
         */
        if (null === $Person) {
            return $Form;
        }

        $Error = false;

        if (isset( $Person['FirstName'] ) && empty( $Person['FirstName'] )) {
            $Form->setError( 'Person[FirstName]', 'Bitte geben Sie einen Vornamen an' );
            $Error = true;
        }
        if (isset( $Person['LastName'] ) && empty( $Person['LastName'] )) {
            $Form->setError( 'Person[LastName]', 'Bitte geben Sie einen Nachnamen an' );
            $Error = true;
        }

        if (!$Error) {

            if (( new Data( $this->Binding ) )->updatePerson( $tblPerson,
                $this->getSalutationById( $Person['Salutation'] ), $Person['Title'], $Person['FirstName'],
                $Person['SecondName'], $Person['LastName'] )
            ) {
                // Change Groups
                if (isset( $Person['Group'] )) {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByPerson( $tblPerson );
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupPerson( $tblGroup, $tblPerson );
                    }
                    // Add current Groups
                    foreach ((array)$Person['Group'] as $tblGroup) {
                        Group::useService()->addGroupPerson(
                            Group::useService()->getGroupById( $tblGroup ), $tblPerson
                        );
                    }
                } else {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByPerson( $tblPerson );
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupPerson( $tblGroup, $tblPerson );
                    }
                }
                return new Success( 'Die Person wurde erfolgreich aktualisiert' )
                .new Redirect( '/People/Person', 3,
                    array( 'tblPerson' => $tblPerson->getId() )
                );
            } else {
                return new Danger( 'Die Person konnte nicht aktualisiert werden' )
                .new Redirect( '/People/Person', 10 );
            }
        }

        return $Form;
    }
}

<?php
namespace SPHERE\Application\People\Group;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Service\Data;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Group\Service\Setup;
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
 * @package SPHERE\Application\People\Group
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
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return ( new Data( $this->Binding ) )->getGroupAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById( $Id )
    {

        return ( new Data( $this->Binding ) )->getGroupById( $Id );
    }

    /**
     * @param IFormInterface $Form
     * @param array          $Group
     *
     * @return IFormInterface|Redirect
     */
    public function createGroup( IFormInterface $Form = null, $Group )
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError( 'Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an' );
            $Error = true;
        } else {
            if ($this->getGroupByName( $Group['Name'] )) {
                $Form->setError( 'Group[Name]', 'Bitte geben Sie einen eineindeutigen Namen für die Gruppe an' );
                $Error = true;
            }
        }

        if (!$Error) {
            if (( new Data( $this->Binding ) )->createGroup(
                $Group['Name'], $Group['Description'], $Group['Remark']
            )
            ) {
                return new Success( 'Die Gruppe wurde erfolgreich erstellt' ).new Redirect( '/People/Group', 3 );
            } else {
                return new Danger( 'Die Gruppe konnte nicht erstellt werden' ).new Redirect( '/People/Group', 10 );
            }
        }

        return $Form;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getGroupByName( $Name );
    }

    /**
     * @param IFormInterface $Form
     * @param TblGroup       $tblGroup
     * @param array          $Group
     *
     * @return IFormInterface|Redirect
     */
    public function updateGroup( IFormInterface $Form = null, TblGroup $tblGroup, $Group )
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError( 'Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an' );
            $Error = true;
        } else {
            $tblGroupTwin = $this->getGroupByName( $Group['Name'] );
            if ($tblGroupTwin && $tblGroupTwin->getId() != $tblGroup->getId()) {
                $Form->setError( 'Group[Name]', 'Bitte geben Sie einen eineindeutigen Namen für die Gruppe an' );
                $Error = true;
            }
        }

        if (!$Error) {
            if (( new Data( $this->Binding ) )->updateGroup(
                $tblGroup, $Group['Name'], $Group['Description'], $Group['Remark']
            )
            ) {
                return new Success( 'Die Änderungen wurden erfolgreich gespeichert' )
                .new Redirect( '/People/Group', 3 );
            } else {
                return new Danger( 'Die Änderungen konnte nicht gespeichert werden' )
                .new Redirect( '/People/Group', 10 );
            }
        }

        return $Form;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByGroup( TblGroup $tblGroup )
    {

        return ( new Data( $this->Binding ) )->getPersonAllByGroup( $tblGroup );
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countPersonAllByGroup( TblGroup $tblGroup )
    {

        return ( new Data( $this->Binding ) )->countPersonAllByGroup( $tblGroup );
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByPerson( TblPerson $tblPerson )
    {

        return ( new Data( $this->Binding ) )->getGroupAllByPerson( $tblPerson );
    }

    /**
     * @param TblGroup  $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeGroupPerson( TblGroup $tblGroup, TblPerson $tblPerson )
    {

        return ( new Data( $this->Binding ) )->removeGroupPerson( $tblGroup, $tblPerson );
    }

    /**
     * @param TblGroup  $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return TblMember
     */
    public function addGroupPerson( TblGroup $tblGroup, TblPerson $tblPerson )
    {

        return ( new Data( $this->Binding ) )->addGroupPerson( $tblGroup, $tblPerson );
    }
}

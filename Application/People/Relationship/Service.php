<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Data;
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
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Relationship
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
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson( TblPerson $tblPerson )
    {

        return ( new Data( $this->Binding ) )->getPersonRelationshipAllByPerson( $tblPerson );
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
            $Form->appendGridGroup( new FormGroup( new FormRow( new FormColumn( new Danger( 'Bitte wählen Sie eine Person' ) )) ) );
            $Error = true;
        }

        if (!$Error) {

            $tblType = $this->getTypeById( $Type['Type'] );
            $tblPersonTo = Person::useService()->getPersonById( $tblPersonTo );

            if (( new Data( $this->Binding ) )->addPersonRelationshipToPerson( $tblPersonFrom, $tblPersonTo, $tblType,
                $Type['Remark'] )
            ) {
                return new Success( 'Die Beziehung wurde erfolgreich hinzugefügt' )
                .new Redirect( '/People/Person', 1, array( 'Id' => $tblPersonFrom->getId() ) );
            } else {
                return new Danger( 'Die Beziehung konnte nicht hinzugefügt werden' )
                .new Redirect( '/People/Person', 10, array( 'Id' => $tblPersonFrom->getId() ) );
            }
        }
        return $Form;
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
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return ( new Data( $this->Binding ) )->getTypeAll();
    }
}

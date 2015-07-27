<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Consumer;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service\Data;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Consumer
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
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupService( $Simulate )
    {

        $Protocol = ( new Setup( $this->Structure ) )->setupDatabaseSchema( $Simulate );
        if (!$Simulate) {
            ( new Data( $this->Binding ) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerById( $Id )
    {

        return ( new Data( $this->Binding ) )->getConsumerById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getConsumerByName( $Name );
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblConsumer
     */
    public function getConsumerBySession( $Session = null )
    {

        return ( new Data( $this->Binding ) )->getConsumerBySession( $Session );
    }

    /**
     * @return bool|TblConsumer[]
     */
    public function getConsumerAll()
    {

        return ( new Data( $this->Binding ) )->getConsumerAll();
    }

    /**
     * @param IFormInterface $Form
     * @param string         $ConsumerAcronym
     * @param string         $ConsumerName
     *
     * @return IFormInterface|Redirect
     */
    public function createConsumer(
        IFormInterface &$Form,
        $ConsumerAcronym,
        $ConsumerName
    ) {

        if (null === $ConsumerName
            && null === $ConsumerAcronym
        ) {
            return $Form;
        }

        $Error = false;
        if (null !== $ConsumerAcronym && empty( $ConsumerAcronym )) {
            $Form->setError( 'ConsumerAcronym', 'Bitte geben Sie ein Mandantenkürzel an' );
            $Error = true;
        }
        if ($this->getConsumerByAcronym( $ConsumerAcronym )) {
            $Form->setError( 'ConsumerAcronym', 'Das Mandantenkürzel muss einzigartig sein' );
            $Error = true;
        }
        if (null !== $ConsumerName && empty( $ConsumerName )) {
            $Form->setError( 'ConsumerName', 'Bitte geben Sie einen gültigen Mandantenname ein' );
            $Error = true;
        }

        if ($Error) {
            return $Form;
        } else {
            ( new Data( $this->Binding ) )->createConsumer( $ConsumerAcronym, $ConsumerName );
            return new Redirect( '/System/Gatekeeper/Authorization/Consumer/Create', 0 );
        }
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByAcronym( $Acronym )
    {

        return ( new Data( $this->Binding ) )->getConsumerByAcronym( $Acronym );
    }
}

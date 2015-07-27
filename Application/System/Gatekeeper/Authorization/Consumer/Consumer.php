<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Consumer;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Consumer
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Consumer
 */
class Consumer implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation( new Link( new Link\Route( __NAMESPACE__ ),
            new Link\Name( 'Mandanten' ) ),
            new Link\Route( '/System/Gatekeeper/Authorization' )
        );
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param IFormInterface $Form
     * @param string         $ConsumerAcronym
     * @param string         $ConsumerName
     *
     * @return IFormInterface|Redirect
     */
    public function executeCreateConsumer(
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
        if ($this->useService()->getConsumerByAcronym( $ConsumerAcronym )) {
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
            $this->useService()->createConsumer( $ConsumerAcronym, $ConsumerName );
            return new Redirect( '/System/Gatekeeper/Authorization/Consumer/Create', 0 );
        }
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'System', 'Gatekeeper', 'Consumer' ),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }


}

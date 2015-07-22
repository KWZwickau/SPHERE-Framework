<?php
namespace SPHERE\Application\System\Gatekeeper\Consumer;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Consumer
 *
 * @package SPHERE\Application\System\Gatekeeper\Consumer
 */
class Consumer implements IModuleInterface
{

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
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
            return new Redirect( '/Sphere/System/Consumer/Create', 0 );
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

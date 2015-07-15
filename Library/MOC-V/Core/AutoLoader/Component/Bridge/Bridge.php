<?php
namespace MOC\V\Core\AutoLoader\Component\Bridge;

use MOC\V\Core\AutoLoader\Component\IBridgeInterface;

/**
 * Class Bridge
 *
 * @package MOC\V\Core\AutoLoader\Component\Bridge
 */
abstract class Bridge implements IBridgeInterface
{

    /**
     * @return IBridgeInterface
     */
    public function registerLoader()
    {

        /**
         * Prevent multiple Loader-Instance
         */
        $Loader = spl_autoload_functions();
        if (is_array( $Loader )) {
            array_walk( $Loader, function ( &$L ) {

                if (is_array( $L )) {
                    $Stack = $L[0];
                } else {
                    $Stack = $L;
                }
                if ($Stack instanceof Bridge) {
                    if ($Stack->getLoaderHash() == $this->getLoaderHash()) {
                        $L = false;
                    }
                }
            }, $this );
            $Loader = in_array( false, $Loader );
        } else {
            // @codeCoverageIgnoreStart
            $Loader = false;
            // @codeCoverageIgnoreEnd
        }
        /**
         * Register Loader-Instance
         */
        if (!$Loader) {
            spl_autoload_register( array( $this, 'loadSourceFile' ), true, false );
        }
        return $this;
    }

    /**
     * @return IBridgeInterface
     */
    public function unregisterLoader()
    {

        spl_autoload_unregister( array( $this, 'loadSourceFile' ) );
        return $this;
    }

}

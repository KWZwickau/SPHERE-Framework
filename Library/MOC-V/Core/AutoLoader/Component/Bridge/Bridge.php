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

        spl_autoload_register(array($this, 'loadSourceFile'), true, false);
        return $this;
    }

    /**
     * @return IBridgeInterface
     */
    public function unregisterLoader()
    {

        spl_autoload_unregister(array($this, 'loadSourceFile'));
        return $this;
    }

}

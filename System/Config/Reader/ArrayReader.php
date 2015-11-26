<?php
namespace SPHERE\System\Config\Reader;

use SPHERE\System\Config\ConfigContainer;

/**
 * Class ArrayReader
 *
 * @package SPHERE\System\Config\Reader
 */
class ArrayReader extends AbstractReader implements ReaderInterface
{

    /**
     * @param array $Array
     *
     * @return ReaderInterface
     */
    public function setConfig($Array)
    {

        $this->Registry = new ConfigContainer($Array);
        return $this;
    }
}

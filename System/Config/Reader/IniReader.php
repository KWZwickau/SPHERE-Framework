<?php
namespace SPHERE\System\Config\Reader;

use SPHERE\System\Config\ConfigContainer;

/**
 * Class IniReader
 *
 * @package SPHERE\System\Config\Reader
 */
class IniReader extends AbstractReader implements ReaderInterface
{

    /**
     * @param string $File
     *
     * @return ReaderInterface
     */
    public function setConfig($File)
    {

        $this->Registry = new ConfigContainer(parse_ini_file($File, true));
        return $this;
    }
}

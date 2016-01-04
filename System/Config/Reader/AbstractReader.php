<?php
namespace SPHERE\System\Config\Reader;

use SPHERE\System\Config\ConfigContainer;

/**
 * Class AbstractReader
 *
 * @package SPHERE\System\Config\Reader
 */
abstract class AbstractReader implements ReaderInterface
{

    /** @var ConfigContainer $Registry */
    protected $Registry = null;

    /**
     * @param string $Key
     *
     * @return mixed|null|ConfigContainer
     */
    public function getValue($Key)
    {

        return $this->Registry->getContainer($Key);
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {

        return $this->Registry;
    }
}

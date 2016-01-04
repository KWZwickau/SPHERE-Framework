<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer\Type;

use SPHERE\Application\Document\Explorer\Storage\Writer\AbstractWriter;

/**
 * Class Temporary
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer\Type
 */
class Temporary extends AbstractWriter
{

    /** @var bool $Destruct */
    private $Destruct = true;

    /**
     * @param string $Prefix
     * @param string $Extension
     * @param bool   $Destruct
     */
    public function __construct($Prefix = 'SPHERE-Temporary', $Extension = 'storage', $Destruct = true)
    {

        $Location = sys_get_temp_dir().DIRECTORY_SEPARATOR.$Prefix.'-'.sha1(uniqid($Prefix, true)).'.'.$Extension;
        $this->setFileLocation($Location);
        $this->Destruct = (bool)$Destruct;
    }

    /**
     *
     */
    public function __destruct()
    {

        if ($this->Destruct && $this->getRealPath()) {
            unlink($this->getRealPath());
        }
    }
}

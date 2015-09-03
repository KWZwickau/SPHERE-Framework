<?php
namespace MOC\V\Component\Template\Component\Parameter\Repository;

use MOC\V\Component\Template\Component\Exception\Repository\EmptyFileException;
use MOC\V\Component\Template\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Template\Component\IParameterInterface;
use MOC\V\Component\Template\Component\Parameter\Parameter;

/**
 * Class FileParameter
 *
 * @package MOC\V\Component\Template\Component\Parameter\Repository
 */
class FileParameter extends Parameter implements IParameterInterface
{

    /** @var string $File */
    private $File = null;

    /**
     * @param string $File
     */
    public function __construct($File)
    {

        $this->setFile($File);
    }

    /**
     * @return string
     */
    public function getFile()
    {

        return $this->File;
    }

    /**
     * @param string $File
     *
     * @throws \MOC\V\Component\Template\Component\Exception\Repository\EmptyFileException
     * @throws TypeFileException
     */
    public function setFile($File)
    {

        if (empty( $File )) {
            throw new EmptyFileException();
        } else {
            if (!is_dir($File)) {
                $this->File = $File;
            } else {
                throw new TypeFileException($File);
            }
        }
    }
}

<?php
namespace MOC\V\Core\FileSystem\Component\Parameter\Repository;

use MOC\V\Core\FileSystem\Component\Exception\Repository\EmptyFileException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use MOC\V\Core\FileSystem\Component\IParameterInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Parameter;

/**
 * Class FileParameter
 *
 * @package MOC\V\Core\FileSystem\Component\Parameter\Repository
 */
class FileParameter extends Parameter implements IParameterInterface
{

    /** @var string $File */
    private $File = null;

    /**
     * @param string $File
     */
    function __construct( $File )
    {

        $this->setFile( $File );
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
     * @throws EmptyFileException
     * @throws \MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException
     */
    public function setFile( $File )
    {

        if (empty( $File )) {
            throw new EmptyFileException();
        } else {
            if (!is_dir( $File )) {
                $this->File = $File;
            } else {
                throw new TypeFileException( $File );
            }
        }
    }
}

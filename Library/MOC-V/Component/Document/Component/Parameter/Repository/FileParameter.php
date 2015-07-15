<?php
namespace MOC\V\Component\Document\Component\Parameter\Repository;

use MOC\V\Component\Document\Component\Exception\Repository\EmptyFileException;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\IParameterInterface;
use MOC\V\Component\Document\Component\Parameter\Parameter;

/**
 * Class FileParameter
 *
 * @package MOC\V\Component\Document\Component\Parameter\Repository
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

        $this->setFile( (string)$File );
    }

    /**
     * @return \SplFileInfo
     */
    public function getFileInfo()
    {

        return new \SplFileInfo( $this->getFile() );
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
     * @throws TypeFileException
     */
    public function setFile( $File )
    {

        if (empty( $File )) {
            throw new EmptyFileException();
        } else {
            if (!is_dir( $File )) {
                $this->File = $File;
            } else {
                throw new TypeFileException( $File.' is a directory!' );
            }
        }
    }

    /**
     * @return string
     */
    function __toString()
    {

        return $this->getFile();
    }
}

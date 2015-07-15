<?php
namespace MOC\V\Component\Documentation\Component\Parameter\Repository;

/**
 * Class ExcludeParameter
 *
 * @package MOC\V\Component\Documentation\Component\Parameter\Repository
 */
class ExcludeParameter
{

    /** @var string $GlobList */
    private $GlobList = null;

    /**
     * @param array $GlobDirectory
     */
    function __construct( $GlobDirectory )
    {

        $this->setGlobList( $GlobDirectory );
    }

    /**
     * @return string
     */
    public function getGlobList()
    {

        return implode( ",", $this->GlobList );
    }

    /**
     * @param array $GlobList
     */
    public function setGlobList( $GlobList )
    {

        $this->GlobList = $GlobList;
    }
}

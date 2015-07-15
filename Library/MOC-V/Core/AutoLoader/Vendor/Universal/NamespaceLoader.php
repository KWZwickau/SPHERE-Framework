<?php
namespace MOC\V\Core\AutoLoader\Vendor\Universal;

use MOC\V\Core\AutoLoader\Vendor\Universal\NamespaceLoader\NamespaceSearch;

/**
 * Class NamespaceLoader
 *
 * @package MOC\V\Core\AutoLoader\Vendor\Universal
 */
class NamespaceLoader extends NamespaceSearch
{

    /**
     * @return string
     */
    public function getLoaderHash()
    {

        return sha1(
            serialize(
                get_object_vars( $this )
            )
        );
    }

    /**
     * @param string $ClassName
     *
     * @return bool
     */
    public function loadClass( $ClassName )
    {

        if ($this->findSource( $ClassName )) {
            return true;
        }
        return false;
    }

    /**
     * @param string $ClassName
     *
     * @return bool
     */
    private function findSource( $ClassName )
    {

        $LoadNamespace = $this->getClassNamespace( $ClassName );
        /**
         * @var string $Namespace
         * @var array  $DirectoryList
         */
        foreach ((array)$this->getNamespaceList() as $Namespace) {
            if (empty( $LoadNamespace ) || empty( $Namespace ) || 0 !== strpos( $LoadNamespace, $Namespace )) {
                continue;
            }
            $DirectoryList = $this->getNamespaceMapping( $Namespace );
            if ($this->searchForClass( $DirectoryList, $ClassName )) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }
            if ($this->searchForClassFallback( $DirectoryList, $ClassName, $Namespace )) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }
            if ($this->searchForInterface( $DirectoryList, $ClassName )) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }
            if ($this->searchForInterfaceFallback( $DirectoryList, $ClassName, $Namespace )) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }
        }
        return false;
    }

    /**
     * @param string $ClassName
     *
     * @return string
     */
    protected function getClassNamespace( $ClassName )
    {

        return substr( $ClassName, 0, strrpos( $ClassName, '\\' ) );
    }
}


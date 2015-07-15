<?php
namespace MOC\V\Core\AutoLoader\Vendor\Universal\NamespaceLoader;

/**
 * Class NamespaceSearch
 *
 * @package MOC\V\Core\AutoLoader\Vendor\Universal
 */
abstract class NamespaceSearch extends NamespaceMapping
{

    /**
     * @param array  $DirectoryList
     * @param string $ClassName
     * @param string $Namespace
     *
     * @return bool
     */
    protected function searchForInterfaceFallback( $DirectoryList, $ClassName, $Namespace )
    {

        return $this->searchForInterface( $DirectoryList,
            trim( preg_replace( '!^'.preg_quote( $Namespace ).'!is', '', $ClassName ), '\\' )
        );
    }

    /**
     * @param array  $DirectoryList
     * @param string $ClassName
     *
     * @return bool
     */
    protected function searchForInterface( $DirectoryList, $ClassName )
    {

        return $this->searchForClass( $DirectoryList,
            preg_replace( '!(.*?)I([^'.preg_quote( '\\' ).']*?)Interface$!is', '$1$2', $ClassName ) );
    }

    /**
     * @param array  $DirectoryList
     * @param string $ClassName
     *
     * @return bool
     */
    protected function searchForClass( $DirectoryList, $ClassName )
    {

        foreach ((array)$DirectoryList as $Directory) {
            $File = $Directory.DIRECTORY_SEPARATOR.str_replace( array( '_', '\\', '/' ), DIRECTORY_SEPARATOR,
                    $ClassName ).'.php';
            if (is_file( $File )) {
                /** @noinspection PhpIncludeInspection */
                require_once( $File );
                return true;
            }
        }
        return false;
    }

    /**
     * @param array  $DirectoryList
     * @param string $ClassName
     * @param string $Namespace
     *
     * @return bool
     */
    protected function searchForClassFallback( $DirectoryList, $ClassName, $Namespace )
    {

        return $this->searchForClass( $DirectoryList,
            trim( preg_replace( '!^'.preg_quote( $Namespace ).'!is', '', $ClassName ), '\\' )
        );
    }
}

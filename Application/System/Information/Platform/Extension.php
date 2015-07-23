<?php
namespace SPHERE\Application\System\Information\Platform;

/**
 * Class Extension
 *
 * @package SPHERE\Application\System\Information\Platform
 */
class Extension
{

    /** @var string $BaseDir */
    private $BaseDir = '';
    /** @var array $ExtensionList */
    private $ExtensionList = array();
    /** @var array $TargetList */
    private $TargetList = array();
    /** @var null|\RecursiveIteratorIterator $TargetTree */
    private $TargetTree = null;
    /** @var array $TargetTree */
    private $ResultList = array();
    /** @var array|string $SkipTree */
    private $SkipTree = array(
        '.git',
        '.idea',
        '/Library/Bootflat',
        '/Library/Bootstrap',
        '/Library/Bootstrap.Checkbox',
        '/Library/Bootstrap.DateTimePicker',
        '/Library/Bootstrap.FileInput',
        '/Library/Bootstrap.Glyphicons',
        '/Library/Bootstrap.Select',
        '/Library/jQuery',
        '/Library/jQuery.Ui',
        '/Library/jQuery.DataTables',
        '/Library/jQuery.DataTables.Plugins',
        '/Library/jQuery.iCheck',
        '/Library/jQuery.Selecter',
        '/Library/jQuery.Stepper',
        '/Library/MathJax',
        '/Library/Moment.Js',
        '/Library/Twitter.Typeahead',
        '/Library/Twitter.Typeahead.Bootstrap',
    );

    private $SizeTree = 0;
    private $SizeCurrent = 0;

    private $ClassFunctionList = array();
    private $NativeFunctionList = array();
    private $UserFunctionList = array();

    function __construct()
    {

        ob_start();
        echo "<pre>";
        echo "Load Extensions...\n";
        ob_flush();
        $this->ExtensionList = get_loaded_extensions();
        echo "Build Target-List...\n";
        ob_flush();
        $this->buildTargetList();
        echo "Build Skip-Tree...\n";
        ob_flush();
        $this->buildSkipTree( __DIR__.'/../../../../' );
        $this->scanTargetTree();
        echo "</pre>";
    }

    private function buildTargetList()
    {

        foreach ($this->ExtensionList as $Extension) {
            if (in_array( strtolower( $Extension ), array( 'core', 'standard' ) )) {
                continue;
            }

            $Reflection = new \ReflectionExtension( $Extension );

            $TargetList = array_merge(
                $Reflection->getClassNames(),
                array_keys( $Reflection->getConstants() ),
                array_keys( $Reflection->getFunctions() )
            );

            if (!is_array( $TargetList ) || count( $TargetList ) == 0) {
                continue;
            }

            $this->TargetList[$Extension] = $TargetList;
        }
    }

    private function buildSkipTree( $Directory )
    {

        $this->BaseDir = realpath( $Directory );

        array_walk( $this->SkipTree, function ( &$Directory ) {

            $Directory = preg_quote( realpath( $this->BaseDir.'/'.$Directory ), '!' );
            if (empty( $Directory )) {
                $Directory = false;
            }
        } );
        $this->SkipTree = array_filter( $this->SkipTree );

        if (empty( $this->SkipTree )) {
            $this->SkipTree = false;
        } else {
            $this->SkipTree = '!^('.implode( '|', $this->SkipTree ).').*?!is';
        }
    }

    private function scanTargetTree()
    {

        echo "Scan Target-Directory ".$this->BaseDir."...\n";
        ob_flush();
        $this->TargetTree = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $this->BaseDir ),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        $this->TargetTree = iterator_to_array( $this->TargetTree );

        echo "Size: ".( $this->SizeTree = count( $this->TargetTree ) )."\n";
        ob_flush();

        array_walk( $this->TargetTree, function ( $Object ) {

            $this->SizeCurrent++;

            if ($this->excludeDirectory( $Object )) {
                return;
            }

            if ($Object->isFile()) {
                $Extension = pathinfo( $Object, PATHINFO_EXTENSION );

                if ($Extension == 'php') {

                    echo "$this->SizeCurrent/".count( $this->TargetList )."/".count( $this->NativeFunctionList )." ";
                    ob_flush();

                    set_time_limit( 120 );

                    $Content = file_get_contents( $Object );
                    $this->analyzeFunctions( $Content, realpath( $Object ) );

                    array_walk( $this->TargetList, function ( $Target, $Extension, $Data ) {

                        /** @noinspection PhpUnusedParameterInspection */
                        array_walk( $Target, function ( $Search, $Index, $Data ) {

                            if (stripos( $Data[0], $Search ) !== false) {
                                //if (!in_array( $Data[1], array_keys( $this->ResultList ) )) {
                                $this->ResultList[$Data[1]][] = realpath( $Data[2] ).' : '.$Search;
                                $this->ResultList[$Data[1]] = array_unique( $this->ResultList[$Data[1]] );
                                //}
//                                unset( $this->TargetList[$Data[1]] );
                            }
                        }, array( $Data[0], $Extension, $Data[1] ) );
                    }, array( $Content, realpath( $Object ) ) );

//                    if (count( $this->TargetList ) == 0) {
//                        return;
//                    }
                }
            }
        } );

        $Declared = get_defined_functions();
        array_walk( $Declared['internal'], function ( &$Function ) {

            $Function = strtolower( $Function );
        } );
        array_walk( $Declared['user'], function ( &$Function ) {

            $Function = strtolower( $Function );
        } );
        array_walk( $this->UserFunctionList, function ( &$Function ) {

            $Function = strtolower( $Function );
        } );

        foreach ((array)$this->NativeFunctionList as $Function => $Location) {
            if (
                in_array( strtolower( $Function ), $Declared['internal'] )
                || in_array( strtolower( $Function ), $Declared['user'] )
                || in_array( strtolower( $Function ), $this->UserFunctionList )
            ) {
                $this->NativeFunctionList[$Function] = false;
            }
        }

        echo "\n\nUndefined Functions: \n";
        var_dump( array_filter( $this->NativeFunctionList ) );

        ksort( $this->ResultList );
        ksort( $this->TargetList );

        foreach (array_keys( $this->ResultList ) as $Module) {
            if (isset( $this->TargetList[$Module] )) {
                unset( $this->TargetList[$Module] );
            }
        }

        echo "\nUsed Modules (except Core,Standard): \n";

        var_dump( $this->ResultList );
        ob_flush();

        echo "\nRequired Modules (except Core,Standard): \n";

        var_dump( array_keys( $this->ResultList ) );
        ob_flush();

        echo "\nAdditional Modules: \n";

        var_dump( array_keys( $this->TargetList ) );
        ob_flush();
    }

    private function excludeDirectory( $Directory )
    {

        if (false === $this->SkipTree) {
            return false;
        }
        $Directory = realpath( $Directory );
        return preg_match( $this->SkipTree, $Directory ) ? true : false;
    }

    private function analyzeFunctions( $Content, $Object )
    {

        $TokenList = token_get_all( $Content );

        $Target = false;
        $Class = false;
        $Braces = 0;
        foreach ((array)$TokenList as $Index => $Token) {

            switch ($Token[0]) {
                case T_CLASS:
                    $Class = true;
                    break;
                case T_FUNCTION:
                    $Target = true;
                    break;
                case T_STRING:
                    if ($Class === true) {
                        $Class = $Token[1];
                    }
                    if ($Target) {
                        $Target = false;
                        $Function = $Token[1];
                        if (false !== $Class) {
                            $this->ClassFunctionList[$Function][] = $Class;
                        } else {
                            $this->UserFunctionList[] = $Function;
                        }
                    }
                    break;

                // Anonymous functions
                case '(':
                case ';':
                    $Target = false;
                    break;

                // Exclude Classes
                case '{':
                    if ($Class) {
                        $Braces++;
                    }
                    break;
                case '}':
                    if ($Class) {
                        $Braces--;
                        if ($Braces === 0) {
                            $Class = false;
                        }
                    }
                    break;
            }

            switch ($Token[0]) {
                case T_STRING:
                    if (
                        $TokenList[$Index + 1] == '('
                    ) {
                        if (isset( $TokenList[$Index - 1][1] )) {
                            if (
                                $TokenList[$Index - 1][1] != '::'
                                && $TokenList[$Index - 1][1] != '->'
                                && $TokenList[$Index - 1][1] != '\\'
                            ) {
                                if (isset( $TokenList[$Index - 2][1] )) {
                                    if ($TokenList[$Index - 2][1] != 'new') {
                                        if (!in_array( $Token[1], array_keys( $this->ClassFunctionList ) )) {
                                            $this->NativeFunctionList[$Token[1]][$Object] = $Token[2];
                                        }
                                    }
                                } else {
                                    if (!in_array( $Token[1], array_keys( $this->ClassFunctionList ) )) {
                                        $this->NativeFunctionList[$Token[1]][$Object] = $Token[2];
                                    }
                                }
                            }
                        } else {
                            if (!in_array( $Token[1], array_keys( $this->ClassFunctionList ) )) {
                                $this->NativeFunctionList[$Token[1]][$Object] = $Token[2];
                            }
                        }
                    }
                    break;
            }
        }
        //$this->ClassFunctionList = array_unique( $this->ClassFunctionList );
    }
}

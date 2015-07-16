<?php
namespace SPHERE\Common;

use SPHERE\System\Extension\Configuration;

/**
 * Class Style
 *
 * @package SPHERE\Common
 */
class Style extends Configuration
{

    /** @var array $SourceList */
    private static $SourceList = array();

    /**
     * Default
     */
    private function __construct()
    {

        $this->setSource( '/Library/Bootstrap/3.3.5/dist/css/bootstrap.min.css' );
//        $this->setSource( '/Library/Bootstrap.Glyphicons/1.9.0/glyphicons_halflings/web/html_css/css/glyphicons-halflings.css' );
//        $this->setSource( '/Library/Bootstrap.Glyphicons/1.9.0/glyphicons/web/html_css/css/glyphicons.css' );
//        $this->setSource( '/Library/Bootstrap.Glyphicons/1.9.0/glyphicons_filetypes/web/html_css/css/glyphicons-filetypes.css' );
//        $this->setSource( '/Library/Bootstrap.Glyphicons/1.9.0/glyphicons_social/web/html_css/css/glyphicons-social.css' );
//        $this->setSource( '/Library/Bootstrap.FileInput/4.1.6/css/fileinput.min.css' );
//        $this->setSource( '/Library/Bootstrap.Checkbox/0.3.3/awesome-bootstrap-checkbox.css' );
//        $this->setSource( '/Library/Bootstrap.Jasny/3.1.3/dist/css/jasny-bootstrap.min.css' );
////        $this->setSource( '/Library/Bootstrap.Select/1.6.4/dist/css/bootstrap-select.min.css' );
//        $this->setSource( '/Library/Bootflat/2.0.4/bootflat/css/bootflat.min.css' );
//        $this->setSource( '/Library/Twitter.Typeahead.Bootstrap/1.0.0/typeaheadjs.css' );
//        $this->setSource( '/Library/Bootstrap.DateTimePicker/3.1.3/build/css/bootstrap-datetimepicker.min.css' );
//        $this->setSource( '/Library/jQuery.DataTables.Plugins/1.0.1/integration/bootstrap/3/dataTables.bootstrap.css' );
//        $this->setSource( '/Library/jQuery.DataTables/1.10.4/extensions/Responsive/css/dataTables.responsive.css' );
//        $this->setSource( '/Sphere/Client/Style/Style.css' );
//        $this->setSource( '/Sphere/Client/Style/PhpInfo.css' );
    }

    /**
     * @param string $Location
     */
    public function setSource( $Location )
    {

        $PathBase = $this->getRequest()->getPathBase();
        if (!in_array( sha1( $Location ), self::$SourceList )) {
            self::$SourceList[sha1( $Location )] = $PathBase.$Location;
        }
    }

    /**
     * @return Style
     */
    public static function getManager()
    {

        return new Style();
    }

    /**
     * @return string
     */
    function __toString()
    {

        $StyleList = self::$SourceList;
        array_walk( $StyleList, function ( &$Location ) {

            $Location = '<link rel="stylesheet" href="'.$Location.'">';
        } );
        return implode( "\n", $StyleList );
    }
}

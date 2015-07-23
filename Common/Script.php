<?php
namespace SPHERE\Common;

use SPHERE\System\Extension\Extension;

/**
 * Class Script
 *
 * @package SPHERE\Common
 */
class Script extends Extension
{

    /** @var array $SourceList */
    private static $SourceList = array();
    /** @var array $ModuleList */
    private static $ModuleList = array();

    /**
     * Default
     */
    private function __construct()
    {

        /**
         * Source (Library)
         */

        $this->setSource(
            'jQuery', '/Library/jQuery/1.11.3/jquery-1.11.3.min.js',
            "'undefined' !== typeof jQuery"
        );
        $this->setSource(
            'jQuery.Ui', '/Library/jQuery.Ui/1.11.4/jquery-ui.min.js',
            "'undefined' !== typeof jQuery.ui"
        );
        $this->setSource(
            'Moment.js', '/Library/Moment.Js/2.8.4/min/moment-with-locales.min.js',
            "'undefined' !== typeof moment"
        );
        $this->setSource(
            'Bootstrap', '/Library/Bootstrap/3.3.5/dist/js/bootstrap.min.js',
            "'function' === typeof jQuery().emulateTransitionEnd"
        );
        $this->setSource(
            'jQuery.Selecter', '/Library/jQuery.Selecter/3.2.4/jquery.fs.selecter.min.js',
            "'undefined' !== typeof jQuery.fn.selecter"
        );
        $this->setSource(
            'jQuery.Stepper', '/Library/jQuery.Stepper/3.0.8/jquery.fs.stepper.min.js',
            "'undefined' !== typeof jQuery.fn.stepper"
        );
        $this->setSource(
            'jQuery.CheckBox', '/Library/jQuery.iCheck/1.0.2/icheck.min.js',
            "'undefined' !== typeof jQuery.fn.iCheck"
        );
        $this->setSource(
            'jQuery.DataTable',
            '/Library/jQuery.DataTables/1.10.7/media/js/jquery.dataTables.min.js',
            "'undefined' !== typeof jQuery.fn.DataTable"
        );
        $this->setSource(
            'jQuery.DataTable.Responsive',
            '/Library/jQuery.DataTables/1.10.7/extensions/Responsive/js/dataTables.responsive.min.js',
            "'undefined' !== typeof jQuery.fn.DataTable.Responsive"
        );
//        $this->setSource(
//            'jQuery.DataTable.Plugin.Sorting.Weekday',
//            '/Library/jQuery.DataTables.Plugins/1.0.1/sorting/weekday.js',
//            "'undefined' !== typeof jQuery.fn.dataTable.ext.type.order['weekday-pre']"
//        );
        $this->setSource(
            'Bootstrap.DataTable',
            '/Library/jQuery.DataTables.Plugins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.min.js',
            "'undefined' !== typeof jQuery.fn.DataTable.ext.renderer.pageButton.bootstrap"
        );
        $this->setSource(
            'Bootstrap.DatetimePicker',
            '/Library/Bootstrap.DateTimePicker/4.14.30/build/js/bootstrap-datetimepicker.min.js',
            "'undefined' !== typeof jQuery.fn.datetimepicker"
        );
        $this->setSource(
            'Bootstrap.FileInput', '/Library/Bootstrap.FileInput/4.1.6/js/fileinput.min.js',
            "'undefined' !== typeof jQuery.fn.fileinput"
        );
        $this->setSource(
            'Bootstrap.Select',
            '/Library/Bootstrap.Select/1.6.4/dist/js/bootstrap-select.min.js',
            "'undefined' !== typeof jQuery.fn.selectpicker"
        );
        $this->setSource(
            'Bootstrap.Jasny',
            '/Library/Bootstrap.Jasny/3.1.3/dist/js/jasny-bootstrap.min.js',
            "'undefined' !== typeof jQuery.fn.inputmask"
        );
        $this->setSource(
            'Twitter.Typeahead', '/Library/Twitter.Typeahead/0.11.1/dist/typeahead.bundle.min.js',
            "'undefined' !== typeof jQuery.fn.typeahead"
        );
        $this->setSource(
            'MathJax', '/Library/MathJax/2.5.0/MathJax.js?config=TeX-MML-AM_HTMLorMML-full',
            "'undefined' !== typeof MathJax"
        );

        /**
         * Module (jQuery plugin)
         */

        $this->setModule(
            'ModAlways', array( 'Bootstrap.Jasny', 'Bootstrap', 'jQuery.Ui', 'jQuery' )
        );
        $this->setModule(
            'ModTable',
            array(
//                'jQuery.DataTable.Plugin.Sorting.Weekday',
                'Bootstrap.DataTable',
                'jQuery.DataTable.Responsive',
                'jQuery.DataTable',
                'jQuery'
            )
        );
        $this->setModule(
            'ModPicker', array( 'Bootstrap.DatetimePicker', 'Moment.js', 'jQuery' )
        );
        $this->setModule(
            'ModSelecter', array( 'jQuery.Selecter', 'jQuery' )
        );
        $this->setModule(
            'ModSelect', array( 'Bootstrap.Select', 'Bootstrap', 'jQuery' )
        );
        $this->setModule(
            'ModCompleter', array( 'Twitter.Typeahead', 'Bootstrap', 'jQuery' )
        );
        $this->setModule(
            'ModUpload', array( 'Bootstrap.FileInput', 'Bootstrap', 'jQuery' )
        );
        $this->setModule(
            'ModCheckBox', array( 'jQuery.CheckBox', 'jQuery' )
        );
        $this->setModule(
            'ModMathJax', array( 'MathJax', 'jQuery' )
        );
        $this->setModule(
            'ModProgress', array( 'jQuery' )
        );
        $this->setModule(
            'ModSortable', array( 'jQuery.Ui', 'jQuery' )
        );
        $this->setModule(
            'ModForm', array( 'jQuery' )
        );
    }

    /**
     * @param string $Alias
     * @param string $Location
     * @param string $Test
     */
    public function setSource( $Alias, $Location, $Test )
    {

        $PathBase = $this->getRequest()->getPathBase();
        if (!in_array( $Alias, self::$SourceList )) {
            self::$SourceList[$Alias] = "Client.Source('".$Alias."','".$PathBase.$Location."',function(){return ".$Test.";});";
        }
    }

    /**
     * @param string $Alias
     * @param array  $Dependencies
     */
    public function setModule( $Alias, $Dependencies = array() )
    {

        if (!in_array( $Alias, self::$ModuleList )) {
            self::$ModuleList[$Alias] = "Client.Module('".$Alias."',".json_encode( $Dependencies ).");";
        }
    }

    /**
     * @return Script
     */
    public static function getManager()
    {

        return new Script();
    }

    /**
     * @return string
     */
    function __toString()
    {

        return '<script type="text/javascript">'
        .implode( "\n", self::$SourceList )."\n"
        .implode( "\n", self::$ModuleList )."\n"
        .'</script>';
    }

}

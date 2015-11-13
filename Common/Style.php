<?php
namespace SPHERE\Common;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\System\Extension\Extension;

/**
 * Class Style
 *
 * @package SPHERE\Common
 */
class Style extends Extension
{

    /** @var array $SourceList */
    private static $SourceList = array();

    /**
     * Default
     */
    private function __construct()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $SettingSurface = MyAccount::useService()->getSettingByAccount($tblAccount, 'Surface');
            if ($SettingSurface) {
                $SettingSurface = $SettingSurface->getValue();
            } else {
                $SettingSurface = 1;
            }
        } else {
            $SettingSurface = 1;
        }

        switch ($SettingSurface) {
            case 1:
                $this->setSource('/Common/Style/Bootstrap.css');
                break;
            case 2:
                $this->setSource('/Common/Style/theme.css');
                break;
            default:
                $this->setSource('/Common/Style/Bootstrap.css');
        }

        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.0/glyphicons_halflings/web/html_css/css/glyphicons-halflings.css');
        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.0/glyphicons/web/html_css/css/glyphicons.css');
        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.0/glyphicons_filetypes/web/html_css/css/glyphicons-filetypes.css');
        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.0/glyphicons_social/web/html_css/css/glyphicons-social.css');
        $this->setSource('/Library/Foundation.Icons/3.0/foundation-icons.css');

        $this->setSource('/Library/jQuery.Selecter/3.2.4/jquery.fs.selecter.min.css');
        $this->setSource('/Library/jQuery.Stepper/3.0.8/jquery.fs.stepper.css');
        $this->setSource('/Library/jQuery.iCheck/1.0.2/skins/all.css');
        $this->setSource('/Library/jQuery.Gridster/0.6.10/dist/jquery.gridster.min.css');
        $this->setSource('/Library/Bootstrap.Checkbox/0.3.3/awesome-bootstrap-checkbox.css');
//        $this->setSource( '/Library/jQuery.DataTables/1.10.7/media/css/jquery.dataTables.min.css' );
        $this->setSource('/Library/jQuery.DataTables/1.10.7/extensions/Responsive/css/dataTables.responsive.css');
        $this->setSource('/Library/jQuery.DataTables.Plugins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css');
        $this->setSource('/Library/Bootstrap.DateTimePicker/4.14.30/build/css/bootstrap-datetimepicker.min.css');
        $this->setSource('/Library/Bootstrap.FileInput/4.1.6/css/fileinput.min.css');
        $this->setSource('/Library/Bootstrap.Select/1.6.4/dist/css/bootstrap-select.min.css');
        $this->setSource('/Library/Twitter.Typeahead.Bootstrap/1.0.1/typeaheadjs.css');

        $this->setSource('/Library/jQuery.jCarousel/0.3.3/examples/responsive/jcarousel.responsive.css');
        $this->setSource('/Library/jQuery.FlowPlayer/6.0.3/skin/functional.css');
        $this->setSource('/Library/Highlight.js/8.8.0/styles/docco.css');

        switch ($SettingSurface) {
            case 1:
                $this->setSource('/Common/Style/Correction.css');
                break;
            case 2:
                $this->setSource('/Common/Style/theme.correction.css');
                break;
            default:
                $this->setSource('/Common/Style/Correction.css');
        }

        $this->setSource('/Common/Style/PhpInfo.css');
    }

    /**
     * @param string $Location
     */
    public function setSource($Location)
    {

        $PathBase = $this->getRequest()->getPathBase();
        if (!in_array(sha1($Location), self::$SourceList)) {
            self::$SourceList[sha1($Location)] = $PathBase.$Location;
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
    public function __toString()
    {

        $StyleList = self::$SourceList;
        array_walk($StyleList, function (&$Location) {

            $Location = '<link rel="stylesheet" href="'.$Location.'">';
        });
        return implode("\n", $StyleList);
    }
}

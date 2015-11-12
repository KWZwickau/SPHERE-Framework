<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage;

use Piwik\FrontController;
use Piwik\Piwik;

/**
 *
 */
class UserLanguage extends \Piwik\Plugin
{
    public static function footerUserCountry(&$out)
    {
        $out .= '<h2 piwik-enriched-headline>' . Piwik::translate('UserLanguage_BrowserLanguage') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('UserLanguage', 'getLanguage');
    }

    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Live.getAllVisitorDetails'              => 'extendVisitorDetails',
            'Request.getRenamedModuleAndAction' => 'renameUserSettingsModuleAndAction',
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['languageCode'] = $instance->getLanguageCode();
        $visitor['language']     = $instance->getLanguage();
    }

    public function postLoad()
    {
        Piwik::addAction('Template.footerUserCountry', array('Piwik\Plugins\UserLanguage\UserLanguage', 'footerUserCountry'));
    }

    public function renameUserSettingsModuleAndAction(&$module, &$action)
    {
        if ($module == 'UserSettings' && ($action == 'getLanguage' || $action == 'getLanguageCode')) {
            $module = 'UserLanguage';
        }
    }
}

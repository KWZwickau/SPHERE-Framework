<?php
namespace SPHERE\System\Extension\Repository;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Utils;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Redirect;

class phpSaml
{

    /**
     * PdfMerge constructor.
     */
    public function __construct()
    {

        require_once(__DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Library'
            . DIRECTORY_SEPARATOR . 'php-saml2'
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'autoload.php');

        Utils::setProxyVars(true);
    }

    /**
     * @throws Error
     */
    public function samlLogin()
    {


        $Auth = new Auth();
        $Auth->login();
    }

    /**
     * @return string
     */
    public function getMetaData()
    {

        try {
            #$auth = new OneLogin_Saml2_Auth($settingsInfo);
            #$settings = $auth->getSettings();
            // Now we only validate SP settings
            $settings = new Settings();
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);
            if (empty($errors)) {
                header('Content-Type: text/xml');
                return $metadata;
            } else {
                throw new Error(
                    'Invalid SP metadata: '.implode(', ', $errors),
                    Error::METADATA_SP_INVALID
                );
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @throws Error
     * @throws \OneLogin\Saml2\ValidationError
     */
    public function getAuthRequest()
    {

//        $auth = new Auth();
//        $response = new Response($auth->getSettings(), $_POST['SAMLResponse']);
//

//        exit;

        echo '<pre>';
        echo 'Session:<br/>';
        var_dump($_SESSION);
        echo '</pre>';

        $needsAuth = empty($_SESSION['samlUserdata']);

        if ($needsAuth) {
            // put SAML settings into an array to avoid placing files in the
            // composer vendor/ directories

            $auth = new Auth();

            if (!empty($_REQUEST['SAMLResponse']) && !empty($_REQUEST['RelayState'])) {
                $auth->processResponse(null);
                $errors = $auth->getErrors();
                if (empty($errors)) {
                    // user has authenticated successfully
                    $needsAuth = false;
                    $_SESSION['samlUserdata'] = $auth->getAttributes();
                }
            }

            // return to Login Mask
            if ($needsAuth) {
                return (!empty($errors)
                    ? new Panel('Error', $errors, Panel::PANEL_TYPE_DANGER)
                    : new Panel('Error', new DangerText('Missing Userdata'), Panel::PANEL_TYPE_DANGER))
                    .new Redirect('/', Redirect::TIMEOUT_ERROR);
            }
        }

        $_SESSION['isAuthenticated'] = $auth->isAuthenticated();

        echo '<pre>';
        echo '<br/>Session:<br/>';
        var_dump($_SESSION);
        echo '</pre>';

//        echo '<pre>';
//        echo 'Post:<br/>';
//        var_dump('Test');
//        var_dump($_POST);
////        echo '<br/>response object:<br/>';
////        var_dump($response);
//        echo '<br/>Session:<br/>';
//        var_dump($_SESSION);
////        echo '<br/>XML Dokument:<br/>';
////        var_dump($response->decryptedDocument);
////        // $response->getXMLDocument()->textContent
//        echo '</pre>';

        //ToDO Old version found http error
//        error_reporting(E_ALL);
////        exit;
//        $samlResponse = new Response($auth->getSettings(), $_POST['SAMLResponse']);
//
//        try {
//            if ($samlResponse->isValid()) {
//                echo 'You are: ' . $samlResponse->getNameId() . '<br>';
//                $attributes = $samlResponse->getAttributes();
//                if (!empty($attributes)) {
//                    echo 'You have the following attributes:<br>';
//                    echo '<table><thead><th>Name</th><th>Values</th></thead><tbody>';
//                    foreach ($attributes as $attributeName => $attributeValues) {
//                        echo '<tr><td>' . htmlentities($attributeName) . '</td><td><ul>';
//                        foreach ($attributeValues as $attributeValue) {
//                            echo '<li>' . htmlentities($attributeValue) . '</li>';
//                        }
//                        echo '</ul></td></tr>';
//                    }
//                    echo '</tbody></table><br><br>';
//                }
//            } else {
//                echo 'Invalid SAML response.';
//            }
//        } catch (Exception $e) {
//            echo 'Invalid SAML response: ' . $e->getMessage();
//        }

    }
}
<?php

namespace SPHERE\Application\RestApi\Public\Indiware;

use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Application\Transfer\Indiware\ErrorLog\JsonReplacementTest;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiIndiware implements IApiInterface
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
           __NAMESPACE__ . '/Log' , __CLASS__  . '::getLog',
        ));
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
           __NAMESPACE__ . '/TimeTable' , __CLASS__  . '::getTimeTable',
        ));
    }

    /**
     * @return JsonResponse
     */
    public static function getLog(): JsonResponse
    {

        exit;
        // http://192.168.92.128/RestApi/Public/Indiware/Log
        $Date = new \DateTime();
        $dateipfad = 'UnitTest/IndiwareLog/'.$Date->format('H_i_s').' Log '.$Date->format('d_m_Y').'.txt';
//        $dateipfad = $Date->format('d.m.Y_h:m:s').'_dataJSON.txt';

        $JsonResponse = new JsonResponse();
        //ToDO Später eingrenzen, jetzt: alle Anfragen sollen für den Test durchkommen.
//        // Überprüfe, ob es sich um eine POST-Anfrage handelt
//        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

            // Lese die Rohdaten aus dem Body des Requests
            $headers = getallheaders(); // Alle Header aus dem Request
            file_put_contents($dateipfad, 'Anfrage-Methode: '.$_SERVER['REQUEST_METHOD']. PHP_EOL, FILE_APPEND);
            file_put_contents($dateipfad, 'Headers: '. PHP_EOL.print_r($headers, true), FILE_APPEND);
            file_put_contents($dateipfad, 'Client-IP: '. print_r($_SERVER['REMOTE_ADDR'], true).PHP_EOL, FILE_APPEND);


            $post = $_POST;
            file_put_contents($dateipfad, 'POST: '. PHP_EOL.print_r($post, true), FILE_APPEND);
            $get = $_GET;
            file_put_contents($dateipfad, 'GET: '. PHP_EOL.print_r($get, true), FILE_APPEND);

            $jsonDaten = file_get_contents('php://input');

//            // Überprüfen, ob die empfangenen Daten ein gültiges JSON sind
//            if (json_decode($jsonDaten, true) === null && json_last_error() !== JSON_ERROR_NONE) {
//                http_response_code(400); // Bad Request
//                echo "Ungültiges JSON";
//                exit;
//            }
            // Schreibe die JSON-Daten in die Textdatei
            if (file_put_contents($dateipfad, 'JSON: '. PHP_EOL.$jsonDaten, FILE_APPEND)) {
                http_response_code(200); // OK
//                echo "JSON erfolgreich gespeichert.";
                return $JsonResponse->setData(array("success" => true, "message" => "JSON saved in file."));
            } else {
                http_response_code(500); // Server-Fehler
//                echo "Fehler beim Speichern der Datei.";
                return $JsonResponse->setData(array("success" => false, "message" => "can't save JSON in file."));
            }
//        } else {
//            http_response_code(405); // Methode nicht erlaubt
////            echo "Nur PUT-Anfragen sind erlaubt.";
//            return $JsonResponse->setData(array("success" => false, "message" => "only PUT-request are allowed."));
//        }
    }

    public static function getTimeTable(string $Savety = ''): JsonResponse
    {

        $JsonResponse = new JsonResponse();
        $StartControlNumber = strpos($Savety, '-');
        $NumberControl = substr($Savety, $StartControlNumber+1);
        $Mandant = substr($Savety, 0, $StartControlNumber);
        $Code = '';
        if((Consumer::useService()->getConsumerByAcronym($Mandant))){
            if(($tblAccount = Account::useService()->getAccountByUsername($Mandant.'-Indiware'))){
                if(($tblSetting = Account::useService()->getSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE))){
                    $Code = $tblSetting->getValue();
                }
                if(($NumberControl) != $Code){
                    // Code stimmt nicht überein
                    return $JsonResponse->setData(array("Identifier" => "error", "message" => "Indiware_ErrorCode_1"));
                }
            } else {
                // Indiware Account fehlt
                return $JsonResponse->setData(array("Identifier" => "error", "message" => "Indiware_ErrorCode_2"));
            }
        } else {
            // Mandant fehlt
            return $JsonResponse->setData(array("Identifier" => "error", "message" => "Indiware_ErrorCode_3"));
        }

        // Login Service-Account
        Account::useService()->createSession($tblAccount, session_id());
        // entfernen alter Log Daten
        Timetable::useService()->destroyTimetableReplacementLogBulk();

        // JSON content laden
        $json = file_get_contents('php://input');
        // Test mit Lokalen Daten
//        $json = (new JsonReplacementTest())->getJson($Mandant);
//        Account::useService()->destroySession(null, session_id());
//        return $JsonResponse->setData(array("Identifier" => "error", "message" => getallheaders(), "JSON" => $json)); // , 'JSON' => $json

        if(($message = Replacement::useService()->importJsonReplacement($json))){
            // Logout Service-Account
            Account::useService()->destroySession(null, session_id());
            return $JsonResponse->setData(array("Identifier" => "error", "message" => $message)); // , 'JSON' => $json
//            return $JsonResponse->setData(array("Identifier" => "success 2", "message" => "JSON 2 saved in file.")); // , 'JSON' => $json
        }
        // Logout Service-Account
        Account::useService()->destroySession(null, session_id());

        return $JsonResponse->setData(array("Identifier" => "success", "message" => "data saved")); // , 'JSON' => $json
    }
}
<?php

namespace SPHERE\Application\RestApi\Public\Indiware;

use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiIndiware implements IApiInterface
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
           __NAMESPACE__ . '/Log' , __CLASS__  . '::getLog',
        ));
    }

    /**
     * @return JsonResponse
     */
    public static function getLog(): JsonResponse
    {

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
}
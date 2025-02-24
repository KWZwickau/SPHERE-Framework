<?php
namespace SPHERE\Application\Transfer\Indiware\ErrorLog;

use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacementLog;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Log
 *
 * @package SPHERE\Application\Transfer\Indiware\ErrorLog
 */
class ErrorLog extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Api Logfile'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendLogOverview'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Json', __CLASS__.'::frontendDoJson'
        ));
    }

    /**
     */
    public static function useService()
    {

    }

    /**
     */
    public static function useFrontend()
    {

    }

    public function frontendDoJson()
    {

        $Stage = new Stage('Json einspielen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

//        $Json = '{
//  "Gesamtexport": {
//    "Informationen": {
//      "Version": "1.1"
//    },
//    "Vertretungsplan": {
//      "Vertretungsplan": [
//        {
//          "Kopf": {
//            "Datei": "Vertretungsplan Schüler2025-01-13.json",
//            "Titel": "Montag, 13. Januar 2025 (B-Woche) ",
//            "Schulname": "Evangelische Oberschule Schneeberg",
//            "Datum": "13.01.2025",
//            "Erstellt": "13.01.2025, 12:31",
//            "Kopfinfo": {
//              "AbwesendeLehrer": [
//                {
//                  "Kurz": "AngSt",
//                  "Grund": "So"
//                },
//                {
//                  "Kurz": "AV",
//                  "Grund": "Kr"
//                },
//                {
//                  "Kurz": "Laube",
//                  "Grund": "Proje"
//                }
//              ],
//              "LehrerMitAenderung": [
//                {
//                  "Kurz": "GV"
//                },
//                {
//                  "Kurz": "BR"
//                },
//                {
//                  "Kurz": "BH"
//                },
//                {
//                  "Kurz": "BC"
//                },
//                {
//                  "Kurz": "BU"
//                },
//                {
//                  "Kurz": "EJ"
//                },
//                {
//                  "Kurz": "CT"
//                }
//              ],
//              "KlassenMitAenderung": [
//                {
//                  "Kurz": "5OS"
//                },
//                {
//                  "Kurz": "6GY"
//                },
//                {
//                  "Kurz": "6OS"
//                },
//                {
//                  "Kurz": "7GY"
//                },
//                {
//                  "Kurz": "7OS"
//                },
//                {
//                  "Kurz": "9GY"
//                },
//                {
//                  "Kurz": "10GY"
//                }
//              ]
//            }
//          },
//          "Aktionen": [
//            {
//              "Ak_Id": 3304,
//              "Ak_UntNr": 46,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 2,
//              "Ak_StundenAnz": 2,
//              "Ak_Fach": "SP",
//              "Ak_VFach": "SP",
//              "Klassen": [
//                "5OS"
//              ],
//              "VKlassen": [
//                "5OS"
//              ],
//              "Lehrer": [
//                "AV"
//              ],
//              "VLehrer": [
//                "BU"
//              ],
//              "Raeume": [
//                "TH 1"
//              ],
//              "VRaeume": [
//                "TH 1"
//              ]
//            },
//            {
//              "Ak_Id": 2542,
//              "Ak_UntNr": 0,
//              "Ak_Art": "Neu",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_DatumNach": "13.01.2025",
//              "Ak_StundeVon": 6,
//              "Ak_StundeNach": 6,
//              "Ak_Fach": "",
//              "Ak_VFach": "RELIs",
//              "Klassen": [
//                "6GY"
//              ],
//              "VKlassen": [
//                "6GY"
//              ],
//              "Lehrer": [
//                "BR"
//              ],
//              "VLehrer": [
//                "BR"
//              ],
//              "Raeume": [],
//              "VRaeume": [
//                "312"
//              ]
//            },
//            {
//              "Ak_Id": 2541,
//              "Ak_UntNr": 0,
//              "Ak_Art": "Neu",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_DatumNach": "13.01.2025",
//              "Ak_StundeVon": 7,
//              "Ak_StundeNach": 7,
//              "Ak_Fach": "",
//              "Ak_VFach": "GEO",
//              "Klassen": [
//                "6OS"
//              ],
//              "VKlassen": [
//                "6OS"
//              ],
//              "Lehrer": [
//                "GV"
//              ],
//              "VLehrer": [
//                "GV"
//              ],
//              "Raeume": [],
//              "VRaeume": [
//                "313"
//              ]
//            },
//            {
//              "Ak_Id": 2538,
//              "Ak_UntNr": 81,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 5,
//              "Ak_Fach": "DE",
//              "Ak_VFach": "TC",
//              "Klassen": [
//                "7GY"
//              ],
//              "VKlassen": [
//                "7GY"
//              ],
//              "Lehrer": [
//                "CT"
//              ],
//              "VLehrer": [
//                "CT"
//              ],
//              "Raeume": [
//                "210"
//              ]
//            },
//            {
//              "Ak_Id": 2540,
//              "Ak_UntNr": 0,
//              "Ak_Art": "Neu",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_DatumNach": "13.01.2025",
//              "Ak_StundeVon": 6,
//              "Ak_StundeNach": 6,
//              "Ak_Fach": "",
//              "Ak_VFach": "TC",
//              "Klassen": [
//                "7GY"
//              ],
//              "VKlassen": [
//                "7GY"
//              ],
//              "Lehrer": [
//                "CT"
//              ],
//              "VLehrer": [
//                "CT"
//              ],
//              "Raeume": []
//            },
//            {
//              "Ak_Id": 2539,
//              "Ak_UntNr": 85,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 7,
//              "Ak_Fach": "GEO",
//              "Ak_VFach": "TC",
//              "Klassen": [
//                "7GY"
//              ],
//              "VKlassen": [
//                "7GY"
//              ],
//              "Lehrer": [
//                "CT"
//              ],
//              "VLehrer": [
//                "CT"
//              ],
//              "Raeume": [
//                "210"
//              ]
//            },
//            {
//              "Ak_Id": 2535,
//              "Ak_UntNr": 100,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 5,
//              "Ak_Fach": "RELI",
//              "Ak_VFach": "TC",
//              "Klassen": [
//                "7OS"
//              ],
//              "VKlassen": [
//                "7OS"
//              ],
//              "Lehrer": [
//                "BH"
//              ],
//              "VLehrer": [
//                "BH"
//              ],
//              "Raeume": [
//                "213"
//              ]
//            },
//            {
//              "Ak_Id": 2536,
//              "Ak_UntNr": 98,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 6,
//              "Ak_Fach": "DE",
//              "Ak_VFach": "TC",
//              "Klassen": [
//                "7OS"
//              ],
//              "VKlassen": [
//                "7OS"
//              ],
//              "Lehrer": [
//                "BC"
//              ],
//              "VLehrer": [
//                "BC"
//              ],
//              "Raeume": [
//                "213"
//              ]
//            },
//            {
//              "Ak_Id": 2537,
//              "Ak_UntNr": 0,
//              "Ak_Art": "Neu",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_DatumNach": "13.01.2025",
//              "Ak_StundeVon": 7,
//              "Ak_StundeNach": 7,
//              "Ak_Fach": "",
//              "Ak_VFach": "TC",
//              "Klassen": [
//                "7OS"
//              ],
//              "VKlassen": [
//                "7OS"
//              ],
//              "Lehrer": [
//                "BR"
//              ],
//              "VLehrer": [
//                "BR"
//              ],
//              "Raeume": []
//            },
//            {
//              "Ak_Id": 3308,
//              "Ak_UntNr": 10,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 7,
//              "Ak_Fach": "BIO",
//              "Ak_VFach": "BIO",
//              "Klassen": [
//                "9GY"
//              ],
//              "VKlassen": [
//                "9GY"
//              ],
//              "Lehrer": [
//                "AV"
//              ],
//              "VLehrer": [
//                "EJ"
//              ],
//              "Raeume": [
//                "Bio"
//              ],
//              "VRaeume": [
//                "Bio"
//              ]
//            },
//            {
//              "Ak_Id": 3306,
//              "Ak_UntNr": 182,
//              "Ak_Art": "Änd.",
//              "Ak_DatumVon": "13.01.2025",
//              "Ak_StundeVon": 5,
//              "Ak_StundenAnz": 2,
//              "Ak_Fach": "BIO",
//              "Ak_VFach": "BIO",
//              "Klassen": [
//                "10GY"
//              ],
//              "VKlassen": [
//                "10GY"
//              ],
//              "Lehrer": [
//                "AV"
//              ],
//              "VLehrer": [
//                "EJ"
//              ],
//              "Raeume": [
//                "Bio"
//              ],
//              "VRaeume": [
//                "Bio"
//              ]
//            }
//          ]
//        }
//      ]
//    }
//  }
//}';
        $Json = '{
  "Vertretungsplan": [
    {
      "Kopf": {
        "Datei": "Vertretungsplan Schüler2025-01-27.json",
        "Titel": "Montag, 27. Januar 2025 ",
        "Schulname": "Evangelische Grundschule Radebeul",
        "Datum": "27.01.2025",
        "Erstellt": "13.02.2025, 18:18",
        "Kopfinfo": {
          "AbwesendeLehrer": [
            {
              "Kurz": "Pfei",
              "Grund": "Kr"
            },
            {
              "Kurz": "Scho",
              "Grund": "So"
            },
            {
              "Kurz": "Wal",
              "Grund": "So"
            },
            {
              "Kurz": "Zei",
              "Grund": "Kr"
            },
            {
              "Kurz": "Zeig",
              "Grund": "So"
            }
          ],
          "LehrerMitAenderung": [
            {
              "Kurz": "Koe"
            },
            {
              "Kurz": "Lis"
            },
            {
              "Kurz": "Ma"
            },
            {
              "Kurz": "Pol"
            },
            {
              "Kurz": "Schu"
            },
            {
              "Kurz": "Schwa"
            }
          ],
          "KlassenMitAenderung": [
            {
              "Kurz": "1Frue"
            },
            {
              "Kurz": "1Herb"
            },
            {
              "Kurz": "1Somm"
            },
            {
              "Kurz": "1Wint"
            },
            {
              "Kurz": "2Frue"
            },
            {
              "Kurz": "2Herb"
            },
            {
              "Kurz": "2Somm"
            },
            {
              "Kurz": "2Wint"
            },
            {
              "Kurz": "3Frue"
            },
            {
              "Kurz": "3Herb"
            },
            {
              "Kurz": "3Somm"
            },
            {
              "Kurz": "3Wint"
            },
            {
              "Kurz": "4Frue"
            },
            {
              "Kurz": "4Herb"
            },
            {
              "Kurz": "4Somm"
            },
            {
              "Kurz": "4Wint"
            }
          ]
        }
      },
      "Aktionen": [
        {
          "Ak_Id": 460,
          "Ak_UntNr": 1,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 5,
          "Ak_Fach": "EN",
          "Ak_VFach": "EN",
          "Klassen": [
            "1Frue",
            "1Herb",
            "1Somm",
            "1Wint"
          ],
          "VKlassen": [
            "1Frue",
            "1Herb",
            "1Somm",
            "1Wint"
          ],
          "Lehrer": [
            "Pfei"
          ],
          "VLehrer": [
            "Schu"
          ],
          "Raeume": [
            ".Som"
          ],
          "VRaeume": [
            ".Som"
          ]
        },
        {
          "Ak_Id": 514,
          "Ak_UntNr": 160,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 6,
          "Ak_Fach": "WP",
          "Ak_VFach": "WP",
          "Klassen": [
            "1Frue",
            "1Somm"
          ],
          "VKlassen": [
            "1Frue",
            "1Somm"
          ],
          "Lehrer": [
            "Lis"
          ],
          "VLehrer": [
            "Pol"
          ],
          "Raeume": [
            ".Früh"
          ],
          "VRaeume": [
            ".Früh"
          ],
          "InfoL": "+1He/Wi"
        },
        {
          "Ak_Id": 495,
          "Ak_UntNr": 169,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 1,
          "Ak_StundenAnz": 2,
          "Ak_Fach": "FAwp",
          "Ak_VFach": "FAwp",
          "Klassen": [
            "1Somm",
            "2Somm",
            "3Somm",
            "4Somm"
          ],
          "VKlassen": [
            "1Somm",
            "2Somm",
            "3Somm",
            "4Somm"
          ],
          "Lehrer": [
            "Wal"
          ],
          "VLehrer": [
            "Koe"
          ],
          "Raeume": [
            ".Som"
          ],
          "VRaeume": [
            ".Som"
          ]
        },
        {
          "Ak_Id": 497,
          "Ak_UntNr": 176,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 1,
          "Ak_StundenAnz": 2,
          "Ak_Fach": "FAwp",
          "Ak_VFach": "FAwp",
          "Klassen": [
            "1Wint",
            "2Wint",
            "3Wint",
            "4Wint"
          ],
          "VKlassen": [
            "1Wint",
            "2Wint",
            "3Wint",
            "4Wint"
          ],
          "Lehrer": [
            "Zei",
            "Schwa"
          ],
          "VLehrer": [
            "Schwa"
          ],
          "Raeume": [
            ".Win"
          ],
          "VRaeume": [
            ".Win"
          ]
        },
        {
          "Ak_Id": 513,
          "Ak_UntNr": 186,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 5,
          "Ak_Fach": "WP",
          "Ak_VFach": "WP",
          "Klassen": [
            "2Frue",
            "2Somm"
          ],
          "VKlassen": [
            "2Frue",
            "2Somm"
          ],
          "Lehrer": [
            "Lis"
          ],
          "VLehrer": [
            "Pol"
          ],
          "Raeume": [
            ".Früh"
          ],
          "VRaeume": [
            ".Früh"
          ],
          "InfoL": "+ 2He/Wi"
        },
        {
          "Ak_Id": 461,
          "Ak_UntNr": 18,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 6,
          "Ak_Fach": "EN",
          "Ak_VFach": "EN",
          "Klassen": [
            "2Frue",
            "2Herb",
            "2Somm",
            "2Wint"
          ],
          "VKlassen": [
            "2Frue",
            "2Herb",
            "2Somm",
            "2Wint"
          ],
          "Lehrer": [
            "Pfei"
          ],
          "VLehrer": [
            "Schu"
          ],
          "Raeume": [
            ".Som"
          ],
          "VRaeume": [
            ".Som"
          ]
        },
        {
          "Ak_Id": 499,
          "Ak_UntNr": 146,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 3,
          "Ak_Fach": "WP",
          "Ak_VFach": "WP",
          "Klassen": [
            "3Frue",
            "3Herb",
            "3Somm",
            "3Wint"
          ],
          "VKlassen": [
            "3Frue",
            "3Herb",
            "3Somm",
            "3Wint"
          ],
          "Lehrer": [
            "Zei"
          ],
          "VLehrer": [
            "Ma"
          ],
          "Raeume": [
            ".Win"
          ],
          "VRaeume": [
            ".Win"
          ]
        },
        {
          "Ak_Id": 459,
          "Ak_UntNr": 23,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 4,
          "Ak_Fach": "BeKo",
          "Ak_VFach": "BeKo",
          "Klassen": [
            "3Frue",
            "3Herb",
            "3Somm",
            "3Wint"
          ],
          "VKlassen": [
            "3Frue",
            "3Herb",
            "3Somm",
            "3Wint"
          ],
          "Lehrer": [
            "Pfei"
          ],
          "VLehrer": [
            "Schu"
          ],
          "Raeume": [
            "Oben"
          ],
          "VRaeume": [
            "Oben"
          ]
        },
        {
          "Ak_Id": 511,
          "Ak_UntNr": 25,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 5,
          "Ak_StundenAnz": 2,
          "Ak_Fach": "MU",
          "Ak_VFach": "WP",
          "Klassen": [
            "3Frue",
            "4Frue",
            "3Somm",
            "4Somm"
          ],
          "VKlassen": [
            "3Frue",
            "3Somm",
            "4Frue",
            "4Somm"
          ],
          "Lehrer": [
            "Zei"
          ],
          "VLehrer": [
            "Lis"
          ],
          "Raeume": [
            "Mus"
          ],
          "VRaeume": [
            "Mus"
          ]
        },
        {
          "Ak_Id": 458,
          "Ak_UntNr": 138,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 3,
          "Ak_Fach": "BeKo",
          "Ak_VFach": "BeKo",
          "Klassen": [
            "4Frue",
            "4Herb",
            "4Somm",
            "4Wint"
          ],
          "VKlassen": [
            "4Frue",
            "4Herb",
            "4Somm",
            "4Wint"
          ],
          "Lehrer": [
            "Pfei"
          ],
          "VLehrer": [
            "Schu"
          ],
          "Raeume": [
            "Oben"
          ],
          "VRaeume": [
            "Oben"
          ]
        },
        {
          "Ak_Id": 500,
          "Ak_UntNr": 147,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "27.01.2025",
          "Ak_StundeVon": 4,
          "Ak_Fach": "WP",
          "Ak_VFach": "WP",
          "Klassen": [
            "4Frue",
            "4Herb",
            "4Somm",
            "4Wint"
          ],
          "VKlassen": [
            "4Frue",
            "4Herb",
            "4Somm",
            "4Wint"
          ],
          "Lehrer": [
            "Zei"
          ],
          "VLehrer": [
            "Ma"
          ],
          "Raeume": [
            ".Win"
          ],
          "VRaeume": [
            ".Win"
          ]
        }
      ]
    }
  ]
}
';
        Replacement::useService()->importJsonReplacement($Json);
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendLogOverview(): Stage
    {
        $Stage = new Stage('Indiware', 'API-Errorlog');
        $Stage->addButton(new Standard('Json "Anfragen"', __NAMESPACE__.'/Json', new Download()));
        $ReplacementLogAll = Timetable::useService()->getTimeTableReplacementLogAll();
        $TableContent = array();
        if($ReplacementLogAll){
            array_walk($ReplacementLogAll, function (&$ReplacementLog) use (&$TableContent) {
                /** @var $ReplacementLog TblTimetableReplacementLog */
                $item = array();
                $item['Date'] = $ReplacementLog->getDate();
                $item['Hour'] = $ReplacementLog->getHour();
                $item['Course'] = $ReplacementLog->getCourse();
                $item['PersonAcronym'] = $ReplacementLog->getPersonAcronym();
                $item['Room'] = $ReplacementLog->getRoom();
                $item['IsCanceled'] = ($ReplacementLog->getIsCanceled() ? "Ausfall" : "" );
                $ErrorList = explode(';', $ReplacementLog->getError());
                $item['Error'] = implode("<br/>", $ErrorList);
                $TableContent[] = $item;
            });
        }

        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, new Title('Auflistung', 'welche Werte konnten nicht importiert werden'), array(
                        'Date' => 'Datum',
                        'Hour' => 'Stunde',
                        'Course' => 'Klasse',
                        'PersonAcronym' => 'Lehrer Kürzel',
                        'Room' => 'Raum',
                        'IsCanceled' => 'Ausfall',
                        'Error' => 'Error',
                    ),
                    array(
                        'order' => array(
                            array(0, 'asc'),
                            array(1, 'asc'),
                            array(2, 'asc')
                        ),
                        'columnDefs' => array(
//                            array('type' => 'de_date', 'targets' => array(0, 1)),
//                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                )
            ))))
        );

        return $Stage;
    }
}
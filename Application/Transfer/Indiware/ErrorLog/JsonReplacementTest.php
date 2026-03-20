<?php

namespace SPHERE\Application\Transfer\Indiware\ErrorLog;

class JsonReplacementTest
{

    public function getJson($Mandant = 'ESS')
    {
        // ESS && EVSR
        if($Mandant == 'ESS'){
            return $this->ESSJson;
        } elseif($Mandant == 'EVSR') {
            return $this->EVSRJson;
        } elseif($Mandant == 'EVSRManual') {
            return $this->EVSRJsonManual;
        } elseif($Mandant == 'KG') {
            return $this->KGJson;
        }
        return '';
    }

    private $ESSJson = '{
  "Gesamtexport": {
    "Informationen": {
      "Version": "1.1"
    },
    "Vertretungsplan": {
      "Vertretungsplan": [
        {
          "Kopf": {
            "Datei": "Vertretungsplan Schüler2025-01-13.json",
            "Titel": "Montag, 13. Januar 2025 (B-Woche) ",
            "Schulname": "Evangelische Oberschule Schneeberg",
            "Datum": "13.01.2025",
            "Erstellt": "13.01.2025, 12:31",
            "Kopfinfo": {
              "AbwesendeLehrer": [
                {
                  "Kurz": "AngSt",
                  "Grund": "So"
                },
                {
                  "Kurz": "AV",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "Laube",
                  "Grund": "Proje"
                }
              ],
              "LehrerMitAenderung": [
                {
                  "Kurz": "GV"
                },
                {
                  "Kurz": "BR"
                },
                {
                  "Kurz": "BH"
                },
                {
                  "Kurz": "BC"
                },
                {
                  "Kurz": "BU"
                },
                {
                  "Kurz": "EJ"
                },
                {
                  "Kurz": "CT"
                }
              ],
              "KlassenMitAenderung": [
                {
                  "Kurz": "5OS"
                },
                {
                  "Kurz": "6GY"
                },
                {
                  "Kurz": "6OS"
                },
                {
                  "Kurz": "7GY"
                },
                {
                  "Kurz": "7OS"
                },
                {
                  "Kurz": "9GY"
                },
                {
                  "Kurz": "10GY"
                }
              ]
            }
          },
          "Aktionen": [
            {
              "Ak_Id": 3304,
              "Ak_UntNr": 46,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 2,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "SP",
              "Ak_VFach": "SP",
              "Klassen": [
                "5OS"
              ],
              "VKlassen": [
                "5OS"
              ],
              "Lehrer": [
                "AV"
              ],
              "VLehrer": [
                "BU"
              ],
              "Raeume": [
                "TH 1"
              ],
              "VRaeume": [
                "TH 1"
              ]
            },
            {
              "Ak_Id": 2542,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 6,
              "Ak_Fach": "",
              "Ak_VFach": "RELIs",
              "Klassen": [
                "6GY"
              ],
              "VKlassen": [
                "6GY"
              ],
              "Lehrer": [
                "BR"
              ],
              "VLehrer": [
                "BR"
              ],
              "Raeume": [],
              "VRaeume": [
                "312"
              ]
            },
            {
              "Ak_Id": 2541,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 7,
              "Ak_Fach": "",
              "Ak_VFach": "GEO",
              "Klassen": [
                "6OS"
              ],
              "VKlassen": [
                "6OS"
              ],
              "Lehrer": [
                "GV"
              ],
              "VLehrer": [
                "GV"
              ],
              "Raeume": [],
              "VRaeume": [
                "313"
              ]
            },
            {
              "Ak_Id": 2538,
              "Ak_UntNr": 81,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 5,
              "Ak_Fach": "DE",
              "Ak_VFach": "TC",
              "Klassen": [
                "7GY"
              ],
              "VKlassen": [
                "7GY"
              ],
              "Lehrer": [
                "CT"
              ],
              "VLehrer": [
                "CT"
              ],
              "Raeume": [
                "210"
              ]
            },
            {
              "Ak_Id": 2540,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 6,
              "Ak_Fach": "",
              "Ak_VFach": "TC",
              "Klassen": [
                "7GY"
              ],
              "VKlassen": [
                "7GY"
              ],
              "Lehrer": [
                "CT"
              ],
              "VLehrer": [
                "CT"
              ],
              "Raeume": []
            },
            {
              "Ak_Id": 2539,
              "Ak_UntNr": 85,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_Fach": "GEO",
              "Ak_VFach": "TC",
              "Klassen": [
                "7GY"
              ],
              "VKlassen": [
                "7GY"
              ],
              "Lehrer": [
                "CT"
              ],
              "VLehrer": [
                "CT"
              ],
              "Raeume": [
                "210"
              ]
            },
            {
              "Ak_Id": 2535,
              "Ak_UntNr": 100,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 5,
              "Ak_Fach": "RELI",
              "Ak_VFach": "TC",
              "Klassen": [
                "7OS"
              ],
              "VKlassen": [
                "7OS"
              ],
              "Lehrer": [
                "BH"
              ],
              "VLehrer": [
                "BH"
              ],
              "Raeume": [
                "213"
              ]
            },
            {
              "Ak_Id": 2536,
              "Ak_UntNr": 98,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 6,
              "Ak_Fach": "DE",
              "Ak_VFach": "TC",
              "Klassen": [
                "7OS"
              ],
              "VKlassen": [
                "7OS"
              ],
              "Lehrer": [
                "BC"
              ],
              "VLehrer": [
                "BC"
              ],
              "Raeume": [
                "213"
              ]
            },
            {
              "Ak_Id": 2537,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "13.01.2025",
              "Ak_DatumNach": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 7,
              "Ak_Fach": "",
              "Ak_VFach": "TC",
              "Klassen": [
                "7OS"
              ],
              "VKlassen": [
                "7OS"
              ],
              "Lehrer": [
                "BR"
              ],
              "VLehrer": [
                "BR"
              ],
              "Raeume": []
            },
            {
              "Ak_Id": 3308,
              "Ak_UntNr": 10,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 7,
              "Ak_Fach": "BIO",
              "Ak_VFach": "BIO",
              "Klassen": [
                "9GY"
              ],
              "VKlassen": [
                "9GY"
              ],
              "Lehrer": [
                "AV"
              ],
              "VLehrer": [
                "EJ"
              ],
              "Raeume": [
                "Bio"
              ],
              "VRaeume": [
                "Bio"
              ]
            },
            {
              "Ak_Id": 3306,
              "Ak_UntNr": 182,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "13.01.2025",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "BIO",
              "Ak_VFach": "BIO",
              "Klassen": [
                "10GY"
              ],
              "VKlassen": [
                "10GY"
              ],
              "Lehrer": [
                "AV"
              ],
              "VLehrer": [
                "EJ"
              ],
              "Raeume": [
                "Bio"
              ],
              "VRaeume": [
                "Bio"
              ]
            }
          ]
        }
      ]
    }
  }
}';

    private $EVSRJson = '{
  "Gesamtexport": {
    "Informationen": {
      "Version": "1.1"
    },
    "Vertretungsplan": {
      "Vertretungsplan": [
        {
          "Kopf": {
            "Datei": "Vertretungsplan Schüler2025-03-06.json",
            "Titel": "Donnerstag, 6. März 2025 ",
            "Schulname": "Evangelische Grundschule Radebeul",
            "Datum": "06.03.2025",
            "Erstellt": "06.03.2025, 15:46",
            "Kopfinfo": {
              "AbwesendeLehrer": [
                {
                  "Kurz": "Lis",
                  "Grund": "Kr"
                }
              ],
              "LehrerMitAenderung": [
                {
                  "Kurz": "Koe"
                },
                {
                  "Kurz": "Li"
                },
                {
                  "Kurz": "Schwa"
                },
                {
                  "Kurz": "Tig"
                }
              ],
              "KlassenMitAenderung": [
                {
                  "Kurz": "1Frue"
                },
                {
                  "Kurz": "2Frue"
                },
                {
                  "Kurz": "3Frue"
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
              "Ak_Id": 556,
              "Ak_UntNr": 159,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.03.2025",
              "Ak_StundeVon": 1,
              "Ak_Fach": "FAwp",
              "Ak_VFach": "FAwp",
              "Klassen": [
                "1Frue",
                "2Frue",
                "3Frue"
              ],
              "VKlassen": [
                "1Frue",
                "2Frue",
                "3Frue"
              ],
              "Lehrer": [
                "Lis"
              ],
              "VLehrer": [
                "Koe"
              ],
              "Raeume": [
                ".Früh"
              ],
              "VRaeume": [
                ".Früh"
              ]
            },
            {
              "Ak_Id": 557,
              "Ak_UntNr": 157,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.03.2025",
              "Ak_StundeVon": 2,
              "Ak_Fach": "FAwp",
              "Ak_VFach": "FAwp",
              "Klassen": [
                "2Frue",
                "3Frue"
              ],
              "VKlassen": [
                "2Frue",
                "3Frue"
              ],
              "Lehrer": [
                "Lis"
              ],
              "VLehrer": [
                "Li"
              ],
              "Raeume": [
                ".Früh"
              ],
              "VRaeume": [
                ".Früh"
              ]
            },
            {
              "Ak_Id": 558,
              "Ak_UntNr": 191,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.03.2025",
              "Ak_StundeVon": 3,
              "Ak_Fach": "FAwp",
              "Ak_VFach": "FAwp",
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
                "Lis"
              ],
              "VLehrer": [
                "Schwa"
              ],
              "Raeume": [
                ".Früh"
              ],
              "VRaeume": [
                ".Früh"
              ]
            },
            {
              "Ak_Id": 559,
              "Ak_UntNr": 191,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.03.2025",
              "Ak_StundeVon": 4,
              "Ak_Fach": "FAwp",
              "Ak_VFach": "FAwp",
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
                "Lis"
              ],
              "VLehrer": [
                "Tig"
              ],
              "Raeume": [
                ".Früh"
              ],
              "VRaeume": [
                ".Früh"
              ]
            }
          ]
        }
      ]
    }
  }
}';

    private $EVSRJsonManual = '{
  "Vertretungsplan": [
    {
      "Kopf": {
        "Datei": "Vertretungsplan Schüler2025-08-12.json",
        "Titel": "Dienstag, 12. August 2025 ",
        "Schulname": "Evangelische Grundschule Radebeul",
        "Datum": "12.08.2025",
        "Erstellt": "12.08.2025, 07:59",
        "Kopfinfo": {
          "LehrerMitAenderung": [
            {
              "Kurz": "Fuchs"
            },
            {
              "Kurz": "Groh"
            },
            {
              "Kurz": "Koe"
            },
            {
              "Kurz": "Lis"
            },
            {
              "Kurz": "Pol"
            },
            {
              "Kurz": "Schu"
            },
            {
              "Kurz": "Wal"
            },
            {
              "Kurz": "We"
            },
            {
              "Kurz": "Zei"
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
          "Ak_Id": 942,
          "Ak_UntNr": 67,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 5,
          "Ak_StundenAnz": 2,
          "Ak_Fach": "REe",
          "Klassen": [
            "1Frue",
            "2Frue",
            "1Somm",
            "2Somm"
          ],
          "Lehrer": [
            "We"
          ],
          "Raeume": [
            ".Früh"
          ]
        },
        {
          "Ak_Id": 949,
          "Ak_UntNr": 37,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 5,
          "Ak_Fach": "DE",
          "Klassen": [
            "1Herb",
            "1Wint"
          ],
          "Lehrer": [
            "Fuchs"
          ],
          "Raeume": [
            ".Win"
          ]
        },
        {
          "Ak_Id": 950,
          "Ak_UntNr": 132,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 6,
          "Ak_Fach": "Lese",
          "Klassen": [
            "1Herb",
            "1Wint"
          ],
          "Lehrer": [
            "Fuchs"
          ],
          "Raeume": [
            ".Win"
          ]
        },
        {
          "Ak_Id": 968,
          "Ak_UntNr": 46,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 5,
          "Ak_Fach": "MA",
          "Klassen": [
            "2Herb",
            "2Wint"
          ],
          "Lehrer": [
            "Pol"
          ],
          "Raeume": [
            ".Her"
          ]
        },
        {
          "Ak_Id": 969,
          "Ak_UntNr": 49,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 6,
          "Ak_Fach": "FÖ",
          "Klassen": [
            "2Herb",
            "2Wint"
          ],
          "Lehrer": [
            "Pol"
          ],
          "Raeume": [
            ".Her"
          ]
        },
        {
          "Ak_Id": 972,
          "Ak_UntNr": 25,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 5,
          "Ak_StundenAnz": 2,
          "Ak_Fach": "MU",
          "Klassen": [
            "3Frue",
            "4Frue",
            "3Somm",
            "4Somm"
          ],
          "Lehrer": [
            "Zei"
          ],
          "Raeume": [
            "Mus"
          ]
        },
        {
          "Ak_Id": 979,
          "Ak_UntNr": 187,
          "Ak_Art": "Ausf.",
          "Ak_DatumVon": "12.08.2025",
          "Ak_StundeVon": 5,
          "Ak_StundenAnz": 2,
          "Ak_Fach": "KU",
          "Klassen": [
            "3Herb",
            "4Herb",
            "3Wint",
            "4Wint"
          ],
          "Lehrer": [
            "Koe"
          ],
          "Raeume": [
            "Kunst"
          ]
        },
        {
          "Ak_Id": 1070,
          "Ak_UntNr": 0,
          "Ak_Art": "Neu",
          "Ak_DatumVon": "12.08.2025",
          "Ak_DatumNach": "12.08.2025",
          "Ak_StundeVon": 1,
          "Ak_StundeNach": 1,
          "Ak_StundenAnz": 4,
          "Ak_Fach": "",
          "Ak_VFach": "KL",
          "Klassen": [
            "4Frue",
            "1Frue",
            "2Frue",
            "3Frue"
          ],
          "VKlassen": [
            "4Frue",
            "1Frue",
            "2Frue",
            "3Frue"
          ],
          "Lehrer": [],
          "VLehrer": [
            "Lis",
            "Schu"
          ],
          "Raeume": [],
          "VRaeume": [
            ".Früh"
          ],
          "InfoK": "Klassenleiterstunde",
          "InfoL": "Klassenleiterstunde"
        },
        {
          "Ak_Id": 1053,
          "Ak_UntNr": 0,
          "Ak_Art": "Neu",
          "Ak_DatumVon": "12.08.2025",
          "Ak_DatumNach": "12.08.2025",
          "Ak_StundeVon": 1,
          "Ak_StundeNach": 1,
          "Ak_StundenAnz": 4,
          "Ak_Fach": "",
          "Ak_VFach": "KL",
          "Klassen": [
            "4Herb",
            "1Herb",
            "2Herb",
            "3Herb"
          ],
          "VKlassen": [
            "4Herb",
            "1Herb",
            "2Herb",
            "3Herb"
          ],
          "Lehrer": [],
          "VLehrer": [
            "Pol",
            "Groh"
          ],
          "Raeume": [],
          "VRaeume": [
            ".Her"
          ],
          "InfoK": "Klassenleiterstunde",
          "InfoL": "Klassenleiterstunde"
        },
        {
          "Ak_Id": 1048,
          "Ak_UntNr": 0,
          "Ak_Art": "Neu",
          "Ak_DatumVon": "12.08.2025",
          "Ak_DatumNach": "12.08.2025",
          "Ak_StundeVon": 1,
          "Ak_StundeNach": 1,
          "Ak_StundenAnz": 4,
          "Ak_Fach": "",
          "Ak_VFach": "KL",
          "Klassen": [
            "4Somm",
            "1Somm",
            "2Somm",
            "3Somm"
          ],
          "VKlassen": [
            "4Somm",
            "1Somm",
            "2Somm",
            "3Somm"
          ],
          "Lehrer": [],
          "VLehrer": [
            "Wal",
            "Koe"
          ],
          "Raeume": [],
          "VRaeume": [
            ".Som"
          ],
          "InfoK": "Klassenleiterstunde",
          "InfoL": "Klassenleiterstunde"
        },
        {
          "Ak_Id": 1033,
          "Ak_UntNr": 0,
          "Ak_Art": "Neu",
          "Ak_DatumVon": "12.08.2025",
          "Ak_DatumNach": "12.08.2025",
          "Ak_StundeVon": 1,
          "Ak_StundeNach": 1,
          "Ak_StundenAnz": 4,
          "Ak_Fach": "",
          "Ak_VFach": "KL",
          "Klassen": [
            "4Wint",
            "1Wint",
            "2Wint",
            "3Wint"
          ],
          "VKlassen": [
            "4Wint",
            "1Wint",
            "2Wint",
            "3Wint"
          ],
          "Lehrer": [],
          "VLehrer": [
            "Fuchs",
            "Zei"
          ],
          "Raeume": [],
          "VRaeume": [
            ".Win"
          ],
          "InfoK": "Klassenleiterstunde",
          "InfoL": "Klassenleiterstunde"
        },
        {
          "Ak_Id": 1033,
          "Ak_UntNr": 0,
          "Ak_Art": "Änd.",
          "Ak_DatumVon": "13.08.2025",
          "Ak_DatumNach": "13.08.2025",
          "Ak_StundeVon": 1,
          "Ak_StundeNach": 1,
          "Ak_StundenAnz": 1,
          "Ak_Fach": "FAwp",
          "Ak_VFach": "MA",
          "Klassen": [
            "4Wint"
          ],
          "VKlassen": [
            "4Wint"
          ],
          "Lehrer": [
            "Li"
          ],
          "VLehrer": [
            "Fuchs"
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
}';

    private $KGJson = '{
  "Gesamtexport": {
    "Informationen": {
      "Version": "1.1"
    },
    "Vertretungsplan": {
      "Vertretungsplan": [
        {
          "Kopf": {
            "Datei": "Vertretungsplan Schüler2026-03-20.json",
            "Titel": "Freitag, 20. März 2026 ",
            "Schulname": "Evangelisches Kreuzgymnasium Dresden",
            "Datum": "20.03.2026",
            "Erstellt": "19.03.2026, 10:07",
            "Kopfinfo": {
              "AbwesendeLehrer": [
                {
                  "Kurz": "GAN",
                  "Grund": "UntLe",
                  "Stunden": "3-6"
                },
                {
                  "Kurz": "GRA",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "GRO",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "LÜT",
                  "Grund": "So"
                },
                {
                  "Kurz": "MOE",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "NIK",
                  "Grund": "UntLe",
                  "Stunden": "2-5"
                },
                {
                  "Kurz": "REU",
                  "Grund": "UntLe",
                  "Stunden": "2-5"
                },
                {
                  "Kurz": "RÖS",
                  "Grund": "UntLe",
                  "Stunden": "3-5"
                },
                {
                  "Kurz": "SMD",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "VLK",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "WER",
                  "Grund": "Kr"
                },
                {
                  "Kurz": "WIN",
                  "Grund": "So"
                }
              ],
              "AbwesendeKlassen": [
                {
                  "Kurz": "06/2",
                  "Stunden": "2-5"
                },
                {
                  "Kurz": "09/1",
                  "Stunden": "3-5"
                },
                {
                  "Kurz": "09/2",
                  "Stunden": "3-5"
                }
              ]
            }
          },
          "Aktionen": [
            {
              "Ak_Id": 24085,
              "Ak_UntNr": 558,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "SPO",
              "Ak_VFach": "SPO",
              "Klassen": [
                "05/2"
              ],
              "VKlassen": [
                "05/2"
              ],
              "Lehrer": [
                "GRA"
              ],
              "VLehrer": [
                "HÄG"
              ],
              "Raeume": [
                "TH 2"
              ],
              "VRaeume": [
                "TH 2"
              ]
            },
            {
              "Ak_Id": 24035,
              "Ak_UntNr": 545,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "BIO",
              "Klassen": [
                "05/2"
              ],
              "Lehrer": [
                "RÖS"
              ],
              "Raeume": [
                "210"
              ]
            },
            {
              "Ak_Id": 24095,
              "Ak_UntNr": 581,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MU",
              "Klassen": [
                "05/4"
              ],
              "Lehrer": [
                "RÖS"
              ],
              "Raeume": [
                "235"
              ],
              "InfoK": "zugunsten von Englisch",
              "InfoL": "zugunsten von Englisch"
            },
            {
              "Ak_Id": 24093,
              "Ak_UntNr": 574,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "EN",
              "Ak_VFach": "EN",
              "Klassen": [
                "05/4"
              ],
              "Lehrer": [
                "PRO"
              ],
              "VLehrer": [
                "PRO"
              ],
              "Raeume": [
                "130"
              ],
              "VRaeume": [
                "130"
              ]
            },
            {
              "Ak_Id": 24093,
              "Ak_UntNr": 574,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "EN",
              "Ak_VFach": "EN",
              "Klassen": [
                "05/4"
              ],
              "Lehrer": [
                "PRO"
              ],
              "VLehrer": [
                "PRO"
              ],
              "Raeume": [
                "130"
              ],
              "VRaeume": [
                "130"
              ]
            },
            {
              "Ak_Id": 24261,
              "Ak_UntNr": 4,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Klassen": [
                "06/1"
              ],
              "Lehrer": [
                "WIN"
              ],
              "Raeume": [
                "140"
              ]
            },
            {
              "Ak_Id": 24200,
              "Ak_UntNr": 28,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "MU",
              "Klassen": [
                "06/2"
              ],
              "Lehrer": [
                "VET"
              ],
              "Raeume": [
                "335"
              ]
            },
            {
              "Ak_Id": 24201,
              "Ak_UntNr": 24,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_Fach": "KLA",
              "Klassen": [
                "06/2"
              ],
              "Lehrer": [
                "REU",
                "WGN"
              ],
              "Raeume": [
                "133"
              ]
            },
            {
              "Ak_Id": 24202,
              "Ak_UntNr": 18,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 4,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Klassen": [
                "06/2"
              ],
              "Lehrer": [
                "REU"
              ],
              "Raeume": [
                "133"
              ]
            },
            {
              "Ak_Id": 24135,
              "Ak_UntNr": 36,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "16.03.2026",
              "Ak_StundeVon": 7,
              "Ak_StundeNach": 5,
              "Ak_Fach": "GE",
              "Ak_VFach": "GE",
              "Klassen": [
                "06/3"
              ],
              "Lehrer": [
                "SMI"
              ],
              "VLehrer": [
                "SMI"
              ],
              "Raeume": [
                "137"
              ],
              "VRaeume": [
                "137"
              ]
            },
            {
              "Ak_Id": 24287,
              "Ak_UntNr": 70,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "LA",
              "Ak_VFach": "LA",
              "Klassen": [
                "07/1"
              ],
              "VKlassen": [
                "07/1"
              ],
              "Lehrer": [
                "NIK"
              ],
              "VLehrer": [
                "SÜT"
              ],
              "Raeume": [
                "340"
              ],
              "VRaeume": [
                "340"
              ]
            },
            {
              "Ak_Id": 24666,
              "Ak_UntNr": 1094,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "KU",
              "Klassen": [
                "07/2"
              ],
              "Lehrer": [
                "VLK"
              ],
              "Raeume": [
                "416"
              ]
            },
            {
              "Ak_Id": 24108,
              "Ak_UntNr": 87,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Ak_VFach": "MA",
              "Klassen": [
                "07/2"
              ],
              "VKlassen": [
                "07/2"
              ],
              "Lehrer": [
                "MOE"
              ],
              "VLehrer": [
                "KAI"
              ],
              "Raeume": [
                "339"
              ],
              "VRaeume": [
                "339"
              ]
            },
            {
              "Ak_Id": 24800,
              "Ak_UntNr": 99,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GE",
              "Klassen": [
                "07/3"
              ],
              "Lehrer": [
                "WER"
              ],
              "Raeume": [
                "338"
              ]
            },
            {
              "Ak_Id": 24087,
              "Ak_UntNr": 1216,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "SPM",
              "Ak_VFach": "SPM",
              "Klassen": [
                "08/3"
              ],
              "VKlassen": [
                "08/3"
              ],
              "Lehrer": [
                "GRA"
              ],
              "VLehrer": [
                "GRA"
              ],
              "Raeume": [
                "TH 2"
              ],
              "VRaeume": [
                "TH 2"
              ],
              "InfoK": "Gemeinsam mit Mädchen Klasse 8.4 bei Frau Scheuermann",
              "InfoL": "Gemeinsam mit Mädchen Klasse 8.4 bei Frau Scheuermann"
            },
            {
              "Ak_Id": 24672,
              "Ak_UntNr": 1099,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "KU",
              "Klassen": [
                "08/3"
              ],
              "Lehrer": [
                "VLK"
              ],
              "Raeume": [
                "316"
              ],
              "InfoK": "zugunsten von Deutsch",
              "InfoL": "zugunsten von Deutsch"
            },
            {
              "Ak_Id": 24670,
              "Ak_UntNr": 175,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 8,
              "Ak_StundeNach": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Ak_VFach": "DE",
              "Klassen": [
                "08/3"
              ],
              "Lehrer": [
                "BIE"
              ],
              "VLehrer": [
                "BIE"
              ],
              "Raeume": [
                "331"
              ],
              "VRaeume": [
                "331"
              ]
            },
            {
              "Ak_Id": 24670,
              "Ak_UntNr": 175,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 8,
              "Ak_StundeNach": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Ak_VFach": "DE",
              "Klassen": [
                "08/3"
              ],
              "Lehrer": [
                "BIE"
              ],
              "VLehrer": [
                "BIE"
              ],
              "Raeume": [
                "331"
              ],
              "VRaeume": [
                "331"
              ]
            },
            {
              "Ak_Id": 24091,
              "Ak_UntNr": 1209,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "SPM",
              "Ak_VFach": "SPM",
              "Klassen": [
                "08/4"
              ],
              "VKlassen": [
                "08/4"
              ],
              "Lehrer": [
                "SEU"
              ],
              "VLehrer": [
                "SEU"
              ],
              "Raeume": [
                "TH 3"
              ],
              "VRaeume": [
                "TH 3"
              ],
              "InfoK": "Gemeinsam mit Mädchen Klasse 8.3",
              "InfoL": "Gemeinsam mit Mädchen Klasse 8.3"
            },
            {
              "Ak_Id": 24027,
              "Ak_UntNr": 1230,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GE",
              "Klassen": [
                "09/1"
              ],
              "Lehrer": [
                "GRO"
              ],
              "Raeume": [
                "238"
              ]
            },
            {
              "Ak_Id": 24037,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 6,
              "Ak_Fach": "MU",
              "Ak_VFach": "MU",
              "Klassen": [
                "09/1",
                "09/2"
              ],
              "VKlassen": [
                "09/1",
                "09/2"
              ],
              "Lehrer": [],
              "VLehrer": [
                "RÖS"
              ],
              "Raeume": [
                "235"
              ],
              "VRaeume": [
                "235"
              ],
              "InfoK": "Auswertung Konzertbesuch",
              "InfoL": "Auswertung Konzertbesuch"
            },
            {
              "Ak_Id": 24032,
              "Ak_UntNr": 219,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "MA",
              "Klassen": [
                "09/1"
              ],
              "Lehrer": [
                "HEI"
              ],
              "Raeume": [
                "238"
              ]
            },
            {
              "Ak_Id": 24033,
              "Ak_UntNr": 219,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 7,
              "Ak_Fach": "MA",
              "Klassen": [
                "09/1"
              ],
              "Lehrer": [
                "HEI"
              ],
              "Raeume": [
                "238"
              ]
            },
            {
              "Ak_Id": 24029,
              "Ak_UntNr": 232,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Klassen": [
                "09/2"
              ],
              "Lehrer": [
                "TSC"
              ],
              "Raeume": [
                "237"
              ]
            },
            {
              "Ak_Id": 24031,
              "Ak_UntNr": 235,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "GE",
              "Klassen": [
                "09/2"
              ],
              "Lehrer": [
                "GRO"
              ],
              "Raeume": [
                "237"
              ]
            },
            {
              "Ak_Id": 24037,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 6,
              "Ak_Fach": "MU",
              "Ak_VFach": "MU",
              "Klassen": [
                "09/2",
                "09/1"
              ],
              "VKlassen": [
                "09/2",
                "09/1"
              ],
              "Lehrer": [],
              "VLehrer": [
                "RÖS"
              ],
              "Raeume": [
                "235"
              ],
              "VRaeume": [
                "235"
              ],
              "InfoK": "Auswertung Konzertbesuch",
              "InfoL": "Auswertung Konzertbesuch"
            },
            {
              "Ak_Id": 24034,
              "Ak_UntNr": 235,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "GE",
              "Klassen": [
                "09/2"
              ],
              "Lehrer": [
                "GRO"
              ],
              "Raeume": [
                "237"
              ]
            },
            {
              "Ak_Id": 24116,
              "Ak_UntNr": 274,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Ak_VFach": "REe",
              "Klassen": [
                "09/4"
              ],
              "VKlassen": [
                "09/4"
              ],
              "Lehrer": [
                "MOE"
              ],
              "VLehrer": [
                "TSC"
              ],
              "Raeume": [
                "232"
              ],
              "VRaeume": [
                "232"
              ]
            },
            {
              "Ak_Id": 24668,
              "Ak_UntNr": 273,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 7,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "KU",
              "Klassen": [
                "09/4"
              ],
              "Lehrer": [
                "VLK"
              ],
              "Raeume": [
                "316"
              ]
            },
            {
              "Ak_Id": 24263,
              "Ak_UntNr": 290,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 4,
              "Ak_Fach": "KU",
              "Ak_VFach": "MU",
              "Klassen": [
                "10/1"
              ],
              "VKlassen": [
                "10/1"
              ],
              "Lehrer": [
                "WIN"
              ],
              "VLehrer": [
                "VET"
              ],
              "Raeume": [
                "316"
              ],
              "VRaeume": [
                "335"
              ]
            },
            {
              "Ak_Id": 24166,
              "Ak_UntNr": 851,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "10/3"
              ],
              "VKlassen": [
                "10/3"
              ],
              "Lehrer": [
                "LOR"
              ],
              "VLehrer": [
                "LOR"
              ],
              "Raeume": [
                "431"
              ],
              "VRaeume": [
                "235"
              ]
            },
            {
              "Ak_Id": 24038,
              "Ak_UntNr": 337,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 5,
              "Ak_Fach": "MU",
              "Ak_VFach": "MU",
              "Klassen": [
                "10/3"
              ],
              "Lehrer": [
                "VET"
              ],
              "VLehrer": [
                "VET"
              ],
              "Raeume": [
                "335"
              ],
              "VRaeume": [
                "235"
              ]
            },
            {
              "Ak_Id": 24038,
              "Ak_UntNr": 337,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 5,
              "Ak_Fach": "MU",
              "Ak_VFach": "MU",
              "Klassen": [
                "10/3"
              ],
              "Lehrer": [
                "VET"
              ],
              "VLehrer": [
                "VET"
              ],
              "Raeume": [
                "335"
              ],
              "VRaeume": [
                "235"
              ]
            },
            {
              "Ak_Id": 24260,
              "Ak_UntNr": 0,
              "Ak_Art": "Neu",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundeNach": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Ak_VFach": "MA",
              "Klassen": [
                "10/4"
              ],
              "VKlassen": [
                "10/4"
              ],
              "Lehrer": [],
              "VLehrer": [
                "NOA"
              ],
              "Raeume": [
                "431"
              ],
              "VRaeume": [
                "430"
              ],
              "InfoK": "BLF-Nachtermin",
              "InfoL": "BLF-Nachtermin"
            },
            {
              "Ak_Id": 24289,
              "Ak_UntNr": 352,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Klassen": [
                "10/4"
              ],
              "Lehrer": [
                "NOA"
              ],
              "Raeume": [
                "430"
              ],
              "InfoK": "Aufgaben zur selbstständigen Bearbeitung werden erteilt",
              "InfoL": "Aufgaben zur selbstständigen Bearbeitung werden erteilt"
            },
            {
              "Ak_Id": 24316,
              "Ak_UntNr": 343,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "CH",
              "Ak_VFach": "CH",
              "Klassen": [
                "10/4"
              ],
              "VKlassen": [
                "10/4"
              ],
              "Lehrer": [
                "GAN"
              ],
              "VLehrer": [
                "KOB"
              ],
              "Raeume": [
                "214"
              ],
              "VRaeume": [
                "237"
              ],
              "InfoK": "Der Chemie-Test findet statt.",
              "InfoL": "Der Chemie-Test findet statt."
            },
            {
              "Ak_Id": 24798,
              "Ak_UntNr": 1135,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Ak_Kurs": "de3",
              "Ak_VKurs": "de3",
              "Klassen": [
                "11"
              ],
              "Lehrer": [
                "WER"
              ],
              "Raeume": [
                "438"
              ]
            },
            {
              "Ak_Id": 24100,
              "Ak_UntNr": 968,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Ak_Kurs": "MA3",
              "Ak_VKurs": "MA3",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "MOE"
              ],
              "Raeume": [
                "213"
              ],
              "InfoK": "Kursaufteilung auf LK12Ma1 und Lk12Ma2 wie im Vorabitur",
              "InfoL": "Kursaufteilung auf LK12Ma1 und Lk12Ma2 wie im Vorabitur"
            },
            {
              "Ak_Id": 24039,
              "Ak_UntNr": 1007,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Ak_Kurs": "ree3",
              "Ak_VKurs": "ree3",
              "Klassen": [
                "12"
              ],
              "VKlassen": [
                "12"
              ],
              "Lehrer": [
                "WGN"
              ],
              "VLehrer": [
                "WGN"
              ],
              "Raeume": [
                "135"
              ],
              "VRaeume": [
                "335"
              ],
              "InfoK": "Expertengespräch, gemeinsam mit Gk12ree2",
              "InfoL": "Expertengespräch, gemeinsam mit Gk12ree2"
            },
            {
              "Ak_Id": 24314,
              "Ak_UntNr": 1045,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "PH",
              "Klassen": [
                "DaZ Pri"
              ],
              "Lehrer": [
                "GAN"
              ],
              "Raeume": [
                "214"
              ]
            },
            {
              "Ak_Id": 24315,
              "Ak_UntNr": 1219,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 5,
              "Ak_Fach": "DAZ",
              "Ak_VFach": "DAZ",
              "Ak_Kurs": "daz alle",
              "Ak_VKurs": "daz alle",
              "Klassen": [
                "DaZ Pri"
              ],
              "Lehrer": [
                "PRI"
              ],
              "VLehrer": [
                "PRI"
              ],
              "Raeume": [
                "239"
              ],
              "VRaeume": [
                "239"
              ]
            },
            {
              "Ak_Id": 24315,
              "Ak_UntNr": 1219,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_DatumNach": "20.03.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 5,
              "Ak_Fach": "DAZ",
              "Ak_VFach": "DAZ",
              "Ak_Kurs": "daz alle",
              "Ak_VKurs": "daz alle",
              "Klassen": [
                "DaZ Pri"
              ],
              "Lehrer": [
                "PRI"
              ],
              "VLehrer": [
                "PRI"
              ],
              "Raeume": [
                "239"
              ],
              "VRaeume": [
                "239"
              ]
            }
          ],
          "Informationen": [
            "Klassen 9.1 und 9.2 Konzertbesuch 3.-6.Stunde // Gk12DS Probentag Aula // Gk12ree2+3 Expertengespräch 5./6.Stunde in Raum 335 // Klasse 6.2 Unterrichtsgang TJG 2.-5.Stunde"
          ]
        }
      ]
    }
  }
}';
}
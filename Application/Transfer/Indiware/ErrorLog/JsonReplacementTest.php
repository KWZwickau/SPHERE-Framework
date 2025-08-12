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
        }
      ]
    }
  ]
}';
}
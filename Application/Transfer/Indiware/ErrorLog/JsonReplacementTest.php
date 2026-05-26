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
        } elseif($Mandant == 'HGGT') {
            return $this->HGGTJson;
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
            "Erstellt": "20.03.2026, 10:17",
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
              "Ak_Id": 24855,
              "Ak_UntNr": 637,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "20.03.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "REk",
              "Klassen": [
                "10/1",
                "10/3"
              ],
              "Lehrer": [
                "HAU"
              ],
              "Raeume": [
                "135"
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
          ]
        }
      ]
    }
  }
}';

    private $HGGTJson = '{
  "Gesamtexport": {
    "Informationen": {
      "Version": "1.1"
    },
    "Vertretungsplan": {
      "Vertretungsplan": [
        {
          "Kopf": {
            "Datei": "Vertretungsplan Schüler2026-05-05.json",
            "Titel": "Dienstag, 5. Mai 2026 (A-Woche) ",
            "Schulname": "Humanistisches Greifenstein Gymnasium",
            "Datum": "05.05.2026",
            "Erstellt": "05.05.2026, 07:04",
            "Kopfinfo": {
              "AbwesendeLehrer": [
                {
                  "Kurz": "EXN",
                  "Grund": "Ko",
                  "Stunden": "1-2"
                },
                {
                  "Kurz": "REU",
                  "Grund": "Kr"
                }
              ],
              "AbwesendeRaeume": [
                {
                  "Kurz": "102",
                  "Stunden": "1-2"
                }
              ],
              "LehrerMitAenderung": [
                {
                  "Kurz": "ASSM"
                },
                {
                  "Kurz": "BRA"
                },
                {
                  "Kurz": "FIED"
                },
                {
                  "Kurz": "FREI"
                },
                {
                  "Kurz": "FRIE"
                },
                {
                  "Kurz": "KLEM"
                },
                {
                  "Kurz": "KOLD"
                },
                {
                  "Kurz": "LIST"
                },
                {
                  "Kurz": "MEI"
                },
                {
                  "Kurz": "SCHM"
                },
                {
                  "Kurz": "SCHUB"
                },
                {
                  "Kurz": "STÜLP"
                },
                {
                  "Kurz": "VIET"
                }
              ],
              "KlassenMitAenderung": [
                {
                  "Kurz": "6a"
                },
                {
                  "Kurz": "6b"
                },
                {
                  "Kurz": "9c"
                },
                {
                  "Kurz": "12"
                }
              ]
            }
          },
          "Aktionen": [
            {
              "Ak_Id": 18853,
              "Ak_UntNr": 184,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 1,
              "Ak_Fach": "DE",
              "Ak_VFach": "DE",
              "Klassen": [
                "9c"
              ],
              "VKlassen": [
                "9c"
              ],
              "Lehrer": [
                "STÜLP"
              ],
              "VLehrer": [
                "STÜLP",
                "FREI"
              ],
              "Raeume": [
                "117"
              ],
              "VRaeume": [
                "117"
              ]
            },
            {
              "Ak_Id": 19227,
              "Ak_UntNr": 306,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "BIO",
              "Ak_Kurs": "BioLK",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "BRA"
              ],
              "Raeume": [
                "108"
              ]
            },
            {
              "Ak_Id": 19228,
              "Ak_UntNr": 307,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "CH",
              "Ak_Kurs": "ChLK",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "FIED"
              ],
              "Raeume": [
                "111"
              ]
            },
            {
              "Ak_Id": 19229,
              "Ak_UntNr": 308,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "EN",
              "Ak_Kurs": "EnLK",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "ASSM"
              ],
              "Raeume": [
                "102"
              ]
            },
            {
              "Ak_Id": 21098,
              "Ak_UntNr": 36,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "MA",
              "Ak_VFach": "MA",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "REU"
              ],
              "VLehrer": [
                "FRIE"
              ],
              "Raeume": [
                "209"
              ],
              "VRaeume": [
                "209"
              ],
              "InfoK": "Aufsicht Aufgaben Mathematik (Frau Reuther)"
            },
            {
              "Ak_Id": 18852,
              "Ak_UntNr": 188,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "GE",
              "Ak_VFach": "DE",
              "Klassen": [
                "9c"
              ],
              "VKlassen": [
                "9c"
              ],
              "Lehrer": [
                "SCHUB"
              ],
              "VLehrer": [
                "STÜLP",
                "FREI"
              ],
              "Raeume": [
                "117"
              ],
              "VRaeume": [
                "117"
              ],
              "InfoK": "verlegt von Mo.(4.5.) 3.St. nach Di.(5.5.) 2.St."
            },
            {
              "Ak_Id": 19234,
              "Ak_UntNr": 325,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "EN2",
              "Ak_Kurs": "En2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "KLEM"
              ],
              "Raeume": [
                "205"
              ]
            },
            {
              "Ak_Id": 19235,
              "Ak_UntNr": 326,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GRW",
              "Ak_Kurs": "Gk2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "FREI"
              ],
              "Raeume": [
                "303"
              ]
            },
            {
              "Ak_Id": 19233,
              "Ak_UntNr": 324,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "CH",
              "Ak_Kurs": "Ch1",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "FIED"
              ],
              "Raeume": [
                "111"
              ]
            },
            {
              "Ak_Id": 20144,
              "Ak_UntNr": 40,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Ak_VFach": "DE",
              "Klassen": [
                "6b"
              ],
              "VKlassen": [
                "6b"
              ],
              "Lehrer": [
                "MEI"
              ],
              "VLehrer": [
                "MEI"
              ],
              "Raeume": [
                "303"
              ],
              "VRaeume": [
                "203"
              ]
            },
            {
              "Ak_Id": 19241,
              "Ak_UntNr": 321,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "INF",
              "Ak_Kurs": "Inf1",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "KOLD"
              ],
              "Raeume": [
                "007"
              ]
            },
            {
              "Ak_Id": 19239,
              "Ak_UntNr": 319,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GE",
              "Ak_Kurs": "Ge2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "VIET"
              ],
              "Raeume": [
                "102"
              ]
            },
            {
              "Ak_Id": 19240,
              "Ak_UntNr": 320,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GE",
              "Ak_Kurs": "Ge3",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "LIST"
              ],
              "Raeume": [
                "203"
              ]
            },
            {
              "Ak_Id": 19247,
              "Ak_UntNr": 305,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 7,
              "Ak_Fach": "MA",
              "Ak_Kurs": "MaLK1",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "FRIE"
              ],
              "Raeume": [
                "313"
              ]
            },
            {
              "Ak_Id": 19245,
              "Ak_UntNr": 303,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 7,
              "Ak_Fach": "DE",
              "Ak_Kurs": "DLK1",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "STÜLP"
              ],
              "Raeume": [
                "103"
              ]
            },
            {
              "Ak_Id": 19246,
              "Ak_UntNr": 304,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "05.05.2026",
              "Ak_StundeVon": 7,
              "Ak_Fach": "DE",
              "Ak_Kurs": "DLK2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "SCHM"
              ],
              "Raeume": [
                "108"
              ]
            }
          ]
        },
        {
          "Kopf": {
            "Datei": "Vertretungsplan Schüler2026-05-06.json",
            "Titel": "Mittwoch, 6. Mai 2026 (A-Woche) ",
            "Schulname": "Humanistisches Greifenstein Gymnasium",
            "Datum": "06.05.2026",
            "Erstellt": "05.05.2026, 07:04",
            "Kopfinfo": {
              "AbwesendeLehrer": [
                {
                  "Kurz": "ASSM",
                  "Grund": "Pr"
                },
                {
                  "Kurz": "KOLD",
                  "Grund": "Fo",
                  "Stunden": "3-5"
                },
                {
                  "Kurz": "REU",
                  "Grund": "Kr"
                }
              ],
              "AbwesendeRaeume": [
                {
                  "Kurz": "006",
                  "Stunden": "6"
                }
              ],
              "LehrerMitAenderung": [
                {
                  "Kurz": "?w"
                },
                {
                  "Kurz": "ADAM"
                },
                {
                  "Kurz": "BRA"
                },
                {
                  "Kurz": "BUSS"
                },
                {
                  "Kurz": "EXN"
                },
                {
                  "Kurz": "FIED"
                },
                {
                  "Kurz": "FREI"
                },
                {
                  "Kurz": "GRUN"
                },
                {
                  "Kurz": "GÜTT"
                },
                {
                  "Kurz": "HAMM"
                },
                {
                  "Kurz": "HARM"
                },
                {
                  "Kurz": "KEHR"
                },
                {
                  "Kurz": "KOLD"
                },
                {
                  "Kurz": "KREN"
                },
                {
                  "Kurz": "KREY"
                },
                {
                  "Kurz": "LÖSC"
                },
                {
                  "Kurz": "MEHL"
                },
                {
                  "Kurz": "SCHA"
                },
                {
                  "Kurz": "SCHM"
                },
                {
                  "Kurz": "SCHÖ"
                },
                {
                  "Kurz": "STÜLP"
                },
                {
                  "Kurz": "UHG"
                }
              ],
              "KlassenMitAenderung": [
                {
                  "Kurz": "5b"
                },
                {
                  "Kurz": "6a"
                },
                {
                  "Kurz": "6b"
                },
                {
                  "Kurz": "6c"
                },
                {
                  "Kurz": "7a"
                },
                {
                  "Kurz": "8a"
                },
                {
                  "Kurz": "8b"
                },
                {
                  "Kurz": "9a"
                },
                {
                  "Kurz": "9b"
                },
                {
                  "Kurz": "9c"
                },
                {
                  "Kurz": "10a"
                },
                {
                  "Kurz": "10b"
                },
                {
                  "Kurz": "12"
                }
              ]
            }
          },
          "Aktionen": [
            {
              "Ak_Id": 21101,
              "Ak_UntNr": 27,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "GÜTT"
              ],
              "VLehrer": [
                "GÜTT"
              ],
              "Raeume": [
                "209"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21102,
              "Ak_UntNr": 26,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "HARM"
              ],
              "VLehrer": [
                "HARM"
              ],
              "Raeume": [
                "119"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21110,
              "Ak_UntNr": 55,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_Fach": "DE",
              "Ak_VFach": "DE",
              "Klassen": [
                "6c"
              ],
              "VKlassen": [
                "6c"
              ],
              "Lehrer": [
                "UHG"
              ],
              "VLehrer": [
                "UHG"
              ],
              "Raeume": [
                "006"
              ],
              "VRaeume": [
                "209"
              ]
            },
            {
              "Ak_Id": 21165,
              "Ak_UntNr": 164,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_Fach": "DE",
              "Klassen": [
                "9b"
              ],
              "Lehrer": [
                "SCHM"
              ],
              "Raeume": [
                "203"
              ]
            },
            {
              "Ak_Id": 21164,
              "Ak_UntNr": 182,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_DatumNach": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 1,
              "Ak_Fach": "INF",
              "Ak_VFach": "INF",
              "Klassen": [
                "9b"
              ],
              "Lehrer": [
                "KOLD"
              ],
              "VLehrer": [
                "KOLD"
              ],
              "Raeume": [
                "007"
              ],
              "VRaeume": [
                "007"
              ]
            },
            {
              "Ak_Id": 21116,
              "Ak_UntNr": 225,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "10b"
              ],
              "VKlassen": [
                "10b"
              ],
              "Lehrer": [
                "ADAM"
              ],
              "VLehrer": [
                "ADAM"
              ],
              "Raeume": [
                "117"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 19250,
              "Ak_UntNr": 308,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "EN",
              "Ak_Kurs": "EnLK",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "ASSM"
              ],
              "Raeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 19248,
              "Ak_UntNr": 306,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "BIO",
              "Ak_Kurs": "BioLK",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "BRA"
              ],
              "Raeume": [
                "108"
              ]
            },
            {
              "Ak_Id": 19249,
              "Ak_UntNr": 307,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 1,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "CH",
              "Ak_Kurs": "ChLK",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "FIED"
              ],
              "Raeume": [
                "111"
              ]
            },
            {
              "Ak_Id": 21104,
              "Ak_UntNr": 42,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "6b"
              ],
              "VKlassen": [
                "6b"
              ],
              "Lehrer": [
                "GÜTT"
              ],
              "VLehrer": [
                "GÜTT"
              ],
              "Raeume": [
                "006"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21103,
              "Ak_UntNr": 41,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "6b"
              ],
              "VKlassen": [
                "6b"
              ],
              "Lehrer": [
                "HARM"
              ],
              "VLehrer": [
                "HARM"
              ],
              "Raeume": [
                "119"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 18857,
              "Ak_UntNr": 164,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "DE",
              "Ak_VFach": "GEO",
              "Klassen": [
                "9b"
              ],
              "VKlassen": [
                "9b"
              ],
              "Lehrer": [
                "SCHM"
              ],
              "VLehrer": [
                "HAMM"
              ],
              "Raeume": [
                "203"
              ],
              "VRaeume": [
                "203"
              ],
              "InfoK": "verlegt von 3.St. nach 2.St."
            },
            {
              "Ak_Id": 18855,
              "Ak_UntNr": 187,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 2,
              "Ak_Fach": "GEO",
              "Ak_VFach": "DE",
              "Klassen": [
                "9c"
              ],
              "VKlassen": [
                "9c"
              ],
              "Lehrer": [
                "HAMM"
              ],
              "VLehrer": [
                "STÜLP"
              ],
              "Raeume": [
                "313"
              ],
              "VRaeume": [
                "313"
              ],
              "InfoK": "verlegt von 5.St. nach 2.St."
            },
            {
              "Ak_Id": 21095,
              "Ak_UntNr": 36,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_Fach": "MA",
              "Ak_VFach": "GEO",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "REU"
              ],
              "VLehrer": [
                "HAMM"
              ],
              "Raeume": [
                "209"
              ],
              "VRaeume": [
                "313"
              ],
              "InfoK": "verlegt von 6.St. nach 3.St."
            },
            {
              "Ak_Id": 21111,
              "Ak_UntNr": 344,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "FR",
              "Ak_VFach": "FR",
              "Klassen": [
                "6c"
              ],
              "VKlassen": [
                "6c"
              ],
              "Lehrer": [
                "SCHÖ"
              ],
              "VLehrer": [
                "SCHÖ"
              ],
              "Raeume": [
                "006"
              ],
              "VRaeume": [
                "209"
              ]
            },
            {
              "Ak_Id": 21159,
              "Ak_UntNr": 106,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "8a"
              ],
              "VKlassen": [
                "8a"
              ],
              "Lehrer": [
                "GÜTT"
              ],
              "VLehrer": [
                "GÜTT"
              ],
              "Raeume": [
                "102"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21121,
              "Ak_UntNr": 105,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "8a"
              ],
              "VKlassen": [
                "8a"
              ],
              "Lehrer": [
                "HARM"
              ],
              "VLehrer": [
                "HARM"
              ],
              "Raeume": [
                "117"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 18861,
              "Ak_UntNr": 156,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_Fach": "MA",
              "Ak_VFach": "MA",
              "Klassen": [
                "9a"
              ],
              "VKlassen": [
                "9a"
              ],
              "Lehrer": [
                "LÖSC"
              ],
              "VLehrer": [
                "LÖSC"
              ],
              "Raeume": [
                "303"
              ],
              "VRaeume": [
                "315"
              ]
            },
            {
              "Ak_Id": 18859,
              "Ak_UntNr": 167,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_Fach": "GEO",
              "Ak_VFach": "DE",
              "Klassen": [
                "9b"
              ],
              "VKlassen": [
                "9b"
              ],
              "Lehrer": [
                "HAMM"
              ],
              "VLehrer": [
                "SCHM",
                "FREI"
              ],
              "Raeume": [
                "315"
              ],
              "VRaeume": [
                "303"
              ],
              "InfoK": "verlegt von 1.St. nach 3.St."
            },
            {
              "Ak_Id": 21108,
              "Ak_UntNr": 189,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GRW",
              "Ak_VFach": "GRW",
              "Klassen": [
                "9c"
              ],
              "VKlassen": [
                "9c"
              ],
              "Lehrer": [
                "KREN"
              ],
              "VLehrer": [
                "KREN"
              ],
              "Raeume": [
                "118"
              ],
              "VRaeume": [
                "109"
              ]
            },
            {
              "Ak_Id": 21160,
              "Ak_UntNr": 206,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "10a"
              ],
              "VKlassen": [
                "10a"
              ],
              "Lehrer": [
                "KREY"
              ],
              "VLehrer": [
                "KREY"
              ],
              "Raeume": [
                "105"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 21118,
              "Ak_UntNr": 205,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "10a"
              ],
              "VKlassen": [
                "10a"
              ],
              "Lehrer": [
                "ADAM"
              ],
              "VLehrer": [
                "ADAM"
              ],
              "Raeume": [
                "119"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 19254,
              "Ak_UntNr": 333,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "BIO",
              "Ak_Kurs": "Bio2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "BUSS"
              ],
              "Raeume": [
                "109"
              ]
            },
            {
              "Ak_Id": 19255,
              "Ak_UntNr": 334,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 3,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "GEO",
              "Ak_Kurs": "Geo2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "EXN"
              ],
              "Raeume": [
                "313"
              ]
            },
            {
              "Ak_Id": 21096,
              "Ak_UntNr": 36,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 4,
              "Ak_Fach": "MA",
              "Ak_VFach": "MA",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "REU"
              ],
              "VLehrer": [
                "SCHA"
              ],
              "Raeume": [
                "209"
              ],
              "VRaeume": [
                "313"
              ],
              "InfoK": "Aufsicht Aufgaben Mathematik (Frau Reuther)"
            },
            {
              "Ak_Id": 21163,
              "Ak_UntNr": 126,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 4,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "8b"
              ],
              "VKlassen": [
                "8b"
              ],
              "Lehrer": [
                "GÜTT"
              ],
              "VLehrer": [
                "GÜTT"
              ],
              "Raeume": [
                "103"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21122,
              "Ak_UntNr": 125,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 4,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "8b"
              ],
              "VKlassen": [
                "8b"
              ],
              "Lehrer": [
                "HARM"
              ],
              "VLehrer": [
                "HARM"
              ],
              "Raeume": [
                "117"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 18860,
              "Ak_UntNr": 173,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 4,
              "Ak_Fach": "EN",
              "Ak_VFach": "DE",
              "Klassen": [
                "9b"
              ],
              "VKlassen": [
                "9b"
              ],
              "Lehrer": [
                "ASSM"
              ],
              "VLehrer": [
                "SCHM",
                "FREI"
              ],
              "Raeume": [
                "303"
              ],
              "VRaeume": [
                "303"
              ],
              "InfoK": "verlegt von 2.St. nach 4.St."
            },
            {
              "Ak_Id": 21120,
              "Ak_UntNr": 272,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "5b"
              ],
              "VKlassen": [
                "5b"
              ],
              "Lehrer": [
                "ADAM"
              ],
              "VLehrer": [
                "ADAM"
              ],
              "Raeume": [
                "109"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 21162,
              "Ak_UntNr": 273,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "5b"
              ],
              "VKlassen": [
                "5b"
              ],
              "Lehrer": [
                "KREN"
              ],
              "VLehrer": [
                "KREN"
              ],
              "Raeume": [
                "211"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 21107,
              "Ak_UntNr": 56,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "6c"
              ],
              "VKlassen": [
                "6c"
              ],
              "Lehrer": [
                "HARM"
              ],
              "VLehrer": [
                "HARM"
              ],
              "Raeume": [
                "119"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21106,
              "Ak_UntNr": 57,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "6c"
              ],
              "VKlassen": [
                "6c"
              ],
              "Lehrer": [
                "GÜTT"
              ],
              "VLehrer": [
                "GÜTT"
              ],
              "Raeume": [
                "006"
              ],
              "VRaeume": [
                "006"
              ]
            },
            {
              "Ak_Id": 21166,
              "Ak_UntNr": 112,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "EN",
              "Ak_VFach": "EN",
              "Klassen": [
                "8a"
              ],
              "VKlassen": [
                "8a"
              ],
              "Lehrer": [
                "ASSM"
              ],
              "VLehrer": [
                "SCHÖ"
              ],
              "Raeume": [
                "102"
              ],
              "VRaeume": [
                "102"
              ],
              "InfoK": "Aufsicht Aufgaben"
            },
            {
              "Ak_Id": 18856,
              "Ak_UntNr": 184,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_Fach": "DE",
              "Ak_VFach": "GEO",
              "Klassen": [
                "9c"
              ],
              "VKlassen": [
                "9c"
              ],
              "Lehrer": [
                "STÜLP"
              ],
              "VLehrer": [
                "HAMM"
              ],
              "Raeume": [
                "118"
              ],
              "VRaeume": [
                "205"
              ],
              "InfoK": "verlegt von 2.St. nach 5.St."
            },
            {
              "Ak_Id": 19260,
              "Ak_UntNr": 311,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Ak_Kurs": "Ma1",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "REU"
              ],
              "Raeume": [
                "115"
              ]
            },
            {
              "Ak_Id": 19261,
              "Ak_UntNr": 312,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "MA",
              "Ak_Kurs": "Ma2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "KEHR"
              ],
              "Raeume": [
                "205"
              ]
            },
            {
              "Ak_Id": 19258,
              "Ak_UntNr": 309,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Ak_Kurs": "D1",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "MEHL"
              ],
              "Raeume": [
                "208"
              ]
            },
            {
              "Ak_Id": 19259,
              "Ak_UntNr": 310,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 5,
              "Ak_StundenAnz": 2,
              "Ak_Fach": "DE",
              "Ak_Kurs": "D2",
              "Klassen": [
                "12"
              ],
              "Lehrer": [
                "BUSS"
              ],
              "Raeume": [
                "108"
              ]
            },
            {
              "Ak_Id": 21097,
              "Ak_UntNr": 28,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "GEO",
              "Ak_VFach": "MA",
              "Klassen": [
                "6a"
              ],
              "VKlassen": [
                "6a"
              ],
              "Lehrer": [
                "HAMM"
              ],
              "VLehrer": [
                "?w"
              ],
              "Raeume": [
                "313"
              ],
              "InfoK": "MA Frau Reuther Ausfall mit Aufgaben (Aufgaben beenden), MA verlegt von 3.St. nach 6.St."
            },
            {
              "Ak_Id": 21113,
              "Ak_UntNr": 63,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "EN",
              "Ak_VFach": "EN",
              "Klassen": [
                "6c"
              ],
              "VKlassen": [
                "6c"
              ],
              "Lehrer": [
                "KREY"
              ],
              "VLehrer": [
                "KREY"
              ],
              "Raeume": [
                "006"
              ],
              "VRaeume": [
                "208"
              ]
            },
            {
              "Ak_Id": 21114,
              "Ak_UntNr": 71,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "REe",
              "Ak_VFach": "REe",
              "Klassen": [
                "7a"
              ],
              "VKlassen": [
                "7a"
              ],
              "Lehrer": [
                "ADAM"
              ],
              "VLehrer": [
                "ADAM"
              ],
              "Raeume": [
                "119"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 21115,
              "Ak_UntNr": 72,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "ETH",
              "Ak_VFach": "ETH",
              "Klassen": [
                "7a"
              ],
              "VKlassen": [
                "7a"
              ],
              "Lehrer": [
                "GRUN"
              ],
              "VLehrer": [
                "GRUN"
              ],
              "Raeume": [
                "117"
              ],
              "VRaeume": [
                "118"
              ]
            },
            {
              "Ak_Id": 21167,
              "Ak_UntNr": 112,
              "Ak_Art": "Ausf.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "EN",
              "Klassen": [
                "8a"
              ],
              "Lehrer": [
                "ASSM"
              ],
              "Raeume": [
                "102"
              ]
            },
            {
              "Ak_Id": 21164,
              "Ak_UntNr": 182,
              "Ak_Art": "Verl.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_DatumNach": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_StundeNach": 1,
              "Ak_Fach": "INF",
              "Ak_VFach": "INF",
              "Klassen": [
                "9b"
              ],
              "Lehrer": [
                "KOLD"
              ],
              "VLehrer": [
                "KOLD"
              ],
              "Raeume": [
                "007"
              ],
              "VRaeume": [
                "007"
              ]
            },
            {
              "Ak_Id": 21105,
              "Ak_UntNr": 184,
              "Ak_Art": "Änd.",
              "Ak_DatumVon": "06.05.2026",
              "Ak_StundeVon": 6,
              "Ak_Fach": "DE",
              "Ak_VFach": "DE",
              "Klassen": [
                "9c"
              ],
              "VKlassen": [
                "9c"
              ],
              "Lehrer": [
                "STÜLP"
              ],
              "VLehrer": [
                "STÜLP"
              ],
              "Raeume": [
                "118"
              ],
              "VRaeume": [
                "205"
              ]
            }
          ],
        }
      ]
    }
  }
}';
}
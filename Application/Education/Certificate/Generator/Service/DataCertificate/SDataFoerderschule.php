<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;

class SDataFoerderschule
{

    public static function setCertificateStandard(Data $Data)
    {

        //ToDO Funktionen anpassen
        self::setFoesHjInfoGeistigeEntwicklung($Data);
        self::setFoesHjGeistigeEntwicklung($Data);
        self::setFoesJGeistigeEntwicklung($Data);
        self::setFoesAbgGeistigeEntwicklung($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setFoesHjInfoGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahresinformation', 'geistige Entwicklung', 'FoesHjInfoGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, true, false);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesHjGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahreszeugnis', 'geistige Entwicklung', 'FoesHjGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, false, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesJGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Jahreszeugnis', 'geistige Entwicklung', 'FoesJGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, false, false);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesAbgGeistigeEntwicklung(Data $Data)
    {

        $Data->createCertificate('Förderschule Abgangszeugnis', 'geistige Entwicklung', 'FoesAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeFoerderSchule());
        // auf dem Zeugnis befinden sich keine Fächer
    }
}
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
        self::setFoesAbsGeistigeEntwicklung($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setFoesHjInfoGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahresinformation', 'geistige Entwicklung', 'FoesHjInfoGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeFoerderSchule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, true, false);
            }
            $Data->updateCertificateNumber($tblCertificate, '2.11');
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
            if ($Data->getTblSchoolTypeFoerderSchule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, false, true);
            }
            $Data->updateCertificateNumber($tblCertificate, '2.11');
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
            if ($Data->getTblSchoolTypeFoerderSchule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeFoerderSchule(),
                    null, false, false);
            }
            $Data->updateCertificateNumber($tblCertificate, '2.11');
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesAbgGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Abgangszeugnis', 'geistige Entwicklung', 'FoesAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeFoerderSchule(), null, false, );
        $Data->updateCertificateNumber($tblCertificate, '2.12');
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setFoesAbsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Abschlusszeugnis', 'geistige Entwicklung', 'FoesAbsGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeFoerderSchule());
        $Data->updateCertificateNumber($tblCertificate, '2.13');
        // auf dem Zeugnis befinden sich keine Fächer
    }
}
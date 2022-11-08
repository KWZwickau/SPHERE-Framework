<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataFoerder
{

    public static function setCertificateStandard(Data $Data)
    {

        //ToDO Funktionen anpassen
        self::setMsHjInfoFsGeistigeEntwicklung($Data);
        self::setMsHjFsGeistigeEntwicklung($Data);
        self::setMsJFsGeistigeEntwicklung($Data);
        self::setMsAbgGeistigeEntwicklung($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahresinformation', 'geistige Entwicklung', 'FoesHjInfoGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Halbjahreszeugnis', 'geistige Entwicklung', 'FoesHjGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setMsJFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Förderschule Jahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'FoesJInfoGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
        // auf dem Zeugnis befinden sich keine Fächer
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbgGeistigeEntwicklung(Data $Data)
    {

        $Data->createCertificate('Förderschule Abgangszeugnis', 'geistige Entwicklung', 'MsAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        // auf dem Zeugnis befinden sich keine Fächer
    }
}
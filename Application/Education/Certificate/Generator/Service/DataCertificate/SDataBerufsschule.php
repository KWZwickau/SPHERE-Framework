<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataBerufsschule
{

    public static function setCertificateStandard(Data $Data)
    {

        self::setBsHj($Data);
    }

    private static function setBsHj(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Berufsschule Halbjahresinformation', '',
            'BsHj');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsschule(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }
    }
}
<?php
namespace SPHERE\Application\Api\Education\Certificate;

use SPHERE\Application\Api\Education\Certificate\Generator\Creator;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Certificate extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Generator/Create', __NAMESPACE__.'\Generator\Creator::createPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/Preview', __NAMESPACE__ . '\Generator\Creator::previewPdf'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public static function createPdf(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
            if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                if (class_exists($CertificateClass)) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass(false);

                    // get Content
                    $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);

                    $File = Creator::buildDummyFile($Certificate, $Content);

                    $FileName = "Zeugnis " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                    // Revisionssicher speichern
                    if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                        if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivision, $Certificate,
                            $File)
                        ) {
                            Prepare::useService()->updatePrepareStudentSetPrinted($tblPrepareStudent);
                        }
                    }

                   return Creator::buildDownloadFile($File, $FileName);
                }
            }
        }

        return false;
    }

}

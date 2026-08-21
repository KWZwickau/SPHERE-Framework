<?php
namespace SPHERE\Application\Education\Certificate;

use SPHERE\Application\Api\Education\Certificate\PrintCertificate\ApiPrintCertificate;
use SPHERE\Application\Education\Certificate\Approve\Approve;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\GradeInformation\GradeInformation;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\PrintCertificate\PrintCertificate;
use SPHERE\Application\Education\Certificate\Reporting\Reporting;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Certificate
 *
 * @package SPHERE\Application\Education\Certificate
 */
class Certificate implements IApplicationInterface
{

    public static function registerApplication(): void
    {

        Setting::registerModule();
        Generate::registerModule();
        Prepare::registerModule();
        Approve::registerModule();
        PrintCertificate::registerModule();
        Reporting::registerModule();
        GradeInformation::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Download', __CLASS__.'::frontendDownload'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Zeugnisse');

        return $Stage;
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param string $Name
     * @param string $IsPreview
     *
     * @return Stage
     */
    public static function frontendDownload($PrepareId = null, $DivisionId = null, string $Name = 'Musterzeugnisse', string $IsPreview = 'true'): Stage
    {
        $stage = new Stage('Zeugnisse werden erstellt', 'Bitte warten..');

        $IsPreview = $IsPreview == 'true';
        $isAutomaticallyApproved = false;

        // normale Zeugnisse
        if ($PrepareId && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
        ) {
            if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())) {
                $Name .= ' ' . $tblDivisionCourse->getName();
            }

            if (!$IsPreview) {
                if (($tblCertificateType = $tblPrepare->getCertificateType())
                    && $tblCertificateType->isAutomaticallyApproved()
                ) {
                    $isAutomaticallyApproved = true;
                }
            }

            $prepareStudents = [];
            $filePointerList = [];
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if ($tblPrepareStudent->getServiceTblCertificate()
                    && ($IsPreview ||
                        (!$tblPrepareStudent->isPrinted() && ($tblPrepareStudent->isApproved() || $isAutomaticallyApproved)))
                ) {
                    $prepareStudents[$tblPrepareStudent->getId()] = 1;
                    $filePointerList[$tblPrepareStudent->getId()] = null;
                }
            }

            $prepareStudentId = array_key_first($prepareStudents);
            $stage->setContent(
                ApiPrintCertificate::receiverBlock(
                    ApiPrintCertificate::pipelineLoadCertificate(
                        $prepareStudentId, $prepareStudents, json_encode($filePointerList), $Name, $IsPreview ? 'PREPARE_STUDENT_PREVIEW' : 'PREPARE_STUDENT_DOWNLOAD'
                    ),
                    'Content_' . $prepareStudentId
                )
            );
        // Abgangszeugnisse
        } elseif ($DivisionId && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))
        ) {
            $Name .= ' ' . $tblDivisionCourse->getName();

            if (($tblCertificateTypeLeave = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
                && $tblCertificateTypeLeave->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            }

            $leaveStudents = [];
            $filePointerList = [];
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if ($tblLeaveStudent->getServiceTblPerson()
                    && $tblLeaveStudent->getServiceTblCertificate()
                    && ($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                    && ($tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId())
                    && ($IsPreview ||
                        (!$tblLeaveStudent->isPrinted() && ($tblLeaveStudent->isApproved() || $isAutomaticallyApproved)))
                ) {
                    $leaveStudents[$tblLeaveStudent->getId()] = 1;
                    $filePointerList[$tblLeaveStudent->getId()] = null;
                }
            }

            $leaveStudentId = array_key_first($leaveStudents);
            $stage->setContent(
                ApiPrintCertificate::receiverBlock(
                    ApiPrintCertificate::pipelineLoadCertificate(
                        $leaveStudentId, $leaveStudents, json_encode($filePointerList), $Name, $IsPreview ? 'LEAVE_STUDENT_PREVIEW' : 'LEAVE_STUDENT_DOWNLOAD'
                    ),
                    'Content_' . $leaveStudentId
                )
            );
        } else {
            $stage->setContent(
                new Warning('Keine Zeugnisse vorhanden.', new Exclamation())
            );
        }

        return $stage;
    }
}

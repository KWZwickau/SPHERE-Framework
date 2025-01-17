<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:41
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Prepare
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Prepare implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse vorbereiten'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Teacher', __NAMESPACE__ . '\Frontend::frontendTeacherSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Headmaster', __NAMESPACE__ . '\Frontend::frontendHeadmasterSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Diploma', __NAMESPACE__ . '\Frontend::frontendDiplomaSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Leave', __NAMESPACE__ . '\Frontend::frontendLeaveSelectStudent')
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare' , __NAMESPACE__ . '\Frontend::frontendPrepare')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Setting' , __NAMESPACE__ . '\Frontend::frontendPrepareSetting')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Setting' , __NAMESPACE__ . '\Frontend::frontendPrepareDiplomaSetting')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Technical\Setting' , __NAMESPACE__ . '\Frontend::frontendPrepareDiplomaTechnicalSetting')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Abitur\Preview' , __NAMESPACE__ . '\Abitur\Frontend::frontendPrepareDiplomaAbiturPreview')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Abitur\BlockI' , __NAMESPACE__ . '\Abitur\Frontend::frontendPrepareDiplomaAbiturBlockI')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Abitur\BlockII' , __NAMESPACE__ . '\Abitur\Frontend::frontendPrepareDiplomaAbiturBlockII')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Abitur\LevelTen' , __NAMESPACE__ . '\Abitur\Frontend::frontendPrepareDiplomaAbiturLevelTen')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Abitur\LevelTen\SetAll' , __NAMESPACE__ . '\Abitur\Frontend::frontendPrepareDiplomaAbiturLevelTenSetAll')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Diploma\Abitur\OtherInformation' , __NAMESPACE__ . '\Abitur\Frontend::frontendPrepareDiplomaAbiturOtherInformation')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Preview', __NAMESPACE__ . '\Frontend::frontendPreparePreview')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Preview\SubjectGrades', __NAMESPACE__ . '\Frontend::frontendPrepareShowSubjectGrades')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Certificate\Show', __NAMESPACE__ . '\Frontend::frontendShowCertificate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Signer', __NAMESPACE__ . '\Frontend::frontendSigner')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\DroppedSubjects', __NAMESPACE__ . '\Frontend::frontendDroppedSubjects')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\DroppedSubjects\Destroy', __NAMESPACE__ . '\Frontend::frontendDestroyDroppedSubjects')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Leave\Student', __NAMESPACE__ . '\Frontend::frontendLeaveStudentTemplate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Leave\Student\Abitur\Points', __NAMESPACE__ . '\Frontend::frontendLeaveStudentAbiturPoints')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Leave\Student\Abitur\Information', __NAMESPACE__ . '\Frontend::frontendLeaveStudentAbiturInformation')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Leave\Student\Abitur\LevelEleven', __NAMESPACE__ . '\Frontend::frontendLeaveStudentAbiturLevelEleven')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\SetIsPrepared', __NAMESPACE__ . '\Frontend::frontendSetIsPrepared')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
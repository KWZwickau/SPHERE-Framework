<?php

namespace SPHERE\Application\Education\Competence;

use SPHERE\Application\Education\Competence\Skill\Skill;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Competence implements IApplicationInterface
{

    public static function registerApplication(): void
    {
        Skill::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kompetenzen'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard(): Stage
    {
        return new Stage('Dashboard', 'Kompetenzen');
    }
}
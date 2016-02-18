<?php
namespace SPHERE\Application\Platform\Roadmap;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Roadmap\Youtrack\Credentials;
use SPHERE\Application\Platform\Roadmap\Youtrack\Issue;
use SPHERE\Application\Platform\Roadmap\Youtrack\Parser;
use SPHERE\Application\Platform\Roadmap\Youtrack\Sprint;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Badge;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Roadmap extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Roadmap'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
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

    public function frontendDashboard()
    {

        $Stage = new Stage('KREDA', 'Roadmap');

        $Parser = new Parser(
            new Credentials(),
            'Typ: Feature,Bug,Aufgabe Teilsystem: {10*},{2*} Status: Erfasst,Offen,{In Bearbeitung},Behoben,{Zu besprechen}'
        );

        try {
            $Map = $Parser->getMap();
        } catch (\Exception $Exception) {
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(new Warning('Roadmap konnte nicht abgerufen werden'))
            ))));
            return $Stage;
        }

        $Sprints = $Map->getSprints();

        $PriorityColor = array(
            'Kritisch' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
            'Hoch'     => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
            'Normal'   => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info',
            'Niedrig'  => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
        );

        $StateColor = array(
            'Erfasst'        => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info',
            'Offen'          => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
            'In Bearbeitung' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
            'Behoben'        => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
            'Zu besprechen'  => '\SPHERE\Common\Frontend\Layout\Repository\Label'
        );

        $TypeColor = array(
            'Bug'     => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
            'Feature' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
            'Aufgabe' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning'
        );

        $SubsystemColor = array(
            '0' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
            '1' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
            '2' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info'
        );

        $LayoutColumns = array();
        /** @var Sprint $Sprint */
        foreach ((array)$Sprints as $Sprint) {

            $SprintComplete = true;
            $IssueList = array();
            $ResolvedList = array();
            $Issues = $Sprint->getIssues();
            /** @var Issue $Issue */
            foreach ((array)$Issues as $Issue) {
                $ColumnList = array();
                if ($Issue->getState() == 'Behoben') {

                    $Description = explode("\n", $Issue->getDescription());
                    array_walk( $Description, function(&$Line){
                        if( empty( $Line ) ) {
                            $Line = false;
                        }
                        if( strpos( $Line, '@' ) === 0 ) {
                            $Line = false;
                        }
                    } );
                    $Description = array_filter( $Description );

                    $ColumnList[] = new LayoutColumn(
                        new Panel(new Label($Issue->getId())

                    .( isset( $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)] )
                        ? new $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)]($Issue->getSubsystem())
                        : $Issue->getSubsystem()
                    )
                    .( isset( $TypeColor[$Issue->getType()] )
                        ? new $TypeColor[$Issue->getType()]($Issue->getType())
                        : $Issue->getType()
                    )


                            .'&nbsp;&nbsp;&nbsp;'.$Issue->getTitle(), new Small(
                                nl2br(trim(implode("\n",
                                    array_slice($Description, 0, 3)
                                )))
                                .' '.( count( $Description ) > 3 ? '[...]' : '')
                            )
                            , Panel::PANEL_TYPE_SUCCESS
                        ));

                    $ResolvedList = array_merge($ResolvedList, $ColumnList);
                } else {
                    $SprintComplete = false;

                    if ($Issue->getState() == 'In Bearbeitung') {
                        $ProgressBar = new ProgressBar($Issue->getTimePercent(), 100 - $Issue->getTimePercent(), 100);
                    } else {
                        $ProgressBar = new ProgressBar($Issue->getTimePercent(), 0, 100);
                    }

                    $ColumnList[] = new LayoutColumn(array(
                        new Panel('#'.$Issue->getId().': '.$Issue->getTitle().' '.$ProgressBar,
                            ( strlen(trim( $Issue->getDescription() )) == 0 ? '' :
                            new Small(nl2br($Issue->getDescription()))),
                            Panel::PANEL_TYPE_INFO, new PullClear(
                                new PullLeft(
                                    ( isset( $PriorityColor[$Issue->getPriority()] )
                                        ? new $PriorityColor[$Issue->getPriority()]($Issue->getPriority())
                                        : $Issue->getPriority()
                                    )
                                ).
                                new PullLeft(
                                    ( isset( $StateColor[$Issue->getState()] )
                                        ? new $StateColor[$Issue->getState()]($Issue->getState())
                                        : $Issue->getState()
                                    )
                                ).
                                new PullLeft(
                                    ( isset( $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)] )
                                        ? new $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)]
                                        ($Issue->getSubsystem())
                                        : $Issue->getSubsystem()
                                    )
                                ).
                                new PullLeft(
                                    ( isset( $TypeColor[$Issue->getType()] )
                                        ? new $TypeColor[$Issue->getType()]($Issue->getType())
                                        : $Issue->getType()
                                    )
                                ).
                                new PullRight(
                                    new External($Issue->getId(),
                                        'https://ticket.swe.haus-der-edv.de/issue/'.$Issue->getId(), null, array(),
                                        false
                                    )
                                )
                            )
                        )
                    ), 11);

                    $ColumnList[] = new LayoutColumn('', 1);

                    $IssueList = array_merge($IssueList, $ColumnList);
                }
            }

            if ($SprintComplete) {
                $SprintList = new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Success('Keine offenen Aufgaben')
                            , 4),
                        new LayoutColumn(
                            new Layout(new LayoutGroup(new LayoutRow($ResolvedList)))
                            , 8),
                    )),
                )));
            } else {
                $SprintList = new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Layout(new LayoutGroup(new LayoutRow($IssueList), new Title('Offen')))
                            , 4),
                        new LayoutColumn(
                            new Layout(new LayoutGroup(new LayoutRow($ResolvedList), new Title('Behoben')))
                            , 8),
                    )),
                )));
            }

            $LayoutColumns[] = new LayoutColumn(
                new Panel(
                    new Info(
                        new PullClear(
                            new PullLeft(
                                ( $SprintComplete ? 'Änderungen in ' : 'Version ' ).$Sprint->getVersion()
                            )
                            .new PullRight(
                                new Label('Freigabe Demo @ '.date('m / Y',
                                        strtotime($Sprint->getTimestampFinish().' +1 day')),
                                    Label::LABEL_TYPE_WARNING)
                                .new Label('Freigabe Live @ '.date('m / Y',
                                        strtotime($Sprint->getTimestampFinish().' +2 month')),
                                    Label::LABEL_TYPE_PRIMARY)
                            )
                        )
                    )
                    .new ProgressBar($this->getDatePercent($Sprint->getTimestampStart(),
                        $Sprint->getTimestampFinish()), 0, 100),
                    (string)$SprintList, ( $SprintComplete ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING ),
                    new Muted(new Small($Sprint->getTimestampStart().' - '.$Sprint->getTimestampFinish()))
                )
            );
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow($LayoutColumns))));

        return $Stage;
    }

    /**
     * @param $Start
     * @param $Finish
     *
     * @return float|int
     */
    private function getDatePercent($Start, $Finish)
    {

        $Start = strtotime($Start);
        $Finish = strtotime($Finish);
        $Now = time();

        if ($Finish - $Start <= 0 || $Now >= $Finish) {
            $Percent = 100;
        } else {
            $Percent = ( $Now - $Start ) / ( $Finish - $Start ) * 100;
        }

        return number_format($Percent, 2, ',', '.');
    }
}

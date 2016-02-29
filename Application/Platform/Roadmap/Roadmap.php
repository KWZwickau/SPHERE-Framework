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
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Roadmap implements IApplicationInterface, IModuleInterface
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

        $Stage = new Stage('KREDA Roadmap');

        try {
            $Map = $this->getRoadmap();
        } catch (\Exception $Exception) {
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(new Warning('Roadmap konnte nicht abgerufen werden'))
            ))));
            return $Stage;
        }

        $Stage->setMessage(
            'Aktuelle Versionen: <br/>'.
            new Label('Preview: '.$Map->getVersionPreview(), Label::LABEL_TYPE_WARNING)
            .' '.
            new Label('Release: '.$Map->getVersionRelease(), Label::LABEL_TYPE_PRIMARY)
        );

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
            'Zu besprechen'  => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger'
        );

        $TypeColor = array(
            'Bug'     => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
            'Feature' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
            'Aufgabe' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning'
        );

        $SubsystemColor = array(
            '0' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
            '1' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info',
            '2' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info'
        );

        $SprintCurrent = null;
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

                    $Description = $this->sanitizeDescription($Issue->getDescription(), 0);
                    $Title = $this->sanitizeTitle($Issue->getTitle());

                    $ColumnList[] = new LayoutColumn(
                        new Panel(

                            new Small($Title), $Description,
                            Panel::PANEL_TYPE_SUCCESS,
                            new Label($Issue->getId()).( isset( $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)] )
                                ? new $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)]($Issue->getSubsystem())
                                : $Issue->getSubsystem()
                            )
                            .( isset( $TypeColor[$Issue->getType()] )
                                ? new $TypeColor[$Issue->getType()]($Issue->getType())
                                : $Issue->getType()
                            )

                        ), 3);

                    $ResolvedList = array_merge($ResolvedList, $ColumnList);
                } else {
                    $SprintComplete = false;
                    if (!$SprintCurrent) {
                        $SprintCurrent = $Sprint->getVersion();
                    }

                    if ($Issue->getState() == 'In Bearbeitung') {
                        $ProgressBar = new ProgressBar($Issue->getTimePercent(), 100 - $Issue->getTimePercent(), 100);
                    } else {
                        $ProgressBar = new ProgressBar($Issue->getTimePercent(), 0, 100);
                    }

                    $Title = $this->sanitizeTitle($Issue->getTitle());
                    $Description = $this->sanitizeDescription($Issue->getDescription(), 6);

                    $ColumnList[] = new LayoutColumn(array(
                        new Panel($Title.' '.$ProgressBar,
                            ( strlen($Description) == 0 ? '' : $Description ),
                            Panel::PANEL_TYPE_INFO,
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new PullClear(
                                                new PullLeft(
                                                    new Label($Issue->getId())
                                                ).
                                                new PullLeft(
                                                    ( isset( $PriorityColor[$Issue->getPriority()] )
                                                        ? new $PriorityColor[$Issue->getPriority()]($Issue->getPriority())
                                                        : $Issue->getPriority()
                                                    )
                                                )
                                                .
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
                                                )
                                            )
                                        ))
                                    ))
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
                            new Layout(new LayoutGroup(new LayoutRow($ResolvedList)))
                        ),
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

            if ($SprintComplete) {
                $VersionHeader = new Success(new PullClear(
                    new PullLeft('Änderungen in '.$Sprint->getVersion())
                    .new PullRight(
                        new Label('Freigabe Demo @ '.date('m / Y',
                                strtotime($Sprint->getTimestampFinish().' +1 day')),
                            Label::LABEL_TYPE_WARNING)
                        .new Label('Freigabe Live @ '.date('m / Y',
                                strtotime($Sprint->getTimestampFinish().' +2 month')),
                            Label::LABEL_TYPE_PRIMARY)
                    )
                ));
                if (!$Sprint->isDone()) {
                    $SprintPercent = $this->getDatePercent($Sprint->getTimestampStart(), $Sprint->getTimestampFinish());
                    $VersionHeader .= new ProgressBar($SprintPercent, 100 - $SprintPercent, 100);
                }
                $VersionFooter = null;
            } else {
                $SprintColor = ( $Sprint->getVersion() == $SprintCurrent
                    ? '\SPHERE\Common\Frontend\Message\Repository\Info'
                    : '\SPHERE\Common\Frontend\Message\Repository\Warning'
                );

                $VersionHeader = new $SprintColor(new PullClear(
                    new PullLeft('Version '.$Sprint->getVersion())
                    .new PullRight(
                        new Label('Freigabe Demo @ '.date('m / Y',
                                strtotime($Sprint->getTimestampFinish().' +1 day')),
                            Label::LABEL_TYPE_WARNING)
                        .new Label('Freigabe Live @ '.date('m / Y',
                                strtotime($Sprint->getTimestampFinish().' +2 month')),
                            Label::LABEL_TYPE_PRIMARY)
                    )
                ));
                $VersionHeader .= new ProgressBar($this->getDatePercent($Sprint->getTimestampStart(),
                    $Sprint->getTimestampFinish()), 0, 100);
                $VersionFooter = new Muted(new Small($Sprint->getTimestampStart().' - '.$Sprint->getTimestampFinish()));
            }

            $LayoutColumns[] = new LayoutColumn(
                new Panel(
                    $VersionHeader,
                    (string)$SprintList, ( $SprintComplete ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING ),
                    $VersionFooter
                )
            );
        }

        $Stage->setContent(
            '<style>.panel.panel-success {margin-bottom:0;}</style>'.
            new Layout(new LayoutGroup(new LayoutRow($LayoutColumns)))
        );

        return $Stage;
    }

    /**
     * Get RoadMap-Object
     *
     * @return Youtrack\Map
     * @trows \Exception
     */
    public function getRoadmap()
    {

        $Parser = new Parser(
            new Credentials(),
            'Typ: Feature,Bug,Aufgabe Teilsystem: {10*},{2*} Status: Erfasst,Offen,{In Bearbeitung},Behoben,{Zu besprechen}'
        );
        return $Parser->getMap();
    }

    /**
     * @param string $Value
     * @param int    $MaxLineCount
     *
     * @return string
     */
    private function sanitizeDescription($Value, $MaxLineCount = 3)
    {

        $Value = explode("\n", $Value);
        array_walk($Value, function (&$Line) {

            if (empty( $Line )) {
                $Line = false;
            }
            if (strpos($Line, '@') === 0) {
                $Line = false;
            }
            if (strpos($Line, 'Line:') === 0) {
                $Line = false;
            }
            if (strpos($Line, '!') === 0) {
                $Line = false;
            }
        });
        $Value = array_filter($Value);
        $Value = preg_replace('!\s+?[\-]+\>!is', ': ', $Value);

        $ShortDescription = trim(implode("\n", array_slice($Value, 0, $MaxLineCount)));
        $LongDescription = trim(implode("\n", array_slice($Value, $MaxLineCount)));

        if (strlen($ShortDescription) == 0 && $MaxLineCount > 0) {
            return new Small(new Italic(new Muted('Keine Beschreibung angegeben')));
        }

        if (strlen($LongDescription) > 0) {
            return new Small(nl2br($ShortDescription))
            .(new Accordion())->addItem(
                new Italic(new Small('[mehr anzeigen]')),
                new Small(nl2br($LongDescription))
            )->getContent();
        } else {
            return new Small(nl2br($ShortDescription));
        }
    }

    /**
     * @param $Value
     *
     * @return string
     */
    private function sanitizeTitle($Value)
    {

        $Value = preg_replace('!Account: [0-9]+!is', '[Support-System Report]', $Value);
        $Value = preg_replace('!^Error!is', 'Anwendungsfehler', $Value);
        $Value = preg_replace('!^Shutdown!is', 'Absturz der Anwendung', $Value);
        $Value = preg_replace('!\bBug\b!is', 'Fehler', $Value);
        $Value = preg_replace('!\s+?[\-]+\>!is', ': ', $Value);
        $Value = preg_replace('!\bnull\b!is', 'leer', $Value);
        return $Value;
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

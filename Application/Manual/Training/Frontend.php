<?php

namespace SPHERE\Application\Manual\Training;

use DateTime;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $WelcomePage
     *
     * @return Stage
     */
    public function frontendTraining($WelcomePage = null): Stage
    {
        if ($WelcomePage) {
            $hasWelcomePageMessage = false;
            Consumer::useService()->createAccountSetting('DoNotShowTraining2026OnWelcomePage', (new DateTime('today'))->format('d.m.Y'));
        } else {
            $hasWelcomePageMessage = !Consumer::useService()->getAccountSettingValue('DoNotShowTraining2026OnWelcomePage');
        }

        $stage = new Stage('Veranstaltungen', 'der ESDI GmbH');

        $today = new DateTime('today');

        $contentList[] = [
            'Title' => 'Individuelle Schulungen',
            'Content' => [
                'Zusätzlich zu den unten aufgeführten Veranstaltungen können jederzeit individuelle Schulungen gebucht werden. Dies empfehlen wir insbesondere 
                    bei personellen Wechsel der Verwaltung oder Schulleitung in Ihrer Schule oder bei neuen Lehrern z.B. zum Schuljahresbeginn. Bei Interesse 
                    wenden Sie sich bitte an die ESDi GmbH unter ' . new Mailto(new Envelope() . ' info@esdigmbh.de', 'info@esdigmbh.de'),
            ]
        ];
        if ($today <= new DateTime('15.09.2026')) {
            $contentList[] = [
                'Title' => 'Canva - das Tool für Layout & Gestaltung',
                'Content' => [
                    '15.09.2026 | 16:00 Uhr | Starter - Webinar',
                    'In diesem Grundlagenkurs lernen Sie das Online-Gestaltungstool Canva kennen. Wir zeigen Ihnen, wie Sie mit wenigen Klicks Beiträge, Flyer
                     und Präsentationen für Ihre Öffentlichkeitsarbeit oder Material für den Unterricht erstellen können. Auch ohne Vorkenntnisse in Grafikdesign
                     sind Sie schnell in der Lage ansprechende Layouts zu gestalten.'
                ]
            ];
        }
        if ($today <= new DateTime('06.10.2026')) {
            $contentList[] = [
                'Title' => 'Canva - das Tool für Layout & Gestaltung',
                'Content' => [
                    '06.10.2026 | 16:00 Uhr | Webinar für Fortgeschrittene',
                    'Dieser Kurs richtet sich an Teilnehmende, die bereits erste Erfahrungen mit Canva gesammelt haben. Sie lernen erweiterte Funktionen kennen,
                     z. B. die Nutzung von Vorlagen, Gestaltung von Social-Media-Inhalten im Corporate Design, Teamarbeit in Canva sowie Tipps und Tricks für
                     professionelle Layouts.'
                ]
            ];
        }
        if ($today <= new DateTime('27.10.2026')) {
            $contentList[] = [
                'Title' => 'Instagram 1',
                'Content' => [
                    '27.10.2026 | 16:00 Uhr | Webinar für Einsteiger',
                    'Instagram eignet sich hervorragend, um Einblicke in das Schulleben zu geben und mit verschiedenen Zielgruppen in Kontakt zu treten. Sie
                     lernen den Aufbau eines Instagram-Profils kennen, erhalten eine Einführung in die Gestaltung von Posts, die Planung von Inhalten
                     (Redaktionsplan) und erste Schritte für eine Postingstrategie.'
                ]
            ];
        }
        if ($today <= new DateTime('10.11.2026')) {
            $contentList[] = [
                'Title' => 'Instagram 2',
                'Content' => [
                    '10.11.2026 | 16:00 Uhr | Webinar für Fortgeschrittene',
                    'Dieser Kurs richtet sich an Nutzer mit ersten Erfahrungen auf Instagram. Im Mittelpunkt stehen die Erstellung von Reels, der gezielte
                     Einsatz von Stories sowie Tipps zur Planung und Organisation von Inhalten. Außerdem erhalten Sie praktische Hinweise zur Steigerung der
                     Reichweite und Einbindung der Schulgemeinschaft.'
                ]
            ];
        }

        if(count($contentList) <= 1){
            $contentList[] = [
                'Title' => (new Link('www.ev-schulen-sachsen.de/veranstaltungen-esdi', 'https://www.ev-schulen-sachsen.de/veranstaltungen-esdi'))->setExternal()
                .'</br>&nbsp;',
                'Content' => []
            ];
        }

        $layoutGroups = [];
        foreach ($contentList as $content) {
            $layoutGroups[] = new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn('', 3),
                    new LayoutColumn(
                        new Center(
                            '<h4>'. new Bold($content['Title']) . '</h4>'
                            . new Listing($content['Content'])
                        )
                        , 6)
                ))
            );
        }
        $layoutGroups[] = new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn('', 3),
                new LayoutColumn(
                    (new Success(
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Zur Anmeldung
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'https://www.ev-schulen-sachsen.de/veranstaltungen-esdi'
                    ))->setExternal()
                        . ($hasWelcomePageMessage
                            ? new PullRight(new Standard('Nicht mehr auf der Startseite anzeigen', '/Manual/Training', null, ['WelcomePage' => 'true']))
                            : '')
                    , 6)
            )),
            new LayoutRow(new LayoutColumn('&nbsp;'))
        ));

        $stage->setContent(
            new Layout($layoutGroups)
        );

        return $stage;
    }
}
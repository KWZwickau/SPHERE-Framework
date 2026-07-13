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
        if ($today <= new DateTime('23.04.2026')) {
            $contentList[] = [
                'Title' => 'DLLP - Nutzertreffen',
                'Content' => [
                    '23.04.2026 | 15 Uhr |Online',
                    'Vor fast 2 Jahren ist das DLLP aus einem Projekt in den Regelbetrieb übergegangen. Damit wird es Zeit sich auszutauschen über Erreichtes,
                    Wünsche und Anregungen und mögliche Stolpersteine oder Herausforderungen.'
                ]
            ];
        }
        if ($today <= new DateTime('06.05.2026')) {
            $contentList[] = [
                'Title' => 'Best-Practice Schulsoftware | Verwaltung',
                'Content' => [
                    '06.05.2026 | 10 Uhr | Hybrides Seminar für Verwaltung/Sekretärinnen',
                    new Container('Austausch vor Ort oder als Online - Teilnehmerin')
                    . new Container('Im Seminar werden die Neuentwicklungen in der Schulsoftware vorgestellt und an praktischen Beispielen Anwendungsfälle erläutert.')
                    . new Container('Es ist gedacht für Sekretärinnen und andere Mitarbeitende in der Verwaltung, die neue Funktionen der Schulsoftware kennenlernen und 
                    die Möglichkeiten optimal nutzen möchten.')
                ]
            ];
        }
        if ($today <= new DateTime('21.05.2026')) {
            $contentList[] = [
                'Title' => 'Best-Practice Schulsoftware | Verwaltung',
                'Content' => [
                    '21.05.2026 | 10 Uhr | Zusatztermin - Online',
                    new Container('Im Seminar werden die Neuentwicklungen in der Schulsoftware vorgestellt und an praktischen Beispielen Anwendungsfälle erläutert.')
                    . new Container('Es ist gedacht für Sekretärinnen und andere Mitarbeitende in der Verwaltung, die neue Funktionen der Schulsoftware kennenlernen und 
                    die Möglichkeiten optimal nutzen möchten.')
                ]
            ];
        }
        if ($today <= new DateTime('03.06.2026')) {
            $contentList[] = [
                'Title' => 'Zeugniserstellung in der Schulsoftware',
                'Content' => [
                    '03.06.2026 | 10 Uhr | Webinar für Schulleitungen',
                    'Im Webinar soll der Ablauf der Zeugniserstellung erläutert werden, sodass Sie als Schulleitung den Prozess gut vorbereiten und 
                    Ihre Lehrkräfte sicher begleiten können',
                ]
            ];
        }
        if ($today <= new DateTime('07.07.2026')) {
            $contentList[] = [
                'Title' => 'Schuljahreswechsel in der Schulsoftware',
                'Content' => [
                    '07.07.2026 | 10 Uhr | Webinar für Verwaltung/Schulleitung',
                    'Im Webinar werden die einzelnen Schritte zum Schuljahreswechsel vorgestellt und an praktischen Beispielen erläutert. Außerdem gibt 
                    es die Möglichkeit, Fragen zu stellen und sich über Best-Practice-Erfahrungen auszutauschen.',
                ]
            ];
        }
        if ($today <= new DateTime('08.07.2026')) {
            $contentList[] = [
                'Title' => 'Schuljahreswechsel im DLLP',
                'Content' => [
                    '08.07.2026 | 10 Uhr | Webinar für Schul-Admins',
                    'Im Webinar werden wiederkehrende Aufgaben im DLLP vorgestellt und über neue Entwicklungen berichtet. Außerdem wird es Raum für Fragen 
                    und Austausch geben.',
                ]
            ];
        }
        if (false) { // wurde abgesagt.
            $contentList[] = [
                'Title' => 'Pädagogische Arbeit in der Schulsoftware',
                'Content' => [
                    '11.08.2026 und 13.08.2026 | 11 Uhr | 2-teiliges Webinar für Pädagoginnen und Pädagogen',
                    'An den beiden Webinartagen werden die Prozesse des Schulalltags in der Schulsoftware vorgestellt und an praktischen Beispielen erläutert. 
                    Außerdem werden Weiterentwicklungen in den Blick genommen und Fragen zum Umgang geklärt.',
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
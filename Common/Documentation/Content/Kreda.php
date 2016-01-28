<?php
namespace SPHERE\Common\Documentation\Content;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Documentation\Designer;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Italic;

/**
 * Class Kreda
 *
 * @package SPHERE\Common\Documentation\Content
 */
class Kreda
{

    /** @var Designer\Book $Book */
    private $Book = null;

    /**
     * @param null|string $Chapter
     * @param null|string $Page
     * @param null|string $Search
     */
    public function __construct($Chapter = null, $Page = null, $Search = null)
    {

        $this->ShowChapter = $Chapter;
        $this->ShowPage = $Page;

        $Designer = new Designer();

        $this->Book = $Designer->createBook('KREDA Handbuch');
        $this->Book->setVisible($Chapter, $Page);

        $this->setChapterGrades($Search);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }

    /**
     * @param $Search
     */
    private function setChapterGrades($Search)
    {
        $Chapter = $this->Book->createChapter('Zensuren', '', true);
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

            /*
             * GradeType
             */
            $Page = $Chapter->createPage('Zensuren-Typen', '', $Search, false);
            $Page->addParagraph(
                'Die Zensuren-Typen bestimmen die Arten/Typen von Zensuren in Kreda. Sie werden in die Kategorien '
                . new Italic('Kopfnote') . ' und ' . new Italic('Leistungsüberprüfung') . ' unterteilt. Die Kateogie
                bestimmt, wo die Zensuren-Typen ausgewählt werden können. Die Zensuren-Typen der Kategorie '
                . new Italic('Kopfnote') . ' können nur bei Kopfnotenaufträgen (für Zeugnisse) gewählt werden, hingegen die Zensuren-Typen
                 der Kategorie ' . new Italic('Leistungsüberprüfung') . ' können nur bei Leistungsüberprüfungen gewählt werden.'
            );

            $Page->addHeadline('Wie erstelle Ich einen neuen Zensuren-Typ?', 'Anlegen', true);
            $Page->addParagraph(
                new PullRight(new Standard('Öffnen', '/Education/Graduation/Gradebook/GradeType', null, null, 'Zur Seite wechseln'))
                .  new Bold('Navigation:') . '&nbsp;&nbsp;&nbsp;' . new Italic('Bildung >> Zensuren >> Zensuren-Typ')
                . '<br><br>'
                . new Bold('Beschreibung:')
                . ' Auf der Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen Zensuren-Typen.
                 Im unteren Bereich der Seite kann ein neuer Zensuren-Typ hinzugefügt werden. Dazu wählen Sie bitte eine
                  Kategorie aus, geben eine Abkürzung und einen Namen ein. Außerdem kann optional eine Beschreibung eingeben
                  werden und der Zensuren-Typ Fett markiert werden (für wichtigte Zensuren-Typen z.B.: Klassenarbeiten).'
                . '<br><br>'
                . new Bold('Beispiel:')
                .  new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Thumbnail(
                                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleGradeTypeNew.jpg'),
                                    ''
                                )
                            )
                        )),
                    ))
                )
            );

            $Page->addHeadline('Wie bearbeite Ich einen vorhandenen Zensuren-Typ?', 'Bearbeiten', true);
            $Page->addParagraph(
                new PullRight(new Standard('Öffnen', '/Education/Graduation/Gradebook/GradeType', null, null, 'Zur Seite wechseln'))
                .  new Bold('Navigation:') . '&nbsp;&nbsp;&nbsp;' . new Italic('Bildung >> Zensuren >> Zensuren-Typ')
                . '<br><br>'
                . new Bold('Beschreibung:')
                . ' Auf der Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen Zensuren-Typen.
                 Um einen Zensuren-Typ zu bearbeiten klicken Sie bitte rechts auf den ' . new Edit()
                . ' -Button des entsprechenden Zensuren-Typs.'
                . '<br><br>'
            );

            /*
             * ScoreRule
             */
            $Page = $Chapter->createPage('Berechnungsvorschrift', '', $Search, false);
            $Page->addParagraph(
                ''
            );

            /*
             * Gradebook
             */
            $Page = $Chapter->createPage('Notenbuch', '', $Search, false);
            $Page->addParagraph(
                ' Das ' . new Italic('Notenbuch') . ' dient der Anzeige der Notenbücher (Zensuren-Übersicht der Schüler), wo
                der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist. Hingegen beim '
                . new Italic('Notenbuch (Leitung)') . ' werden alle Notenbücher angezeigt.'
            );

            $Page->addHeadline('Wie wähle Ich ein Notenbuch aus?', 'Auswählen', true);
            $Page->addParagraph(
                new PullRight(new Standard('Öffnen', '/Education/Graduation/Gradebook/Gradebook', null, null, 'Zur Seite wechseln'))
                .  new Bold('Navigation:') . '&nbsp;&nbsp;&nbsp;' . new Italic('Bildung >> Zensuren >> Notenbuch')
                . '<br><br>'
                . new Bold('Beschreibung:')
                . ' Auf der Auswahl-Seite befindet sich eine Übersicht über die Fach-Klassen(-Gruppen).
                 Um ein Notenbuch zu öffnen klicken Sie bitte rechts auf den ' . new Select()
                . ' -Button der entsprechenden Fach-Klasse.'
                . '<br><br>'
            );

            $Page->addHeadline('Welche Informationen werden im Notenbuch angezeigt?', 'Anzeigen', true);
            $Page->addParagraph(
                new Bold('Beschreibung:')
                . ' Links oben wird die ausgewählte Fach-Klasse (und falls vorhanden die Gruppe) angezeigt.
                 Wenn eine Berechnungsvorschrift bei der Fach-Klasse hinterlegt ist, wird diese rechts oben angezeigt.
                   Als erstes wird bei der Berechnungsvorschrift der Name, gefolgt von den Berechnungsvarianten (beginnend
                    mit der höchsten Priorität [kleinste Zahl]), angezeigt. Unterhalb der Fach-Klasse und der
                    Berechnungsvorschrift befindet sich die Zensuren-Übersicht. Bei dieser werden links alle Schüler der
                     Fach-Klasse(-Gruppe) dargestellt. Zusätzlich wird der Zensuren-Durchschnitt des Schülers für das
                     gesamte Schuljahr, und in Klammern die Priorität der verwendenten Berechnungsvariante, angezeigt.
                     Neben den Schülern werden alle Zeiträume des Schuljahres mit den Zensuren der Schüler in diesem Zeitraum dargestellt.
                     Dazu wird über den Zensuren das Datum und die Abkürzung der zugehörigen Leistungsüberprüfung angezeigt.
                     Zum Abschluss eines jeden Zeitraums wird der Zensuren-Durchschnitt des Schülers für diesen
                     Zeitraum, und in Klammern die Priorität der verwendenten Berechnungsvariante, angezeigt. '
                . '<br><br>'
                . new Bold('Beispiel:')
                .  new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Thumbnail(
                                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleGradebookView.jpg'),
                                    ''
                                )
                            )
                        )),
                    ))
                )
            );


        }
    }
}

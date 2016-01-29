<?php
namespace SPHERE\Common\Documentation\Content;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Documentation\Designer;
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

        $this->setChapterGrades($Chapter, $Page, $Search);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }

    /**
     * @param $Chapter
     * @param $Page
     * @param $Search
     */
    private function setChapterGrades($Chapter, $Page, $Search)
    {
        $Chapter = $this->Book->createChapter('Zensuren', '', true);
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

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
                . new Bold('Bespiel:')
                .  new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Thumbnail(
                                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleGradeTypeNew.JPG'),
                                    ''
                                )
                            )
                        )),
                    ))
                )
            );

            $Page->addHeadline('Wie bearbeite Ich einen vorhandenen Zensuren-Typ?', 'Bearbeiten', true);


        }
    }
}

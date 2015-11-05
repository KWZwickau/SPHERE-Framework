<?php
namespace SPHERE\Common\Documentation\Content;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Documentation\Designer;
use SPHERE\Common\Documentation\Designer\Book;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Small;

/**
 * Class StyleBook
 *
 * @package SPHERE\Common\Documentation\Content
 */
class StyleBook
{

    /** @var Book $Book */
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

        $this->Book = $Designer->createBook('Styleguide & Cookbook');
        $this->Book->setVisible($Chapter, $Page);

        $Chapter = $this->Book->createChapter('Struktur', 'von Cluster bis Service');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

            $Page = $Chapter->createPage('1. Cluster', 'Strukturen & Namen', $Search);
            $Page->addHeadline('1.1. Allgemein');
            $Page->addParagraph(new Small(new Danger('Der CLUSTER bildet immer die Basis für eine (oder mehrere) APPLICATION und liegt direkt im Anwendungsverzeichnis')));
            $Page->addSeparator();

            $Page->addParagraph('Im folgenden wird');
            $Page->addCode('/{ROOT}/');
            $Page->addParagraph('als Platzhalter für die Installationsbasis verwendet (z.B: /var/www, /htdocs, etc...)');

            $Page->addHeadline('1.2. Verzeichnisstruktur');
            $Page->addParagraph(new Small(new Danger('Reservierte Php-Schlüsselwörter DÜRFEN NICHT als Verzeichnisnamen verwendet werden')));
            $Page->addSeparator();

            $Page->addParagraph('Alle CLUSTER liegen im Anwendungspfad von SPHERE');
            $Page->addCode('/{ROOT}/Application/');

            $Page->addParagraph('in einem Unterverzeichnis, welches den CLUSTER eineindeutig kennnzeichnet z.B:');
            $Page->addCode('/{ROOT}/Application/ClusterName/');

            $Page->addParagraph('Dieses Unterverzeichnis enthält eine Php-Datei mit identischem Namen');
            $Page->addCode('/{ROOT}/Application/ClusterName/ClusterName.php');

            $Page->addHeadline('1.3. Dateistruktur');
            $Page->addParagraph(new Small(new Danger('Der NAMESPACE in einer Datei/Klasse MUSS IMMER der Verzeichnisstruktur entsprechen!')));
            $Page->addSeparator();

            $Page->addParagraph('In dieser Datei wird die "Cluster-Klasse" mit identischem Namen definiert, z.B:');
            $Page->addCode(array(
                'namespace SPHERE\Application\ClusterName;',
                '',
                'class ClusterName',
                '{',
                '}'
            ),
                'Man kann sehen, daß reservierte Schlüsselworte (von Php) nicht als Cluster-Name verwendet werden können');
            $Page->addParagraph('Die Klasse MUSS IMMER das Cluster-Interface implementieren');
            $Page->addCode(array(
                'namespace SPHERE\Application\ClusterName;',
                '',
                'use SPHERE\Application\IClusterInterface;',
                '',
                'class ClusterName implements IClusterInterface',
                '{',
                "\t".'public static function registerCluster()',
                "\t".'{',
                "\t".'}',
                '}'
            ), 'Der Cluster enthält für gewöhnlich nur diese eine Methode');

            $Page->addHeadline('1.4. Registrieren von Anwendungen');
            $Page->addSeparator();
            $Page->addCode(array(
                'namespace SPHERE\Application\ClusterName;',
                '',
                'use SPHERE\Application\IClusterInterface;',
                'use SPHERE\Application\ClusterName\ApplicationName\ApplicationName;',
                '',
                'class ClusterName implements IClusterInterface',
                '{',
                "\t".'public static function registerCluster()',
                "\t".'{',
                "\t\t".'ApplicationName::registerApplication();',
                "\t".'}',
                '}'
            ), 'Code-Beispiel');

            $Page->addHeadline('1.5. Registrieren von Menüpunkten');
            $Page->addSeparator();
            $Page->addCode('// TODO', 'Code-Beispiel');

            $Page = $Chapter->createPage('2. Application', 'Strukturen & Namen', $Search);
            $Page->addHeadline('2.1. Allgemein');
            $Page->addParagraph(new Small(new Danger('Die APPLICATION bildet immer die Basis für ein (oder mehrere) MODULE und liegt im Cluster-Verzeichnis')));
            $Page->addSeparator();
        }

        $Chapter = $this->Book->createChapter('Names-Konventionen', 'Frontend');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
        }

        $Chapter = $this->Book->createChapter('Names-Konventionen', 'Backend');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
            $Page = $Chapter->createPage('Entity', 'Konstanten / Attribute / Methoden', $Search);

            $Page->addHeadline('Beispiel', 'Quelltext');
            $Page->addSeparator();
            $File = FileSystem::getFileLoader('Common/Documentation/Content/StyleBook/Entity.txt');
            $Page->addCode(explode("\n",
                preg_replace("![\n]+!is", "\n", trim(file_get_contents($File->getRealPath()), "\n"))
            ));

            $Page->addHeadline('Konstanten');
            $Page->addSeparator();
            $Page->addCode(array("const ATTR_VARIABLE_WITH_FULL_NAME = 'VariableWithFullName';"));

            $Page = $Chapter->createPage('Setup', 'Struktur/Methoden/Variablen', $Search);

            $Page = $Chapter->createPage('Data', 'Struktur/Methoden/Variablen', $Search);
            $Page->addHeadline('Allgemein');
            $Page->addParagraph(new Small(new Danger('Enthält NUR Methoden die DIREKT mit ENTITIES arbeiten, OHNE zusätzliche Logik')));
            $Page->addSeparator();
            $Page->addHeadline('Methoden-Namen');
            $Page->addSeparator();

            $Page->addHeadline('Auslesen von Daten');

            $Page->addCode(array(
                'public function getEntityNameAll(){};',
                'public function countEntityNameAll(){};',
            ));

            $Page->addHeadline('Manipulation von Daten');

            $Page->addCode(array(
                'public function createEntityName( ..Eigenschaften,.. ){};',
                'public function updateEntityName( TblEntityName $tblEntityName, ..Eigenschaften,.. ){};',
                'public function destroyEntityName( TblEntityName $tblEntityName ){};',
            ));

            $Page->addHeadline('Verknüpfen von Daten');

            $Page->addCode(array(
                'public function addEntityNameAToEntityNameB( TblEntityNameA $tblEntityNameA, TblEntityNameB $tblEntityNameB ){};',
                'public function removeEntityNameAFromEntityNameB( TblEntityNameA $tblEntityNameA, TblEntityNameB $tblEntityNameB ){};',
            ));

            $Page = $Chapter->createPage('Service', 'Struktur/Methoden/Variablen', $Search);
            $Page->addHeadline('Allgemein');
            $Page->addParagraph(new Small(new Danger('Enthält NUR Methoden die NICHT DIREKT mit ENTITIES arbeiten')));
            $Page->addParagraph(new Small(new Danger('Verwendet NUR Methoden aus DATA um ENTITIES zu manipulieren und IMMER mit zusätzlicher Logik')));
            $Page->addSeparator();

            $Page->addHeadline('Benutzerdaten (Frontend)');
            $Page->addParagraph(new Small(new Danger('POST: Werden durch ein Formular eingegeben / manipuliert')));
            $Page->addParagraph(new Small(new Danger('GET: Werden durch einen signierten Link manipuliert')));
            $Page->addSeparator();

            $Page->addHeadline('Auslesen von Daten (Frontend/Backend)');

            $Page->addCode(array(
                'public function getEntityNameAll(){};',
                'public function countEntityNameAll(){};',
            ));

            $Page->addHeadline('POST - Daten (Formulare)');

            $Page->addCode(array(
                'public function createEntityName( IFormInterface $Form, ..Eigenschaften,.. ){};',
                'public function changeEntityName( IFormInterface $Form, ..Eigenschaften,.. ){};',
                'public function destroyEntityName( IFormInterface $Form, ..Eigenschaften,.. ){};',
            ));

            $Page->addHeadline('GET - Daten (Link)');

            $Page->addCode(array(
                'public function addEntityNameAToEntityNameB( EntityNameA $EntityNameA, EntityNameB $EntityNameB ){};',
                'public function removeEntityNameAFromEntityNameB( EntityNameA $EntityNameA, EntityNameB $EntityNameB ){};',
            ));

            $Page->addHeadline('Interne - Datenmanipulation (z.B: Importe)');

            $Page->addCode(array(
                'public function insertEntityName( ..Eigenschaften,.. ){};',
                'public function updateEntityName( TblEntityName $tblEntityName, ..Eigenschaften,.. ){};',
                'public function deleteEntityName( TblEntityName $tblEntityName ){};',
            ));
        }

        $Chapter = $this->Book->createChapter('Datenbank', 'Verbindungen');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
            $Page = $Chapter->createPage('Entities', '', $Search);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }
}

<?php
namespace SPHERE\Common\Documentation\Content;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Documentation\Designer;
use SPHERE\Common\Documentation\Designer\Book;

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

        $Chapter = $this->Book->createChapter('Names-Konventionen', 'Frontend');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
        }
        $Chapter = $this->Book->createChapter('Names-Konventionen', 'Backend');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
            $Page = $Chapter->createPage('Entity', 'Konstanten/Attribute/Funktionen', $Search);

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
            $Page = $Chapter->createPage('Service', 'Struktur/Methoden/Variablen', $Search);
        }

        $Chapter = $this->Book->createChapter('Datenbank', 'Verbindungen');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
            $Page = $Chapter->createPage('Entities', '', $Search);
        }

//        $Chapter = $this->Book->createChapter('IT – Sicherheitskonzept', 'für die Nutzung des Software KREDA');
//        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {
//            $Page = $Chapter->createPage('Dokument', 'Stand: 01.06.2015', $Search);
//            $Page->addHeadline('1. Präambel');
//            $Page->addSeparator();
//            $Page->addParagraph('Die Software KREDA ist ein Schulverwaltungsprogramm, in welchem unterschiedlichste personenbezogene Informationen verarbeitet werden.');
//            $Page->addParagraph('Es wird auf Veranlassung der Schulstiftung im Bereich der evangelischen Schulen in Sachsen eingesetzt.');
//
//            $Page->addHeadline('2. Begriffsbestimmungen');
//            $Page->addSeparator();
//            $Page->addParagraph('Folgende Begriffsbestimmungen gelten für das Sicherheitskonzept:');
//            $Page->addParagraph('Software KREDA: Das ist ein Computerprogramm in Form einer mandantenfähigen WEB-Applikation mit zentraler Datenhaltung (verteilt auf mehrere Datenbanken/Server).');
//            $Page->addParagraph('Mandant: Ein (1) Mandant entspricht hierbei einem (1) Schulträger, der die Software KREDA ausschließlich für eigene Zwecke nutzt. Unter Schulträger wird in diesem Sicherheitskonzept eine natürliche Person oder eine juristische Person (des privaten oder öffentlichen Rechtes verstanden, der eine oder mehrere Ersatzschulen entsprechend den gesetzlichen Regelung des Freistaates Sachsen betreibt.');
//            $Page->addParagraph('Hosting-Anbieter: Ein Unternehmen bzw. eine Institution, bei der die technischen und organisatorischen Vorkehrungen getroffen werden, um die Software KREDA in der oben genannten Form für die Nutzung durch die Mandanten bereitzustellen und die darin verwalteten Daten sowie die Software KREDA selbst so zu sichern, dass bei Fehlern eine zeitlich angemessene Wiederinbetriebnahme ermöglicht wird.');
//            $Page->addParagraph('Support-Dienstleister: Ein Unternehmen bzw. eine Institution, dass die technischen Abläufe in der Software KREDA entwickelt, betreut, Unterstützung für die Mandanten anbietet und Fehler in dieser Software behebt.');
//            $Page->addParagraph('Schulstiftung: Die Schulstiftung der evangelisch-lutherischen Landeskirche Sachsen, die die Bereitstellung der Software KREDA für die Mandanten organisatorisch betreut und dabei insbesondere');
//            $Page->addParagraph('- die Kommunikation zwischen den einzelnen hier benannten Partnern betreut,');
//            $Page->addParagraph('- der Zulassung von Mandanten als Nutzer zustimmt und');
//            $Page->addParagraph('- die Einhaltung des Sicherheitskonzeptes sowie der Qualität des Schulverwaltungsprogramms insgesamt kontrolliert.');
//
//            $Page->addCode(array(
//                'public function __toString() {',
//                '    return (string) $this->Book;',
//                '}'
//            ));
//
//            $Page->addHeadline('3. Klassifizierung von Daten');
//            $Page->addSeparator();
//            $Page->addHeadline('3.1. allgemeine Informationen');
//            $Page->addSeparator();
//            $Page->addParagraph('- allgemeine Verwaltungsinformationen (Bezeichnung der Schule, Anschriftendaten der Schule und Informationen zum Schulträger)');
//            $Page->addParagraph('- Kontaktinformationen zur Schule');
//            $Page->addParagraph('- Lizenzinformationen');
//            $Page->addParagraph('- Ansprechpartner seitens des Programmierers');
//            $Page->addParagraph('- Ansprechpartner in der Schulstiftung');
//            $Page->addParagraph('- Anschrift des Datenschutzbeauftragten der Schulstiftung');
//        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }
}

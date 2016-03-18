<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendStudent()
    {

        $Stage = new Stage('Schüler', 'wählen');

        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');

        $StudentTable = array();
        if ($tblGroup) {
            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonAll) {
                array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$StudentTable) {

                    $tblDivisionStudent = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                    if ($tblDivisionStudent) {
                        array_walk($tblDivisionStudent,
                            function (TblDivisionStudent $tblDivisionStudent) use (&$StudentTable, $tblPerson) {

                                $tblDivision = $tblDivisionStudent->getTblDivision();

                                $StudentTable[] = array(
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Student'  => $tblPerson->getLastFirstName(),
                                    'Option'   => new Standard(
                                        'Weiter', '/Education/Graduation/Certificate/Template', new ChevronRight(),
                                        array(
                                            'Id' => $tblDivisionStudent->getId()
                                        ), 'Auswählen')
                                );
                            }
                        );
                    }
                });
            } else {
                // TODO: Error
            }

            $Stage->setContent(
                new TableData($StudentTable)
            );

        } else {
            // TODO: Error
        }

        return $Stage;
    }

    /**
     * @param null|int $Id TblDivisionStudent
     *
     * @return Stage
     */
    public function frontendTemplate($Id = null)
    {

        $Stage = new Stage('Vorlage', 'wählen');

        if ($Id) {
            $tblDivisionStudent = Division::useService()->getDivisionStudentById($Id);
            if ($tblDivisionStudent) {
                $tblPerson = $tblDivisionStudent->getServiceTblPerson();
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        );
                        if ($tblStudentTransfer) {
                            // TODO: Find Templates in Database (DMS)

                            $TemplateTable[] = array(
                                'Template' => 'Hauptschulzeugnis',
                                'Option'   => new Standard(
                                    'Weiter', '/Education/Graduation/Certificate/Data', new ChevronRight(), array(
                                    'Id'       => $tblDivisionStudent->getId(),
                                    'Template' => 1
                                ), 'Auswählen')
                            );

                            $Stage->setContent(
                                new Layout(array(
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(array(
                                            new Panel('Aktuelle Schule: ', array(
                                                $tblStudentTransfer->getServiceTblCompany()->getName()
                                            )),
                                            new Panel('Aktuelle Schulart: ', array(
                                                $tblStudentTransfer->getServiceTblType()->getName()
                                            )),
                                            new Panel('Aktueller Bildungsgang: ', array(
                                                $tblStudentTransfer->getServiceTblCourse()->getName()
                                            )),
                                        ))
                                    ), new Title('Schüler-Informationen')),
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(
                                            new TableData($TemplateTable)
                                        )
                                    ), new Title('Verfügbare Vorlagen')),
                                ))
                            );

                        } else {
                            $Stage->setContent(
                                new Warning('Vorlage kann nicht gewählt werden, da dem Schüler in der Schülerakte keine aktuelle Schulart zugewiesen wurde.')
                            );
                        }
                    } else {
                        $Stage->setContent(
                            new Warning('Vorlage kann nicht gewählt werden, da dem Schüler keine Schülerakte zugewiesen wurde.')
                            .new Standard('Zum Schüler', '/People/Person', new Person(),
                                array('Id' => $tblPerson->getId()))
                        );
                    }
                } else {
                    // TODO: Error
                }
            } else {
                $Stage->setContent(
                    new Warning('Vorlage kann nicht gewählt werden, da dem Schüler keine Klasse zugewiesen wurde.')
                );
            }
        } else {
            // TODO: Error
        }

        return $Stage;
    }

    /**
     * @param null|int $Id TblDivisionStudent
     * @param          $Template
     *
     * @return Stage
     */
    public function frontendData($Id, $Template)
    {

        $Stage = new Stage('Daten', 'eingeben');

        if ($Id) {
            $tblDivisionStudent = Division::useService()->getDivisionStudentById($Id);
            if ($tblDivisionStudent) {
                $tblPerson = $tblDivisionStudent->getServiceTblPerson();
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {

                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        );
                        if ($tblStudentTransfer) {

                            $tblPerson = $tblStudent->getServiceTblPerson();
                            $tblDivision = $tblDivisionStudent->getTblDivision();
                            $tblYear = $tblDivision->getServiceTblYear();

                            $Global = $this->getGlobal();
                            $Global->POST['Data']['School']['Name'] = $tblStudentTransfer->getServiceTblCompany()->getName();
                            $Global->POST['Data']['School']['Type'] = $tblStudentTransfer->getServiceTblType()->getName();
                            $Global->POST['Data']['School']['Course'] = $tblStudentTransfer->getServiceTblCourse()->getName();
                            $Global->POST['Data']['School']['Year'] = $tblYear->getName();
                            $Global->POST['Data']['Name'] = $tblPerson->getLastFirstName();
                            $Global->POST['Data']['Division'] = $tblDivision->getDisplayName();
                            $Global->savePost();

                            $Stage->setContent(
                                new Layout(array(
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(array(
                                            new Panel('Aktuelle Schule: ', array(
                                                $tblStudentTransfer->getServiceTblCompany()->getName()
                                            )),
                                            new Panel('Aktuelle Schulart: ', array(
                                                $tblStudentTransfer->getServiceTblType()->getName()
                                            )),
                                            new Panel('Aktueller Bildungsgang: ', array(
                                                $tblStudentTransfer->getServiceTblCourse()->getName()
                                            )),
                                        ))
                                    ), new Title('Schüler-Informationen')),
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(
                                            new Form(
                                                new FormGroup(
                                                    new FormRow(array(
                                                        new FormColumn(
                                                            new Panel('Schuldaten', array(
                                                                (new TextField('Data[School][Name]', 'Schule',
                                                                    'Schule')),
                                                                (new TextField('Data[School][Type]', 'Schulart',
                                                                    'Schulart')),
                                                                (new TextField('Data[School][Course]', 'Bildungsgang',
                                                                    'Bildungsgang')),
                                                                (new TextField('Data[School][Year]', 'Schuljahr',
                                                                    'Schuljahr')),
                                                            )), 4),
                                                        new FormColumn(
                                                            new Panel('Schüler', array(
                                                                (new TextField('Data[Name]', 'Name', 'Name')),
                                                                (new TextField('Data[Division]', 'Klasse', 'Klasse')),
                                                            )), 4),
                                                    ))
                                                )
                                                , new Primary('Vorschau erstellen'),
                                                '/Education/Graduation/Certificate/Create',
                                                array('Template' => $Template))
                                        )
                                    ), new Title('Verfügbare Daten-Felder')),
                                ))
                            );
                        } else {
                            // TODO: Error
                        }
                    } else {
                        // TODO: Error
                    }
                } else {
                    // TODO: Error
                }
            } else {
                // TODO: Error
            }
        } else {
            // TODO: Error
        }
        return $Stage;
    }

    public function frontendCreate($Data, $Content = null)
    {

        // TODO: Find Template in Database (DMS)
        $this->getCache(new TwigHandler())->clearCache();

        $Header = (new Slice())
            ->addSection(
                (new Section())
                    ->addColumn(
                        (new Element())
                            ->setContent('MS Abgangszeugnis 3g.pdf')
                            ->styleTextSize('12px')
                            ->styleTextColor('#CCC')
                            ->styleAlignCenter()
                        , '25%'
                    )->addColumn(
                        (new Element\Sample())
                            ->styleTextSize('30px')
                    )->addColumn(
                        (new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '200px')), '25%'
                    )
            );

        $Content = (new Frame())->addDocument(
            (new Document())
                ->addPage(
                    (new Page())
                        ->addSlice(
                            $Header
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('ABGANGSZEUGNIS')
                                        ->styleTextSize('30px')
                                        ->styleAlignCenter()
                                        ->styleMarginTop('32%')
                                )
                        )
                )
                ->addPage(
                    (new Page())
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vorname und Name:')
                                            , '25%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('geboren am')
                                            , '25%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Division }}')
                                                ->styleBorderBottom()
                                            , '20%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('in')
                                                ->styleAlignCenter()
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('wohnhaft in')
                                            , '25%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.School.Course }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('hat')
                                            , '10%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.School.Name }}')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom()
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom()
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('besucht')
                                                ->styleAlignRight()
                                            , '15%')
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Name und Anschrift der Schule')
                                        ->styleTextSize('12px')
                                        ->styleTextColor('#CCC')
                                        ->styleAlignCenter()
                                        ->styleMarginTop('5px')
                                        ->styleMarginBottom('5px')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß § 28 Abs. 1 Nr. 1 SchulG')
                                        ->styleMarginTop('8px')
                                )
                                ->addElement(
                                    (new Element())
                                        ->setContent('die Schulart Mittelschule –')
                                        ->styleMarginTop('8px')
                                )
                                ->addElement(
                                    (new Element())
                                        ->setContent('Hauptschulbildungsgang/Realschulbildungsgang¹.')
                                        ->styleMarginTop('8px')
                                )
                                ->styleAlignCenter()
                                ->styleMarginTop('20%')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('¹ Zutreffendes ist zu unterstreichen.')
                                                ->styleTextSize('10px')
                                                ->styleBorderTop()
                                            , '33%')
                                        ->addColumn(
                                            (new Element())
                                        )
                                )
                        )
                )
                ->addPage(
                    (new Page())
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vorname und Name:')
                                            , '25%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                            , '45%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Klasse')
                                                ->styleAlignCenter()
                                            , '10%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Division }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Leistungen in den einzelnen Fächern:')
                                        ->styleTextSize('20px')
                                        ->styleMarginTop('15px')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Deutsch')
                                            , '35%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('1')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#DDD')
                                                ->styleBorderBottom('2px', '#999')
                                            , '14%')
                                        ->addColumn(
                                            (new Element())
                                            , '2%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Mathematik')
                                            , '35%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('2')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#DDD')
                                                ->styleBorderBottom('2px', '#999')
                                            , '14%')
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Englisch')
                                            , '35%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('3')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#DDD')
                                                ->styleBorderBottom('2px', '#999')
                                            , '14%')
                                        ->addColumn(
                                            (new Element())
                                            , '2%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Biologie')
                                            , '35%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('3')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#DDD')
                                                ->styleBorderBottom('2px', '#999')
                                            , '14%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Wahlpflichtbereich:')
                                        ->styleTextSize('20px')
                                        ->styleMarginTop('15px')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Nuff nuff')
                                                ->styleBorderBottom()
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('3')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#DDD')
                                                ->styleBorderBottom('2px', '#999')
                                            , '14%')
                                )
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Neigungskurs (Neigungskursbereich)/Vertiefungskurs/2. Fremdsprache (abschlussorientiert)¹')
                                                ->styleTextSize('10px')
                                        )
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Bemerkungen:')
                                            , '22%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Dumdidum')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Datum:')
                                            , '15%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent(date('d.m.Y'))
                                                ->styleBorderBottom()
                                            , '15%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Schulleiter(in)')
                                                ->styleAlignCenter()
                                                ->styleBorderTop()
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Dienstsiegel der Schule')
                                                ->styleAlignCenter()
                                                ->styleBorderTop()
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Klassenlehrer(in)')
                                                ->styleAlignCenter()
                                                ->styleBorderTop()
                                            , '30%')
                                )
                                ->styleMarginTop('15px')
                        )
                )
        );

        $Content->setData($Data);

        $Preview = $Content->getContent();

        $FileLocation = Storage::useWriter()->getTemporary('pdf', 'Zeugnistest-'.date('Ymd-His'), false);
        /** @var DomPdf $Document */
        $Document = \MOC\V\Component\Document\Document::getPdfDocument($FileLocation->getFileLocation());
        $Document->setContent($Content->getTemplate());
        $Document->saveFile(new FileParameter($FileLocation->getFileLocation()));

        $Stage = new Stage();

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(array(
//                $FileLocation->getFileLocation(),
                '<div class="cleanslate">'.$Preview.'</div>'
            ), 12),
//            new LayoutColumn(array(
//                '<pre><code class="small">'.( str_replace("\n", " ~~~ ",
//                    file_get_contents($FileLocation->getFileLocation())) ).'</code></pre>'
//                FileSystem::getDownload($FileLocation->getRealPath(),
//                    "Zeugnis ".date("Y-m-d H:i:s").".pdf")->__toString()
//            ), 6),
        )))));

        return $Stage;
    }
}

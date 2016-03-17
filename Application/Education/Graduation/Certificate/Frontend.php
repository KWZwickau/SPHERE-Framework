<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Template\Template;
use SPHERE\Application\Document\Explorer\Storage\Storage;
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
                                new Warning( 'Vorlage kann nicht gewählt werden, da dem Schüler in der Schülerakte keine aktuelle Schulart zugewiesen wurde.' )
                            );
                        }
                    } else {
                        $Stage->setContent(
                            new Warning( 'Vorlage kann nicht gewählt werden, da dem Schüler keine Schülerakte zugewiesen wurde.' )
                            .new Standard( 'Zum Schüler', '/People/Person', new Person(), array( 'Id' => $tblPerson->getId() ) )
                        );
                    }
                } else {
                    // TODO: Error
                }
            } else {
                $Stage->setContent(
                    new Warning( 'Vorlage kann nicht gewählt werden, da dem Schüler keine Klasse zugewiesen wurde.' )
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

    public function frontendCreate($Data, $Template)
    {

        // TODO: Find Template in Database (DMS)

        $this->getCache(new TwigHandler())->clearCache();
        $Template = Template::getTemplate(__DIR__.'/Vorlage.twig');
        $Template->setVariable('Data', $Data);

        $FileLocation = Storage::useWriter()->getTemporary('pdf', 'Zeugnistest', true);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($FileLocation->getFileLocation());
        $Document->setContent($Template);
        $Document->saveFile(new FileParameter($FileLocation->getFileLocation()));

        $Stage = new Stage('Vorschau');

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(array(
                $FileLocation->getFileLocation(),
                '<div class="cleanslate">'.$Template->getContent().'</div>'
            ), 6),
            new LayoutColumn(array(
                '<pre><code class="small">'.( str_replace("\n"," ~~~ ",file_get_contents($FileLocation->getFileLocation())) ).'</code></pre>'
//                FileSystem::getDownload($FileLocation->getRealPath(),
//                    "Zeugnis ".date("Y-m-d H:i:s").".pdf")->__toString()
            ), 6),
        )))));

        return $Stage;
    }
}

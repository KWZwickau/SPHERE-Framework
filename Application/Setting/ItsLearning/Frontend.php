<?php
namespace SPHERE\Application\Setting\ItsLearning;

use SPHERE\Application\Api\Setting\ItsLearning\ApiItsLearning;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Frontend\Icon\Repository\Link as LinkIcon;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension
{

    /**
     * @return Stage
     */
    public function frontendDownload($Year = null)
    {

        $tblYearTempList = Term::useService()->getYearByNow();
        if($tblYearTempList && count($tblYearTempList) == 1){
            $tblYear = current($tblYearTempList);
            $_POST['Year'] = $tblYear->getId();
            $ApiReceiver = ApiItsLearning::receiverContent(ApiItsLearning::pipelineLoad($tblYear->getId()));
        } else {
            $Wait = new Info('Bitte wählen Sie ein Schuljahr aus.'.new ProgressBar(100, 0, 0, 12));
            $ApiReceiver = ApiItsLearning::receiverContent($Wait);
        }

        $Stage = new Stage('itslearning', 'Benutzer exportieren');
        $Stage->setMessage(
            new Container('Die Validierung beinhaltet nur Schüler- und Lehrerdaten.')
            .new Container('Da itslearning auch ohne die Eltern genutzt werden kann, erfolgt an dieser Stelle keine Validierung.')
            .new Container('Die Daten der Eltern werden automatisch mit ergänzt, wenn entsprechende Benutzeraccounts vorhanden sind.')
        .new Container('Die Spalte "Kind-Nr." wird anhand der Sorgeberechtigtenbeziehungen automatisch generiert und dient zur manuellen Bearbeitung der CSV Datei vor dem
            Import nach itslearning.')
        .new Container('('.(new Link('Support Indiware', 'https://support.itslearning.com/de/support/solutions/articles/7000057991-nutzerimport-der-erziehungsberechtigten',
                    new LinkIcon(), array(), 'Link zu externem Support'))->setExternal().')')
        );

        $tblYearList = Term::useService()->getYearAllSinceYears(1);

        $Stage->setContent(
            new Form(new FormGroup(new FormRow(new FormColumn(
                    (new SelectBox('Year', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearList)))->ajaxPipelineOnChange(ApiItsLearning::pipelineLoad())
            ))))
            .$ApiReceiver
        );

        return $Stage;
    }

    /**
     * @return Layout|string
     */
    public function loadContentComplete($Year)
    {
        $StudentAccountList = ItsLearning::useService()->getStudentCustodyAccountList($Year);
        $TableStudentWarningContent = array();
        if(!empty($StudentAccountList)){
            foreach($StudentAccountList as $PersonId => $Data){
                if($Data['Account'] && $Data['Level'] && $Data['Division']){
                    continue;
                }
                $Item = array();
                $tblPerson = Person::useService()->getPersonById($PersonId);
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Info'] = '';
                if(!$Data['Account']){
                    $Item['Info'] .= new Warning('Schüler ohne Account', null, false, 3, 2);
                }
                if(!$Data['Division'] || !$Data['Level']){
                    $Item['Info'] .= new Warning('Schüler ohne Klasse/Jahrgang', null, false, 3, 2);
                }
                array_push($TableStudentWarningContent, $Item);
            }
        }

        $TeacherAccountList = ItsLearning::useService()->getTeacherAccountList();
        $TableTeacherWarningContent = array();
        if(!empty($TeacherAccountList)){
            foreach($TeacherAccountList as $PersonId => $Data){
                if($Data['Account']){
                    continue;
                }
                $Item = array();
                $tblPerson = Person::useService()->getPersonById($PersonId);
                $Item['Name'] = $tblPerson->getLastFirstName();
                // einzige Bedingung, kommen mehrere hinzu, könnte die Auswahl wieder interessant werden.
//                $Item['Info'] = '';
//                if(!$Data['Account']){
                $Item['Info'] = new Warning('Lehrer ohne Account', null, false, 3, 2);
//                }
                array_push($TableTeacherWarningContent, $Item);
            }
        }

        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new External('CSV Schüler & Sorgeberechtigte herunterladen', '/Api/Transfer/ItsLearning/StudentCustody/Download', new Download(), array('Year' => $Year), false, External::STYLE_BUTTON_PRIMARY)
                    .new Title(new Family().' Export Schüler/Sorgeberechtigte nach itslearning')
                    .(!empty($TableStudentWarningContent)
                        ? new TableData($TableStudentWarningContent, null,
                            array(
                                'Name' => 'Schüler',
                                'Info' => 'Warnung',
                            ))
                        : new Success('Keine Warnungen für den Export der Schüler / Sorgeberechtigten')
                    )
                    , 6),
                new LayoutColumn(
                    new External('CSV Lehrer herunterladen', '/Api/Transfer/ItsLearning/Teacher/Download', new Download(), array(), false, External::STYLE_BUTTON_PRIMARY)
                    .new Title(new PersonKey().' Export Lehrer nach itslearning')
                    .(!empty($TableTeacherWarningContent)
                        ? new TableData($TableTeacherWarningContent, null,
                            array(
                                'Name' => 'Lehrer',
                                'Info' => 'Warnung',
                            ))
                        : new Success('Keine Warnungen für den Export der Lehrer')
                    )
                    , 6),
            ))
        )));
    }
}
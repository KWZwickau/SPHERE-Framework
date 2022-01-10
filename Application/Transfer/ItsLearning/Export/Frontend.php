<?php
namespace SPHERE\Application\Transfer\ItsLearning\Export;

use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
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
    public function frontendDownload()
    {

        $Stage = new Stage('itslearning', 'Benutzer exportieren');
        $Stage->addButton(new Standard('Zurück', '/Transfer/ItsLearning', new ChevronLeft()));

        $StudentAccountList = Export::useService()->getStudentCustodyAccountList();
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
                    $Item['Info'] .= new Warning('Schüler ohne Account', null, false, 3, 3);
                }
                if(!$Data['Division'] || !$Data['Level']){
                    $Item['Info'] .= new Warning('Schüler ohne Klasse/Jahrgang', null, false, 3, 3);
                }
                array_push($TableStudentWarningContent, $Item);
            }
        }

        $TeacherAccountList = Export::useService()->getTeacherAccountList();
        $TableTeacherWarningContent = array();
        if(!empty($TeacherAccountList)){
            foreach($TeacherAccountList as $PersonId => $Data){
                if($Data['Account']){
                    continue;
                }
                $Item = array();
                $tblPerson = Person::useService()->getPersonById($PersonId);
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Info'] = '';
                if(!$Data['Account']){
                    $Item['Info'] .= new Warning('Lehrer ohne Account', null, false, 3, 3);
                }
                array_push($TableTeacherWarningContent, $Item);
            }
        }

        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Primary('CSV Schüler & Sorgeberechtigte herunterladen', '/Api/Transfer/ItsLearning/StudentCustody/Download', new Download())
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
                    new Primary('CSV Lehrer herunterladen', '/Api/Transfer/ItsLearning/Teacher/Download', new Download())
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
        ))));

        return $Stage;
    }
}
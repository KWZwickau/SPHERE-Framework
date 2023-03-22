<?php
namespace SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonReportingStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendSuSList($ShowContent = null)
    {

//        ini_set('memory_limit', '1G');

        $Stage = new Stage('Auswertung', 'SuS Gesamtliste');
        $Stage->addButton((new Primary('Download', '/Api/Reporting/CustomEKBO/BerlinZentrum/SuSList/Download', new Download()))->setExternal());
        $Stage->addButton(new Standard('Übersicht Laden', '/Reporting/Custom/SuSList', new Repeat(), array('ShowContent' => 1)));

        if($ShowContent){
            $tblPersonList = array();
            if(($tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))){
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroupStudent);
            }
            $TableContent = Person::useService()->createSuSList();
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(

                                new TableData($TableContent, null,
                                    array(
                                        'PersonId'             => 'PersonId',
                                        'StudentNumber'        => 'Schülernummer',
                                        'Division'             => 'JG',
                                        'PersonGroupKL'        => 'KL',
                                        'PersonGroupTeam'      => 'TEAM',
                                        'PersonGroupG'         => 'Gruppe',
                                        'LastName'             => 'Nachname',
                                        'CallName'             => 'Rufname',
                                        'FirstName'            => 'Vorname',
                                        'SecondName'           => 'Zweiter Vorname',
                                        'PersonGroupTutor'     => 'Tutor',
                                        'Birthday'             => 'Geburtsdatum',
                                        'Birthplace'           => 'Geburtsort',
                                        'Gender'               => 'Geschlecht',
                                        'AddressRemark'        => 'Adresszusatz_Kind',
                                        'ExcelStreet'          => 'Strasse Kind',
                                        'Code'                 => 'PLZ Kind',
                                        'City'                 => 'Stadt Kind',
                                        'Nationality'          => 'Nationalität',
                                        'Denomination'         => 'Kirche',
                                        'LeavingSchool'        => 'Grundschule',
                                        'PersonIdS2'           => 'PersonId_S2',
                                        'TitleS2'              => 'Akad. Titel_S2',
                                        'LastNameS2'           => 'Nachname_S2',
                                        'FirstNameS2'          => 'Vorname_S2',
                                        'AddressRemarkS2'      => 'Adresszusatz_S2',
                                        'ExcelStreetS2'        => 'Straße_S2',
                                        'CodeS2'               => 'PLZ_S2',
                                        'CityS2'               => 'Ort_S2',
                                        'MailS2'               => 'Mail_S2',
                                        'Mail2S2'              => 'Mail_S2_Zwei',
                                        'RemarkS2'             => 'Bemerkung_S2',
                                        'PersonIdS1'           => 'PersonId_S1',
                                        'TitleS1'              => 'Titel_S1',
                                        'LastNameS1'           => 'Nachname_S1',
                                        'FirstNameS1'          => 'Vorname_S1',
                                        'AddressRemarkS1'      => 'Adresszusatz_S1',
                                        'ExcelStreetS1'        => 'Straße_S1',
                                        'CodeS1'               => 'PLZ_S1',
                                        'CityS1'               => 'Ort_S1',
                                        'MailS1'               => 'Mail_S1',
                                        'Mail2S1'              => 'Mail_S1_Zwei',
                                        'RemarkS1'             => 'Bemerkung_S1',
                                        'EnterDate'            => 'Zugang',
                                        'LeaveDate'            => 'Abgang',
                                        'Region'               => 'stadtbezirk',
                                        'Mail'                 => 'Mailschüler',
                                        'Masern'               => 'Masern',
                                        'Foreign_Language1'    => '1. Framddsprache',
                                        'Foreign_Language1_JG' => '1 ab JG',
                                        'Foreign_Language2'    => '2. Framddsprache',
                                        'Foreign_Language2_JG' => '2 ab JG',
                                        'Foreign_Language3'    => '3. Framddsprache',
                                        'Foreign_Language3_JG' => '3 ab JG',
                                        'UserName'             => 'Benutzernamen',
                                        'MigrationBackground'  => 'Nicht dt. Herkunftssprache',
                                    ),
                                    array(
                                        'order' => array(
                                            array(6, 'asc'),
                                            array(8, 'asc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 6, 8),

                                        ),
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    PersonReportingStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
                ))
            );
        }

        return $Stage;
    }
}

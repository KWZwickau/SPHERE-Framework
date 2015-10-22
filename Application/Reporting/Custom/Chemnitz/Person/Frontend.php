<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendClassList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Klassenliste');

        $View->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Custom/Chemnitz/Common/ClassList/Download', new Download())
        );

        $studentList = Person::useService()->createClassList();
        $View->setContent(
            new TableData($studentList, null,
                array(
                    'Salutation'   => 'Anrede',
                    'Father'       => 'Vorname V.',
                    'Mother'       => 'Vorname M.',
                    'LastName'     => 'Name',
                    'Denomination' => 'Konfession',
                    'StreetName'   => 'Straße',
                    'StreetNumber' => 'Hausnr.',
                    'City'         => 'PLZ Ort',
                    'FirstName'    => 'Schüler',
                    'Birthday'     => 'Geburtsdatum',
                    'Birthplace'   => 'Geburtsort',
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendClassListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Klassenliste.xls";
        $studentList = Person::useService()->createClassList();
        Person::useService()->createClassListExcel($studentList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendStaffList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Liste der Mitarbeiter');

        $View->addButton(
            new Primary('Herunterladen',
                '/Reporting/Custom/Chemnitz/Person/StaffList/Download', new Download())
        );

        $staffList = Person::useService()->createStaffList();

        $View->setContent(
            new TableData($staffList, null,
                array(
                    'Salutation'   => 'Anrede',
                    'FirstName'    => 'Vorname',
                    'LastName'     => 'Name',
                    'Birthday'     => 'Geburtsdatum',
                    'Division'     => 'Unterbereich',
                    'StreetName'   => 'Straße',
                    'StreetNumber' => 'Hausnr.',
                    'Code'         => 'PLZ',
                    'City'         => 'Ort',
                    'Phone1'       => 'Telefon 1',
                    'Phone2'       => 'Telefon 2',
                    'Mail'         => 'Mail',
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendStaffListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Mitarbeiterliste.xls";
        $staffList = Person::useService()->createStaffList();
        Person::useService()->createStaffListExcel($staffList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendMedicList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Arztliste');

        $View->addButton(
            new Primary('Herunterladen',
                '/Reporting/Custom/Chemnitz/Person/MedicList/Download', new Download())
        );

        $studentList = Person::useService()->createMedicList();

        $View->setContent(
            new TableData($studentList, null,
                array(
                    'LastName'     => 'Name',
                    'FirstName'    => 'Vorname',
                    'Birthday'     => 'Geburtsdatum',
                    'StreetName'   => 'Straße',
                    'StreetNumber' => 'Hausnr.',
                    'Code'         => 'PLZ',
                    'City'         => 'Wohnort',
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendMedicListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Arztliste.xls";
        $studentList = Person::useService()->createMedicList();
        Person::useService()->createMedicListExcel($studentList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendParentTeacherConferenceList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Liste für Elternabende');

        $View->addButton(
            new Primary('Herunterladen',
                '/Reporting/Custom/Chemnitz/Person/ParentTeacherConferenceList/Download', new Download())
        );

        $studentList = Person::useService()->createParentTeacherConferenceList();

        $View->setContent(
            new TableData($studentList, null,
                array(
                    'LastName'   => 'Name',
                    'FirstName'  => 'Vorname',
                    'Attendance' => 'Anwesenheit',
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendParentTeacherConferenceListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Elternabende.xls";
        $studentList = Person::useService()->createParentTeacherConferenceList();
        Person::useService()->createParentTeacherConferenceListExcel($studentList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendClubMemberList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Liste der Vereinsmitglieder');

        $View->addButton(
            new Primary('Herunterladen',
                '/Reporting/Custom/Chemnitz/Person/ClubMemberList/Download', new Download())
        );

        $clubMemberList = Person::useService()->createClubMemberList();

        $View->setContent(
            new TableData($clubMemberList, null,
                array(
                    'Salutation'   => 'Anrede',
                    'FirstName'    => 'Vorname',
                    'LastName'     => 'Name',
                    'StreetName'   => 'Straße',
                    'StreetNumber' => 'Hausnr.',
                    'Code'         => 'PLZ',
                    'City'         => 'Ort',
                    'Phone'        => 'Telefon',
                    'Mail'         => 'Mail',
                    'Directorate'  => 'Vorstand'
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendClubMemberListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Vereinsmitgliederliste.xls";
        $clubMemberList = Person::useService()->createClubMemberList();
        Person::useService()->createClubMemberListExcel($clubMemberList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Neuanmeldungen/Interessenten');

        $View->addButton(
            new Primary('Herunterladen',
                '/Reporting/Custom/Chemnitz/Person/InterestedPersonList/Download', new Download())
        );

        $interestedPersonList = Person::useService()->createInterestedPersonList();

        $View->setContent(
            new TableData($interestedPersonList, null,
                array(
                    'RegistrationDate' => 'Anmeldedatum',
                    'FirstName'        => 'Vorname',
                    'LastName'         => 'Name',
                    'SchoolYear'       => 'Schuljahr',
                    'CompanyOptionA'   => 'Schulart 1',
                    'CompanyOptionB'   => 'Schulart 2',
                    'StreetName'       => 'Straße',
                    'StreetNumber'     => 'Hausnummer',
                    'Code'             => 'PLZ',
                    'City'             => 'Ort',
                    'Birthday'         => 'Geburtsdatum',
                    'Birthplace'       => 'Geburtsort',
                    'Nationality'      => 'Staatsangeh.',
                    'Denomination'     => 'Bekenntnis',
                    'Siblings'         => 'Geschwister',
                    'Hoard'            => 'Hort',
                    'FatherSalutation' => 'Anrede V',
                    'FatherLastName'   => 'Name V',
                    'FatherFirstName'  => 'Vorname V',
                    'MotherSalutation' => 'Anrede M',
                    'MotherLastName'   => 'Name M',
                    'MotherFirstName'  => 'Vorname M',
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Interessentenliste.xls";
        $interestedPersonList = Person::useService()->createInterestedPersonList();
        Person::useService()->createInterestedPersonListExcel($interestedPersonList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendSchoolFeeList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Schulgeldliste');

        $View->addButton(
            new Primary('Herunterladen',
                '/Reporting/Custom/Chemnitz/Person/SchoolFeeList/Download', new Download())
        );

        $studentList = Person::useService()->createSchoolFeeList();

        $View->setContent(
            new TableData($studentList, null,
                array(
                    'DebtorNumber'     => 'Deb.-Nr.',
                    'Reply'            => 'Bescheid geschickt',
                    'FatherSalutation' => 'Anrede V',
                    'FatherLastName'   => 'Name V',
                    'FatherFirstName'  => 'Vorname V',
                    'MotherSalutation' => 'Anrede M',
                    'MotherLastName'   => 'Name M',
                    'MotherFirstName'  => 'Vorname M',
                    'Records'          => 'Unterlagen eingereicht',
                    'LastSchoolFee'    => 'SG Vorjahr',
                    'Remarks'          => 'Bemerkungen',
                    'StreetName'       => 'Straße',
                    'StreetNumber'     => 'Hausnummer',
                    'Code'             => 'PLZ',
                    'City'             => 'Ort',
                ),
                false
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendSchoolFeeListDownload()
    {

        $View = new Stage();

        $fileLocation = "Chemnitz Schulgeldliste.xls";
        $studentList = Person::useService()->createSchoolFeeList();
        Person::useService()->createSchoolFeeListExcel($studentList, $fileLocation);

        $View->setContent(
            FileSystem::getDownload($fileLocation)
        );

        return $View;
    }
}

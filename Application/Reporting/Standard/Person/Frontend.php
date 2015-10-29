<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Standard\Person
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
        $View->setTitle('Auswertung');
        $View->setDescription('Klassenliste');

        $View->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/ClassList/Download', new Download())
        );

        $studentList = Person::useService()->createClassList();
        $View->setContent(
            new TableData($studentList, null,
                array(
                    'Salutation'   => 'Anrede',
                    'FirstName'    => 'Vorname',
                    'LastName'     => 'Name',
                    'Denomination' => 'Konfession',
                    'Birthday'     => 'Geburtsdatum',
                    'Birthplace'   => 'Geburtsort',
                    'Address'      => 'Adresse',
                ),
                false
            )
        );

        return $View;
    }
}

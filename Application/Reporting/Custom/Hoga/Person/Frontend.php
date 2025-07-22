<?php
namespace SPHERE\Application\Reporting\Custom\Hoga\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Hoga\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool $isLoad
     *
     * @return Stage
     */
    public function frontendCleverReach($isLoad = false)
    {

        $Stage = new Stage('HOGA - Clever Reach', 'Auswertung über E-Mail Identifier');
        $Route = '/Api/Reporting/Custom/Hoga/Common/CleverReach/Download';

        $Stage->addButton((new Primary('Herunterladen Excel', $Route, new Download()))->setExternal());
        $Stage->addButton((new Primary('Herunterladen CSV', $Route.'CSV', new Download()))->setExternal());
//        $Stage->addButton(new Primary('Herunterladen Excel', '/Reporting/Custom/Hoga/Person/CleverReach/Load', new Download(), array('Route' => $Route)));
//        $Stage->addButton(new Primary('Herunterladen CSV', '/Reporting/Custom/Hoga/Person/CleverReach/Load', new Download(), array('Route' => $Route.'CSV')));
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

        if(!$isLoad){
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('', 3),
                new LayoutColumn(
                    new Info(new Center('Anzeigen der Daten kann etwas Zeit in Anspruch nehmen')
                        .new Container(new Center(new Standard(' Anzeige Laden', '/Reporting/Custom/Hoga/Person/CleverReach/Load', new EyeOpen()))))
                , 6)
            )))));
            return $Stage;
        }
        set_time_limit(600);

        $TableContent = Person::useService()->createCleverReachList();
        $tblGroupCustody = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        if(($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroupCustody))){
            $countChildMax = Person::useService()->getChildMaxCount($tblPersonList);
        }

        $TableHeader = array(
            'Mail'       => 'E-Mail',
            'Salutation' => 'Anrede',
            'LastName'   => 'Name',
            'FirstName'  => 'Vorname'
        );
        for($i = 1; $i <= $countChildMax; $i++){
            $TableHeader['DivisionChild'.$i] = 'Klasse des Kindes '.$i;
            $TableHeader['SchoolTypeChild'.$i] = 'Schulart des Kindes '.$i;
            $TableHeader['LastNameChild'.$i] = 'Name des Kindes '.$i;
            $TableHeader['FirstNameChild'.$i] = 'Vorname des Kindes '.$i;
            $TableHeader['GenderChild'.$i] = 'Geschlecht des Kindes '.$i;
            $TableHeader['SecondLanguageChild'.$i] = 'ggf. 2. Fremdsprache des Kindes '.$i.' (nur wenn das Kind diese auch aktuell besucht)';
            $TableHeader['ThirdLanguageChild'.$i] = 'ggf. 3. Fremdsprache des Kindes '.$i.' (nur wenn das Kind diese auch aktuell besucht)';
            $TableHeader['ReligionChild'.$i] = 'Ethik o. Religion Kind '.$i; // auch Ethik
            $TableHeader['ProfilChild'.$i] = 'ggf. Profil des Kindes '.$i;
        }

        $Stage->setContent(
            new Layout(array(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null, $TableHeader,
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2, 3),
                        ),
//                        "pageLength" => -1,
                        "responsive" => false
                    )
                )
            ))))));
        return $Stage;
    }

    public function frontendLoad($Route = '')
    {

        $Load = new Info('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
            .new Container((new ProgressBar(0, 100, 0, 10))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)));
        if(!$Route){
            $Reload = new RedirectScript('/Reporting/Custom/Hoga/Person/CleverReach', 0, array('isLoad' => true));
        } else {
            $Reload = new RedirectScript($Route, 0);
        }

        $Stage = new Stage('HOGA - Clever Reach', 'Auswertung über E-Mail Identifier');
        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn($Load),
            new LayoutColumn($Reload)
        )))));
        return $Stage;
    }
}

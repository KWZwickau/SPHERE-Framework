<?php
namespace SPHERE\Application\Transfer\Davinci\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Transfer\Import\Standard\ImportStandard;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;


/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Davinci\Import
 */
class Import extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/StudentCourse/Prepare', __CLASS__.'::frontendTimetableImport'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service();
//            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
//            __DIR__.'/Service/Entity',
//            __NAMESPACE__.'\Service\Entity'

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Davinci', 'Datentransfer');

        $PanelTimetable[] = new PullClear('Stundenplan aus Davinci: '.
            new Center(new Standard('', '/Transfer/Davinci/Import/StudentCourse/Prepare', new Upload()))); // ToDO Link

        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Import Stundenplan:', $PanelTimetable
                                , Panel::PANEL_TYPE_INFO)
                        , 4),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param null $File
     *
     * @return Stage
     */
    public function frontendTimetableImport($File = null)
    {

        $Stage = new Stage('Import', 'Stundenplan aus Davinci');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Davinci/Import',
                new ChevronLeft()
            )
        );
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                ImportStandard::useService()->createInterestedFromFile(
                                    new Form(new FormGroup(new FormRow(new FormColumn(
                                        new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                            array('showPreview' => false))
                                    ))), new Primary('Hochladen'))
                                    , $File
                                )
                                .new WarningText(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }
}
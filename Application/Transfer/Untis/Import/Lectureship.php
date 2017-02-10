<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Lectureship extends Import
{


    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Lectureship', __CLASS__ . '::frontendUpload'
        ));

        parent::registerModule();
    }

    /**
     * @param null|UploadedFile $File
     * @param null|int $tblYear
     * @return Stage
     */
    public function frontendUpload(UploadedFile $File = null, $tblYear = null)
    {

        $Stage = new Stage('Untis', 'Daten importieren');
        $Stage->setMessage('Lehraufträge importieren');

        Debugger::screenDump( $File, $tblYear );

        if( $File === null || $tblYear === null || $tblYear <= 0 ) {
            // TODO: Form Error
            $Stage->setContent(
                new Redirect( new Route(__NAMESPACE__), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
                ) )
            );
            return $Stage;
        }

        if ($File && !$File->getError()) {

            $Extension = ( $File->getClientOriginalExtension() == 'txt'
                ? 'csv'
                : $File->getClientOriginalExtension()
            );

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

            $Gateway = new LectureshipGateway($Payload->getRealPath());

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList())
                            )
                        )
                        , new Title('Validierung'))
                )
            );

            Debugger::screenDump($Gateway->getResultList());

        } else {
            // TODO: Upload Error
        }

        return $Stage;
    }
}
<?php
namespace SPHERE\Application\Manual\Help;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\Help
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Ticket
     * @param null $Attachment
     *
     * @return Stage
     */
    public function frontendHelp($Ticket = null, $Attachment = null)
    {

        $Stage = new Stage('Hilfe', 'Downloadbereich');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn('', 3),
                        new LayoutColumn(
                            new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('', 3),
                                new LayoutColumn('<h4>Schulsoftware Download der Hilfe</h4>'
                                    .new Link(new Thumbnail(
                                        FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                        , 'Allgemeine Hilfe '.new Muted(new Small('Stand:&nbsp;25.11.2021'))), '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Help'))
                                , 6),
                                new LayoutColumn('', 3),
                            ))))
                        , 6),
                        new LayoutColumn('', 3)
                    )),

                    new LayoutRow(
                        new LayoutColumn(new Ruler().'<h4>Weitere Downloads</h4>')
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png')
                                , 'Benutzerrechte'), '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'UserRole'))
                        , 3),
                        new LayoutColumn(
                            new Link(
                                new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png'), 'Anleitung Fakturierung '.new Muted(new Small('Stand:&nbsp;14.12.2021')))
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Billing'))
                            , 3),
                        new LayoutColumn(new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                                , 'Import aus Indiware'), '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Indiware'))
                        , 3),
                        new LayoutColumn(new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png')
                                , 'Druck Abschlusszeugnisse A3'), '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'PrintA3Certificate'))
                        , 3),

                    ))
                ))
            )
        );

        return $Stage;
    }
}

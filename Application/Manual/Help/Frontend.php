<?php
namespace SPHERE\Application\Manual\Help;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
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

        $isUcsConsumer = ($tblConsumer = Consumer::useService()->getConsumerBySession())
            && Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS);

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
                                        , 'Allgemeine Hilfe '.new Muted(new Small('Stand:&nbsp;18.04.2023'))), '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Help'))
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
                        new LayoutColumn(new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png')
                                , 'Benutzerrechte', 'empfohlene Benutzerrechte Stand:&nbsp;27.07.2022'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'UserRole')
                        ), 2),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                , 'Digitales Klassenbuch', 'Stand:&nbsp;09.11.2022'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'DigitalClassBook')
                        ), 2),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                , 'Anleitung Fakturierung', 'Stand:&nbsp;21.02.2022'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Billing')
                        ), 2),
                        new LayoutColumn(new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png')
                                , 'Druck Abschlusszeugnisse A3'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'PrintA3Certificate')
                        ), 2),
                        new LayoutColumn(new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                , 'ESDi Leistungsbeschreibung', 'Stand 26.01.2023'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'ESDi'))
                        , 2),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                            , 'Indiware Import', 'Leitfaden zur Informationsbeschaffung'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Indiware')
                        ), 2),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                            , 'Untis Import', 'Leitfaden zur Informationsbeschaffung'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Untis')
                        ), 2),
                    )),
                    new LayoutRow(array(
                        $isUcsConsumer
                            ? new LayoutColumn(new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                , 'Schnittstelle Schulsoftware zu DLLP / UCS', 'Stand:&nbsp;11.05.2023'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'SSW_UCS_DLLP')
                            ), 2)
                            : null
                    ))
                ))
            )
        );

        return $Stage;
    }
}

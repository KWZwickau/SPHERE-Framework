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

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
//                        new LayoutColumn('', 1),
                        new LayoutColumn('<h4>Schulsoftware Download der Hilfe</h4>'
                            . new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                , 'Allgemeine Hilfe', 'Stand:&nbsp;17.06.2024'))->setPictureHeight(), '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Help'))
                            , 3),
                        new LayoutColumn('<h4>Lehrvideos <b>Kursverwaltung</b></h4>'
                            . (new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                                , 'Kursverwaltung', 'Stand:&nbsp;31.05.2023'))->setPictureHeight(), 'https://www.youtube.com/playlist?list=PLvZfeA-UBJ_z_MRV2-lVLoW3cnYJ4wEJh'))
                                ->setExternal()
                            , 3),
                        new LayoutColumn('<h4>Lehrvideos <b>Notenbuch</b></h4>'
                            . (new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                                , 'Notenbuch', 'Stand:&nbsp;07.07.2023'))->setPictureHeight(), 'https://www.youtube.com/playlist?list=PLvZfeA-UBJ_wjWmbKjMZbzBab1MJx-xKO'))
                                ->setExternal()
                            , 3),
//                        new LayoutColumn('', 2),
                    )),

                    new LayoutRow(
                        new LayoutColumn(new Ruler().'<h4>Weitere Downloads</h4>')
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                                , 'Schulsoftware Leistungsbeschreibung', 'Stand 19.10.2023'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Leistungsbeschreibung'))
                        , 3),
                        new LayoutColumn(new Link((new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png')
                                , 'Benutzerrechte', 'empfohlene Benutzerrechte Stand:&nbsp;27.10.2023'))->setPictureHeight()
                                , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'UserRole')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Schnittstelle Schulsoftware zu DLLP', 'Stand:&nbsp;19.05.2025'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'SSW_DLLP')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Schuljahreswechsel Schulsoftware zu DLLP', 'Stand:&nbsp;21.05.2025'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'SSW_DLLP_year')
                        ), 3)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Digitales Klassenbuch', 'Stand:&nbsp;09.11.2022'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'DigitalClassBook')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Anleitung Fakturierung', 'Stand:&nbsp;21.02.2022'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Billing')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                            , 'Indiware Import', 'Leitfaden zur Informationsbeschaffung'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Indiware')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                            , 'Untis Import', 'Leitfaden zur Informationsbeschaffung'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Untis')
                        ), 3),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Kurzleitfaden Zeugniserstellung', 'Stand:&nbsp;28.04.2024'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Certificate')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWInfo.png')
                            , 'Kurzleitfaden Abschluss-/Abgangszeugnisse', 'Stand:&nbsp;18.02.2025'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Exam')
                        ), 3),
                        new LayoutColumn(new Link((new Thumbnail(
                            FileSystem::getFileLoader('/Common/Style/Resource/SSWPrint.png')
                            , 'Druck Abschlusszeugnisse A3', 'Stand:&nbsp;01.06.2021'))->setPictureHeight()
                            , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'PrintA3Certificate')
                        ), 3),
                    )),
                ))
            )
        );

        return $Stage;
    }
}

<?php
namespace SPHERE\Application\People\Person\Frontend;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Center;

/**
 * Class FrontendPersonPicture
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendPersonPicture extends FrontendReadOnly
{
    const TITLE = 'Personendaten';

    /**
     * @param null $PersonId
     *
     * @return string
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public static function getPersonPictureContent($PersonId = null)
    {
        $Image = '';

        $PictureHeight = '360px';
        $PictureBorderRadius = '10px';
        $PictureMarginTop = '49px';

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {

            $tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson);
            if($tblPersonPicture){
                $Image = $tblPersonPicture->getPicture($PictureHeight, $PictureBorderRadius, $PictureMarginTop);
            }
        }

        if(!$Image){
            $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence - Kopie.png');
            $Image = '<img src="'.$File->getLocation().'" style="border-radius: '.$PictureBorderRadius.'; height: '.$PictureHeight.'; margin-top: '.$PictureMarginTop.'">';
        }

        return ApiPersonPicture::receiverModal(). ApiPersonPicture::receiverBlock()
            .(new Link(new Center($Image), '#'))->ajaxPipelineOnClick(ApiPersonPicture::pipelineOpenModalPersonPicture($PersonId));
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getPersonPictureModalContent($PersonId = null)
    {
        $Image = '';

        $PictureHeight = '360px';
        $PictureBorderRadius = '0';
        $PictureMarginTop = '0';

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {

            $tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson);
            if($tblPersonPicture){
                $Image = $tblPersonPicture->getPicture($PictureHeight, $PictureBorderRadius, $PictureMarginTop);
            }
        }

        if(!$Image){
            $Image = new Info('kein Bild hinterlegt');
        }

        $Content = 'aktuelles Bild'.
            new Container($Image)
            .new Container(new Form(new FormGroup(new FormRow(array(
                new FormColumn(new FileUpload('FileUpload', '', 'Photo Upload')),
                new FormColumn((new Primary('Speichern', '#', new Save()))->ajaxPipelineOnClick(ApiPersonPicture::pipelineSavePersonPicture($PersonId)))
            )))));

        return $Content;
    }
}
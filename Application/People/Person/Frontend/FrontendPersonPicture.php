<?php
namespace SPHERE\Application\People\Person\Frontend;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FrontendPersonPicture
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendPersonPicture extends FrontendReadOnly
{
    const TITLE = 'Personendaten';

    /**
     * @param                   $PersonId
     * @param                   $Group
     * @param UploadedFile|null $FileUpload
     *
     * @return string
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public static function getPersonPictureContent($PersonId = null, $Group = null)
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

        return new Center((new Link($Image, '#'))->ajaxPipelineOnClick(ApiPersonPicture::pipelineEditPersonPicture($PersonId, $Group)));
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getEditPersonPictureContent($PersonId = null, $Group = null, UploadedFile $FileUpload = null)
    {
        $Image = '';

        $PictureHeight = '220px';
        $PictureBorderRadius = '0';
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
        $Image.= '<div style="height: 7px">&nbsp;</div>';

        //ToDO API Buttons
        $ButtonAbort = new Standard('Abbrechen', '/People/Person', new Disable(), array('Id' => $PersonId, 'Group' => $Group)); //->ajaxPipelineOnClick(ApiPersonPicture::pipelineLoadPersonPictureContent($PersonId, $Group));
        $ButtonDelete = (new DangerLink('Löschen', '#', new Disable()))->ajaxPipelineOnClick(ApiPersonPicture::pipelineLoadPersonPictureContent($PersonId, $Group));

        $form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(array(new FileUpload('FileUpload', '', 'Photo Upload'))),
            new FormColumn(array(new Primary('Speichern', new Save()), $ButtonAbort, $ButtonDelete))
            ))),null, false);

//        $form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Content = new Container(new Center($Image))
            .new Container(new Well(
                Storage::useService()->createPersonPicture($form, $PersonId, $Group, $FileUpload)
            ));

        return ApiPersonPicture::receiverBlock().$Content;
    }
}
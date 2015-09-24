<?php
namespace SPHERE\Application\Api\Test;

use MOC\V\Component\Template\Template;
use SPHERE\Application\Platform\System\Test\Test as App;
use SPHERE\Application\Platform\System\Test\Test;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Api\Test
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param $Id
     *
     * @return string
     */
    public function ShowImage($Id = null)
    {

        $Image = App::useService()->getTestPictureById($Id);
        ob_start();
        if ($Image) {
            //header("Content-Type: ".$Image->getImgType());
            header("Content-Type: image/png");
            //header("Content-Type: image/jpeg");
            $Content = imagecreatefromstring(stream_get_contents($Image->getImgData()));
            if (imagesx($Content) == imagesy($Content)) {
                $Thumb = imagecreatetruecolor(300, 300);
                imagecopyresampled($Thumb, $Content, 0, 0, 0, 0, 300, 300, imagesx($Content), imagesy($Content));
            } elseif (imagesx($Content) > imagesy($Content)) {

                $ImageY = ( imagesy($Content) / imagesx($Content) ) * 300;
                $Thumb = imagecreatetruecolor(300, $ImageY);
                imagecopyresampled($Thumb, $Content, 0, 0, 0, 0, 300, $ImageY, imagesx($Content), imagesy($Content));
            } else {
                $ImageX = ( imagesx($Content) / imagesy($Content) ) * 300;
                $Thumb = imagecreatetruecolor($ImageX, 300);
                imagecopyresampled($Thumb, $Content, 0, 0, 0, 0, $ImageX, 300, imagesx($Content), imagesy($Content));
            }

            //print imagejpeg($Thumb,null,100);
            print imagepng($Thumb, null, 9);

        }
        return ob_get_clean();
    }

    /**
     * @param $Id
     *
     * @return string
     */
    public function ShowContent($Id = null)
    {

        $Image = App::useService()->getTestPictureById($Id);
        ob_start();
        if ($Image) {
            header("Content-Type: text/plain");
            print stream_get_contents($Image->getImgData());
        }
        return ob_get_clean();
    }

    /**
     * @param null       $Id
     * @param bool|false $Option
     *
     * @return bool|string
     */
    public function ShowThumbnail($Id = null, $Option = false)
    {

        if ($Id === null) {
            return false;
        }
        $Picture = Test::useService()->getTestPictureById($Id);
        $Name = $Picture->getName();

        $Auth = new Authenticator(new Get());
        $Query = http_build_query($Auth->getAuthenticator()->createSignature(array('Id' => $Id),
            '/Api/Test/ShowImage'));
        $Button = null;
        if ($Option) {
            $Button = new Standard('', '/Platform/System/Test/Upload/Delete/Check', new Remove(), array('Id' => $Id),
                'Löschen');
        }

        return Template::getTwigTemplateString('<div class="thumbnail">
            <img class="img-responsive" src="/Api/Test/ShowImage?'.$Query.'">
            <div class="caption text-center">
            {{ Name }}
            </div>
            <div class="text-right" >
            {{ Button }}
            </div>
        </div>')
            ->setVariable('Button', $Button)
            ->setVariable('Name', $Name)
            ->getContent();
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function ShowFile($Id = null)
    {

        $Auth = new Authenticator(new Get());

        $Query = http_build_query($Auth->getAuthenticator()->createSignature(array('Id' => $Id),
            '/Api/Test/ShowContent'));

        return '<iframe id="File-'.$Id.'" style="display: block" src="/Api/Test/ShowContent?'.$Query.'"></iframe>';
    }
}

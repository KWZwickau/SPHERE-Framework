<?php

namespace SPHERE\Application\Platform\System\Test;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Test\Service\Data;
use SPHERE\Application\Platform\System\Test\Service\Entity\TblPicture;
use SPHERE\Application\Platform\System\Test\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Platform\System\Test
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface|null $form
     * @param int|null            $PersonId
     * @param UploadedFile|null   $FileUpload
     *
     * @return IFormInterface|string|null
     */
    public function uploadNow(IFormInterface &$form = null, $PersonId = null, UploadedFile $FileUpload = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $FileUpload
        ) {
            return $form;
        }

        if (!$FileUpload) {
            $form->setError('FileUpload', 'Bitte wählen Sie eine Datei');
            return $form;
        }

        try {
            $Upload = $this->getUpload('FileUpload', $FileUpload->getPath())
                ->validateMaxSize('2M')
                ->validateMimeType(array(
                    'image/png',
//                    'image/gif',
//                    'image/jpeg',
//                    'image/jpg',
                ));
//                    ->doUpload();

            $Dimension = $Upload->getDimensions();
            if(!($tblPerson = Person::useService()->getPersonById($PersonId))){
                $form .= new Danger('Person nicht gefunden');
            }

            if (!(new Data($this->getBinding()))->createPicture(
                $tblPerson, $Upload->getContent()

//                $Upload->getName(),
//                $Upload->getFilename(),
//                $Upload->getExtension(),
//                $Upload->getContent(),
//                $Upload->getMimeType(),
//                $Upload->getSize(),
//                $Dimension['width'],
//                $Dimension['height']
            )) {
                $form .= new Danger('Der Upload konnte nicht erfasst werden');
            } else {
                $form .= new Success('Der Upload ist erfasst')
                .new Redirect('/Platform/System/Test/TestSite', Redirect::TIMEOUT_SUCCESS);
                return $form;
            }
            unlink($Upload->getLocation().DIRECTORY_SEPARATOR.$Upload->getFilename());

        } catch (\Exception $Exception) {
            $form->setError('FileUpload', $Exception->getMessage());
        }

        return $form;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblPicture
     */
    public function getPictureByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPictureByPerson($tblPerson);
    }

    /**
     * @param TblPicture $tblPicture
     *
     * @return string
     */
    public function destroyPicture(TblPicture $tblPicture)
    {

        (new Data($this->getBinding()))->destroyPicture($tblPicture);
        return new Success('Das Bild wurde erfolgreich gelöscht')
        .new Redirect('/Platform/System/Test/TestSite', 1);
    }
}

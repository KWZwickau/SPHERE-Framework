<?php

namespace SPHERE\Application\Platform\System\Test;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\System\Test\Service\Data;
use SPHERE\Application\Platform\System\Test\Service\Entity\TblTestPicture;
use SPHERE\Application\Platform\System\Test\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service extends Extension implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        return (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param UploadedFile        $FileUpload
     *
     * @return IFormInterface|string
     */
    public function UploadNow(IFormInterface &$Stage = null, $FileUpload)
    {

        /**
         * Skip to Frontend
         */
        if (false === $FileUpload
        ) {
            return $Stage;
        }

        if (!$FileUpload) {
            $Stage->setError('FileUpload', 'Bitte wählen Sie eine Datei');

        } else {

            try {
                $Upload = $this->getUpload('FileUpload', __DIR__)
                    ->validateMaxSize('2M')
                    ->validateMimeType(array(
                        'image/png',
                        'image/gif',
                        'image/jpeg',
                        'image/jpg',
                    ));
//                    ->doUpload();

                $Dimension = $Upload->getDimensions();

                if (!(new Data($this->Binding))->createTestPicture(
                    $Upload->getName(),
                    $Upload->getFilename(),
                    $Upload->getExtension(),
                    $Upload->getContent(),
                    $Upload->getMimeType(),
                    $Upload->getSize(),
                    $Dimension['width'],
                    $Dimension['height']
                )
                ) {
                    $Stage .= new Danger('Der Upload konnte nicht erfasst werden');
//                        .new Redirect('/Platform/System/Test/Upload', 20);

                } else {
                    $Stage .= new Success('Der Upload ist erfasst');
//                        .new Redirect('/Platform/System/Test/Upload', 20);
                }

//                unlink($Upload->getLocation().DIRECTORY_SEPARATOR.$Upload->getFilename());

            } catch (\Exception $Exception) {

                $Stage->setError('FileUpload', $Exception->getMessage());
                return $Stage;
            }
        }
        return $Stage;
    }

    /**
     * @return bool|TblTestPicture[]
     */
    public function getTestPictureAll()
    {

        return (new Data($this->Binding))->getTestPictureAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblTestPicture
     */
    public function getTestPictureById($Id)
    {

        return (new Data($this->Binding))->getTestPictureById($Id);
    }
}

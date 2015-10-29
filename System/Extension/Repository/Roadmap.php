<?php
namespace SPHERE\System\Extension\Repository;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Roadmap\Release;

/**
 * Class Roadmap
 *
 * @package SPHERE\System\Extension\Repository
 */
class Roadmap
{

    /** @var Release[] $Release */
    private $Release = array();

    /**
     * @return Stage
     */
    public function getStage()
    {

        $Stage = new Stage('Roadmap');
        $Stage->setContent(
            implode($this->Release)
        );
        return $Stage;
    }

    public function getPdf()
    {

        Debugger::screenDump(__METHOD__);

        /** @var DomPdf $Document */
        $Document = Document::getDocument('Roadmap.pdf');
        $Document->setContent(
            Template::getTwigTemplateString(implode($this->Release))
        );
        Debugger::screenDump(__METHOD__);

        $Document->saveFile(new \MOC\V\Component\Document\Component\Parameter\Repository\FileParameter('Roadmap.pdf'));

        Debugger::screenDump(__METHOD__);

    }

    /**
     * @param string $Version Semantic-Version-Number (x.x.x)
     * @param string $Description
     * @param bool   $isDone  Stable & Public
     *
     * @return Release
     */
    public function createRelease($Version = '0.1.0', $Description = '', $isDone = null)
    {

        $Release = new Release($Version, $Description, $isDone);
        array_push($this->Release, $Release);
        return $Release;
    }
}


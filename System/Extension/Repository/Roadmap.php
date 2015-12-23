<?php
namespace SPHERE\System\Extension\Repository;

use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\FileTypePdf;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Roadmap\Category;
use SPHERE\System\Extension\Repository\Roadmap\Feature;
use SPHERE\System\Extension\Repository\Roadmap\Release;

/**
 * Class Roadmap
 *
 * @package SPHERE\System\Extension\Repository
 */
class Roadmap
{

    public static $ExcludeDone = false;
    /** @var Release[] $Release */
    private $Release = array();

    /**
     * @param bool $withDone
     *
     * @return Stage
     */
    public function getStage($withDone = true)
    {

        $Stage = new Stage('Roadmap');
        if ($withDone) {
            self::$ExcludeDone = false;
            $Stage->addButton(
                new Primary('Download', '/Api/Roadmap/Download', new Download())
            );
            $Stage->setContent(implode($this->Release));
        } else {
            self::$ExcludeDone = true;
            $ReleaseList = $this->Release;
            array_walk($ReleaseList, function (Release &$Release) {

                if ($Release->isDone()) {
                    $Release = false;
                } else {
                    $CategoryList = $Release->getCategoryList();
                    array_walk($CategoryList, function (Category &$Category) {

                        if ($Category->getStatus()->getDonePercent() == 100) {
                            $Category = false;
                        } else {
                            $FeatureList = $Category->getFeatureList();
                            array_walk($FeatureList, function (Feature &$Feature) {

                                if ($Feature->getStatus()->getDonePercent() == 100) {
                                    $Feature = false;
                                } else {

                                }
                            });
                            $Category->setFeatureList(array_filter($FeatureList));
                        }
                    });
                    $Release->setCategoryList(array_filter($CategoryList));
                }
            });
            $ReleaseList = array_filter($ReleaseList);
            $Stage->setContent(
                implode($ReleaseList).
                new Standard('Download', '/Api/Roadmap/Download', new FileTypePdf())
            );
        }
        return $Stage;
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

    /**
     * @return string
     */
    public function getVersionNumber()
    {

        $Last = null;
        foreach ($this->Release as $Release) {
            if ($Release->isDone()) {
                $Last = $Release;
            } else {
                if (!$Last) {
                    $Last = $Release;
                }
            }
        }
        if ($Last) {
            return $Last->getVersion();
        } else {
            return 'x.x.x';
        }
    }
}


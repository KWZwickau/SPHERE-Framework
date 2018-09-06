<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 22.06.2017
 * Time: 08:36
 */

namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\C01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\D01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E02_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E03;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E04;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E04_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E05;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\E07;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\F01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS\G01;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

/**
 * Class KamenzReportGS
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class KamenzReportGS extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Kamenz-Statistik GS';
    }

    /**
     * @param array $pageList
     *
     * @return $this|Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSliceArray(C01::getContent())
                ->addSliceArray(D01::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E01::getContent())
                ->addSliceArray(E02::getContent())
                ->addSliceArray(E02_1::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E03::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E04::getContent())
                ->addSliceArray(E04_1::getContent())
                ->addSliceArray(E05::getContent())
                ->addSliceArray(E07::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(F01::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(G01::getContent())
            )
        );
    }
}
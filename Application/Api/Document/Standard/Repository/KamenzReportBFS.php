<?php

namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\B01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\B02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\F01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\K01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\N01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\N03;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\N05;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\S01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\S02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\S04;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS\S04_1_1;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

/**
 * Class KamenzReportBFS
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class KamenzReportBFS extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Kamenz-Statistik';
    }

    /**
     *
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_1_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_1_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_2_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_2_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_2_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B01::getContent('B01_2_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_1_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_1_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_2_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_2_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_2_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(B02::getContent('B02_2_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(F01::getContent('F01_1', 'Berufsfachschule'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(F01::getContent('F01_2', 'Berufsfachschule'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(K01::getContent())
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_1_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_1_1_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_2_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_2_U'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_2_1_A'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N01::getContent('N01_2_1_U'))
//            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_1_A'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_1_U'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_1_1_A'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_1_1_U'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_2_A'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_2_U'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_2_1_A'))
            )
            ->addPage((new Page())
                ->addSliceArray(N01::getContent('N02_2_1_U'))
            )






//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N03_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N03_1_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N03_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N03_2_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N04_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N03::getContent('N04_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N05::getContent('N05'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(N05::getContent('N05_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S01::getContent('S01'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S01::getContent('S01_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S02::getContent('S02_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S02::getContent('S02_1_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S02::getContent('S02_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S02::getContent('S02_2_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S02::getContent('S03_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S02::getContent('S03_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S04::getContent('S04_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S04::getContent('S04_2'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S04_1_1::getContent('S04_1_1'))
//            )
//            ->addPage((new Page())
//                ->addSliceArray(S04_1_1::getContent('S04_2_1'))
//            )
        );
    }
}
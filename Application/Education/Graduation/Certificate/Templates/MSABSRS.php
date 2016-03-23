<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

class MSABSRS extends Extension implements IFrontendInterface
{

    public function frontendCreate($Data, $Content = null)
    {

        // TODO: Find Template in Database (DMS)
        $this->getCache(new TwigHandler())->clearCache();

        $Header = (new Slice())
            ->addSection(
                (new Section())
                    ->addColumn(
                        (new Element())
                            ->setContent('MS RS Abschlusszeugnis 3K.pdf')
                            ->styleTextSize('12px')
                            ->styleTextColor('#CCC')
                            ->styleAlignCenter()
                        , '25%'
                    )->addColumn(
                        (new Element\Sample())
                            ->styleTextSize('30px')
                    )->addColumn(
                        (new Element())
                        , '25%'
                    )
            );

        $Content = (new Frame())->addDocument(
            (new Document())
                ->addPage(
                    (new Page())
                        ->addSlice(
                            $Header
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                            , '68%'
                                        )->addColumn(
                                            (new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '200px')), '25%'
                                        )->addColumn(
                                            (new Element())
                                            , '7%'
                                        )
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('ABSCHLUSSZEUGNIS')
                                        ->styleTextSize('27px')
                                        ->styleAlignCenter()
                                        ->styleMarginTop('32%')
                                        ->styleTextBold()
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('der Mittelschule')
                                        ->styleTextSize('22px')
                                        ->styleAlignCenter()
                                        ->styleMarginTop('15px')
                                )
                        )
                )
                ->addPage(
                    (new Page())
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vorname und Name:')
                                            , '22%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('50px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('geboren am')
                                            , '22%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Division }}')
                                                ->styleBorderBottom()
                                            , '20%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('in')
                                                ->styleAlignCenter()
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('wohnhaft in')
                                            , '22%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.School.Course }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('hat')
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.School.Name }}')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom('1px', '#BBB')
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom('1px', '#BBB')
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('besucht')
                                                ->styleAlignRight()
                                            , '10%')
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Name und Anschrift der Schule')
                                        ->styleTextSize('9px')
                                        ->styleTextColor('#999')
                                        ->styleAlignCenter()
                                        ->styleMarginTop('5px')
                                        ->styleMarginBottom('5px')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('und hat nach Bestehen der Abschlussprüfung den')
                                        ->styleMarginTop('8px')
                                        ->styleAlignLeft()
                                )
                                ->addElement(
                                    (new Element())
                                        ->setContent('REALSCHULABSCHLUSS')
                                        ->styleMarginTop('18px')
                                        ->styleTextSize('20px')
                                        ->styleTextBold()
                                )
                                ->addElement(
                                    (new Element())
                                        ->setContent('erworben.')
                                        ->styleMarginTop('20px')
                                        ->styleAlignLeft()
                                )
                                ->styleAlignCenter()
                                ->styleMarginTop('20%')
                        )
//                        ->addSlice(
//                            (new Slice())
//                                ->addSection(
//                                    (new Section())
//                                        ->addColumn(
//                                            (new Element())
//                                                ->setContent('¹ Zutreffendes ist zu unterstreichen.')
//                                                ->styleTextSize('9px')
//                                                ->styleBorderTop()
//                                            , '33%')
//                                        ->addColumn(
//                                            (new Element())
//                                        )
//                                )
//                                ->styleMarginTop('518px')
//                        )
                )
                ->addPage(
                    (new Page())
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vorname und Name:')
                                            , '25%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                            , '45%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Klasse')
                                                ->styleAlignCenter()
                                            , '10%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Division }}')
                                                ->styleBorderBottom()
                                        )
                                )->styleMarginTop('50px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Leistungen in den einzelnen Fächern:')
                                        ->styleMarginTop('15px')
                                        ->styleTextBold()
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Deutsch')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Mathematik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('7px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Englisch')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Biologie')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Kunst/Musik¹')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Chemie')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Geschichte/Geographie¹')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Physik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Gemeinschaftskunde/Rechtserziehung')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Sport')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                                ->styleBorderBottom()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('EV./Kath. Religion/Ethik¹')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                                ->styleBorderBottom()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Informatik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Wahlpflichtbereich:')
                                        ->styleMarginTop('15px')
                                        ->styleTextBold()
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                            , '9%')
                                )
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vertiefungskurs/2. Fremdsprache (abschlussorientiert)¹')
                                                ->styleTextSize('10px')
                                        )
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Leistungen in Fächern, die in Klassenstufe 9 abgeschlossen wurden:')
                                        ->styleMarginTop('15px')
                                        ->styleTextBold()
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Kunst/Musik¹')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Geschichte/Geographie¹')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('7px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Wirtschaft-Technik-Haushalt/Soziales')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                                ->styleBorderBottom()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('7px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Bemerkungen:')
                                            , '16%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Datum:')
                                            , '7%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent(date('d.m.Y'))
                                                ->styleBorderBottom('1px', '#000')
                                                ->styleAlignCenter()
                                            , '23%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                )
                                ->styleMarginTop('30px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Der Prüfungsausschuss')
                                                ->styleAlignCenter()
                                            , '40%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBorderBottom('1px', '#000')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '40%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBorderBottom('1px', '#000')
                                            , '30%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vorsitzende(r)')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Dienstsiegel der Schule')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Mitglied')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                            , '70%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBorderBottom('1px', '#000')
                                            , '30%')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                            , '70%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Mitglied')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->styleBorderBottom()
                                            , '30%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                            , '70%'
                                        )
                                )->styleMarginTop('208px')
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Notenerläuterung:<br/>
                                                    1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                                    6 = ungenügend<br/>
                                                    ¹ &nbsp;&nbsp;&nbsp; Zutreffendes ist zu unterstreichen.')
                                                ->styleTextSize('9.5px')
                                            , '30%')
                                )
                        )
                )
        );

        $Content->setData($Data);

        $Preview = $Content->getContent();

        $Stage = new Stage();

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(array(
                '<div class="cleanslate">'.$Preview.'</div>'
            ), 12),
        )))));

        return $Stage;
    }
}
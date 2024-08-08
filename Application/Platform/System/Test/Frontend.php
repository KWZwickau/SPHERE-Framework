<?php
namespace SPHERE\Application\Platform\System\Test;

use MOC\V\Core\FileSystem\FileSystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SPHERE\Application\Api\Platform\Test\ApiSystemTest;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Button\Reset;
use SPHERE\Common\Frontend\Form\Repository\Button\Standard as BtnStandard;
use SPHERE\Common\Frontend\Form\Repository\Button\Success;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\Editor;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextCaptcha;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title as TitleForm;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Badge;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutSocial;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Platform\Test
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPlatform()
    {
//        $Global = $this->getGlobal();
//        $this->getDebugger()->screenDump($Global);
//        $this->getDebugger()->screenDump($_REQUEST);
//        $this->getDebugger()->screenDump($_FILES);

        $Stage = new Stage('Test', 'Frontend');

        $Stage->setMessage(
            'Message: Red alert. Processor of a distant x-ray vision, lower the death! Make it so, chemical
             wind! Fantastic nanomachines, to the alpha quadrant.Boldly sonic showers lead to the understanding. The 
             death is a ship-wide cosmonaut. Wobble nosily like a post-apocalyptic space suit.Cosmonauts are the 
             emitters of the fantastic ionic cannon. Where is the strange teleporter?'
        );

        $Stage->addButton(
            new Standard('Link', new Route(__NAMESPACE__), null, array(), true)
        );
        $Stage->addButton(
            new External('Link', 'http://www.google.de')
        );

        $D1 = new TblDivisionCourse();$D1->setName('A');$D1->setId(1);
        $D2 = new TblDivisionCourse();$D2->setName('B');$D2->setId(2);
        $D3 = new TblDivisionCourse();$D3->setName('C');$D3->setId(3);
        $D4 = new TblDivisionCourse();$D4->setName('D');$D4->setId(4);
        $D5 = new TblDivisionCourse();$D5->setName('E');$D5->setId(5);
        $D6 = new TblDivisionCourse();$D6->setName('F');$D6->setId(6);
        $D7 = new TblDivisionCourse();$D7->setName('G');$D7->setId(7);
        $D8 = new TblDivisionCourse();$D8->setName('H');$D8->setId(8);

        $Check = array($D1, $D2, $D3, $D4, $D5, $D6, $D7, $D8);
        $Check2 = array($D1, $D2, $D3, $D4, $D5, $D6, $D7);
        $Check3 = array($D1, $D2);

        $IconList = array();
        if (false !== ( $Path = realpath(__DIR__.'/../../../../Common/Frontend/Icon/Repository') )) {
            $Iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($Path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                $Namespace = '\SPHERE\Common\Frontend\Icon\Repository';
                $Class = $FileInfo->getBasename('.php');
                $Loader = $Namespace.'\\'.$Class;

                $IconList[$Class] = new PullLeft(
                    '<div style="margin: 5px; border: 1px solid silver; width: 100px;">'
                    .'<div style="font-size: large; border-bottom: 1px dotted silver;" class="text-center">'.new $Loader().'</div>'
                    .'<div class="text-center">'.$Class.'</div>'
                    .'</div>'
                );
            }
            ksort($IconList);
        }

        $Receiver = 'Modal benötigt die API Route "/Api/Platform/Test/ApiSystemTest"'
            .ApiSystemTest::receiverFirstModal()
            .ApiSystemTest::receiverSecondModal()
            .ApiSystemTest::receiverThirdModal();
        $firstReceiverButton = (new Standard('Öffne ein Modal', ApiSystemTest::getEndpoint()))
            ->ajaxPipelineOnClick(ApiSystemTest::pipelineOpenFirstModal());
        $secondReceiverButton = (new Standard('Modal mit Form', ApiSystemTest::getEndpoint()))
            ->ajaxPipelineOnClick(ApiSystemTest::pipelineOpenSecondModal());
        $thirdReceiverButton = (new Standard('Modal mit "Laden"', ApiSystemTest::getEndpoint()))
            ->ajaxPipelineOnClick(ApiSystemTest::pipelineOpenThirdModal());
        $fourReceiverButton = (new Standard('Modal mit "Tabs"', ApiSystemTest::getEndpoint()))
            ->ajaxPipelineOnClick(ApiSystemTest::pipelineOpenFourthModal());
        // reconstruct Table with content
        $CheckboxList = array(
            new CheckBox('ToggleSelective1', 'T1', 1),
            new CheckBox('ToggleSelective2', 'T2', 2),
            new CheckBox('ToggleSelective3', 'T3', 3),
            new CheckBox('ToggleSelective4', 'T4', 4),
            new CheckBox('ToggleSelective5', 'T5', 5)
        );


        $CheckBoxTable = new TableData(
            array(
                '1' => array('Check' => new CheckBox('ToggleTable1', 'C1', 1),),
                '2' => array('Check' => new CheckBox('ToggleTable2', 'C2', 2),),
                '3' => array('Check' => new CheckBox('ToggleTable3', 'C3', 3),),
                '4' => array('Check' => new CheckBox('ToggleTable4', 'C4', 4),),
            ), null, array('Check' => 'CheckBox'), null);

        // Editor Beispiel
        if(!isset($_POST['Feld1'])){
            $_POST['Feld1'] = '<strong>Wert 1</strong>';
        }
        if(!isset($_POST['Feld2'])){
            $_POST['Feld2'] = '<strong>Wert 2</strong>';
        }
        if(!isset($_POST['Feld3'])){
            $_POST['Feld3'] = '<strong>Wert 3</strong>';
        }
        if(!isset($_POST['Feld4'])){
            $_POST['Feld4'] = '<strong>Wert 4</strong>';
        }

        $TableEditorList = array();
        $Editor = array('Description' => 'Inputfelder' ,'first' => new Editor('Feld1'), 'second' => new Editor('Feld2'), 'third' => new TextArea('Feld3'), 'fourth' => new TextField('Feld4'));$TableEditorList[] = $Editor;
        $Editor = array('Description' => 'Feldobjekt' ,'first' => 'Editor', 'second' => 'Editor 2', 'third' => 'Text Area', 'fourth' => 'Text Filed');$TableEditorList[] = $Editor;
        $Editor = array('Description' => 'Verhalten' ,'first' => 'behält HTML Tags', 'second' => 'behält HTML Tags', 'third' => 'trimt die HTML Tags weg', 'fourth' => 'trimt die HTML Tags weg');$TableEditorList[] = $Editor;

        $Stage->setContent(
            (new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new AutoCompleter('AutoCompleter', 'AutoCompleter', 'AutoCompleter',
                                array('123', '234', '345')),
                            new SelectCompleter('SelectCompleter', 'SelectCompleter', 'SelectCompleter',
                                array('', '1+','1','1-', '2', '2-','2+','3','3-','3+','4','4-','4+','5','5-','5+','6','6-','6+')
                            )
                        ), 3),
                        new FormColumn(array(
                            new CheckBox('CheckBox', 'CheckBox', 'c1'),
                            new RadioBox('RadioBox1', 'RadioBox1a', '1a'),
                        ), 3),
                        new FormColumn(
                            new DatePicker('DatePicker', 'DatePicker', 'DatePicker')
                            , 3),
                        new FormColumn(array(
                            new FileUpload('FileUpload[1]', 'FileUpload', 'FileUpload'),
                            new FileUpload('FileUpload[2]', 'FileUpload', 'FileUpload'),
                            new FileUpload('FileUpload[A]', 'FileUpload', 'FileUpload'),
                            new FileUpload('FileUpload[B]', 'FileUpload', 'FileUpload'),
                        ), 3),
                    )),
                    new FormRow(array(
                        new FormColumn(
                            new HiddenField('HiddenField', 'HiddenField', 'HiddenField')
                            , 3),
                        new FormColumn(
                            new NumberField('NumberField', 'NumberField', 'NumberField')
                            , 3),
                        new FormColumn(
                            new PasswordField('PasswordField', 'PasswordField', 'PasswordField')
                            , 3),
                        new FormColumn(array(
                            new RadioBox('RadioBox1', 'RadioBox1b', '1b'),
                            new RadioBox('RadioBox2', 'RadioBox2', '2'),
                            new RadioBox('RadioBox3', 'RadioBox3', '3'),
                        ), 1),
                        new FormColumn(
                            new Panel('Farben bei RadioBox',
                                new RadioBox('RadioBoxColor1', 'Standard', 1, RadioBox::RADIO_BOX_TYPE_DEFAULT)
                                    .new RadioBox('RadioBoxColor2', 'Black', 2, RadioBox::RADIO_BOX_TYPE_BLACK)
                                    .new RadioBox('RadioBoxColor3', 'Info', 3, RadioBox::RADIO_BOX_TYPE_INFO)
                                    .new RadioBox('RadioBoxColor4', 'Success', 4, RadioBox::RADIO_BOX_TYPE_SUCCESS)
                                    .new RadioBox('RadioBoxColor5', 'Warning', 5, RadioBox::RADIO_BOX_TYPE_WARNING)
                                    .new RadioBox('RadioBoxColor6', 'Danger', 6, RadioBox::RADIO_BOX_TYPE_DANGER)
                                )
                        , 1),
                        new FormColumn(
                            new Panel('ToolTip',
                                new ToolTip('Test mit Umbruch', 'Text der umbricht bei Platzmangel')
                                .new Ruler()
                                .new Container(new ToolTip('Test ohne Umbruch (benötigt bei z.B. kleinen spalten in Tabellen)', 'Text der nicht umbrechen kann', false))
                            )
                        , 1),
                    )),
                    new FormRow(array(
                        new FormColumn(array(
                            (new SelectBox('SelectBox1', 'SelectBox - Bootstrap (funktioniert nicht auf Tablet\'s)',
                                array('0' => 'A', '2' => '1', '3' => '2', '4' => '3'), new Select()
                            ))->configureLibrary( SelectBox::LIBRARY_SELECTER ),
                            (new SelectBox('SelectBox2', 'SelectBox2 - Lang (ab 7 Einträge) - Default',
                                array('{{ Id }}{{ Name }} nochmal {{ Name }} "Twig test"' => $Check)
                            ))->configureLibrary( SelectBox::LIBRARY_SELECT2 ),
                            (new SelectBox('SelectBox3', 'SelectBox2 - Kurz',
                                array('{{ Id }}{{ Name }} nochmal {{ Name }} "Twig test"' => $Check2)
                            ))->configureLibrary( SelectBox::LIBRARY_SELECT2 ),
                            (new SelectBox('SelectBox4', 'SelectBox2 - Filter bei weniger Einträgen aktivieren',
                                array('{{ Id }}{{ Name }} nochmal {{ Name }} "Twig test"' => $Check3)
                            ))->setMinimumResultForSerach(3),
                        ), 3),
                        new FormColumn(
                            new TextArea('TextArea', 'TextArea', 'TextArea')
                            , 3),
//                        new FormColumn(
//                            new TextCaptcha('TextCaptcha', 'TextCaptcha', 'TextCaptcha')
//                            , 3),
                        new FormColumn(
                            new TextField('TextField', 'TextField', 'TextField')
                            , 3),
                    )),
                    new FormRow(array(
                        new FormColumn(array(
                            new ToggleCheckbox('Alle wählen/abwählen', $CheckBoxTable),
                            $CheckBoxTable
                        ), 6),
                        new FormColumn(
                            new Layout( new LayoutGroup(new LayoutRow( array(
                                new LayoutColumn(
                                    new ToggleSelective('Alle wählen/abwählen', array(
                                        'ToggleSelective1', 'ToggleSelective2', 'ToggleSelective3', 'ToggleSelective4', 'ToggleSelective5'
                                    ))
                                    , 3),
                                new LayoutColumn(
                                    new ToggleSelective('2-5 '.new Check().' / '.new Unchecked(), array(
                                        'ToggleSelective2', 'ToggleSelective3', 'ToggleSelective4', 'ToggleSelective5'
                                    ))
                                    , 2),
                                new LayoutColumn(
                                    new ToggleSelective('4-5 '.new Check().' / '.new Unchecked(), array(
                                        'ToggleSelective4', 'ToggleSelective5'
                                    ))
                                    , 2),
                                new LayoutColumn(
                                    (new ToggleSelective(new Bold('4-5 '.new Check()), array(
                                        'ToggleSelective4', 'ToggleSelective5'
                                    )))->setMode(1)
                                    , 2),
                                new LayoutColumn(
                                    (new ToggleSelective(new Bold('4-5 '.new Unchecked()), array(
                                        'ToggleSelective4', 'ToggleSelective5'
                                    )))->setMode(2)
                                    , 2),
                                new LayoutColumn(
                                    $CheckboxList
                                )
                            ))))
                        , 6)
                    ))
//                    new FormRow( array(
//                        new FormColumn(
//                            new \SPHERE\Common\Frontend\Form\Repository\Title('Title')
//                        ,3),
//                        new FormColumn(
//                            new Aspect('Aspect')
//                        ,3),
//                    ) )
                ), new TitleForm('Form-Title')),
                array(
                    new Primary('Primary'),
                    new Danger('Danger'),
                    new Success('Success'),
                    new Reset('Reset'),
                    new BtnStandard('Standard')
                )
            ))->setConfirm('Wirklich?')
            .new Form(new FormGroup(new FormRow(array(new FormColumn(
                new TableData($TableEditorList, null,
                    array(
                        'Description' => 'Zeilenbeschreibung',
                        'first' => 'Spalte 1',
                        'second' => 'Spalte 2',
                        'third' => 'Spalte 3',
                        'fourth' => 'Spalte 4'),
                    false
                )),
            )), new TitleForm('Editor und das '.htmlspecialchars('<tag>').' verhalten mit POST/REQUEST')), new Primary('Abschicken'))
            .new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
//                        new LayoutColumn( array(
//                            new Address( null )
//                        ), 3 ),
                        new LayoutColumn(array(
                            new Badge('Badge')
                        ), 3),
                        new LayoutColumn(array(
                            new Container('Container')
                        ), 3),
                        new LayoutColumn(array(
                            new Header('Header')
                        ), 3),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Label('Label')
                        ), 3),
                        new LayoutColumn(array(
                            new Listing('Listing')
                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Panel', array('Conten 1', 'Content 2', 'Content 3'),
                                Panel::PANEL_TYPE_DEFAULT, 'Footer')
                        ), 3),
                        new LayoutColumn(array(
                            new PullRight('PullRight')
                        ), 3),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
                                'Title', 'Description',
                                array(new \SPHERE\Common\Frontend\Link\Repository\Primary('Primary', ''))
                            )
                        ), 3),
                        new LayoutColumn(array(
                            new Well('Well')
                        ), 3),
                        new LayoutColumn(
                            new TableData(array(
                                array('A' => 1, 'B' => '2'),
                                array('A' => 2, 'B' => '34567890'),
                                array('A' => 'SelectBox Width Test DT', 'B' =>
                                    (new SelectBox('SelectBox2DT', 'SelectBox - jQuery Select2',
                                    array('{{ Id }}{{ Name }}{{ Name }} {{ Id }}{{ Name }}{{ Name }}' => $Check)
                                    ))->configureLibrary( SelectBox::LIBRARY_SELECT2 )
                                ),
                                array('A' => 'SelectCompleter', 'B' =>
                                    new SelectCompleter('SelectCompleterDT', 'SelectCompleter', 'SelectCompleter',
                                        array('', '1+','1','1-', '2', '2-','2+','3','3-','3+','4','4-','4+','5','5-','5+','6','6-','6+')
                                    )
                                )
                            ))
                            , 6),

                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            '<hr/>'
                        )),

                    )),

                ), new Title('Layout-Title')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new LayoutTabs(array(
                            new LayoutTab('Name1', 0),
                            new LayoutTab('Name2', 1),
                            new LayoutTab('Name3', 2),
                        )), 3),
                        new LayoutColumn(
                            (new LayoutSocial())
                                ->addMediaItem('Head1', new Paragraph('Content').new Paragraph('Content'), new Time())
                                ->addMediaItem('Head2', 'Content',
                                    '<img src="/Common/Style/Resource/logo_kreide2.png" alt="Logo" class="image-responsive" style="width:20px;"/>',
                                    '', LayoutSocial::ALIGN_BOTTOM)
                                ->addMediaList(
                                    (new LayoutSocial())
                                        ->addMediaItem('Head2.1',
                                            new Well(new Paragraph('Content').new Paragraph('Content')),
                                            '<img src="/Common/Style/Resource/logo_kreide2.png" alt="Logo" class="image-responsive" style="width:20px;"/>',
                                            '', LayoutSocial::ALIGN_TOP)
                                        ->addMediaItem('', new Well('Content'),
                                            '<img src="/Common/Style/Resource/logo_kreide2.png" alt="Logo" class="image-responsive" style="width:20px;"/>',
                                            '', LayoutSocial::ALIGN_MIDDLE)
                                )
                            , 4),
                    )),

                ), new Title('Layout Development')),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $Receiver
                            , 3),
                        new LayoutColumn(
                            $firstReceiverButton
                            .$secondReceiverButton
                            .$thirdReceiverButton
                            .$fourReceiverButton
                            , 9)
                    ))
                    , new Title('ModalReceiver')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(implode($IconList)),
                    )),
                ), new Title('Icons'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $FileUpload
     *
     * @return Stage
     */
    public function frontendTestSite($PersonId = null, $FileUpload = null)
    {

        $Stage = new Stage('Test Site', '');
//        echo ini_get('upload_max_filesize'), ", " , ini_get('post_max_size');

//        // Editor Beispiel
//        if(!isset($_POST['Feld1'])){
//            $_POST['Feld1'] = '<strong>Wert 1</strong>';
//        }
//        if(!isset($_POST['Ebene1']['Feld2'])){
//            $_POST['Ebene1']['Feld2'] = '<em><strong>Wert 2</strong></em>';
//        }
//        if(!isset($_POST['Ebene1']['Ebene2']['Feld3'])){
//            $_POST['Ebene1']['Ebene2']['Feld3'] = '<span style="text-decoration: underline;"><strong>Wert 3</strong></span>';
//        }
//        if(!isset($_POST['Feld4'])){
//            $_POST['Feld4'] = '<strong>Wert 4</strong>';
//        }
//        if(!isset($_POST['Feld5'])){
//            $_POST['Feld5'] = '<strong>Wert 5</strong>';
//        }
//        if(!isset($_POST['Data']['Feld6'])){
//            $_POST['Data']['Feld6'] = '<strong>Wert 6</strong>';
//        }
//
//        $TableEditorList = array();
//        $Editor = array(
//            'Description' => 'Inputfelder',
//            'first' => new Editor('Feld1'),
//            'second' => new Editor('Ebene1[Feld2]'),
//            'third' => new Editor('Ebene1[Ebene2][Feld3]'),
//            'fourth' => new TextArea('Feld4'),
//            'fifth' => new TextField('Feld5'),
//            'sixth' => new TextField('Data[Feld6]')
//        );
//        $TableEditorList[] = $Editor;
//        $Editor = array('Description' => 'Feldobjekt',
//                        'first' => 'Editor',
//                        'second' => 'Editor 2 Ebenen im POST',
//                        'third' => 'Editor 3 Ebenen im POST',
//                        'fourth' => 'Text Area',
//                        'fifth' => 'Text Filed',
//                        'sixth' => 'Text Filed 2 Ebenen im POST'
//        );
//        $TableEditorList[] = $Editor;
//        $Editor = array('Description' => 'Verhalten',
//                        'first' => 'behält HTML Tags',
//                        'second' => 'behält HTML Tags',
//                        'third' => 'behält HTML Tags',
//                        'fourth' => 'trimt die HTML Tags weg',
//                        'fifth' => 'trimt die HTML Tags weg',
//                        'sixth' => 'trimt die HTML Tags weg'
//        );
//        $TableEditorList[] = $Editor;
//
//        $Stage->setContent(
//            new Form(new FormGroup(new FormRow(array(new FormColumn(
//                new TableData($TableEditorList, null,
//                    array(
//                        'Description' => 'Zeilenbeschreibung',
//                        'first'       => 'Spalte 1',
//                        'second'      => 'Spalte 2',
//                        'third'       => 'Spalte 3',
//                        'fourth'      => 'Spalte 4',
//                        'fifth'       => 'Spalte 5',
//                        'sixth'       => 'Spalte 6'
//                    ),
//                    false
//                )),
//            )), new TitleForm('Editor und das '.htmlspecialchars('<tag>').' verhalten mit POST/REQUEST')), new Primary('Abschicken'))
//        );

        if(!$PersonId){

            $Picture = '';
            $TableContent = array();
            if(($tblPersonAll = Person::useService()->getPersonAll())){
                foreach($tblPersonAll as $tblPerson){
                    $item = array();
                    $item['Name'] = $tblPerson->getFullName();
                    $item['Picture'] = '';
                    if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                        $item['Picture'] = $tblPersonPicture->getPicture('70px', '15px');
//                        if($tblPerson->getId() ==1228)
//                        $Picture = $tblPersonPicture->getFile('40px', '40px');
                    }
                    $item['Option'] = new Standard('', '/Platform/System/Test/TestSite', new Plus(), array('PersonId' => $tblPerson->getId()));
                    array_push($TableContent, $item);
                }
            }
            $Stage->setContent(
                new TableData($TableContent, null, array(
                'Name' => 'Vollständiger Name',
                'Picture' => 'Bild',
                'Option' => '',
                )
            ));
        } else {
            $form = new Form(new FormGroup(new FormRow(new FormColumn(
                new FileUpload('FileUpload', '', 'Photoupload')
            ))));
            $form->appendFormButton(new Primary('Speichern', new Save()));
            $form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Well(
                    Storage::useService()->uploadNow($form, $PersonId, $FileUpload)
                )
            );
        }

        return $Stage;
    }
}

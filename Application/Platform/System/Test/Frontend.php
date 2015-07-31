<?php
namespace SPHERE\Application\Platform\System\Test;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Button\Reset;
use SPHERE\Common\Frontend\Form\Repository\Button\Success;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextCaptcha;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Badge;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Platform\Test
 */
class Frontend implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPlatform()
    {

        $Stage = new Stage( 'Test', 'Frontend' );

        $Stage->setContent(
            ( new Form(
                new FormGroup( array(
                    new FormRow( array(
                        new FormColumn(
                            new AutoCompleter( 'AutoCompleter', 'AutoCompleter', 'AutoCompleter',
                                array( '123', '234', '345' ) )
                            , 3 ),
                        new FormColumn( array(
                            new CheckBox( 'CheckBox', 'CheckBox', 'c1' ),
                            new RadioBox( 'RadioBox1', 'RadioBox1a', '1a' ),
                        ), 3 ),
                        new FormColumn(
                            new DatePicker( 'DatePicker', 'DatePicker', 'DatePicker' )
                            , 3 ),
                        new FormColumn(
                            new FileUpload( 'FileUpload', 'FileUpload', 'FileUpload' )
                            , 3 ),
                    ) ),
                    new FormRow( array(
                        new FormColumn(
                            new HiddenField( 'HiddenField', 'HiddenField', 'HiddenField' )
                            , 3 ),
                        new FormColumn(
                            new NumberField( 'NumberField', 'NumberField', 'NumberField' )
                            , 3 ),
                        new FormColumn(
                            new PasswordField( 'PasswordField', 'PasswordField', 'PasswordField' )
                            , 3 ),
                        new FormColumn( array(
                            new RadioBox( 'RadioBox1', 'RadioBox1b', '1b' ),
                            new RadioBox( 'RadioBox2', 'RadioBox2', '2' ),
                            new RadioBox( 'RadioBox3', 'RadioBox3', '3' ),
                        ), 3 ),
                    ) ),
                    new FormRow( array(
                        new FormColumn(
                            new SelectBox( 'SelectBox', 'SelectBox',
                                array( '0' => 'A', '2' => '1', '3' => '2', '4' => '3' ) )
                            , 3 ),
                        new FormColumn(
                            new TextArea( 'TextArea', 'TextArea', 'TextArea' )
                            , 3 ),
                        new FormColumn(
                            new TextCaptcha( 'TextCaptcha', 'TextCaptcha', 'TextCaptcha' )
                            , 3 ),
                        new FormColumn(
                            new TextField( 'TextField', 'TextField', 'TextField' )
                            , 3 ),
                    ) ),
//                    new FormRow( array(
//                        new FormColumn(
//                            new \SPHERE\Common\Frontend\Form\Repository\Title('Title')
//                        ,3),
//                        new FormColumn(
//                            new Aspect('Aspect')
//                        ,3),
//                    ) )
                ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Form-Title' ) ),
                array(
                    new Primary( 'Primary' ),
                    new Danger( 'Danger' ),
                    new Success( 'Success' ),
                    new Reset( 'Reset' )
                )
            ) )
            .new Layout(
                new LayoutGroup( array(
                    new LayoutRow( array(
//                        new LayoutColumn( array(
//                            new Address( null )
//                        ), 3 ),
                        new LayoutColumn( array(
                            new Badge( 'Badge' )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Container( 'Container' )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Header( 'Header' )
                        ), 3 ),
                    ) ),
                    new LayoutRow( array(
                        new LayoutColumn( array(
                            new Label( 'Label' )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Listing( 'Listing' )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Panel( 'Panel', array( 'Conten 1', 'Content 2', 'Content 3' ),
                                Panel::PANEL_TYPE_DEFAULT, 'Footer' )
                        ), 3 ),
                        new LayoutColumn( array(
                            new PullRight( 'PullRight' )
                        ), 3 ),
                    ) ),
                    new LayoutRow( array(
                        new LayoutColumn( array(
                            new Thumbnail(
                                FileSystem::getFileLoader( '/Common/Style/Resource/logo_kreide2.png' ),
                                'Title', 'Description',
                                array( new \SPHERE\Common\Frontend\Link\Repository\Primary( 'Primary', '' ) )
                            )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Well( 'Well', array() )
                        ), 3 ),
                        new LayoutColumn(
                            new TableData( array(
                                array( 'A' => 1, 'B' => '2' ),
                                array( 'A' => 2, 'B' => '34567890' )
                            ) )
                            , 6 ),

                    ) ),
                ), new Title( 'Layout-Title' ) )
            )
        );

        return $Stage;
    }

}

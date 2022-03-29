<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction;

use SPHERE\Application\Api\Education\ClassRegister\ApiInstructionSetting;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendInstructionSetting(): Stage
    {
        $stage = new Stage('Einstellungen', 'Belehrungen');
        $stage->setContent(ApiInstructionSetting::receiverModal()
            . (new Primary(
                new Plus() . ' Belehrung hinzufügen',
                ApiInstructionSetting::getEndpoint()
            ))->ajaxPipelineOnClick(ApiInstructionSetting::pipelineOpenCreateInstructionModal())
            . new Container('&nbsp;')
            . ApiInstructionSetting::receiverBlock($this->loadInstructionTable(), 'InstructionContent')
        );

        return  $stage;
    }

    /**
     * @return string
     */
    public function loadInstructionTable(): string
    {
        $dataList = array();
        if (($tblInstructionList = Instruction::useService()->getInstructionAll())) {
            foreach ($tblInstructionList as $tblInstruction) {
                $dataList[] = array(
                    'Subject' => $tblInstruction->getSubject(),
                    'Content' => $tblInstruction->getContent(),
                    'Option' =>
                        (new Standard(
                            '',
                            ApiInstructionSetting::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiInstructionSetting::pipelineOpenEditInstructionModal($tblInstruction->getId()))
                        . (new Standard(
                            '',
                            ApiInstructionSetting::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiInstructionSetting::pipelineOpenDeleteInstructionModal($tblInstruction->getId()))
                );
            }
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Subject' => 'Thema',
                'Content' => 'Inhalt',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array(0, 'asc')
                ),
                'columnDefs' => array(
                    array('width' => '60px', 'targets' => -1),
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param null $InstructionId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formInstruction($InstructionId = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $InstructionId
            && ($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Subject'] = $tblInstruction->getSubject();
            $Global->POST['Data']['Content'] = $tblInstruction->getContent();
            $Global->savePost();
        }

        if ($InstructionId) {
            $saveButton = (new Primary('Speichern', ApiInstructionSetting::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiInstructionSetting::pipelineEditInstructionSave($InstructionId));
        } else {
            $saveButton = (new Primary('Speichern', ApiInstructionSetting::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiInstructionSetting::pipelineCreateInstructionSave());
        }
        $buttonList[] = $saveButton;

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Data[Subject]', 'Thema', 'Thema', new Edit()))->setRequired()
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextArea('Data[Content]', 'Inhalt', 'Inhalt', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $buttonList
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendInstruction(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Belehrungen');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));
        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if ($tblDivision || $tblGroup) {
            $content = '';
            // todo Belehrungen immer anzeigen mit möglichkeit mehrere hinzuzufügen
            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear
                        ),
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                            '/Education/ClassRegister/Digital/Instruction', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new Title(new CommodityItem() . ' Belehrungen'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }
}
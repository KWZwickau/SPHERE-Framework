<?php
namespace SPHERE\Application\Api\People\Meta;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class MassReplaceTransfer extends Extension
{

    const CLASS_MASS_REPLACE_TRANSFER = 'SPHERE\Application\Api\People\Meta\MassReplaceTransfer';
    const METHOD_REPLACE_CURRENT_SCHOOL = 'replaceCurrentSchool';

    /**
     * @return Service
     */
    private function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            'SPHERE\Application\People\Meta\Student/Service/Entity',
            'SPHERE\Application\People\Meta\Student\Service\Entity'
        );
    }

    public function replaceCurrentSchool( $modalField, $CloneField, $PersonId )
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

//        return new Code( print_r( $this->getGlobal()->POST, true ) )
//        .new Code( print_r( $CloneField, true ) );

//        $this->useService()->createTransfer($modalField, $Meta, $PersonId, $StudentTransferTypeIdentifier);

        // Success!
        return ApiTransfer::pipelineClose($Field, $CloneField);
    }
}
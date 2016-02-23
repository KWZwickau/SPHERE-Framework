<?php
namespace SPHERE\Common\Frontend\Link\Repository\Backward;

use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;

/**
 * Class History
 * @package SPHERE\Common\Frontend\Link\Repository\Backward
 */
class History
{
    /** @var Step[] $StepList */
    private $StepList = array();

    /**
     * @param Step $Step
     */
    public function addStep(Step $Step)
    {

        $Data = $Step->getData();
        if ($Step->isValid() && !isset($Data['_goBack'])) {
            // Ignore same Step
            $LastStep = end($this->StepList);
            if ($LastStep) {
                if ($LastStep->getRoute() == $Step->getRoute()) {
                    return;
                }
            }
            // Add New Step
            $Step->setGoBack();
            array_push($this->StepList, $Step);
            (new Session())->saveHistory($this);
        }

        if (isset($Data['_goBack']) && !empty($this->StepList)) {
            /** @var Step $Last */
            if (isset($this->StepList[count($this->StepList) - 2])) {
                $Last = $this->StepList[count($this->StepList) - 2];
                $Signature = (new Authenticator(new Get()))->getAuthenticator();
                $Last = $Step->getPath()
                    . '?' . http_build_query($Signature->createSignature($Last->getData(), $Last->getPath()));

                if (
                    $Last == $Step->getRoute()
                ) {
                    array_pop($this->StepList);
                    (new Session())->saveHistory($this);
                }
            }
        }
    }

    /**
     * @return Step
     */
    public function getStep()
    {
        if (isset($this->StepList[count($this->StepList) - 2])) {
            return $this->StepList[count($this->StepList) - 2];
        }
        return new Step('/');
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return (count($this->StepList) - 1);
    }
}

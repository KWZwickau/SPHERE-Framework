<?php
namespace SPHERE\Common\Frontend\Link\Repository\Backward;

/**
 * Class History
 *
 * @package SPHERE\Common\Frontend\Link\Repository\Backward
 */
class History
{

    /** @var Step[] $StepStack */
    private $StepStack = array();

    /**
     * Get current Back-Step
     *
     * @return false|Step
     */
    public function getStep()
    {

        if (!empty( $this->StepStack )) {
            if (( $Count = count($this->StepStack) ) == 1) {
                return false;
                return current($this->StepStack);
            } else {
                return $this->StepStack[$Count - 2];
            }
        }
        return null;
    }

    /**
     * Add Step if not equals last History-Step
     *
     * @param Step $Step
     *
     * @return bool|null true - added, false - removed, null - not handled
     */
    public function setStep(Step $Step)
    {

        $Last = $this->getLastStep();
        // Is not a GoBack Step
        if (!$Step->isGoBack()) {
            // Stack is empty
            if (!$Last && $Step->isValid()) {
                // Make Step > GoBack & Add to Stack
                $Step->setGoBack();
                array_push($this->StepStack, $Step);
                return true;

            } // Stack is not empty
            elseif ($Step->isValid() && $Last->getRoute() != $Step->getRoute()) {
                // Make Step > GoBack & Add to Stack
                $Step->setGoBack();
                array_push($this->StepStack, $Step);
                return true;
            }
        } else {
            if (
                ( $Last->getPath() != $Step->getPath() )
                || ( $Last->getCleanData() != $Step->getCleanData() )
            ) {
                $this->removeStep($Last);
                return false;
            }
        }
        // Step not handled
        return null;
    }

    /**
     * Get last History-Step
     *
     * @return false|Step
     */
    private function getLastStep()
    {

        if (!empty( $this->StepStack )) {
            return end($this->StepStack);
        }
        return false;
    }

    /**
     * Remove Step if is last History-Step
     *
     * @param Step $Step
     *
     * @return bool
     */
    private function removeStep(Step $Step)
    {

        $Last = $this->getLastStep();
        if ($Last && $Last->getRoute() == $Step->getRoute()) {
            array_pop($this->StepStack);
            return true;
        }
        return false;
    }
}

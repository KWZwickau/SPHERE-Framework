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
     * @param Step $Step
     *
     * @return bool
     */
    public function cleanStep(Step $Step)
    {

        // Clean only if Step is a GoBack Step
        if ($Step->isGoBack()) {
            $this->removeStep($Step);
            return true;
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
    public function removeStep(Step $Step)
    {

        $Last = $this->getLastStep();
        if ($Last && $Last->getRoute() == $Step->getRoute()) {
            array_pop($this->StepStack);
            return true;
        }
        return false;
    }

    /**
     * Get last History-Step
     *
     * @return false|Step
     */
    public function getLastStep()
    {

        if (!empty( $this->StepStack )) {
            return end($this->StepStack);
        }
        return false;
    }

    /**
     * Get current Back-Step
     *
     * @return false|Step
     */
    public function getBackStep()
    {

        if (!empty( $this->StepStack )) {
            if( ($Count = count($this->StepStack)) == 1 ) {
                return current($this->StepStack);
            } else {
                return $this->StepStack[$Count-2];
            }
        }
        return false;
    }

    /**
     * Add Step if not equals last History-Step
     *
     * @param Step $Step
     *
     * @return bool
     */
    public function addStep(Step $Step)
    {

        // Is not a GoBack Step
        if (!$Step->isGoBack()) {
            $Last = $this->getLastStep();
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
        }
        // Step not added
        return false;
    }
}

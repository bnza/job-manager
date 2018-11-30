<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;

class TaskEntity extends AbstractJobManagerEntity implements TaskEntityInterface
{
    /**
     * @var JobEntity
     */
    private $job;

    /**
     * @var int
     */
    private $num;

    /**
     * @var int
     */
    private $currentStepNum = 0;

    /**
     * @var int
     */
    private $stepsNum = 0;

    public function __construct($job, int $num = -1)
    {
        if ($job) {
            if ($job instanceof JobEntity) {
                $this->job = $job;
            } elseif (is_string($job)) {
                $this->job = new JobEntity($job);
            } else {
                throw new \InvalidArgumentException('Invalid Job parameter: only job ID string or JobEntity instance are permitted');
            }
        }

        if (-1 != $num) {
            $this->num = $num;
        }
    }

    public function getJob(): ?JobEntityInterface
    {
        return $this->job;
    }

    public function setJob(JobEntityInterface $job): TaskEntityInterface
    {
        $this->job = $job;

        return $this;
    }

    public function getNum(): int
    {
        return $this->num;
    }

    public function setNum($num): TaskEntityInterface
    {
        $this->num = (int) $num;

        return $this;
    }

    public function setCurrentStepNum($num): TaskEntityInterface
    {
        $this->currentStepNum = (int) $num;

        return $this;
    }

    public function getCurrentStepNum(): int
    {
        return $this->currentStepNum;
    }

    public function setStepsNum($num): TaskEntityInterface
    {
        $this->stepsNum = (int) $num;

        return $this;
    }

    public function getStepsNum(): int
    {
        return $this->stepsNum;
    }
}

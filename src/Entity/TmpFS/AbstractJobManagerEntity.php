<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;

abstract class AbstractJobManagerEntity implements JobManagerEntityInterface
{
    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $currentStepNum = 0;

    /**
     * @var int
     */
    protected $stepsNum = 0;

    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrentStepNum(): int
    {
        return $this->currentStepNum;
    }

    public function getStepsNum(): int
    {
        return $this->stepsNum;
    }
}

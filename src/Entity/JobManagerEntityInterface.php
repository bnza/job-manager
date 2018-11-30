<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Entity;

interface JobManagerEntityInterface
{
    public function getClass(): string;

    public function getName(): string;

    public function getCurrentStepNum(): int;

    public function getStepsNum(): int;
}

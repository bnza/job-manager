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
    private $name = '';

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): JobManagerEntityInterface
    {
        $this->class = $class;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): JobManagerEntityInterface
    {
        $this->name = $name;

        return $this;
    }
}
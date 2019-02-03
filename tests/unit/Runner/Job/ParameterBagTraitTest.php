<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Job;


use Bnza\JobManagerBundle\Runner\Job\ParameterBagTrait;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterBagTraitTest extends \PHPUnit\Framework\TestCase
{
    use MockUtilsTrait;

    public function testMethodGetParameterWithFalseThrowFlagWillReturnNull()
    {
        $mock = $this->getMockForTypeWithMethods(ParameterBagTrait::class, ['getParameters']);
        $mock->expects($this->once())->method('getParameters')->willReturn(new ParameterBag());
        $this->assertNull($mock->getParameter('foo', false));
    }
}

<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Tests\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\TmpFS\AbstractJobManagerEntity;

class AbstractJobManagerEntityTest extends \PHPUnit\Framework\TestCase
{
    private function getStub(): AbstractJobManagerEntity
    {
        return $this->getMockForAbstractClass(AbstractJobManagerEntity::class);
    }

    public function propertyProvider()
    {
        return [
            ['Class', self::class],
            ['Name', 'Job\\Task name']
        ];
    }

    /**
     * @dataProvider propertyProvider
     * @param $prop
     * @param $value
     */
    public function testSetGetClass(string $prop, $value)
    {
        $stub = $this->getStub();
        $stub->{"set$prop"}($value);
        $this->assertEquals($value, $stub->{"get$prop"}());
    }
}
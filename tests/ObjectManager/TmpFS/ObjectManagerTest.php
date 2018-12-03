<?php
/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 30/11/18
 * Time: 15.48.
 */

namespace Bnza\JobManagerBundle\Tests\ObjectManager\TmpFS;

use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Filesystem\Filesystem;

class ObjectManagerTest extends \PHPUnit\Framework\TestCase
{
    private $jobId = 'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec';
    private $taskNum = 83;
    /**
     * @var ObjectManager
     */
    private $om;

    public function setUp()
    {
        $this->om = new ObjectManager('test');
    }

    private function handleEntityProp(JobManagerEntityInterface $entity, string $action, string $prop, $value = false)
    {
        $inflector = new Inflector();
        $method = $action.$inflector->classify($prop);
        if ($action == 'set') {
            $entity->$method($value);
        } else if ($action == 'get') {
            return $entity->$method();
        }

    }

    public function testEmptyConstructor()
    {
        $om = new ObjectManager();
        $this->assertEquals(sys_get_temp_dir().'/bnza/dev/job_manager/jobs', $om->getBasePath());
    }

    public function testEnvConstructor()
    {
        $om = $this->om;
        $this->assertEquals(sys_get_temp_dir().'/bnza/test/job_manager/jobs', $om->getBasePath());

        return $om;
    }

    public function testPathConstructor()
    {
        $om = new ObjectManager('test', __DIR__);
        $this->assertEquals(__DIR__.'/bnza/test/job_manager/jobs', $om->getBasePath());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /.+ does not exist/
     */
    public function testNonExistentPathConstructorThrowsInvalidArgumentException()
    {
        $om = new ObjectManager('test', '/non-existent-dir');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /.+ is not readable/
     */
    public function testNonReadablePathConstructorThrowsInvalidArgumentException()
    {
        $om = new ObjectManager('test', '/root');
    }

    /**
     * @depends testEnvConstructor
     *
     * @param ObjectManager $om
     *
     * @return ObjectManager
     */
    public function testGetEntityJobPath(ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $path = $om->getBasePath().DIRECTORY_SEPARATOR.$this->jobId;
        $this->assertEquals($path, $om->getEntityPath($entity));

        return $om;
    }

    /**
     * @depends testEnvConstructor
     *
     * @param ObjectManager $om
     *
     * @return ObjectManager
     */
    public function testGetEntityTaskPath(ObjectManager $om)
    {
        $entity = new TaskEntity($this->jobId, $this->taskNum);
        $path = $om->getBasePath()
            .DIRECTORY_SEPARATOR
            .$this->jobId
            .DIRECTORY_SEPARATOR
            .'tasks'
            .DIRECTORY_SEPARATOR
            .$this->taskNum;
        $this->assertEquals($path, $om->getEntityPath($entity));

        return $om;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /.+ job not found. You must persist it before single property/
     * @depends testEnvConstructor
     *
     * @param ObjectManager $om
     */
    public function testPersistNonExistentJobPropertiesThrowsLogicException(ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $om->persist($entity, 'non_existent_property');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /.+ is not a valid job property/
     * @depends testGetEntityJobPath
     *
     * @param ObjectManager $om
     */
    public function testPersistJobNonExistentPropertiesThrowsInvalidArgumentException(ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $path = $om->getEntityPath($entity);
        mkdir($path, 0700, true);
        $om->persist($entity, 'non_existent_property');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /.+ is not a valid job property/
     * @depends testGetEntityJobPath
     *
     * @param ObjectManager $om
     */
    public function testRefreshJobNonExistentPropertiesThrowsInvalidArgumentException(ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $path = $om->getEntityPath($entity);
        mkdir($path, 0700, true);
        $om->refresh($entity, 'non_existent_property');
    }

    public function jobPropertiesProvider()
    {
        return [
            ['status', 23],
            ['class', self::class],
            ['name', 'Job name'],
            ['steps_num', 4],
            ['current_step_num', 3],
        ];
    }

    public function taskPropertiesProvider()
    {
        return [
            ['class', self::class],
            ['name', 'Task name'],
            ['steps_num', 4],
            ['current_step_num', 3],
        ];
    }

    /**
     * @param ObjectManager $om
     * @param string        $prop
     * @param $value
     * @depends      testGetEntityJobPath
     * @dataProvider jobPropertiesProvider
     */
    public function testPersistJobProperty(string $prop, $value, ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $path = $om->getEntityPath($entity);
        mkdir($path, 0700, true);
        $om->persist($entity, $prop);
        $propPath = $path.DIRECTORY_SEPARATOR.$prop;
        $this->assertFileExists($propPath);
        $this->assertEquals(file_get_contents($propPath), (string) $value);
    }

    /**
     * @depends testGetEntityJobPath
     * @param ObjectManager $om
     */
    public function testPersistJob(ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $om->persist($entity);
        $this->assertFileExists($om->getEntityPath($entity));
    }

    /**
     * @param ObjectManager $om
     * @param string        $prop
     * @param $value
     * @depends      testGetEntityJobPath
     * @dataProvider taskPropertiesProvider
     */
    public function testPersistTaskProperty(string $prop, $value, ObjectManager $om)
    {
        $entity = new TaskEntity($this->jobId, $this->taskNum);
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $path = $om->getEntityPath($entity);
        mkdir($path, 0700, true);
        $om->persist($entity, $prop);
        $propPath = $path.DIRECTORY_SEPARATOR.$prop;
        $this->assertFileExists($propPath);
        $this->assertEquals(file_get_contents($propPath), (string) $value);
    }

    /**
     * @depends testGetEntityJobPath
     * @param ObjectManager $om
     */
    public function testPersistTask(ObjectManager $om)
    {
        $entity = new TaskEntity($this->jobId, $this->taskNum);
        $om->persist($entity);
        $this->assertFileExists($om->getEntityPath($entity));
    }

    /**
     * @depends testGetEntityJobPath
     * @param ObjectManager $om
     * @dataProvider jobPropertiesProvider
     */
    public function testRefreshJob(string $prop, $value, ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $om->persist($entity);
        $entity2 = new JobEntity($this->jobId);
        $om->refresh($entity2);
        $this->assertEquals($entity, $entity2);
    }

    /**
     * @depends      testGetEntityJobPath
     * @dataProvider taskPropertiesProvider
     * @param string $prop
     * @param $value
     * @param ObjectManager $om
     */
    public function testRefreshJobProperty(string $prop, $value, ObjectManager $om)
    {
        $entity = new JobEntity($this->jobId);
        $path = $om->getEntityPath($entity);
        mkdir($path,0700, true);
        file_put_contents($path.DIRECTORY_SEPARATOR.$prop, $value);
        $om->refresh($entity, $prop);
        $this->assertEquals((string) $value, $this->handleEntityProp($entity, 'get', $prop));
    }

    /**
     * @depends testGetEntityTaskPath
     * @param ObjectManager $om
     * @dataProvider taskPropertiesProvider
     */
    public function testRefreshTask(string $prop, $value, ObjectManager $om)
    {
        $entity = new TaskEntity($this->jobId, $this->taskNum);
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $om->persist($entity);
        $entity2 = new TaskEntity($this->jobId, $this->taskNum);
        $om->refresh($entity2);
        $this->assertEquals($entity, $entity2);
    }

    /**
     * @depends      testGetEntityJobPath
     * @dataProvider taskPropertiesProvider
     * @param string $prop
     * @param $value
     * @param ObjectManager $om
     */
    public function testRefreshTaskProperty(string $prop, $value, ObjectManager $om)
    {
        $entity = new TaskEntity($this->jobId, $this->taskNum);
        $path = $om->getEntityPath($entity);
        mkdir($path,0700, true);
        file_put_contents($path.DIRECTORY_SEPARATOR.$prop, $value);
        $om->refresh($entity, $prop);
        $this->assertEquals((string) $value, $this->handleEntityProp($entity, 'get', $prop));
    }


    public function tearDown()
    {
        $path = $this->om->getBasePath();
        if (file_exists($path)) {
            $fs = new Filesystem();
            $fs->remove($path);
        }
    }
}

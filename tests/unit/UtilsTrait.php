<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests;

use Doctrine\Common\Inflector\Inflector;
use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Runner\Task\FileSystem\FileSystemTrait;

trait UtilsTrait
{
    use FileSystemTrait;

    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @var string
     */
    private $originDir;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @return Inflector
     */
    private function getPropertyInflector(): Inflector
    {
        if (!$this->inflector) {
            $this->inflector = new Inflector();
        }

        return $this->inflector;
    }

    private function getPropertyInflectedMethod(string $action, string $prop): string
    {
        return $action.$this->getPropertyInflector()->classify($prop);
    }

    /**
     * @param RunnableEntityInterface $entity
     * @param string                  $action
     * @param string                  $prop
     * @param bool                    $value
     *
     * @return mixed
     */
    private function handleEntityProp(RunnableEntityInterface $entity, string $action, string $prop, $value = false)
    {
        $method = $this->getPropertyInflectedMethod($action, $prop);
        if ('set' == $action) {
            $entity->$method($value);
        } elseif ('get' == $action) {
            return $entity->$method();
        }
    }

    /**
     * Get a private or protected method for testing/documentation purposes.
     * How to use for MyClass->foo():
     *      $cls = new MyClass();
     *      $foo = PHPUnitUtil::getPrivateMethod($cls, 'foo');
     *      $foo->invoke($cls, $...);.
     *
     * @param object|string $obj  The instantiated instance of your class
     * @param string        $name The name of your private/protected method
     *
     * @return \ReflectionMethod The method you asked for
     *
     * @throws \ReflectionException
     */
    private static function getNonPublicMethod($obj, $name)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function setUpTestDirectories()
    {
        $this->originDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-origin-dir';
        $this->targetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-target-dir';
        foreach ([$this->originDir, $this->targetDir] as $dir) {
            if (file_exists($dir)) {
                $this->getFileSystem()->remove($dir);
            }
            \mkdir($dir);
        }
    }

    public function tearDownTestDirectories()
    {
        foreach ([$this->originDir, $this->targetDir] as $dir) {
            if (file_exists($dir)) {
                $this->getFileSystem()->remove($dir);
            }
        }
    }

    public function assertTestDirectoriesAreEmpty()
    {
        foreach ([$this->originDir, $this->targetDir] as $dir) {
            $this->fileExists($dir);
            $this->assertEquals(0, count(glob("$dir/*")));
        }
    }

    public function getAssetsDir(): string
    {
        $dir = __DIR__;
        $path = realpath($dir.'/../assets/');
        return $path;
    }

    protected function copyZipFromAssetsToOriginDir(string $basename): string
    {
        $destination = $this->originDir.DIRECTORY_SEPARATOR.$basename;
        \copy(
            \implode(
                DIRECTORY_SEPARATOR,
                [
                    $this->getAssetsDir(),
                    'zip',
                    $basename
                ]
            ),
            $destination
        );
        return $destination;
    }
}

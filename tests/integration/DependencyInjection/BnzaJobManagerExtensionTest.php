<?php

use Bnza\JobManagerBundle\DependencyInjection\BnzaJobManagerExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Serializer\Serializer;

class BnzaJobManagerExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new BnzaJobManagerExtension()
          ];
    }

    public function testExtensionServices()
    {
        $this->load();
        $serializer = $this->container->get('bnza_job_manager_task_entity_json_serializer');
        $this->assertInstanceOf(Serializer::class, $serializer);
    }
}

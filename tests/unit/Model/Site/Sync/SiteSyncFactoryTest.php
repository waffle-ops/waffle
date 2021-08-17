<?php

namespace Waffle\Tests\Command\Command;

use Waffle\Model\Site\Sync\DrushSiteSync;
use Waffle\Model\Site\Sync\SiteSyncFactory;
use Waffle\Tests\TestCase;

class SiteSyncFactoryTest extends TestCase
{

    public function validInputDataProvider()
    {
        return [
            ['drupal7', DrushSiteSync::class],
            ['drupal8', DrushSiteSync::class],
            ['drupal9', DrushSiteSync::class],
        ];
    }

    /**
     * @dataProvider validInputDataProvider
     */
    public function testValidLookup($input, $expectedClass)
    {
        $sut = static::getSystemUnderTest(SiteSyncFactory::class);

        $handler = $sut->getSiteSyncAdapter($input);

        $this->assertInstanceOf($expectedClass, $handler);
    }

    public function testInvalidLookup()
    {
        $this->expectException(\Exception::class);

        $sut = static::getSystemUnderTest(SiteSyncFactory::class);

        $sut->getSiteSyncAdapter('invalid');
    }
}

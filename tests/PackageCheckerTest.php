<?php

namespace SimonHamp\Ensemble\Tests;

use PHPUnit\Framework\TestCase;
use SimonHamp\Ensemble\PackageChecker;

class PackageCheckerTest extends TestCase
{
    private $all;

    protected function setUp() : void
    {
        PackageChecker::setCwd(realpath(__DIR__.'/../'));

        PackageChecker::setComposerPath(realpath(__DIR__.'/../vendor/bin/composer'));

        $this->all = json_decode(
            PackageChecker::getJson('outdated', [PackageChecker::SHOW_ALL]),
            true
        );
    }

    public function testCanGetPackagesAsJson()
    {
        $this->assertIsArray($this->all);
        $this->assertNotEmpty($this->all);
    }

    public function testCanGetOutdatedPackagesAsJson()
    {
        $outdated = json_decode(PackageChecker::getJson('outdated'), true);

        $this->assertNotEquals($this->all, $outdated);
    }

    public function testCanGetMinorUpdatePackagesAsJson()
    {
        $minor = json_decode(
            PackageChecker::getJson('outdated', [PackageChecker::MINOR_ONLY]),
            true
        );

        $this->assertNotEquals($this->all, $minor);
    }

    public function testCanGetPackageLicenseInfoAsJson()
    {
        $licenses = json_decode(
            PackageChecker::getJson('licenses'),
            true
        );

        $this->assertIsArray($licenses);
        $this->assertNotEmpty($licenses);
    }
}

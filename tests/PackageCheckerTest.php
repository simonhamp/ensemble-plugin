<?php

namespace SimonHamp\Ensemble\Tests;

use PHPUnit\Framework\TestCase;
use SimonHamp\Ensemble\PackageChecker;

class PackageCheckerTest extends TestCase
{
    private $all;

    public function setUp()
    {
        PackageChecker::setCwd(realpath(__DIR__.'/../'));

        $this->all = json_decode(
            PackageChecker::getJson('outdated', [PackageChecker::SHOW_ALL]),
            true
        );
    }

    public function testCanGetPackagesAsJson()
    {
        $this->assertInternalType('array', $this->all);
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
}

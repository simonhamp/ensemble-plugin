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

        $this->all = json_decode(PackageChecker::all(), true);
    }

    public function testCanGetPackagesAsJson()
    {
        $this->assertInternalType('array', $this->all);
        $this->assertNotEmpty($this->all);
    }

    public function testCanGetOutdatedPackagesAsJson()
    {
        $outdated = json_decode(PackageChecker::outdated(), true);

        $this->assertNotEquals($this->all, $outdated);
    }

    public function testCanGetMinorUpdatePackagesAsJson()
    {
        $minor = json_decode(PackageChecker::minor(), true);

        $this->assertNotEquals($this->all, $minor);
    }
}

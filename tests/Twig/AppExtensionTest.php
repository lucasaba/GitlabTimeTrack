<?php

namespace App\Tests\Twig;

use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

class AppExtensionTest extends TestCase
{
    public function testGetFilters()
    {
        $filters = (new AppExtension())->getFilters();
        $this->assertEquals(1, count($filters));
        $timeFormatFilter = $filters[0];
        $this->assertTrue($timeFormatFilter instanceof TwigFilter);
        $this->assertEquals('gitlab_time_format', $timeFormatFilter->getName());
    }

    /**
     * @dataProvider getTestData
     * @param $timeInSeconds
     * @param $expectedResult
     */
    public function testGitlabTimeFormatFilter($timeInSeconds, $expectedResult)
    {
        $extension = new AppExtension();
        $this->assertEquals($expectedResult, $extension->gitlabTimeFormatFilter($timeInSeconds));
    }

    public function getTestData()
    {
        return [
            [null, '0'],
            [10, '0h 00m'],
            [60, '0h 01m'],
            [3600, '1h 00m'],
            [431820, '4d 23h 57m']
        ];
    }
}

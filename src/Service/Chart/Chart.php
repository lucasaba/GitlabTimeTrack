<?php

declare(strict_types=1);

namespace App\Service\Chart;

use App\Entity\Issue;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\LineChart;

class Chart
{
    private $chartData;

    public function __construct(ChartData $chartData)
    {
        $this->chartData = $chartData;
    }


    public function timeSpendByDate(Issue ...$issues): LineChart
    {
        $arrayToDataTable = $this->chartData->dataSpendByDay(...$issues);
        $chart = new LineChart();
        $chart->getData()->setArrayToDataTable($arrayToDataTable);
        $chart->getOptions()->setTitle('Remaining time in hours');
        $chart->getOptions()->setCurveType('function');
        $chart->getOptions()->setLineWidth(4);
        $chart->getOptions()->getLegend()->setPosition('none');

        return $chart;
    }
}
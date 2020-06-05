<?php
namespace App\Twig;

use CMEN\GoogleChartsBundle\GoogleCharts\Options\Days;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    const DAYS_PER_WEEK = 5;
    const HOURS_PER_DAY = 8;

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('gitlab_time_format', [$this, 'gitlabTimeFormatFilter']),
        );
    }

    /**
     * Filter for formatting times as in GitLab
     * e.g. 1d 15h 30m  or  1h 10m
     * @param int|null $number
     * @return string
     */
    public function gitlabTimeFormatFilter(int $number = null): string
    {
        if ($number == null) {
            return '0';
        }
        $output = '';
        $days = (int) floor($number / (60*60*self::HOURS_PER_DAY));
        if ($days >= self::DAYS_PER_WEEK) {
            $weeks = (int) floor($days / self::DAYS_PER_WEEK);
            $days -= $weeks * self::DAYS_PER_WEEK;
            $output .= $weeks . 'w ';
            $number -= $weeks * self::DAYS_PER_WEEK*60*60*self::HOURS_PER_DAY;
        }
        if ($days > 0) {
            $output .= $days . 'd ';
            $number -= $days*60*60*self::HOURS_PER_DAY;
        }

        $output .= gmdate('G\\h i\\m', $number);

        return $output;
    }
}

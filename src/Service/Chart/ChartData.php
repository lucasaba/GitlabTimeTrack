<?php

declare(strict_types=1);

namespace App\Service\Chart;

use App\Entity\Issue;
use App\Entity\Note;
use Doctrine\Persistence\ObjectManager;

class ChartData
{
    private $em;
    private $totalTimeSpentByIssue = [];
    private $totalTimeEstimateByIssue = [];
    private $totalRemainingTimeByDate = [];

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function dataSpendByDay(Issue ...$issues): array
    {
        $arrayToDataTable[] = ['Day', 'remaining time'];
        $data = [];
        foreach ($issues as $issue) {
            foreach ($issue->getNotes() as $note) {
                if (!isset($this->totalTimeEstimateByIssue[$note->getIssue()->getId()])) {
                    $this->totalTimeEstimateByIssue[$note->getIssue()->getId()] = 0;
                }

                $row = $this->parseEstimateTimeItem($note);
                if ($row['hours'] !== 0) {
                    $this->totalTimeEstimateByIssue[$note->getIssue()->getId()] += $row['hours'];
                    $data[$row['estimate_at']]['estimate'][$note->getId()] = $row['hours'];
                } else {
                    $row = $this->parseRemoveEstimateTimeItem($note);
                    if ($row['hours'] !== 0) {
                        $data[$row['estimate_at']]['estimate'][$note->getId()] = -$row['hours'];
                    }
                }

                if (!isset($this->totalTimeSpentByIssue[$note->getIssue()->getId()])) {
                    $this->totalTimeSpentByIssue[$note->getIssue()->getId()] = 0;
                }

                $row = $this->parseSpentTimeItem($note);
                if ($row['hours'] !== 0) {
                    $this->totalTimeSpentByIssue[$note->getIssue()->getId()] += $row['hours'];
                    $data[$row['spent_at']]['spend'][$note->getId()] = $row['hours'];
                } else {
                    $row = $this->parseRemoveSpentTimeItem($note);
                    if ($row['hours'] !== 0) {
                        $data[$row['spent_at']]['spend'][$note->getId()] = -$row['hours'];
                    }
                }
            }
        }

        ksort($data);
        $totalRemaining = 0;
        foreach($data as $date => $dataTime) {
            if (!isset($this->totalRemainingTimeByDate[$date])) {
                $this->totalRemainingTimeByDate[$date] = 0;
            }
            if (isset($dataTime['issue'])) {
                foreach($dataTime['issue'] as $issueTime) {
                    $this->totalRemainingTimeByDate[$date] += $issueTime;
                    $totalRemaining += $issueTime;
                }
            }
            if (isset($dataTime['estimate'])) {
                foreach($dataTime['estimate'] as $noteTime) {
                    $this->totalRemainingTimeByDate[$date] += $noteTime;
                    $totalRemaining += $noteTime;
                }
            }

            if (isset($dataTime['spend'])) {
                foreach($dataTime['spend'] as $noteTime) {
                    $totalRemaining -= $noteTime;
                    $this->totalRemainingTimeByDate[$date] = $totalRemaining;
                }
            }
        }

        foreach ($this->totalRemainingTimeByDate as $date => $timeSpend) {
            if ($date !== "") {
                $arrayToDataTable[] = [new \DateTimeImmutable($date), $timeSpend];
            }
        }

        return $arrayToDataTable;
    }

    private function parseEstimateTimeItem(Note $note): array
    {
        $hours = 0;
        $spentAt = null;

        $pattern = '/changed time estimate to ((?:(?:\d{1,3}[wdhms])\s+)+)/';
        preg_match($pattern, $note->getBody() . ' ', $match);
        if (!empty($match) && count($match) === 2) {
            $times = array_filter(explode(' ', $match[1]));
            foreach ($times as $time) {
                $hours += $this->parseTime(trim($time));
            }
            $spentAt = $note->getCreatedAt()->format('Y-m-d');
        }

        $row = [
            'estimate_at' => $spentAt,
            'hours' => $hours,
        ];
        return $row;
    }


    private function parseRemoveEstimateTimeItem(Note $note): array
    {

        $hours = 0;
        $spentAt =  new \DateTime();
        ;

        $pattern = '/removed time spent/';
        preg_match($pattern, $note->getBody(), $match);
        if (!empty($match) && count($match) === 1) {
            $hours = $this->totalTimeEstimateByIssue[$note->getIssue()->getId()];
            ;
            $spentAt = $note->getCreatedAt()->format('Y-m-d');
        }

        return [
            'estimate_at' => $spentAt,
            'hours' => $hours,
        ];
    }

    private function parseSpentTimeItem(Note $note): array
    {
        $hours = 0;
        $spentAt = null;

        $pattern = '/(added|subtracted) ((?:(?:\d{1,3}[wdhms])\s+)+)of time spent at (\d{4}-\d{2}-\d{2})/';
        preg_match($pattern, $note->getBody(), $match);

        if (!empty($match) && count($match) === 4) {
            $sign = $match[1] === 'added' ? 1 : -1;
            $times = array_filter(explode(' ', $match[2]));
            foreach ($times as $time) {
                $hours += $sign * $this->parseTime(trim($time));
            }
            $spentAt = $match[3];
        }

        $row = [
            'spent_at' => $spentAt,
            'hours' => $hours,
        ];
        return $row;
    }

    private function parseRemoveSpentTimeItem(Note $note): array
    {

        $hours = 0;
        $spentAt =  new \DateTime();
        ;

        $pattern = '/removed time spent/';
        preg_match($pattern, $note->getBody(), $match);
        if (!empty($match) && count($match) === 1) {
            $hours = $this->totalTimeSpentByIssue[$note->getIssue()->getId()];
            ;
            $spentAt = $note->getCreatedAt()->format('Y-m-d');
        }

        return [
            'spent_at' => $spentAt,
            'hours' => $hours,
        ];
    }

    private function parseTime(string $time)
    {
        $value = mb_substr($time, 0, -1);
        $period = mb_substr($time, -1);
        if ($period === 's') {
            return $value / 3600;
        }
        if ($period === 'm') {
            return $value / 60;
        }
        if ($period === 'h') {
            return $value;
        }
        if ($period === 'd') {
            return $value * 8;
        }
        if ($period === 'w') {
            return $value * 8 * 5;
        }
        throw new \InvalidArgumentException(sprintf('Unknown period %s (time %s)', $period, $time));
    }

}
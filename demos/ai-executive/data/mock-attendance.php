<?php

declare(strict_types=1);

/**
 * Daily attendance for the current month (school days of October 2026),
 * calibrated so the average equals the students' current-month attendance.
 */
function exe_mock_daily_attendance(array $students, MockRandom $rng): array
{
    $holidays = ['2026-10-13', '2026-10-23'];
    $total = count($students);
    $avg = array_sum(array_map(static fn ($s) => end($s['att']), $students)) / max(1, $total);
    $late = array_sum(array_column($students, 'late')) / max(1, $total);

    $days = [];
    for ($d = 1; $d <= 31; $d++) {
        $date = sprintf('2026-10-%02d', $d);
        $dow = (int) date('N', strtotime($date));
        if ($dow >= 6 || in_array($date, $holidays, true)) {
            continue;
        }
        $days[] = $date;
    }

    $noise = array_map(static fn () => $rng->gauss(0, 0.9), $days);
    $shift = array_sum($noise) / count($noise);   // keep the monthly mean exact

    $result = [];
    foreach ($days as $i => $date) {
        $rate = MockRandom::clamp($avg + $noise[$i] - $shift + ((int) date('N', strtotime($date)) === 1 ? -0.6 : 0), 50, 100);
        $result[] = [
            'date'    => $date,
            'rate'    => round($rate, 1),
            'absent'  => (int) round($total * (100 - $rate) / 100),
            'late'    => (int) round($total * $late / count($days) * MockRandom::clamp($rng->gauss(1, 0.25), 0.3, 2)),
        ];
    }
    return $result;
}

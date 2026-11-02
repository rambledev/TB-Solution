<?php

declare(strict_types=1);

require_once __DIR__ . '/mock-classrooms.php';
require_once __DIR__ . '/mock-academic.php';
require_once __DIR__ . '/mock-teachers.php';
require_once __DIR__ . '/mock-students.php';
require_once __DIR__ . '/mock-attendance.php';
require_once __DIR__ . '/mock-activities.php';

/**
 * Seeded random helpers — the same scenario + seed always produces the same dataset.
 */
final class MockRandom
{
    public function __construct(int $seed)
    {
        mt_srand($seed);
    }

    public function float(float $min = 0.0, float $max = 1.0): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public function int(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    public function chance(float $p): bool
    {
        return $this->float() < $p;
    }

    public function gauss(float $mean = 0.0, float $sd = 1.0): float
    {
        $u = max(1e-9, $this->float());
        return $mean + $sd * sqrt(-2 * log($u)) * cos(2 * M_PI * $this->float());
    }

    public function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    public function shuffle(array &$items): void
    {
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }
    }

    public static function clamp(float $v, float $min, float $max): float
    {
        return max($min, min($max, $v));
    }
}

/**
 * Build one complete, internally consistent dataset for a scenario.
 */
function exe_generate_dataset(string $scenarioKey, int $seed): array
{
    exe_log('MockData', 'generate', 'START', ['scenario' => $scenarioKey, 'seed' => $seed]);
    $started = microtime(true);

    $scenarios = exe_config('scenarios');
    if (!isset($scenarios[$scenarioKey])) {
        $scenarioKey = exe_config('default_scenario');
    }
    $scenario = $scenarios[$scenarioKey];
    $rng = new MockRandom($seed);
    $names = require __DIR__ . '/names.php';

    $rooms = exe_mock_classrooms($scenario['size'], $scenario, $rng);
    $teachers = exe_mock_teachers($rooms, $scenario['size'], $scenario, $names, $rng);
    $students = exe_mock_students($rooms, $scenario, $names, $rng);
    $homeroom = [];
    foreach ($teachers as $t) {
        if ($t['homeroom'] !== null) {
            $homeroom[$t['homeroom']] = $t['id'];
        }
    }
    foreach ($rooms as &$room) {
        $room['homeroom'] = $homeroom[$room['id']] ?? null;
    }
    unset($room);

    $count = count($students);
    $staff = $scenario['size'] === 'large' ? 26 : 12;
    $months = exe_config('months');
    $in = [0, 2, 1, 3, 1, 2];
    $out = [0, 1, 2, 1, 3, 1];
    $enrollment = [];
    $current = $count;
    for ($i = 5; $i >= 0; $i--) {
        $enrollment[$i] = $current;
        $current = $current - $in[$i] + $out[$i];
    }
    ksort($enrollment);

    $dataset = [
        'meta' => [
            'school'        => exe_config('school.name'),
            'academicYear'  => exe_config('school.academic_year'),
            'scenario'      => $scenarioKey,
            'scenarioLabel' => $scenario['label'],
            'scenarioTh'    => $scenario['th'],
            'seed'          => $seed,
            'generatedAt'   => date('c'),
            'terms'         => array_column(exe_config('terms'), 'key'),
            'months'        => $months,
            'staff'         => $staff,
            'staffPrev'     => $staff - ($scenarioKey === 'at-risk' ? -1 : 0),
            'teachersPrev'  => count($teachers) - ($scenarioKey === 'at-risk' ? -2 : 1),
            'enrollment'    => array_values($enrollment),
            'transfersIn'   => $in,
            'transfersOut'  => $out,
        ],
        'subjects'   => exe_mock_subjects(),
        'classrooms' => array_map(static fn ($r) => array_diff_key($r, ['effect' => 1]) + ['tag' => $r['effect']['tag']], $rooms),
        'teachers'   => $teachers,
        'students'   => $students,
        'daily'      => exe_mock_daily_attendance($students, $rng),
        'activities' => exe_mock_activities($count, $scenario, $rng),
    ];

    exe_log('MockData', 'generate', 'END', ['ms' => round((microtime(true) - $started) * 1000, 1), 'students' => $count, 'teachers' => count($teachers)]);
    return $dataset;
}

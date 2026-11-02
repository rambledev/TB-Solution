<?php

declare(strict_types=1);

/**
 * Classroom structure: ป.1–ม.6. Normal = 18 rooms (ป. 2 rooms/grade, ม. 1 room/grade), Large = 42 rooms.
 *
 * @return list<array{id: string, name: string, grade: string, gradeLabel: string, level: string, size: int, effect: array}>
 */
function exe_mock_classrooms(string $size, array $scenario, MockRandom $rng): array
{
    exe_log('MockData', 'classrooms', 'START', ['size' => $size]);

    $grades = [];
    foreach (range(1, 6) as $g) {
        $grades[] = ['key' => 'P' . $g, 'label' => 'ป.' . $g, 'level' => 'primary', 'rooms' => $size === 'large' ? 4 : 2];
    }
    foreach (range(1, 6) as $g) {
        $grades[] = ['key' => 'M' . $g, 'label' => 'ม.' . $g, 'level' => 'secondary', 'rooms' => $size === 'large' ? 3 : 1];
    }

    $rooms = [];
    foreach ($grades as $grade) {
        for ($r = 1; $r <= $grade['rooms']; $r++) {
            $rooms[] = [
                'id'         => $grade['key'] . '-' . $r,
                'name'       => $grade['label'] . '/' . $r,
                'grade'      => $grade['key'],
                'gradeLabel' => $grade['label'],
                'level'      => $grade['level'],
                'size'       => 0,
                'effect'     => ['att' => $rng->gauss(0, 0.5), 'gpa' => $rng->gauss(0, 0.08), 'sub' => $rng->gauss(0, 2.5), 'tag' => 'normal'],
            ];
        }
    }

    // Distribute the student total evenly across rooms.
    $total = $size === 'large' ? 1470 : 600;
    $base = intdiv($total, count($rooms));
    $extra = $total - $base * count($rooms);
    foreach ($rooms as $i => &$room) {
        $room['size'] = $base + ($i < $extra ? 1 : 0);
    }
    unset($room);

    // Scenario story: a few clearly weaker rooms and a few outstanding ones.
    $order = range(0, count($rooms) - 1);
    $rng->shuffle($order);
    foreach (array_slice($order, 0, $scenario['problem_rooms']) as $i) {
        $rooms[$i]['effect'] = ['att' => -3.2, 'gpa' => -0.38, 'sub' => -11, 'tag' => 'problem'];
    }
    foreach (array_slice($order, $scenario['problem_rooms'], $scenario['star_rooms']) as $i) {
        $rooms[$i]['effect'] = ['att' => 1.6, 'gpa' => 0.3, 'sub' => 6, 'tag' => 'star'];
    }

    exe_log('MockData', 'classrooms', 'END', ['rooms' => count($rooms), 'students' => $total]);
    return $rooms;
}

<?php

declare(strict_types=1);

/**
 * Teachers with workload, records and feedback. Class outcomes (attendance,
 * achievement, development, submission) are derived later from the students
 * of the rooms they teach, so the evaluation always matches the student data.
 */
function exe_mock_teachers(array $rooms, string $size, array $scenario, array $names, MockRandom $rng): array
{
    exe_log('MockData', 'teachers', 'START', ['rooms' => count($rooms)]);

    $total = $size === 'large' ? 84 : 35;
    $departments = exe_mock_departments();
    $roomIds = array_column($rooms, 'id');

    // Teacher count per department by share; the remainder goes to the largest groups.
    $counts = array_map(static fn ($d) => max(1, (int) floor($total * $d['share'])), $departments);
    for ($i = 0; array_sum($counts) < $total; $i++) {
        $counts[$i % 4]++;
    }

    $teachers = [];
    $n = 0;
    foreach ($departments as $di => $dept) {
        // Split all rooms between the teachers of this department.
        $chunks = array_fill(0, $counts[$di], []);
        foreach ($roomIds as $ri => $roomId) {
            $chunks[$ri % $counts[$di]][] = $roomId;
        }
        for ($i = 0; $i < $counts[$di]; $i++) {
            $n++;
            $female = $rng->chance(0.66);
            $first = $rng->pick($female ? $names['woman'] : $names['man']);
            $spread = $scenario['load_spread'];
            $hours = (int) round(MockRandom::clamp($rng->gauss(18, 1.6 * $spread), 10, 28));
            if ($spread > 1.5 && $rng->chance(0.18)) {
                $hours = $rng->int(25, 28);
            }
            $years = $rng->int(1, 30);
            $teachers[] = [
                'id'          => 'T' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'name'        => ($female ? $rng->pick(['นาง', 'นางสาว']) : 'นาย') . $first . ' ' . $rng->pick($names['surname']),
                'gender'      => $female ? 'F' : 'M',
                'department'  => $dept['name'],
                'subject'     => $dept['subject'],
                'position'    => $years < 3 ? 'ครูผู้ช่วย' : ($years < 10 ? 'ครู' : ($years < 20 ? 'ครูชำนาญการ' : 'ครูชำนาญการพิเศษ')),
                'years'       => $years,
                'classes'     => $chunks[$i] ?: [$rng->pick($roomIds)],
                'homeroom'    => null,
                'hours'       => $hours,
                'duties'      => $spread > 1.5 && $hours >= 25 ? 4 : $rng->int(1, 3),
                'lessonLog'   => (int) round(MockRandom::clamp($rng->gauss(88 + $scenario['dev'] * 2.5, 8), 50, 100)),
                'training'    => (int) round(MockRandom::clamp($rng->gauss(25 + $scenario['dev'] * 4, 12), 2, 60)),
                'works'       => (int) round(MockRandom::clamp($rng->gauss(2.5 + $scenario['dev'], 1.8), 0, 8)),
                'fbStudent'   => round(MockRandom::clamp($rng->gauss(4.05 + $scenario['dev'] * 0.12, 0.4), 2.6, 5), 2),
                'fbExec'      => round(MockRandom::clamp($rng->gauss(4.0 + $scenario['dev'] * 0.12, 0.42), 2.6, 5), 2),
            ];
        }
    }

    // One homeroom teacher per room, spread across departments.
    $order = range(0, count($teachers) - 1);
    $rng->shuffle($order);
    foreach ($roomIds as $i => $roomId) {
        $teachers[$order[$i % count($order)]]['homeroom'] ??= $roomId;
    }

    exe_log('MockData', 'teachers', 'END', ['teachers' => count($teachers)]);
    return $teachers;
}

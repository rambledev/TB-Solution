<?php

declare(strict_types=1);

/**
 * Student records: 6 months of attendance and submission, 3 terms of GPA,
 * subject scores and development, behaviour and activity participation.
 */
function exe_mock_students(array $rooms, array $scenario, array $names, MockRandom $rng): array
{
    exe_log('MockData', 'students', 'START', ['rooms' => count($rooms)]);

    $subjects = exe_mock_subjects();
    $season = [0.3, -0.5, -0.9, -0.2, 0.3, 0.2];  // พ.ค.–ต.ค.
    $students = [];
    $n = 0;

    foreach ($rooms as $room) {
        $gradeNo = (int) substr($room['grade'], 1);
        $older = $room['level'] === 'secondary' && $gradeNo >= 4;
        $fx = $room['effect'];

        for ($i = 0; $i < $room['size']; $i++) {
            $n++;
            $female = $rng->chance(0.5);
            $ability = $rng->gauss();
            $engage = 0.55 * $ability + 0.83 * $rng->gauss();

            // Attendance (6 months; index 5 = current month).
            $attBase = $scenario['att'] + $fx['att'] + 1.4 * $engage;
            if ($rng->chance($scenario['risk'])) {
                $attBase -= $rng->float(5, 12);
            }
            $att = [];
            foreach ($season as $m => $s) {
                $att[] = round(MockRandom::clamp($attBase + $scenario['att_trend'] * ($m - 5) + $s + $rng->gauss(0, 0.7), 55, 100), 1);
            }

            // GPA for 3 terms (index 2 = latest).
            $g2 = MockRandom::clamp($scenario['gpa'] + $fx['gpa'] + 0.5 * $ability + ($attBase - 94) * 0.03, 1.0, 4.0);
            if ($rng->chance($scenario['risk'] * 0.8)) {
                $g2 = MockRandom::clamp($g2 - $rng->float(0.4, 0.8), 1.0, 4.0);
            }
            $g1 = MockRandom::clamp($g2 - $scenario['gpa_trend'] + $rng->gauss(0, 0.12), 1.0, 4.0);
            $g0 = MockRandom::clamp($g1 - $scenario['gpa_trend'] + $rng->gauss(0, 0.12), 1.0, 4.0);
            $gpa = [round($g0, 2), round($g1, 2), round($g2, 2)];

            // Subject scores per term. Reading (Thai) in ป.3 is weaker unless the school is excellent.
            $scores = [];
            foreach ($gpa as $t => $g) {
                foreach ($subjects as $key => $subject) {
                    $shift = ($room['grade'] === 'P3' && $key === 'thai' && $t === 2 && $scenario['dev'] < 2) ? -6.5 : 0;
                    $shift += ($room['grade'] === 'M2' && $key === 'math' && $t === 2 && $scenario['dev'] < 2) ? -4.5 : 0;
                    $scores[$t][$key] = (int) round(MockRandom::clamp(50 + $g * 7.6 + $subject['offset'] + $shift + $rng->gauss(0, 4), 20, 100));
                }
            }

            // Assignment submission (6 months).
            $subBase = $scenario['sub'] + $fx['sub'] + 6 * $ability + ($attBase - 94) * 0.6;
            $sub = [];
            foreach (range(0, 5) as $m) {
                $sub[] = (int) round(MockRandom::clamp($subBase + $scenario['sub_trend'] * ($m - 5) + $rng->gauss(0, 3), 15, 100));
            }

            // Development score per term and behaviour.
            $d0 = MockRandom::clamp(70 + 8 * $ability + $rng->gauss(0, 4), 30, 100);
            $d1 = MockRandom::clamp($d0 + $scenario['dev'] * 1.6 + $rng->gauss(0, 3), 30, 100);
            $d2 = MockRandom::clamp($d1 + $scenario['dev'] * 1.6 + $rng->gauss(0, 3), 30, 100);
            $behavior = (int) round(MockRandom::clamp(86 + 5 * $engage - ($attBase < 88 ? 10 : 0) + $rng->gauss(0, 6), 30, 100));

            $current = end($att);
            $first = $rng->pick($female ? $names['girl'] : $names['boy']);
            $students[] = [
                'id'        => 'S' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'code'      => (string) (69000 + $n),
                'name'      => ($older ? ($female ? 'นางสาว' : 'นาย') : ($female ? 'เด็กหญิง' : 'เด็กชาย')) . $first . ' ' . $rng->pick($names['surname']),
                'first'     => $first,
                'gender'    => $female ? 'F' : 'M',
                'room'      => $room['id'],
                'grade'     => $room['grade'],
                'att'       => $att,
                'absent'    => (int) round((100 - $current) / 100 * 21),
                'late'      => (int) round(MockRandom::clamp($rng->gauss((100 - $current) * 0.35, 1.2), 0, 12)),
                'streak'    => $current < 86 ? $rng->int(3, 7) : ($current < 90 ? $rng->int(0, 3) : 0),
                'gpa'       => $gpa,
                'scores'    => $scores,
                'sub'       => $sub,
                'dev'       => [(int) round($d0), (int) round($d1), (int) round($d2)],
                'behavior'  => $behavior,
                'incidents' => $behavior < 70 ? $rng->int(1, 4) : ($rng->chance(0.08) ? 1 : 0),
                'act'       => (int) round(MockRandom::clamp(6 + 2.5 * $engage + $scenario['dev'] + $rng->gauss(0, 1.5), 0, 12)),
            ];
        }
    }

    exe_log('MockData', 'students', 'END', ['students' => count($students)]);
    return $students;
}

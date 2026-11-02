<?php

declare(strict_types=1);

/**
 * Subject catalogue. Scored subjects are tracked per student and per term.
 */
function exe_mock_subjects(): array
{
    return [
        'thai' => ['name' => 'ภาษาไทย',       'offset' => 1.0],
        'math' => ['name' => 'คณิตศาสตร์',    'offset' => -4.5],
        'sci'  => ['name' => 'วิทยาศาสตร์',   'offset' => -2.0],
        'eng'  => ['name' => 'ภาษาอังกฤษ',   'offset' => -3.5],
        'soc'  => ['name' => 'สังคมศึกษา',    'offset' => 2.5],
    ];
}

/**
 * Teacher subject groups (8 กลุ่มสาระ). "subject" links to a scored subject when one exists.
 */
function exe_mock_departments(): array
{
    return [
        ['name' => 'ภาษาไทย',                  'subject' => 'thai', 'share' => 0.15],
        ['name' => 'คณิตศาสตร์',               'subject' => 'math', 'share' => 0.17],
        ['name' => 'วิทยาศาสตร์และเทคโนโลยี', 'subject' => 'sci',  'share' => 0.17],
        ['name' => 'ภาษาต่างประเทศ',          'subject' => 'eng',  'share' => 0.15],
        ['name' => 'สังคมศึกษาฯ',              'subject' => 'soc',  'share' => 0.12],
        ['name' => 'สุขศึกษาและพลศึกษา',       'subject' => null,   'share' => 0.08],
        ['name' => 'ศิลปะ',                    'subject' => null,   'share' => 0.08],
        ['name' => 'การงานอาชีพ',              'subject' => null,   'share' => 0.08],
    ];
}

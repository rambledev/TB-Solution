-- ==========================================================================
-- AI School Intelligence — MySQL 8 / MariaDB schema (prepared for the next phase)
--
-- The demo currently generates the same data in PHP (lib/SchoolData.php).
-- To switch to a database, load this schema, import data via the Data Import
-- Center templates, and replace SchoolData::get() with a repository that
-- returns the same array shape.
-- ==========================================================================

SET NAMES utf8mb4;

CREATE TABLE schools (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(200) NOT NULL,
    academic_year CHAR(4)      NOT NULL,
    current_term  VARCHAR(10)  NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id     INT UNSIGNED NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('executive','teacher','staff','student','parent') NOT NULL,
    display_name  VARCHAR(200) NOT NULL,
    linked_id     VARCHAR(20)  NULL COMMENT 'teacher/student id for role-scoped views',
    last_login_at DATETIME NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grades (
    code      VARCHAR(4)  PRIMARY KEY COMMENT 'K1..K3, P1..P6, M1..M6',
    label     VARCHAR(10) NOT NULL,
    level     ENUM('kindergarten','primary','secondary') NOT NULL,
    sort_no   TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teachers (
    id           VARCHAR(10) PRIMARY KEY,
    school_id    INT UNSIGNED NOT NULL,
    full_name    VARCHAR(200) NOT NULL,
    department   VARCHAR(50)  NOT NULL,
    position     VARCHAR(50)  NOT NULL,
    years        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (school_id) REFERENCES schools(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rooms (
    name              VARCHAR(10) PRIMARY KEY COMMENT 'e.g. ม.2/2',
    grade_code        VARCHAR(4)  NOT NULL,
    homeroom_teacher  VARCHAR(10) NULL,
    FOREIGN KEY (grade_code) REFERENCES grades(code),
    FOREIGN KEY (homeroom_teacher) REFERENCES teachers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE students (
    id          VARCHAR(10) PRIMARY KEY,
    code        VARCHAR(20) NOT NULL UNIQUE,
    full_name   VARCHAR(200) NOT NULL,
    gender      ENUM('M','F') NOT NULL,
    room        VARCHAR(10) NOT NULL,
    status      ENUM('active','transferred_out','graduated') NOT NULL DEFAULT 'active',
    enrolled_at DATE NULL,
    left_at     DATE NULL,
    FOREIGN KEY (room) REFERENCES rooms(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_daily (
    student_id VARCHAR(10) NOT NULL,
    date       DATE NOT NULL,
    status     ENUM('present','late','absent','leave_sick','leave_personal') NOT NULL,
    PRIMARY KEY (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subjects (
    code  VARCHAR(20) PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    group_key VARCHAR(20) NOT NULL COMMENT 'thai, math, science, social, english, health, arts, career'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grade_results (
    student_id   VARCHAR(10) NOT NULL,
    term         VARCHAR(10) NOT NULL COMMENT 'e.g. 1/2569',
    subject_code VARCHAR(20) NOT NULL,
    teacher_id   VARCHAR(10) NULL,
    score        DECIMAL(5,2) NOT NULL,
    grade_point  DECIMAL(3,2) NOT NULL,
    PRIMARY KEY (student_id, term, subject_code),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_code) REFERENCES subjects(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assignments_summary (
    student_id      VARCHAR(10) NOT NULL,
    term            VARCHAR(10) NOT NULL,
    assigned        SMALLINT UNSIGNED NOT NULL,
    submitted       SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (student_id, term)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_notes (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  VARCHAR(10) NOT NULL,
    author_id   INT UNSIGNED NOT NULL,
    note_type   VARCHAR(50) NOT NULL,
    note        TEXT NULL COMMENT 'restricted: homeroom, counselor, executive only',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teacher_workload (
    teacher_id     VARCHAR(10) NOT NULL,
    term           VARCHAR(10) NOT NULL,
    teaching_hours TINYINT UNSIGNED NOT NULL COMMENT 'per week',
    extra_duties   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    lesson_log_pct TINYINT UNSIGNED NULL,
    followup_pct   TINYINT UNSIGNED NULL,
    PRIMARY KEY (teacher_id, term),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teacher_development (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id  VARCHAR(10) NOT NULL,
    kind        ENUM('training','work','activity') NOT NULL,
    title       VARCHAR(255) NOT NULL,
    hours       DECIMAL(5,1) NULL,
    date        DATE NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teacher_surveys (
    teacher_id VARCHAR(10) NOT NULL,
    term       VARCHAR(10) NOT NULL,
    source     ENUM('student','executive') NOT NULL,
    score      DECIMAL(3,2) NOT NULL COMMENT '1.00 - 5.00',
    responses  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (teacher_id, term, source),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE school_kpis (
    kpi_key  VARCHAR(30) NOT NULL,
    month    CHAR(7)     NOT NULL COMMENT 'YYYY-MM',
    value    DECIMAL(5,2) NOT NULL,
    target   DECIMAL(5,2) NOT NULL,
    PRIMARY KEY (kpi_key, month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE data_imports (
    id          VARCHAR(20) PRIMARY KEY,
    source      ENUM('excel','csv','pdf','manual','api','db') NOT NULL,
    dataset     VARCHAR(100) NOT NULL,
    file_name   VARCHAR(255) NULL,
    records     INT UNSIGNED NOT NULL DEFAULT 0,
    status      ENUM('imported','pending','failed') NOT NULL,
    note        VARCHAR(255) NULL,
    created_by  INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

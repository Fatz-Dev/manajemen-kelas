-- SQLite database structure for Manajemen Kelas application

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL CHECK(role IN ('admin', 'teacher', 'student')),
    class_id INTEGER NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive'))
);

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_name TEXT NOT NULL,
    homeroom_teacher_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homeroom_teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Trigger for updated_at timestamp on classes
CREATE TRIGGER IF NOT EXISTS update_classes_timestamp
AFTER UPDATE ON classes
BEGIN
    UPDATE classes SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_name TEXT NOT NULL,
    teacher_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Trigger for updated_at timestamp on subjects
CREATE TRIGGER IF NOT EXISTS update_subjects_timestamp
AFTER UPDATE ON subjects
BEGIN
    UPDATE subjects SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Class-Subject relationship table
CREATE TABLE IF NOT EXISTS class_subject (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE (class_id, subject_id)
);

-- Assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    subject_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    due_date TIMESTAMP NOT NULL,
    status TEXT DEFAULT 'draft' CHECK(status IN ('draft', 'published', 'closed')),
    file_path TEXT NULL,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Trigger for updated_at timestamp on assignments
CREATE TRIGGER IF NOT EXISTS update_assignments_timestamp
AFTER UPDATE ON assignments
BEGIN
    UPDATE assignments SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Submissions table
CREATE TABLE IF NOT EXISTS submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    assignment_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    submission_text TEXT NULL,
    file_path TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade REAL NULL,
    feedback TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (assignment_id, student_id)
);

-- Trigger for updated_at timestamp on submissions
CREATE TRIGGER IF NOT EXISTS update_submissions_timestamp
AFTER UPDATE ON submissions
BEGIN
    UPDATE submissions SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Trigger for updated_at timestamp on users
CREATE TRIGGER IF NOT EXISTS update_users_timestamp
AFTER UPDATE ON users
BEGIN
    UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Insert default admin user (password: admin123)
INSERT OR IGNORE INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Administrator', 'admin@example.com', 'admin');

-- Sample data for classes
INSERT OR IGNORE INTO classes (class_name) VALUES 
('Kelas 10A'),
('Kelas 10B'),
('Kelas 11A'),
('Kelas 11B'),
('Kelas 12A'),
('Kelas 12B');

-- Sample teacher users
INSERT OR IGNORE INTO users (username, password, full_name, email, role) VALUES
('guru1', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Budi Santoso', 'budi@example.com', 'teacher'),
('guru2', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Siti Rahayu', 'siti@example.com', 'teacher'),
('guru3', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Joko Widodo', 'joko@example.com', 'teacher'),
('guru4', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Ani Kusuma', 'ani@example.com', 'teacher');

-- Sample student users
INSERT OR IGNORE INTO users (username, password, full_name, email, role, class_id) VALUES
('siswa1', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Deni Pradana', 'deni@example.com', 'student', 1),
('siswa2', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Ratna Dewi', 'ratna@example.com', 'student', 1),
('siswa3', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Eko Prasetyo', 'eko@example.com', 'student', 2),
('siswa4', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Maya Sari', 'maya@example.com', 'student', 2),
('siswa5', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Rizki Pratama', 'rizki@example.com', 'student', 3),
('siswa6', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Dian Safitri', 'dian@example.com', 'student', 3);

-- Update homeroom teachers
UPDATE classes SET homeroom_teacher_id = 2 WHERE id = 1;
UPDATE classes SET homeroom_teacher_id = 3 WHERE id = 2;
UPDATE classes SET homeroom_teacher_id = 4 WHERE id = 3;
UPDATE classes SET homeroom_teacher_id = 5 WHERE id = 4;

-- Sample subjects
INSERT OR IGNORE INTO subjects (subject_name, teacher_id) VALUES
('Matematika', 2),
('Bahasa Indonesia', 3),
('Bahasa Inggris', 4),
('Fisika', 5),
('Kimia', 2),
('Biologi', 3);

-- Sample class_subject relationships
INSERT OR IGNORE INTO class_subject (class_id, subject_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 1), (2, 2), (2, 5), (2, 6),
(3, 1), (3, 2), (3, 3), (3, 5),
(4, 1), (4, 2), (4, 4), (4, 6);

-- Sample assignments
INSERT OR IGNORE INTO assignments (title, description, subject_id, class_id, due_date, status, created_by) VALUES
('Tugas Matematika Bab 1', 'Kerjakan soal-soal di halaman 15-16', 1, 1, datetime('now', '+7 days'), 'published', 2),
('Tugas Bahasa Indonesia: Menulis Esai', 'Buatlah esai tentang lingkungan hidup', 2, 1, datetime('now', '+5 days'), 'published', 3),
('UTS Bahasa Inggris', 'Ujian tengah semester Bahasa Inggris', 3, 2, datetime('now', '+3 days'), 'published', 4),
('Praktikum Fisika', 'Laporan praktikum tentang gerak lurus', 4, 3, datetime('now', '+10 days'), 'published', 5);

-- Sample submissions
INSERT OR IGNORE INTO submissions (assignment_id, student_id, submission_text, submitted_at, grade, feedback) VALUES
(1, 6, 'Jawaban tugas matematika saya...', datetime('now', '-1 days'), 85.5, 'Bagus, tapi masih ada beberapa kesalahan'),
(2, 6, 'Esai Lingkungan Hidup oleh Deni Pradana...', datetime('now', '-2 days'), 90.0, 'Sangat baik!'),
(1, 7, 'Jawaban tugas matematika dari Ratna...', datetime('now'), NULL, NULL);

-- MySQL database structure for Manajemen Kelas application
-- Compatible with MySQL 5.7 and up

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS manajemen_kelas_db;
USE manajemen_kelas_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    class_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    homeroom_teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (homeroom_teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key to users table for class_id
ALTER TABLE users
ADD CONSTRAINT fk_user_class
FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL;

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Class-Subject relationship table
CREATE TABLE IF NOT EXISTS class_subject (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_subject (class_id, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    due_date DATETIME NOT NULL,
    status ENUM('draft', 'published', 'closed') DEFAULT 'draft',
    file_path VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Submissions table
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT NULL,
    file_path VARCHAR(255) NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5,2) NULL,
    feedback TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment_student (assignment_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Administrator', 'admin@example.com', 'admin');

-- Insert sample classes
INSERT INTO classes (class_name) VALUES
('Kelas 10A'),
('Kelas 10B'),
('Kelas 11A'),
('Kelas 11B');

-- Insert sample teachers (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('guru1', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Budi Santoso', 'budi@example.com', 'teacher'),
('guru2', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Siti Rahayu', 'siti@example.com', 'teacher'),
('guru3', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Joko Widodo', 'joko@example.com', 'teacher'),
('guru4', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Ani Kusuma', 'ani@example.com', 'teacher');

-- Update homeroom teachers
UPDATE classes SET homeroom_teacher_id = 2 WHERE id = 1;
UPDATE classes SET homeroom_teacher_id = 3 WHERE id = 2;
UPDATE classes SET homeroom_teacher_id = 4 WHERE id = 3;
UPDATE classes SET homeroom_teacher_id = 5 WHERE id = 4;

-- Insert sample students (password: admin123)
INSERT INTO users (username, password, full_name, email, role, class_id) VALUES
('siswa1', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Deni Pradana', 'deni@example.com', 'student', 1),
('siswa2', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Ratna Dewi', 'ratna@example.com', 'student', 1),
('siswa3', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Eko Prasetyo', 'eko@example.com', 'student', 2),
('siswa4', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Maya Sari', 'maya@example.com', 'student', 2),
('siswa5', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Rizki Pratama', 'rizki@example.com', 'student', 3),
('siswa6', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Dian Safitri', 'dian@example.com', 'student', 3);

-- Insert sample subjects
INSERT INTO subjects (subject_name, teacher_id) VALUES
('Matematika', 2),
('Bahasa Indonesia', 3),
('Bahasa Inggris', 4),
('Fisika', 5),
('Kimia', 2),
('Biologi', 3);

-- Insert class_subject relationships
INSERT INTO class_subject (class_id, subject_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 1), (2, 2), (2, 5), (2, 6),
(3, 1), (3, 2), (3, 3), (3, 5),
(4, 1), (4, 2), (4, 4), (4, 6);

-- Insert sample assignments
INSERT INTO assignments (title, description, subject_id, class_id, due_date, status, created_by) VALUES
('Tugas Matematika Bab 1', 'Kerjakan soal-soal di halaman 15-16', 1, 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 'published', 2),
('Tugas Bahasa Indonesia: Menulis Esai', 'Buatlah esai tentang lingkungan hidup', 2, 1, DATE_ADD(NOW(), INTERVAL 5 DAY), 'published', 3),
('UTS Bahasa Inggris', 'Ujian tengah semester Bahasa Inggris', 3, 2, DATE_ADD(NOW(), INTERVAL 3 DAY), 'published', 4),
('Praktikum Fisika', 'Laporan praktikum tentang gerak lurus', 4, 3, DATE_ADD(NOW(), INTERVAL 10 DAY), 'published', 5);

-- Insert sample submissions
INSERT INTO submissions (assignment_id, student_id, submission_text, submitted_at, grade, feedback) VALUES
(1, 6, 'Jawaban tugas matematika saya...', DATE_SUB(NOW(), INTERVAL 1 DAY), 85.5, 'Bagus, tapi masih ada beberapa kesalahan'),
(2, 6, 'Esai Lingkungan Hidup oleh Deni Pradana...', DATE_SUB(NOW(), INTERVAL 2 DAY), 90.0, 'Sangat baik!'),
(1, 7, 'Jawaban tugas matematika dari Ratna...', NOW(), NULL, NULL);

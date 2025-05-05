-- Database: `manajemen_kelas_db`

CREATE DATABASE IF NOT EXISTS `manajemen_kelas_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `manajemen_kelas_db`;

-- Struktur tabel untuk users

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk classes

CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(50) NOT NULL,
  `homeroom_teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk subjects

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(50) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk class_subject (relasi Many-to-Many)

CREATE TABLE `class_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_subject_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk assignments

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `due_date` datetime NOT NULL,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk submissions

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_text` text,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `grade` float DEFAULT NULL,
  `feedback` text,
  PRIMARY KEY (`id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk notifications

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur tabel untuk announcements

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data admin
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('admin', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Administrator', 'admin@example.com', 'admin');

-- Data kelas
INSERT INTO `classes` (`class_name`) VALUES
('Kelas 10A'),
('Kelas 10B'),
('Kelas 11A'),
('Kelas 11B');

-- Data guru
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('guru1', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Budi Santoso', 'budi@example.com', 'teacher'),
('guru2', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Siti Rahayu', 'siti@example.com', 'teacher'),
('guru3', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Joko Widodo', 'joko@example.com', 'teacher'),
('guru4', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Ani Kusuma', 'ani@example.com', 'teacher');

-- Hubungkan guru dengan kelas sebagai wali kelas
UPDATE `classes` SET `homeroom_teacher_id` = 2 WHERE `id` = 1;
UPDATE `classes` SET `homeroom_teacher_id` = 3 WHERE `id` = 2;
UPDATE `classes` SET `homeroom_teacher_id` = 4 WHERE `id` = 3;
UPDATE `classes` SET `homeroom_teacher_id` = 5 WHERE `id` = 4;

-- Data siswa
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `class_id`) VALUES
('siswa1', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Deni Pradana', 'deni@example.com', 'student', 1),
('siswa2', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Ratna Dewi', 'ratna@example.com', 'student', 1),
('siswa3', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Eko Prasetyo', 'eko@example.com', 'student', 2),
('siswa4', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Maya Sari', 'maya@example.com', 'student', 2),
('siswa5', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Rizki Pratama', 'rizki@example.com', 'student', 3),
('siswa6', '$2y$10$IFmB3oH98jx1qrS9OLFbyu1.BhpCGxDZkTBB6vTYq6s4TUoEJ88te', 'Dian Safitri', 'dian@example.com', 'student', 3);

-- Data mata pelajaran
INSERT INTO `subjects` (`subject_name`, `teacher_id`) VALUES
('Matematika', 2),
('Bahasa Indonesia', 3),
('Bahasa Inggris', 4),
('Fisika', 5),
('Kimia', 2),
('Biologi', 3);

-- Relasi mata pelajaran dengan kelas
INSERT INTO `class_subject` (`class_id`, `subject_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 1), (2, 2), (2, 5), (2, 6),
(3, 1), (3, 2), (3, 3), (3, 5),
(4, 1), (4, 2), (4, 4), (4, 6);

-- Data tugas
INSERT INTO `assignments` (`title`, `description`, `subject_id`, `class_id`, `due_date`, `status`, `created_by`) VALUES
('Tugas Matematika Bab 1', 'Kerjakan soal-soal di halaman 15-16', 1, 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 'published', 2),
('Tugas Bahasa Indonesia: Menulis Esai', 'Buatlah esai tentang lingkungan hidup', 2, 1, DATE_ADD(NOW(), INTERVAL 5 DAY), 'published', 3),
('UTS Bahasa Inggris', 'Ujian tengah semester Bahasa Inggris', 3, 2, DATE_ADD(NOW(), INTERVAL 3 DAY), 'published', 4),
('Praktikum Fisika', 'Laporan praktikum tentang gerak lurus', 4, 3, DATE_ADD(NOW(), INTERVAL 10 DAY), 'published', 5);

-- Data pengumpulan tugas
INSERT INTO `submissions` (`assignment_id`, `student_id`, `submission_text`, `submitted_at`, `grade`, `feedback`) VALUES
(1, 6, 'Jawaban tugas matematika saya...', DATE_SUB(NOW(), INTERVAL 1 DAY), 85.5, 'Bagus, tapi masih ada beberapa kesalahan'),
(2, 6, 'Esai Lingkungan Hidup oleh Deni Pradana...', DATE_SUB(NOW(), INTERVAL 2 DAY), 90.0, 'Sangat baik!'),
(1, 7, 'Jawaban tugas matematika dari Ratna...', NOW(), NULL, NULL);

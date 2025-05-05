<?php
/**
 * Class-related functions for the application
 */

// Include database functions
require_once __DIR__ . '/database.php';

/**
 * Create a new class
 * @param array $data Class data
 * @return int|false Class ID or false if creation fails
 */
function createClass($data) {
    // Validate required fields
    if (!isset($data['class_name']) || empty($data['class_name'])) {
        return false;
    }
    
    // Insert class
    return insert('classes', $data);
}

/**
 * Update a class
 * @param int $classId Class ID
 * @param array $data Class data to update
 * @return bool Success or failure
 */
function updateClass($classId, $data) {
    // Check if class exists
    if (!valueExists('classes', 'id', $classId)) {
        return false;
    }
    
    // Update class
    return update('classes', $data, 'id = ?', [$classId]);
}

/**
 * Get class by ID
 * @param int $classId Class ID
 * @return array|null Class data or null if not found
 */
function getClassById($classId) {
    $sql = "SELECT c.*, u.full_name as homeroom_teacher_name
            FROM classes c
            LEFT JOIN users u ON c.homeroom_teacher_id = u.id
            WHERE c.id = ?";
    
    return fetchRow($sql, [$classId]);
}

/**
 * Get all classes
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of classes
 */
function getAllClasses($limit = 50, $offset = 0) {
    $sql = "SELECT c.*, u.full_name as homeroom_teacher_name,
                  (SELECT COUNT(*) FROM users WHERE class_id = c.id AND role = 'student') as student_count
            FROM classes c
            LEFT JOIN users u ON c.homeroom_teacher_id = u.id
            ORDER BY c.class_name
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$limit, $offset], 'ii');
}

/**
 * Count all classes
 * @return int Number of classes
 */
function countAllClasses() {
    return countRows('classes');
}

/**
 * Get classes by teacher
 * @param int $teacherId Teacher ID
 * @return array List of classes
 */
function getClassesByTeacher($teacherId) {
    $sql = "SELECT DISTINCT c.*, 
                  (SELECT COUNT(*) FROM users WHERE class_id = c.id AND role = 'student') as student_count
            FROM classes c
            JOIN class_subject cs ON c.id = cs.class_id
            JOIN subjects s ON cs.subject_id = s.id
            WHERE s.teacher_id = ? OR c.homeroom_teacher_id = ?
            ORDER BY c.class_name";
    
    return fetchAll($sql, [$teacherId, $teacherId], 'ii');
}

/**
 * Get students by class
 * @param int $classId Class ID
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of students
 */
function getStudentsByClass($classId, $limit = 50, $offset = 0) {
    $sql = "SELECT id, username, full_name, email, created_at
            FROM users
            WHERE class_id = ? AND role = 'student'
            ORDER BY full_name
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$classId, $limit, $offset], 'iii');
}

/**
 * Count students by class
 * @param int $classId Class ID
 * @return int Number of students
 */
function countStudentsByClass($classId) {
    return countRows('users', 'class_id = ? AND role = "student"', [$classId]);
}

/**
 * Get class name by ID
 * @param int $classId Class ID
 * @return string Class name or empty string if not found
 */
function getClassNameById($classId) {
    if (!$classId) {
        return '';
    }
    
    $sql = "SELECT class_name FROM classes WHERE id = ? LIMIT 1";
    $result = fetchRow($sql, [$classId]);
    
    return $result ? $result['class_name'] : '';
}

/**
 * Create a new subject
 * @param array $data Subject data
 * @return int|false Subject ID or false if creation fails
 */
function createSubject($data) {
    // Validate required fields
    if (!isset($data['subject_name']) || empty($data['subject_name'])) {
        return false;
    }
    
    // Insert subject
    return insert('subjects', $data);
}

/**
 * Update a subject
 * @param int $subjectId Subject ID
 * @param array $data Subject data to update
 * @return bool Success or failure
 */
function updateSubject($subjectId, $data) {
    // Check if subject exists
    if (!valueExists('subjects', 'id', $subjectId)) {
        return false;
    }
    
    // Update subject
    return update('subjects', $data, 'id = ?', [$subjectId]);
}

/**
 * Get subject by ID
 * @param int $subjectId Subject ID
 * @return array|null Subject data or null if not found
 */
function getSubjectById($subjectId) {
    $sql = "SELECT s.*, u.full_name as teacher_name
            FROM subjects s
            LEFT JOIN users u ON s.teacher_id = u.id
            WHERE s.id = ?";
    
    return fetchRow($sql, [$subjectId]);
}

/**
 * Get all subjects
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of subjects
 */
function getAllSubjects($limit = 50, $offset = 0) {
    $sql = "SELECT s.*, u.full_name as teacher_name
            FROM subjects s
            LEFT JOIN users u ON s.teacher_id = u.id
            ORDER BY s.subject_name
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$limit, $offset], 'ii');
}

/**
 * Count all subjects
 * @return int Number of subjects
 */
function countAllSubjects() {
    return countRows('subjects');
}

/**
 * Get subjects by teacher
 * @param int $teacherId Teacher ID
 * @return array List of subjects
 */
function getSubjectsByTeacher($teacherId) {
    $sql = "SELECT s.*
            FROM subjects s
            WHERE s.teacher_id = ?
            ORDER BY s.subject_name";
    
    return fetchAll($sql, [$teacherId]);
}

/**
 * Get subjects by class
 * @param int $classId Class ID
 * @return array List of subjects
 */
function getSubjectsByClass($classId) {
    $sql = "SELECT s.*, u.full_name as teacher_name
            FROM subjects s
            JOIN class_subject cs ON s.id = cs.subject_id
            JOIN users u ON s.teacher_id = u.id
            WHERE cs.class_id = ?
            ORDER BY s.subject_name";
    
    return fetchAll($sql, [$classId]);
}

/**
 * Assign subject to class
 * @param int $classId Class ID
 * @param int $subjectId Subject ID
 * @return bool Success or failure
 */
function assignSubjectToClass($classId, $subjectId) {
    // Check if already assigned
    $sql = "SELECT id FROM class_subject WHERE class_id = ? AND subject_id = ?";
    $result = fetchRow($sql, [$classId, $subjectId]);
    
    if ($result) {
        return true; // Already assigned
    }
    
    // Insert assignment
    $data = [
        'class_id' => $classId,
        'subject_id' => $subjectId
    ];
    
    return insert('class_subject', $data) !== false;
}

/**
 * Remove subject from class
 * @param int $classId Class ID
 * @param int $subjectId Subject ID
 * @return bool Success or failure
 */
function removeSubjectFromClass($classId, $subjectId) {
    $sql = "DELETE FROM class_subject WHERE class_id = ? AND subject_id = ?";
    
    $stmt = executeQuery($sql, [$classId, $subjectId]);
    
    if ($stmt === false) {
        return false;
    }
    
    $success = ($stmt->affected_rows > 0);
    $stmt->close();
    
    return $success;
}

/**
 * Get classes by subject
 * @param int $subjectId Subject ID
 * @return array List of classes
 */
function getClassesBySubject($subjectId) {
    $sql = "SELECT c.*
            FROM classes c
            JOIN class_subject cs ON c.id = cs.class_id
            WHERE cs.subject_id = ?
            ORDER BY c.class_name";
    
    return fetchAll($sql, [$subjectId]);
}

/**
 * Check if teacher teaches class
 * @param int $teacherId Teacher ID
 * @param int $classId Class ID
 * @return bool True if teaches, false otherwise
 */
function teacherTeachesClass($teacherId, $classId) {
    $sql = "SELECT 1
            FROM classes c
            LEFT JOIN class_subject cs ON c.id = cs.class_id
            LEFT JOIN subjects s ON cs.subject_id = s.id
            WHERE c.id = ? AND (s.teacher_id = ? OR c.homeroom_teacher_id = ?)
            LIMIT 1";
    
    $result = fetchRow($sql, [$classId, $teacherId, $teacherId], 'iii');
    
    return ($result !== null);
}



/**
 * Get subjects taught by teacher in specific class
 * @param int $teacherId Teacher ID
 * @param int $classId Class ID
 * @return array List of subjects
 */
function getSubjectsByTeacherAndClass($teacherId, $classId) {
    $sql = "SELECT s.*
            FROM subjects s
            JOIN class_subject cs ON s.id = cs.subject_id
            WHERE s.teacher_id = ? AND cs.class_id = ?
            ORDER BY s.subject_name";
    
    return fetchAll($sql, [$teacherId, $classId], 'ii');
}

/**
 * Get available teachers
 * @return array List of teachers
 */
function getAvailableTeachers() {
    $sql = "SELECT id, full_name
            FROM users
            WHERE role = 'teacher'
            ORDER BY full_name";
    
    return fetchAll($sql);
}



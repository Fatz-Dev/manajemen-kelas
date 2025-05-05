<?php
/**
 * Assignment-related functions for the application
 */

// Include database functions
require_once __DIR__ . '/database.php';

/**
 * Create a new assignment
 * @param array $data Assignment data
 * @return int|false Assignment ID or false if creation fails
 */
function createAssignment($data) {
    // Validate required fields
    $requiredFields = ['title', 'description', 'subject_id', 'class_id', 'due_date', 'created_by'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }
    
    // Insert assignment
    return insert('assignments', $data);
}

/**
 * Update an assignment
 * @param int $assignmentId Assignment ID
 * @param array $data Assignment data to update
 * @return bool Success or failure
 */
function updateAssignment($assignmentId, $data) {
    // Check if assignment exists
    if (!valueExists('assignments', 'id', $assignmentId)) {
        return false;
    }
    
    // Update assignment
    return update('assignments', $data, 'id = ?', [$assignmentId]);
}

/**
 * Get assignment by ID
 * @param int $assignmentId Assignment ID
 * @return array|null Assignment data or null if not found
 */
function getAssignmentById($assignmentId) {
    $sql = "SELECT a.*, s.subject_name, c.class_name, u.full_name as teacher_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN classes c ON a.class_id = c.id
            JOIN users u ON a.created_by = u.id
            WHERE a.id = ?";
    
    return fetchRow($sql, [$assignmentId]);
}

/**
 * Get assignments by class
 * @param int $classId Class ID
 * @param string $status Assignment status (optional)
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of assignments
 */
function getAssignmentsByClass($classId, $status = null, $limit = 10, $offset = 0) {
    $params = [$classId];
    $statusCondition = '';
    
    if ($status !== null) {
        $statusCondition = ' AND a.status = ?';
        $params[] = $status;
    }
    
    $params[] = $limit;
    $params[] = $offset;
    
    $sql = "SELECT a.*, s.subject_name, u.full_name as teacher_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN users u ON a.created_by = u.id
            WHERE a.class_id = ?$statusCondition
            ORDER BY a.due_date ASC, a.created_at DESC
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, $params);
}

/**
 * Get assignments by teacher
 * @param int $teacherId Teacher ID
 * @param string $status Assignment status (optional)
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of assignments
 */
function getAssignmentsByTeacher($teacherId, $status = null, $limit = 10, $offset = 0) {
    $params = [$teacherId];
    $statusCondition = '';
    
    if ($status !== null) {
        $statusCondition = ' AND a.status = ?';
        $params[] = $status;
    }
    
    $params[] = $limit;
    $params[] = $offset;
    
    $sql = "SELECT a.*, s.subject_name, c.class_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN classes c ON a.class_id = c.id
            WHERE a.created_by = ?$statusCondition
            ORDER BY a.due_date ASC, a.created_at DESC
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, $params);
}

/**
 * Count assignments by teacher
 * @param int $teacherId Teacher ID
 * @param string $status Assignment status (optional)
 * @return int Number of assignments
 */
function countAssignmentsByTeacher($teacherId, $status = null) {
    $params = [$teacherId];
    $statusCondition = '';
    
    if ($status !== null) {
        $statusCondition = ' AND status = ?';
        $params[] = $status;
    }
    
    return countRows('assignments', 'created_by = ?' . $statusCondition, $params);
}

/**
 * Count assignments by class
 * @param int $classId Class ID
 * @param string $status Assignment status (optional)
 * @return int Number of assignments
 */
function countAssignmentsByClass($classId, $status = null) {
    $params = [$classId];
    $statusCondition = '';
    
    if ($status !== null) {
        $statusCondition = ' AND status = ?';
        $params[] = $status;
    }
    
    return countRows('assignments', 'class_id = ?' . $statusCondition, $params);
}

/**
 * Submit an assignment
 * @param array $data Submission data
 * @return int|false Submission ID or false if submission fails
 */
function submitAssignment($data) {
    // Validate required fields
    $requiredFields = ['assignment_id', 'student_id'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }
    
    // Check if already submitted
    $existingSubmission = fetchRow(
        "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?",
        [$data['assignment_id'], $data['student_id']]
    );
    
    if ($existingSubmission) {
        // Update existing submission
        $submissionId = $existingSubmission['id'];
        if (update('submissions', $data, 'id = ?', [$submissionId])) {
            return $submissionId;
        }
        return false;
    }
    
    // Insert new submission
    return insert('submissions', $data);
}

/**
 * Get submission by ID
 * @param int $submissionId Submission ID
 * @return array|null Submission data or null if not found
 */
function getSubmissionById($submissionId) {
    $sql = "SELECT s.*, u.full_name as student_name, a.title as assignment_title, a.due_date
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            JOIN assignments a ON s.assignment_id = a.id
            WHERE s.id = ?";
    
    return fetchRow($sql, [$submissionId]);
}

/**
 * Get submission by assignment ID and student ID
 * @param int $assignmentId Assignment ID
 * @param int $studentId Student ID
 * @return array|null Submission data or null if not found
 */
function getSubmissionByAssignmentAndStudent($assignmentId, $studentId) {
    $sql = "SELECT s.*, a.title as assignment_title, a.due_date
            FROM submissions s
            JOIN assignments a ON s.assignment_id = a.id
            WHERE s.assignment_id = ? AND s.student_id = ?";
    
    return fetchRow($sql, [$assignmentId, $studentId]);
}

/**
 * Get submissions by assignment
 * @param int $assignmentId Assignment ID
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of submissions
 */
function getSubmissionsByAssignment($assignmentId, $limit = 50, $offset = 0) {
    $sql = "SELECT s.*, u.full_name as student_name
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.assignment_id = ?
            ORDER BY s.submitted_at DESC
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$assignmentId, $limit, $offset], 'iii');
}

/**
 * Count submissions by assignment
 * @param int $assignmentId Assignment ID
 * @return int Number of submissions
 */
function countSubmissionsByAssignment($assignmentId) {
    return countRows('submissions', 'assignment_id = ?', [$assignmentId]);
}

/**
 * Get submissions by student
 * @param int $studentId Student ID
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of submissions
 */
function getSubmissionsByStudent($studentId, $limit = 10, $offset = 0) {
    $sql = "SELECT s.*, a.title as assignment_title, a.due_date, a.status as assignment_status,
                  subj.subject_name
            FROM submissions s
            JOIN assignments a ON s.assignment_id = a.id
            JOIN subjects subj ON a.subject_id = subj.id
            WHERE s.student_id = ?
            ORDER BY s.submitted_at DESC
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$studentId, $limit, $offset], 'iii');
}

/**
 * Count submissions by student
 * @param int $studentId Student ID
 * @return int Number of submissions
 */
function countSubmissionsByStudent($studentId) {
    return countRows('submissions', 'student_id = ?', [$studentId]);
}

/**
 * Grade a submission
 * @param int $submissionId Submission ID
 * @param float $grade Grade
 * @param string $feedback Feedback
 * @return bool Success or failure
 */
function gradeSubmission($submissionId, $grade, $feedback) {
    $data = [
        'grade' => $grade,
        'feedback' => $feedback
    ];
    
    return update('submissions', $data, 'id = ?', [$submissionId]);
}

/**
 * Get student grades by class
 * @param int $classId Class ID
 * @return array List of students with average grade
 */
function getStudentGradesByClass($classId) {
    $sql = "SELECT u.id, u.full_name, COUNT(s.id) as submissions_count, 
                  AVG(s.grade) as average_grade, MIN(s.grade) as min_grade,
                  MAX(s.grade) as max_grade
            FROM users u
            LEFT JOIN submissions s ON u.id = s.student_id
            LEFT JOIN assignments a ON s.assignment_id = a.id AND a.class_id = ?
            WHERE u.role = 'student' AND u.class_id = ?
            GROUP BY u.id
            ORDER BY average_grade DESC";
    
    return fetchAll($sql, [$classId, $classId], 'ii');
}

/**
 * Get ungraded submissions count by teacher
 * @param int $teacherId Teacher ID
 * @return int Number of ungraded submissions
 */
function getUngradedSubmissionsCount($teacherId) {
    $sql = "SELECT COUNT(s.id) as count
            FROM submissions s
            JOIN assignments a ON s.assignment_id = a.id
            WHERE a.created_by = ? AND s.grade IS NULL";
    
    $result = fetchRow($sql, [$teacherId]);
    
    return $result ? $result['count'] : 0;
}

/**
 * Get student performance data
 * @param int $studentId Student ID
 * @return array Performance data
 */
function getStudentPerformance($studentId) {
    $sql = "SELECT 
                COUNT(s.id) as submissions_count,
                COUNT(CASE WHEN s.grade IS NOT NULL THEN 1 END) as graded_count,
                AVG(s.grade) as average_grade,
                (SELECT COUNT(a2.id) 
                 FROM assignments a2 
                 JOIN classes c ON a2.class_id = c.id
                 JOIN users u ON u.class_id = c.id
                 WHERE u.id = ? AND a2.status != 'draft') as total_assignments
            FROM submissions s
            WHERE s.student_id = ?";
    
    return fetchRow($sql, [$studentId, $studentId], 'ii');
}

<?php
/**
 * Assignment-related functions for the application
 */

// Include database functions
require_once __DIR__ . '/database.php';

/**
 * Get assignment status text
 * @param string $status Assignment status
 * @param string $dueDate Due date
 * @return string Status text
 */
function getAssignmentStatusText($status, $dueDate) {
    if ($status === 'draft') {
        return 'Draft';
    } elseif ($status === 'published') {
        if (isOverdue($dueDate)) {
            return 'Tenggat Berakhir';
        }
        return 'Aktif';
    } elseif ($status === 'archived') {
        return 'Diarsipkan';
    }
    return 'Tidak Diketahui';
}

/**
 * Get assignment status class for styling
 * @param string $status Assignment status
 * @param string $dueDate Due date
 * @return string CSS class
 */
function getAssignmentStatusClass($status, $dueDate) {
    if ($status === 'draft') {
        return 'bg-gray-100 text-gray-800';
    } elseif ($status === 'published') {
        if (isOverdue($dueDate)) {
            return 'bg-yellow-100 text-yellow-800';
        }
        return 'bg-green-100 text-green-800';
    } elseif ($status === 'archived') {
        return 'bg-gray-100 text-gray-800';
    }
    return 'bg-gray-100 text-gray-800';
}

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

// This function has been moved below with updated implementation

/**
 * Get submissions by assignment
 * @param int $assignmentId Assignment ID
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of submissions
 */
// Function getSubmissionsByAssignment is defined below (lines 605-618) with different parameters

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

// This function has been replaced with a more detailed implementation below

/**
 * Get recent assignments by class and teacher
 * @param int $classId Class ID
 * @param int $teacherId Teacher ID
 * @param int $limit Limit (default: 5)
 * @return array List of assignments
 */
function getRecentAssignmentsByClassAndTeacher($classId, $teacherId, $limit = 5) {
    $sql = "SELECT a.*, s.subject_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            WHERE a.class_id = ? AND (a.created_by = ? OR s.teacher_id = ?)
            ORDER BY a.created_at DESC
            LIMIT ?";
    
    return fetchAll($sql, [$classId, $teacherId, $teacherId, $limit], 'iiis');
}

// Using getAssignmentStatusText from helpers.php instead

// getAssignmentStatusClass is defined in helpers.php

/**
 * Get recent assignments by class
 * @param int $classId Class ID
 * @param string $status Assignment status (optional)
 * @param int $limit Limit (default: 5)
 * @return array List of assignments
 */
function getRecentAssignmentsByClass($classId, $status = null, $limit = 5) {
    $params = [$classId];
    $statusCondition = '';
    
    if ($status !== null) {
        $statusCondition = ' AND a.status = ?';
        $params[] = $status;
    }
    
    $params[] = $limit;
    
    $sql = "SELECT a.*, s.subject_name, u.full_name as teacher_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN users u ON a.created_by = u.id
            WHERE a.class_id = ?$statusCondition
            ORDER BY a.due_date ASC, a.created_at DESC
            LIMIT ?";
    
    return fetchAll($sql, $params);
}

/**
 * Get submission by assignment and student
 * @param int $assignmentId Assignment ID
 * @param int $studentId Student ID
 * @return array|null Submission data or null if not found
 */
function getSubmissionByAssignmentAndStudent($assignmentId, $studentId) {
    $sql = "SELECT *
            FROM submissions
            WHERE assignment_id = ? AND student_id = ?
            LIMIT 1";
    
    $results = fetchAll($sql, [$assignmentId, $studentId], 'ii');
    return !empty($results) ? $results[0] : null;
}

/**
 * Get student performance data
 * @param int $studentId Student ID
 * @return array Performance metrics
 */
function getStudentPerformance($studentId) {
    // Get student's class
    $user = getUserById($studentId);
    if (empty($user) || empty($user['class_id'])) {
        return [
            'total_assignments' => 0,
            'submissions_count' => 0,
            'graded_count' => 0,
            'average_grade' => 0
        ];
    }
    
    $classId = $user['class_id'];
    
    // Get total assignments for the class
    $sqlAssignments = "SELECT COUNT(*) as total
                      FROM assignments
                      WHERE class_id = ? AND status = 'published'";
    $assignmentsResult = fetchAll($sqlAssignments, [$classId], 'i');
    $totalAssignments = isset($assignmentsResult[0]['total']) ? $assignmentsResult[0]['total'] : 0;
    
    // Get submissions by this student
    $sqlSubmissions = "SELECT s.*, a.title as assignment_title
                      FROM submissions s
                      JOIN assignments a ON s.assignment_id = a.id
                      WHERE s.student_id = ?";
    $submissions = fetchAll($sqlSubmissions, [$studentId], 'i');
    
    // Count submissions
    $submissionsCount = count($submissions);
    
    // Count graded submissions and calculate average
    $gradedCount = 0;
    $totalGrade = 0;
    
    foreach ($submissions as $submission) {
        if (isset($submission['grade']) && $submission['grade'] !== null) {
            $gradedCount++;
            $totalGrade += $submission['grade'];
        }
    }
    
    $averageGrade = $gradedCount > 0 ? $totalGrade / $gradedCount : 0;
    
    return [
        'total_assignments' => $totalAssignments,
        'submissions_count' => $submissionsCount,
        'graded_count' => $gradedCount,
        'average_grade' => $averageGrade
    ];
}

/**
 * Create a new submission
 * @param array $data Submission data
 * @return bool Success status
 */
function createSubmission($data) {
    // Required fields
    if (!isset($data['assignment_id']) || !isset($data['student_id']) || !isset($data['content'])) {
        return false;
    }
    
    // Prepare SQL query and parameters
    $sql = "INSERT INTO submissions (assignment_id, student_id, content, file_path, submitted_at) VALUES (?, ?, ?, ?, ?)";
    $params = [
        $data['assignment_id'],
        $data['student_id'],
        $data['content'],
        $data['file_path'] ?? null,
        $data['submitted_at'] ?? date('Y-m-d H:i:s')
    ];
    $types = 'iisss';
    
    // Execute the query
    return executeQuery($sql, $params, $types);
}

/**
 * Update an existing submission
 * @param int $submissionId Submission ID
 * @param array $data Updated submission data
 * @return bool Success status
 */
function updateSubmission($submissionId, $data) {
    // Check submission ID
    if (!$submissionId) {
        return false;
    }
    
    // Prepare SQL parts
    $setParts = [];
    $params = [];
    $types = '';
    
    // Add parameters based on provided data
    if (isset($data['content'])) {
        $setParts[] = 'content = ?';
        $params[] = $data['content'];
        $types .= 's';
    }
    
    if (isset($data['file_path'])) {
        $setParts[] = 'file_path = ?';
        $params[] = $data['file_path'];
        $types .= 's';
    }
    
    if (isset($data['submitted_at'])) {
        $setParts[] = 'submitted_at = ?';
        $params[] = $data['submitted_at'];
        $types .= 's';
    }
    
    if (isset($data['grade'])) {
        $setParts[] = 'grade = ?';
        $params[] = $data['grade'];
        $types .= 'd';
    }
    
    if (isset($data['feedback'])) {
        $setParts[] = 'feedback = ?';
        $params[] = $data['feedback'];
        $types .= 's';
    }
    
    if (isset($data['graded_at'])) {
        $setParts[] = 'graded_at = ?';
        $params[] = $data['graded_at'];
        $types .= 's';
    }
    
    // If no data to update
    if (empty($setParts)) {
        return false;
    }
    
    // Add submission ID to parameters
    $params[] = $submissionId;
    $types .= 'i';
    
    // Build the query
    $sql = "UPDATE submissions SET " . implode(', ', $setParts) . " WHERE id = ?";
    
    // Execute the query
    return executeQuery($sql, $params, $types);
}

/**
 * Get assignments by subject ID
 * @param int $subjectId Subject ID
 * @param string $status Assignment status (optional)
 * @return array List of assignments
 */
function getAssignmentsBySubject($subjectId, $status = null) {
    $params = [$subjectId];
    $statusCondition = '';
    
    if ($status !== null) {
        $statusCondition = ' AND a.status = ?';
        $params[] = $status;
    }
    
    $sql = "SELECT a.*, s.subject_name, u.full_name as teacher_name
            FROM assignments a
            JOIN subjects s ON a.subject_id = s.id
            JOIN users u ON a.created_by = u.id
            WHERE a.subject_id = ?$statusCondition
            ORDER BY a.due_date ASC, a.created_at DESC";
    
    return fetchAll($sql, $params, 'i' . ($status !== null ? 's' : ''));
}

// Using getAssignmentsByClass function defined earlier (lines 67-89)

/**
 * Count submissions for an assignment
 * @param int $assignmentId Assignment ID
 * @return int Number of submissions
 */
function countSubmissionsByAssignment($assignmentId) {
    $sql = "SELECT COUNT(*) as count FROM submissions WHERE assignment_id = ?";
    $result = fetchAll($sql, [$assignmentId], 'i');
    return isset($result[0]['count']) ? (int)$result[0]['count'] : 0;
}

/**
 * Check if a date is overdue
 * @param string $dueDate Due date
 * @return bool True if overdue, false otherwise
 */
function isOverdue($dueDate) {
    $now = new DateTime();
    $due = new DateTime($dueDate);
    return $now > $due;
}

/**
 * Get all submissions for a specific assignment
 * @param int $assignmentId Assignment ID
 * @return array List of submissions with student information
 */
function getSubmissionsByAssignment($assignmentId) {
    $sql = "SELECT s.*, u.full_name as student_name, u.username
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.assignment_id = ?
            ORDER BY s.submitted_at DESC";
    
    return fetchAll($sql, [$assignmentId], 'i');
}

// Using getSubmissionById function defined earlier (lines 195-203)

// Using getAssignmentsByTeacher function defined earlier (lines 98-120)

/**
 * Count graded and ungraded submissions for an assignment
 * @param int $assignmentId Assignment ID
 * @return array Counts
 */
function getSubmissionStats($assignmentId) {
    // Count all submissions
    $sqlTotal = "SELECT COUNT(*) as total FROM submissions WHERE assignment_id = ?";
    $totalResult = fetchAll($sqlTotal, [$assignmentId], 'i');
    $total = isset($totalResult[0]['total']) ? (int)$totalResult[0]['total'] : 0;
    
    // Count graded submissions
    $sqlGraded = "SELECT COUNT(*) as graded FROM submissions WHERE assignment_id = ? AND grade IS NOT NULL";
    $gradedResult = fetchAll($sqlGraded, [$assignmentId], 'i');
    $graded = isset($gradedResult[0]['graded']) ? (int)$gradedResult[0]['graded'] : 0;
    
    return [
        'total' => $total,
        'graded' => $graded,
        'ungraded' => $total - $graded
    ];
}

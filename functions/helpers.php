<?php
/**
 * Helper functions for the application
 */

/**
 * Display alert message
 * @param string $message Alert message
 * @param string $type Alert type (success, danger, warning, info)
 * @return void
 */
function setAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear alert message
 * @return array|null Alert data or null if no alert
 */
function getAlert() {
    $alert = isset($_SESSION['alert']) ? $_SESSION['alert'] : null;
    unset($_SESSION['alert']);
    return $alert;
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitize output
 * @param string $value Value to sanitize
 * @return string Sanitized value
 */
function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Get GET parameter
 * @param string $name Parameter name
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Parameter value
 */
function getParam($name, $default = null) {
    return isset($_GET[$name]) ? $_GET[$name] : $default;
}

/**
 * Get POST parameter
 * @param string $name Parameter name
 * @param mixed $default Default value if parameter doesn't exist
 * @return mixed Parameter value
 */
function postParam($name, $default = null) {
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}

/**
 * Format date
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M Y') {
    if (!$date) {
        return '';
    }
    
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Format datetime
 * @param string $datetime Datetime string
 * @param string $format Datetime format
 * @return string Formatted datetime
 */
function formatDatetime($datetime, $format = 'd M Y, H:i') {
    if (!$datetime) {
        return '';
    }
    
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Check if date is past
 * @param string $date Date string
 * @return bool True if past, false otherwise
 */
function isPast($date) {
    if (!$date) {
        return false;
    }
    
    $datetime = new DateTime($date);
    $now = new DateTime();
    
    return $datetime < $now;
}

/**
 * Generate a random string
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Get file extension
 * @param string $filename Filename
 * @return string File extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file has allowed extension
 * @param string $filename Filename
 * @param array $allowedExtensions Allowed extensions
 * @return bool True if allowed, false otherwise
 */
function hasAllowedExtension($filename, $allowedExtensions) {
    $ext = getFileExtension($filename);
    return in_array($ext, $allowedExtensions);
}

/**
 * Format file size
 * @param int $bytes File size in bytes
 * @param int $precision Precision
 * @return string Formatted file size
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Upload file
 * @param array $file File data from $_FILES
 * @param string $uploadDir Upload directory
 * @param array $allowedExtensions Allowed extensions
 * @param int $maxSize Maximum file size in bytes
 * @return array|false File info or false if upload fails
 */
function uploadFile($file, $uploadDir, $allowedExtensions, $maxSize = 5242880) {
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Check file extension
    if (!hasAllowedExtension($file['name'], $allowedExtensions)) {
        return false;
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        return false;
    }
    
    // Generate unique filename
    $extension = getFileExtension($file['name']);
    $filename = generateRandomString(16) . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return false;
    }
    
    return [
        'filename' => $filename,
        'filepath' => $filepath,
        'original_name' => $file['name'],
        'size' => $file['size'],
        'type' => $file['type']
    ];
}

/**
 * Generate pagination HTML
 * @param array $pagination Pagination data
 * @return string Pagination HTML
 */
function generatePagination($pagination) {
    $html = '<div class="flex items-center justify-between mt-4">';
    
    // Info
    $html .= '<div class="text-sm text-gray-600">';
    $html .= 'Showing ' . (($pagination['currentPage'] - 1) * $pagination['itemsPerPage'] + 1) . ' to ';
    $html .= min($pagination['currentPage'] * $pagination['itemsPerPage'], $pagination['totalItems']) . ' of ' . $pagination['totalItems'] . ' entries';
    $html .= '</div>';
    
    // Links
    $html .= '<div class="flex space-x-1">';
    
    // Previous
    if ($pagination['prevPage']) {
        $html .= '<a href="' . $pagination['prevPage'] . '" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">&laquo;</a>';
    } else {
        $html .= '<span class="px-3 py-1 bg-gray-100 text-gray-400 rounded cursor-not-allowed">&laquo;</span>';
    }
    
    // Page links
    foreach ($pagination['links'] as $link) {
        if ($link['current']) {
            $html .= '<span class="px-3 py-1 bg-blue-600 text-white rounded">' . $link['page'] . '</span>';
        } else {
            $html .= '<a href="' . $link['url'] . '" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">' . $link['page'] . '</a>';
        }
    }
    
    // Next
    if ($pagination['nextPage']) {
        $html .= '<a href="' . $pagination['nextPage'] . '" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">&raquo;</a>';
    } else {
        $html .= '<span class="px-3 py-1 bg-gray-100 text-gray-400 rounded cursor-not-allowed">&raquo;</span>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Format grade
 * @param float|null $grade Grade
 * @return string Formatted grade
 */
function formatGrade($grade) {
    if ($grade === null) {
        return 'Belum Dinilai';
    }
    
    return number_format($grade, 1);
}

/**
 * Get assignment status class
 * @param string $status Assignment status
 * @param string $dueDate Due date
 * @return string CSS class
 */
function getAssignmentStatusClass($status, $dueDate) {
    if ($status === 'draft') {
        return 'bg-gray-200 text-gray-800';
    }
    
    if ($status === 'closed') {
        return 'bg-red-200 text-red-800';
    }
    
    if (isPast($dueDate)) {
        return 'bg-yellow-200 text-yellow-800';
    }
    
    return 'bg-green-200 text-green-800';
}

/**
 * Get assignment status text
 * @param string $status Assignment status
 * @param string $dueDate Due date
 * @return string Status text
 */
function getAssignmentStatusText($status, $dueDate) {
    if ($status === 'draft') {
        return 'Draft';
    }
    
    if ($status === 'closed') {
        return 'Ditutup';
    }
    
    if (isPast($dueDate)) {
        return 'Tenggat Lewat';
    }
    
    return 'Aktif';
}

/**
 * Generate breadcrumbs HTML
 * @param array $items Breadcrumb items
 * @return string Breadcrumbs HTML
 */
function generateBreadcrumbs($items) {
    $html = '<nav class="text-sm mb-4" aria-label="Breadcrumb">';
    $html .= '<ol class="list-none p-0 inline-flex space-x-1">';
    
    $count = count($items);
    
    foreach ($items as $i => $item) {
        $html .= '<li class="flex items-center">';
        
        if ($i < $count - 1) {
            $html .= '<a href="' . $item['url'] . '" class="text-blue-600 hover:text-blue-800">' . escape($item['label']) . '</a>';
            $html .= '<span class="mx-1 text-gray-500">/</span>';
        } else {
            $html .= '<span class="text-gray-500">' . escape($item['label']) . '</span>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Get user role display name
 * @param string $role User role
 * @return string Role display name
 */
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'teacher' => 'Guru',
        'student' => 'Murid'
    ];
    
    return isset($roles[$role]) ? $roles[$role] : $role;
}

/**
 * Get time ago
 * @param string $datetime Datetime string
 * @return string Time ago
 */
function getTimeAgo($datetime) {
    if (!$datetime) {
        return '';
    }
    
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Baru saja';
    }
    
    $intervals = [
        31536000 => 'tahun',
        2592000 => 'bulan',
        604800 => 'minggu',
        86400 => 'hari',
        3600 => 'jam',
        60 => 'menit'
    ];
    
    foreach ($intervals as $seconds => $label) {
        $count = floor($diff / $seconds);
        
        if ($count > 0) {
            return $count . ' ' . $label . ' yang lalu';
        }
    }
    
    return 'Baru saja';
}
?>

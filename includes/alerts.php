<?php
/**
 * Display alert message
 * @param string $message Alert message
 * @param string $type Alert type (success, danger, warning, info)
 * @return string Alert HTML
 */
function displayAlert($message, $type = 'info') {
    $bgColor = 'bg-blue-100 text-blue-700 border-blue-300';
    $icon = '<i class="fas fa-info-circle"></i>';
    
    switch ($type) {
        case 'success':
            $bgColor = 'bg-green-100 text-green-700 border-green-300';
            $icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'danger':
        case 'error':
            $bgColor = 'bg-red-100 text-red-700 border-red-300';
            $icon = '<i class="fas fa-exclamation-circle"></i>';
            break;
        case 'warning':
            $bgColor = 'bg-yellow-100 text-yellow-700 border-yellow-300';
            $icon = '<i class="fas fa-exclamation-triangle"></i>';
            break;
    }
    
    $html = '<div class="mb-4 mx-4 p-4 ' . $bgColor . ' border rounded flex justify-between items-start" role="alert" id="alert-message">';
    $html .= '<div class="flex items-center">';
    $html .= '<span class="mr-2">' . $icon . '</span>';
    $html .= '<span>' . $message . '</span>';
    $html .= '</div>';
    $html .= '<button type="button" class="text-sm" onclick="closeAlert()">×</button>';
    $html .= '</div>';
    
    return $html;
}
?>

<script>
    function closeAlert() {
        const alert = document.getElementById('alert-message');
        if (alert) {
            alert.style.display = 'none';
        }
    }
    
    // Auto-close alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alert = document.getElementById('alert-message');
        if (alert) {
            setTimeout(function() {
                closeAlert();
            }, 5000);
        }
    });
</script>

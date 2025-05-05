<?php
// Include configuration and helper functions
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions/helpers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Tailwind configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb', // bg-blue-600
                    }
                }
            }
        }
    </script>
    
    <?php if (isset($extraHeadContent)) echo $extraHeadContent; ?>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">
    <?php 
    $showNavigation = isset($hideNavigation) ? !$hideNavigation : true;
    
    if ($showNavigation) {
        require_once __DIR__ . '/navigation.php';
    }
    
    // Get and display alert if exists
    $alert = getAlert();
    if ($alert) {
        require_once __DIR__ . '/alerts.php';
        echo displayAlert($alert['message'], $alert['type']);
    }
    ?>
    
    <div class="flex-grow flex <?php echo isset($fullWidth) && $fullWidth ? '' : 'container mx-auto px-4 py-6'; ?>">
    <?php 
    // Display sidebar if needed and user is logged in
    if (isLoggedIn() && isset($showSidebar) && $showSidebar) {
        require_once __DIR__ . '/sidebar.php';
    }
    ?>
    
    <main class="flex-grow <?php echo isset($showSidebar) && $showSidebar ? 'ml-0 lg:ml-64' : ''; ?>">
        <?php if (isset($pageHeader)): ?>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?php echo escape($pageHeader); ?></h1>
            <?php if (isset($pageDescription)): ?>
            <p class="text-gray-600 mt-1"><?php echo escape($pageDescription); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

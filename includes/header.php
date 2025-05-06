<?php
// Include helper functions.  config.php removed as per user request.
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
        ?>
        <nav class="bg-blue-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="<?php echo BASE_URL; ?>" class="text-white font-bold text-xl">
                                Manajemen Kelas
                            </a>
                        </div>
                    </div>

                    <!-- Desktop menu -->
                    <div class="hidden md:flex justify-end flex-1">
                        <div class="flex items-center space-x-4">
                            <?php include 'navigation.php'; ?>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex md:hidden">
                        <button type="button" onclick="toggleMobileMenu()" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div class="md:hidden hidden" id="mobile-menu">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                        <?php include 'navigation.php'; ?>
                    </div>
                </div>
            </div>
        </nav>

        <script>
            function toggleMobileMenu() {
                const mobileMenu = document.getElementById('mobile-menu');
                mobileMenu.classList.toggle('hidden');
            }
        </script>
        <?php
    }

    // Get and display alert if exists
    $alert = getAlert();
    if ($alert) {
        require_once __DIR__ . '/alerts.php';
        echo displayAlert($alert['message'], $alert['type']);
    }
    ?>

    <div class="flex-grow flex">
        <?php 
        // Display sidebar if needed and user is logged in
        if (isLoggedIn() && isset($showSidebar) && $showSidebar) {
            require_once __DIR__ . '/sidebar.php';
        }
        ?>

        <main class="flex-grow <?php echo isset($showSidebar) && $showSidebar ? 'ml-0 lg:ml-64' : ''; ?> p-3 md:p-4 lg:p-5 overflow-x-hidden min-h-screen bg-gray-50">
            <div class="<?php echo isset($fullWidth) && $fullWidth ? '' : 'container mx-auto max-w-7xl'; ?> mt-2">
        <?php if (isset($pageHeader)): ?>
        <div class="mb-3">
            <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?php echo escape($pageHeader); ?></h1>
            <?php if (isset($pageDescription)): ?>
            <p class="text-sm text-gray-600"><?php echo escape($pageDescription); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
        </main>
    </div>
</body>
</html>
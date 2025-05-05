<?php
// Include configuration file and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';

// Page details
$pageTitle = "Reset Password";
$hideNavigation = false;
?>

<?php include '../includes/header.php'; ?>

<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-blue-600 text-white text-center">
                <h2 class="text-2xl font-bold">Reset Password</h2>
                <p class="text-blue-200">Masukkan email Anda untuk melakukan reset password</p>
            </div>
            
            <form action="<?php echo BASE_URL; ?>/auth/password_reset_process.php" method="post" class="py-6 px-8" data-validate>
                <?php if (isset($_GET['status']) && $_GET['status'] === 'sent'): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                        Instruksi reset password telah dikirim ke email Anda jika email tersebut terdaftar di sistem kami.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                        Terjadi kesalahan. Silahkan coba lagi.
                    </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="email-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Reset Password
                    </button>
                </div>
            </form>
            
            <div class="py-4 px-8 bg-gray-50 border-t text-center">
                <p class="text-sm text-gray-600">
                    <a href="<?php echo BASE_URL; ?>/login.php" class="text-blue-600 hover:underline">
                        Kembali ke halaman login
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
// Student Navigation File
// This file contains navigation links for student user role

// Define navigation items for student
$navItems = [
    [
        'title' => 'Dashboard',
        'url' => BASE_URL . '/student/dashboard.php',
        'icon' => 'fa-tachometer-alt',
        'activePattern' => '/student\/dashboard\.php$/'
    ],
    [
        'title' => 'Tugas',
        'url' => BASE_URL . '/student/assignments.php',
        'icon' => 'fa-tasks',
        'activePattern' => '/student\/(assignments|view_assignment|submit_assignment|subject_assignments)\.php/'
    ],
    [
        'title' => 'Nilai',
        'url' => BASE_URL . '/student/grades.php',
        'icon' => 'fa-chart-line',
        'activePattern' => '/student\/grades\.php$/'
    ],
    [
        'title' => 'Profil',
        'url' => BASE_URL . '/profile.php',
        'icon' => 'fa-user',
        'activePattern' => '/profile\.php$/'
    ],
];

// Get current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Render navigation items
foreach ($navItems as $item) {
    // Check if current page matches the pattern for this item
    $isActive = preg_match($item['activePattern'], $currentPath);
    
    // Output the navigation item
    echo '<li>';
    echo '<a href="' . $item['url'] . '" class="flex items-center py-3 px-4 rounded-md ' . 
         ($isActive ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600') . '">';
    echo '<i class="fas ' . $item['icon'] . ' w-5 mr-2 text-center"></i>';
    echo '<span>' . $item['title'] . '</span>';
    echo '</a>';
    echo '</li>';
}

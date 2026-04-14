<?php
// Email Configuration
// Change these settings to configure email functionality

// Option 1: Gmail (requires app password)
$email_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'muenriquez@tip.edu.ph',
    'password' => 'lciwrjxaljxqfaux',
    'from_email' => 'muenriquez@tip.edu.ph',
    'from_name' => 'Eggcipe'
];

// Option 2: Mailtrap (for testing - no real emails sent)
// $email_config = [
//     'host' => 'smtp.mailtrap.io',
//     'port' => 2525,
//     'username' => 'your-mailtrap-username',
//     'password' => 'your-mailtrap-password',
//     'from_email' => 'test@recipeapp.com',
//     'from_name' => 'Recipe App'
// ];

// Option 3: Disable emails (for testing without email functionality)
// $email_config = [
//     'host' => 'localhost',
//     'port' => 587,
//     'username' => '',
//     'password' => '',
//     'from_email' => 'noreply@recipeapp.com',
//     'from_name' => 'Recipe App'
// ];

return $email_config;
?>

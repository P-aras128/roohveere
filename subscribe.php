<?php
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pdo->prepare('INSERT IGNORE INTO subscribers (email) VALUES (?)')->execute([strtolower($email)]);
        flash('success', 'Shukriya! Aap subscribe ho gaye.');
    } else {
        flash('error', 'Sahi email likhein.');
    }
}
redirect('');

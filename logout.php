<?php
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
    flash('info', 'Aap logout ho gaye.');
}
redirect('');

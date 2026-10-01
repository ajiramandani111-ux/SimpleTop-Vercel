<?php
require_once __DIR__ . '/includes/session.php';
unset($_SESSION['user']);
$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anda telah keluar.'];
header('Location: index.php');
exit;

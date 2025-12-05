<?php
session_start();
$_SESSION['otp_time'] = time();
echo json_encode(['status' => 'success']);
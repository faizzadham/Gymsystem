<?php
require_once '../auth.php';       // auth.php is in the parent folder
requireAdmin();
require_once '../connectdb.php';  // Changed from db.php to connectdb.php

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status    = $_GET['action'] ?? '';

// 1. Capture and sanitize inputs
$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status    = $_GET['action'] ?? '';

// 2. Define permitted status updates
$allowedStatuses = ['Approved', 'Rejected', 'Completed'];

// 3. Process the update if inputs are valid
if ($bookingId && in_array($status, $allowedStatuses)) {
    
    $query = "UPDATE session_bookings SET booking_status = ? WHERE booking_id = ?";
    $stmt  = $conn->prepare($query);
    
    if ($stmt->execute([$status, $bookingId])) {
        $message = urlencode("Booking " . strtolower($status) . " successfully");
        header("Location: bookings.php?msg=$message");
    } else {
        // Optional: Handle database errors
        header("Location: bookings.php?msg=Error+updating+record");
    }
    
} else {
    // 4. Redirect if action is invalid or ID is missing
    header("Location: bookings.php");
}

exit();
<?php
require_once '../auth.php';       
requireAdmin();
require_once '../connectdb.php';  

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status    = $_GET['action'] ?? '';


$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status    = $_GET['action'] ?? '';


$allowedStatuses = ['Approved', 'Rejected', 'Completed'];


if ($bookingId && in_array($status, $allowedStatuses)) {
    
    $query = "UPDATE session_bookings SET booking_status = ? WHERE booking_id = ?";
    $stmt  = $conn->prepare($query);
    
    if ($stmt->execute([$status, $bookingId])) {
        $message = urlencode("Booking " . strtolower($status) . " successfully");
        header("Location: bookings.php?msg=$message");
    } else {
        
        header("Location: bookings.php?msg=Error+updating+record");
    }
    
} else {
    
    header("Location: bookings.php");
}

exit();
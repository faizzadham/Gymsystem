<?php
$conn = mysqli_connect("localhost", "root","", "gym_db");

if(!$conn){
    die("Connection Error " . mysqli_error($conn));
}
//echo "Congratulation! It's Connected<br><br>";
?>
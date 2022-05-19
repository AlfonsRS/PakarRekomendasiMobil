<?php
    include "db_connection.php";
    $reset = "UPDATE rules SET A=1, U=1, M=0, D=0, TD=0, FD=0;";
    if ($conn->query($reset) === TRUE) {
    // echo "Reset1 berhasil";
    // echo "<br>";
    } 
    else {
    echo "Error: " . $reset . "<br>" . $conn->error;
    }
    $reset = "UPDATE question SET answer=null;";
    if ($conn->query($reset) === TRUE) {
    // echo "Reset2 berhasil";
    // echo "<br>";
    } 
    else {
    echo "Error: " . $reset . "<br>" . $conn->error;
    }
    $reset = "UPDATE question SET confidence=null;";
    if ($conn->query($reset) === TRUE) {
    // echo "Reset2 berhasil";
    // echo "<br>";
    } 
    else {
    echo "Error: " . $reset . "<br>" . $conn->error;
    }
    $reset = "UPDATE premises SET statuses='FR';";
    if ($conn->query($reset) === TRUE) {
    echo "Reset berhasil";
    echo "<br>";
    } 
    else {
    echo "Error: " . $reset . "<br>" . $conn->error;
    }

?>
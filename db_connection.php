<?php
 $host = "127.0.0.1"; // IP MySQL nya dimana
 $db = "pakar";
 $user = "root";
 $pass = "";
 $charset = "utf8mb4";
 
 $conn = new mysqli($host, $user, $pass,$db) or die("Connect failed: %s\n". $conn -> error);
   
?>
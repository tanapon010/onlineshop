<?php
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "online_shop";

    $dns = "mysql:host=$host;dbname=$database";
    try {
        $conn = new PDO($dns, $username, $password);
        $conn->setattribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //echo "PDO : Connected successfully";
        } catch(PDOException $e){
        echo "connection failed: " . $e->getmessage();
    }

?>

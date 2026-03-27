<?php  

if ($_SERVER["REQUEST_METHOD"]) == "POST") {
    $email = $_POST["email"]; 
    $spamText = $_POST["spamText"];
    
    try {
        require_once "dbh25.inc.php";
        $query = "INSERT INTO users (email, spamText) VALUES
        (?, ?);" ;

        $stmt = $pdo->prepare($query);
        $stmt- >bindParam(":username", $spamText);
        $stmt- >bindParam(":pwd",$pwd);
        $stmt- >bindParam(":email", $email);
        $stmt- >execute()
        $pdo = null;
        $stmt = null;
        

    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
        //throws error if something goes wrong
    }
}   else { 
    header("Location: ../index25.php");
}
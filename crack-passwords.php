<?php
/*****************************************************************************
 * 
 * PASSWORD CRACKER SCRIPT by MURAT DIKICI
 * 
 * This is the base page for initializing and running cracking functions in the classes
 * 
*/

header("Cache-Control: no-cache, must-revalidate");
header("Content-type: text/html; charset=utf-8");

set_time_limit(500);

include_once "./config.php";
include "./classes/db.php";
include "./classes/cracker.php";

$recordsArray = array();
$recordsToIDs = array();
$charset = $_POST["charset"];
$passwordLengths = $_POST["passwordLengths"];

$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$cracker = new Cracker;

try {
    // Attempting to query the database
    $records = $db->query("SELECT * FROM not_so_smart_users");
    
    foreach ($records as $row) {
        $recordsArray[] = $row["password"];
        $recordsToIDs[$row["password"]] = $row["user_id"];
    }

    // Calling the cracker function to create $found array
    $found = $cracker->generateAndTestPasswords(
        $charset,
        $passwordLengths,
        $recordsArray, 
        $recordsToIDs
    );

    echo json_encode($found);
} catch (Exception $e) {
    // Handling any exceptions that may occur
    echo "Error: " . $e->getMessage();
}
?>
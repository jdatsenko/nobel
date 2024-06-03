<?php
include "../../config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['prizes_id'])) {
    $prizes_id = $_POST['prizes_id'];

    $sql = "DELETE FROM prizes WHERE id = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $prizes_id);

    if ($stmt->execute()) {
        echo "Row deleted successfully";
    } else {
        echo "Failed to delete row: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "prizes_id parameter is missing";
}

$conn->close();
?>



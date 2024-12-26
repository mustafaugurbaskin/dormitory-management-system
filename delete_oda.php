<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$id = $_GET['id'];
$sql = "DELETE FROM odalar WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: dashboard.php?tab=odalar");
} else {
    echo "Hata: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
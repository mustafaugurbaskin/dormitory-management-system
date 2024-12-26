<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$id = $_GET['id'];

// Resim dosyasını sil
$result = $conn->query("SELECT resim FROM yurtlar WHERE id = $id");
$yurt = $result->fetch_assoc();
$resim_path = json_decode($yurt['resim'], true)['path'];

if (file_exists($resim_path)) {
    unlink($resim_path); // Resim dosyasını sil
}

// Yurdu veritabanından sil
$sql = "DELETE FROM yurtlar WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboard.php?tab=yurtlar");
} else {
    echo "Hata: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

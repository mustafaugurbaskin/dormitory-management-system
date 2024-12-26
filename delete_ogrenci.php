<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$id = $_GET['id'];

// Öğrencinin bilgilerini al
$sql = "SELECT fotograf, oda_numarasi FROM ogrenciler WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $fotograf_path = json_decode($row['fotograf'], true)['path'];
    $oda_numarasi = $row['oda_numarasi'];

    // Fotoğrafı sil
    if (file_exists($fotograf_path)) {
        unlink($fotograf_path);
    }

    // Öğrenciyi sil
    $sql = "DELETE FROM ogrenciler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Oda doluluğunu güncelle
        $update_oda_query = $conn->prepare("UPDATE odalar SET mevcut = mevcut - 1 WHERE numarasi = ?");
        $update_oda_query->bind_param("s", $oda_numarasi);
        $update_oda_query->execute();

        header("Location: dashboard.php?tab=ogrenciler");
        exit;
    } else {
        echo "Hata: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>

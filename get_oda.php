<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$yurt_kodu = $_GET['yurt_kodu'];

$result = $conn->query("SELECT numarasi, kapasitesi, (SELECT COUNT(*) FROM ogrenciler WHERE oda_numarasi = numarasi) AS dolu FROM odalar WHERE tekil_yurt_kodu = '$yurt_kodu'");
$options = "<option value=''>Bir oda seçin</option>";
while ($row = $result->fetch_assoc()) {
    $disabled = $row['dolu'] >= $row['kapasitesi'] ? 'disabled' : '';
    $options .= "<option value='{$row['numarasi']}' $disabled>{$row['numarasi']} (Doluluk: {$row['dolu']}/{$row['kapasitesi']})</option>";
}

echo $options;
$conn->close();

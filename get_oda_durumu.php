<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

if (isset($_GET['oda_kodu'])) {
    $oda_kodu = $_GET['oda_kodu'];
    $sql = "SELECT kapasite, (SELECT COUNT(*) FROM ogrenciler WHERE oda_numarasi = ?) AS doluluk FROM odalar WHERE oda_numarasi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $oda_kodu, $oda_kodu);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['doluluk'] >= $row['kapasite']) {
        echo json_encode(['durum' => 'doldu']);
    } else {
        echo json_encode(['durum' => 'bos']);
    }
} else {
    echo json_encode(['durum' => 'bos']);
}

$conn->close();
?>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

// Parametreleri al
$oda_kodu = isset($_GET['oda_kodu']) ? $_GET['oda_kodu'] : '';
$yurt_kodu = isset($_GET['tekil_yurt_kodu']) ? $_GET['tekil_yurt_kodu'] : '';

// Parametre kontrolü
if (empty($oda_kodu) || empty($yurt_kodu)) {
    echo "<tr><td colspan='7'>Lütfen bir yurt ve oda seçin.</td></tr>";
    exit;
}

// Öğrencileri çek
$query = "
    SELECT * 
    FROM ogrenciler 
    WHERE oda_numarasi = ? 
      AND tekil_yurt_kodu = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $oda_kodu, $yurt_kodu);
$stmt->execute();
$result = $stmt->get_result();

$response = "";

// Öğrencileri listele
while ($row = $result->fetch_assoc()) {
    $fotograf_json = json_decode($row['fotograf'], true);
    $fotograf_path = htmlspecialchars($fotograf_json['path']);
    $response .= "
        <tr>
            <td>{$row['tc_kimlik_no']}</td>
            <td>{$row['adi']}</td>
            <td>{$row['soyadi']}</td>
            <td>{$row['telefon']}</td>
            <td>{$row['adres']}</td>
            <td><img src='$fotograf_path' alt='Fotoğraf' style='width: 100px; height: auto;'></td>
            <td>
                <a href='edit_ogrenci.php?id={$row['id']}' class='btn btn-primary btn-sm'>Düzenle</a>
                <a href='delete_ogrenci.php?id={$row['id']}' class='btn btn-danger btn-sm' style='background-color: #dc3545'>Sil</a>
            </td>
        </tr>
    ";
}

// Öğrenci yoksa mesaj göster
echo $response ?: "<tr><td colspan='7'>Bu odada öğrenci yok.</td></tr>";
$conn->close();

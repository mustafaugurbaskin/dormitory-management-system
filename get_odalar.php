<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$yurt_kodu = $_GET['yurt_kodu'];
$exclude_full = isset($_GET['exclude_full']) ? intval($_GET['exclude_full']) : 0;

// Odaları getir
$query = "
    SELECT odalar.id, odalar.numarasi, odalar.kati, odalar.kapasitesi,
           (SELECT COUNT(*) FROM ogrenciler 
            WHERE ogrenciler.oda_numarasi = odalar.numarasi 
            AND ogrenciler.tekil_yurt_kodu = odalar.tekil_yurt_kodu) AS doluluk
    FROM odalar
    WHERE odalar.tekil_yurt_kodu = '$yurt_kodu'
";

$result = $conn->query($query);

// Dropdown ve tablo için hazırlık
$response = [
    "dropdown" => "<option value=''>Bir oda seçin</option>",
    "table" => ""
];

while ($row = $result->fetch_assoc()) {
    $is_full = $row['doluluk'] >= $row['kapasitesi'];
    $disabled = ($is_full && $exclude_full) ? 'disabled' : '';
    $label = $is_full ? " (Tam Dolu)" : "";

    $response['dropdown'] .= "<option value='{$row['numarasi']}' $disabled>{$row['numarasi']} (Doluluk: {$row['doluluk']}/{$row['kapasitesi']})$label</option>";
    $response['table'] .= "
        <tr>
            <td>{$row['numarasi']}</td>
            <td>{$row['kati']}</td>
            <td>{$row['kapasitesi']}</td>
            <td>{$row['doluluk']}/{$row['kapasitesi']}</td>
            <td>
                <a href='view_oda.php?oda_numarasi={$row['numarasi']}&yurt_kodu=$yurt_kodu' class='btn btn-primary btn-sm' style='border:none'>Görüntüle</a>
                <a href='edit_oda.php?id={$row['id']}' class='btn btn-warning btn-sm' style='border:none'>Düzenle</a>
            </td>
        </tr>
    ";
}

echo json_encode($response);
$conn->close();

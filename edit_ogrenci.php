<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adi = $_POST['adi'];
    $soyadi = $_POST['soyadi'];
    $telefon = $_POST['telefon'];
    $oda_numarasi = $_POST['oda_numarasi'];
    $yurt_kodu = $_POST['yurt_kodu'];

    $sql = "SELECT fotograf, oda_numarasi FROM ogrenciler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $fotograf_path = json_decode($row['fotograf'], true)['path'];
    $eski_oda_numarasi = $row['oda_numarasi'];

    if (!empty($_FILES['fotograf']['name'])) {
        $upload_dir = "ogrenci_resmi/";
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $uploaded_file = $_FILES['fotograf']['tmp_name'];
        $file_extension = pathinfo($_FILES['fotograf']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_types)) {
            $random_name = uniqid() . '.' . $file_extension;
            $fotograf_path = $upload_dir . $random_name;

            if (!move_uploaded_file($uploaded_file, $fotograf_path)) {
                die("Fotoğraf yüklenirken bir hata oluştu.");
            }
        } else {
            die("Sadece JPG ve PNG dosyaları yüklenebilir.");
        }
    }

    $fotograf_json = json_encode(['path' => $fotograf_path]);

    $sql = "UPDATE ogrenciler SET adi = ?, soyadi = ?, telefon = ?, oda_numarasi = ?, tekil_yurt_kodu = ?, fotograf = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $adi, $soyadi, $telefon, $oda_numarasi, $yurt_kodu, $fotograf_json, $id);
    if ($stmt->execute()) {
        header("Location: dashboard.php?tab=ogrenciler");
        exit;
    } else {
        echo "Hata: " . $stmt->error;
    }
}

$sql = "SELECT * FROM ogrenciler WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f7f8fc;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        .form-label {
            font-weight: bold;
            color: #495057;
        }

        .btn-primary {
            background-color: #6a0dad;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #531c99;
        }

        img {
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-center">Öğrenci Düzenle</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="adi" class="form-label">Adı</label>
            <input type="text" class="form-control" id="adi" name="adi" value="<?= htmlspecialchars($row['adi']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="soyadi" class="form-label">Soyadı</label>
            <input type="text" class="form-control" id="soyadi" name="soyadi" value="<?= htmlspecialchars($row['soyadi']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefon" class="form-label">Telefon</label>
            <input type="text" class="form-control" id="telefon" name="telefon" value="<?= htmlspecialchars($row['telefon']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="yurt_kodu" class="form-label">Yurt Seç</label>
            <select name="yurt_kodu" id="yurt_kodu" class="form-select" required>
                <option value="">Yurt Seç</option>
                <?php
                $cinsiyet = $row['cinsiyet'];
                $yurtlar = $conn->query("SELECT * FROM yurtlar WHERE yurt_tipi = '$cinsiyet'");
                while ($yurt = $yurtlar->fetch_assoc()) {
                    $selected = ($yurt['tekil_yurt_kodu'] === $row['tekil_yurt_kodu']) ? "selected" : "";
                    echo "<option value='{$yurt['tekil_yurt_kodu']}' $selected>{$yurt['yurtadi']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="oda_numarasi" class="form-label">Oda Numarası</label>
            <select name="oda_numarasi" id="oda_numarasi" class="form-select" required>
                <option value="">Önce Yurt Seçin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="fotograf" class="form-label">Fotoğraf</label>
            <input type="file" class="form-control" id="fotograf" name="fotograf" accept=".jpg,.jpeg,.png">
            <?php if (!empty($row['fotograf'])): ?>
                <img src="<?= htmlspecialchars(json_decode($row['fotograf'], true)['path']) ?>" alt="Mevcut Fotoğraf" style="width: 100px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary w-100">Güncelle</button>
    </form>
</div>
<script>
$(document).ready(function () {
    $('#yurt_kodu').change(function () {
        var yurtKodu = $(this).val();
        var cinsiyet = "<?= $row['cinsiyet'] ?>"; // PHP'den öğrenci cinsiyetini al
        
        if (yurtKodu) {
            $.ajax({
                url: 'get_odalar.php',
                type: 'GET',
                dataType: 'json',
                data: { yurt_kodu: yurtKodu, cinsiyet: cinsiyet },
                success: function (response) {
                    $('#oda_numarasi').html(response.dropdown);
                },
                error: function () {
                    alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                }
            });
        } else {
            $('#oda_numarasi').html('<option value="">Bir oda seçin</option>');
        }
    });
});
</script>
</body>
</html>

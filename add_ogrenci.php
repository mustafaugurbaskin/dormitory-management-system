<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rekoryurdu";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
    $conn->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");

    if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

    // Form verilerini güvenli bir şekilde al
    $tc_kimlik_no = isset($_POST['tc_kimlik_no']) ? trim($_POST['tc_kimlik_no']) : '';
    $adi = ucwords(strtolower(trim($_POST['adi'])));
    $soyadi = ucwords(strtolower(trim($_POST['soyadi'])));    
    var_dump(mb_detect_encoding($_POST['adi']), $_POST['adi']);


    $telefon = isset($_POST['telefon']) ? trim($_POST['telefon']) : '';
    $adres = isset($_POST['adres']) ? trim($_POST['adres']) : '';
    $cinsiyet = isset($_POST['cinsiyet']) ? $_POST['cinsiyet'] : '';
    $fotograf_path = "";

    // Boş alan kontrolü
    if (empty($tc_kimlik_no) || empty($adi) || empty($soyadi) || empty($telefon) || empty($adres) || empty($cinsiyet)) {
        die("Lütfen tüm alanları doldurun.");
    }

    // T.C. Kimlik No kontrolü
    $check_tc_query = $conn->prepare("SELECT * FROM ogrenciler WHERE tc_kimlik_no = ?");
    $check_tc_query->bind_param("s", $tc_kimlik_no);
    $check_tc_query->execute();
    $tc_result = $check_tc_query->get_result();
    if ($tc_result->num_rows > 0) {
        die("Bu T.C. Kimlik No ile kayıtlı bir öğrenci zaten var.");
    }

    // Resim yükleme işlemi
    if (!empty($_FILES['fotograf']['name'])) {
        $upload_dir = "ogrenci_resmi/";
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = pathinfo($_FILES['fotograf']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $random_name = uniqid() . '.' . $file_extension;
            $fotograf_path = $upload_dir . $random_name;
            if (!move_uploaded_file($_FILES['fotograf']['tmp_name'], $fotograf_path)) {
                die("Resim yüklenirken bir hata oluştu.");
            }
        } else {
            die("Sadece JPG ve PNG dosyaları yüklenebilir.");
        }
    }

    // Cinsiyete göre uygun yurt ve oda seçimi
    $yurt_query = $conn->prepare("
        SELECT * FROM yurtlar 
        WHERE yurt_tipi = ? AND 
              kapasite > (SELECT COUNT(*) FROM ogrenciler WHERE ogrenciler.tekil_yurt_kodu = yurtlar.tekil_yurt_kodu)
    ");
    $yurt_query->bind_param("s", $cinsiyet);
    $yurt_query->execute();
    $yurt_result = $yurt_query->get_result();
    $selected_yurt = null;
    $selected_oda = null;

    while ($yurt = $yurt_result->fetch_assoc()) {
        $yurt_kodu = $yurt['tekil_yurt_kodu'];

        $oda_query = $conn->prepare("
            SELECT * FROM odalar 
            WHERE tekil_yurt_kodu = ? AND 
                  kapasitesi > (SELECT COUNT(*) FROM ogrenciler WHERE ogrenciler.oda_numarasi = odalar.numarasi AND ogrenciler.tekil_yurt_kodu = ?)
        ");
        $oda_query->bind_param("ss", $yurt_kodu, $yurt_kodu);
        $oda_query->execute();
        $oda_result = $oda_query->get_result();

        if ($oda_result->num_rows > 0) {
            $selected_oda = $oda_result->fetch_assoc();
            $selected_yurt = $yurt;
            break;
        }
    }

    if ($selected_yurt && $selected_oda) {
        $fotograf_json = json_encode(['path' => $fotograf_path]);
        $insert_query = $conn->prepare("
            INSERT INTO ogrenciler (tc_kimlik_no, adi, soyadi, telefon, adres, oda_numarasi, fotograf, tekil_yurt_kodu, cinsiyet) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert_query->bind_param("sssssssss", $tc_kimlik_no, $adi, $soyadi, $telefon, $adres, $selected_oda['numarasi'], $fotograf_json, $selected_yurt['tekil_yurt_kodu'], $cinsiyet);
        if ($insert_query->execute()) {
            header("Location: dashboard.php?tab=ogrenciler&success=Öğrenci başarıyla eklendi: {$selected_yurt['yurtadi']} - Oda {$selected_oda['numarasi']}.");
            exit;
        } else {
            die("Hata: " . $insert_query->error);
        }
    } else {
        die("Uygun yurt veya oda bulunamadı.");
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Öğrenci Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }
        .alert {
            margin-top: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Yeni Öğrenci Ekle</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="tc_kimlik_no" class="form-label">T.C. Kimlik No</label>
            <input type="text" class="form-control" name="tc_kimlik_no" placeholder="Örn: 12345678901" required pattern="\d{11}">
        </div>
        <div class="mb-3">
            <label for="adi" class="form-label">Adı</label>
            <input type="text" class="form-control" name="adi" placeholder="Örn: Mehmet" required>
        </div>
        <div class="mb-3">
            <label for="soyadi" class="form-label">Soyadı</label>
            <input type="text" class="form-control" name="soyadi" placeholder="Örn: Yılmaz" required>
        </div>
        <div class="mb-3">
            <label for="telefon" class="form-label">Telefon</label>
            <input type="text" class="form-control" name="telefon" placeholder="Örn: 05555555555" required>
        </div>
        <div class="mb-3">
            <label for="adres" class="form-label">Adres</label>
            <textarea class="form-control" name="adres" placeholder="Örn: Sakarya, Serdivan" required></textarea>
        </div>
        <div class="mb-3">
            <label for="cinsiyet" class="form-label">Cinsiyet</label>
            <select class="form-select" name="cinsiyet" required>
                <option value="Kız">Kız</option>
                <option value="Erkek">Erkek</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="fotograf" class="form-label">Fotoğraf</label>
            <input type="file" class="form-control" name="fotograf" accept=".jpg,.jpeg,.png" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ekle</button>
    </form>
</div>
</body>
</html>

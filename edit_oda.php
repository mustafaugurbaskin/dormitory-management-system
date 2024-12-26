<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kapasitesi = $_POST['kapasitesi'];

    // Güncel doluluk bilgisi
    $sql_doluluk = "SELECT (SELECT COUNT(*) FROM ogrenciler WHERE oda_numarasi = odalar.numarasi AND odalar.id = ?) AS doluluk FROM odalar WHERE id = ?";
    $stmt_doluluk = $conn->prepare($sql_doluluk);
    $stmt_doluluk->bind_param("ii", $id, $id);
    $stmt_doluluk->execute();
    $result_doluluk = $stmt_doluluk->get_result();
    $doluluk = $result_doluluk->fetch_assoc()['doluluk'];

    // Kapasite kontrolü
    if ($kapasitesi < $doluluk) {
        $error_message = "Kapasite, doluluk değerinden daha düşük olamaz!";
    } else {
        // Kapasiteyi güncelle
        $sql = "UPDATE odalar SET kapasitesi = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $kapasitesi, $id);
        if ($stmt->execute()) {
            header("Location: dashboard.php?tab=odalar&success=Kapasite başarıyla güncellendi!");
            exit;
        } else {
            $error_message = "Hata: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Oda bilgilerini çek
$sql = "SELECT * FROM odalar WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Mevcut doluluk bilgisi
$sql_doluluk = "SELECT (SELECT COUNT(*) FROM ogrenciler WHERE oda_numarasi = odalar.numarasi AND odalar.id = ?) AS doluluk FROM odalar WHERE id = ?";
$stmt_doluluk = $conn->prepare($sql_doluluk);
$stmt_doluluk->bind_param("ii", $id, $id);
$stmt_doluluk->execute();
$result_doluluk = $stmt_doluluk->get_result();
$doluluk = $result_doluluk->fetch_assoc()['doluluk'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oda Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #6a0dad;
            border-color: #6a0dad;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #5c0cbb;
            border-color: #5c0cbb;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2 class="text-center">Oda Düzenle</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="doluluk" class="form-label">Doluluk</label>
                <input type="text" class="form-control" id="doluluk" value="<?= htmlspecialchars($doluluk) ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="kapasitesi" class="form-label">Kapasite</label>
                <input type="number" class="form-control" id="kapasitesi" name="kapasitesi" 
                       value="<?= htmlspecialchars($row['kapasitesi']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Güncelle</button>
            <a href="dashboard.php?tab=odalar" class="btn btn-secondary w-100 mt-2">Geri Dön</a>
        </form>
    </div>
</div>
</body>
</html>

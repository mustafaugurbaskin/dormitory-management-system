<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekor Yurtlar Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Red Hat Display', sans-serif;
            background-color: #f8f9fa;
        }

        /* Header */
        header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            background-color: transparent;
            z-index: 10;
        }

        .header-title {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .header-button {
            background-color: #007bff;
            color: #ffffff;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .header-button:hover {
            background-color: #0056b3;
            color: #ffffff;
        }

        /* Hero Section */
        .hero {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            height: 500px;
            background-image: url("bg.jpg");
            background-size: cover;
            background-position: center;
            color: #fff;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .hero h1,
        .hero p {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 42px;
            font-weight: 900;
        }

        .hero p {
            font-size: 18px;
            width: 70%;
        }

        /* Card Section */
        .yurt-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .yurt-card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
        }

        .progress {
            height: 16px;
            border-radius: 8px;
        }

        .progress-bar {
            font-weight: 600;
            font-size: 14px;
            line-height: 16px;
            text-align: center;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .card-text {
            color: #555;
            font-size: 16px;
            margin-bottom: 15px;
        }

        /* Footer */
        footer {
            background-color: #343a40;
            color: #fff;
            padding: 15px;
        }

        footer p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="d-flex justify-content-between align-items-center px-5">
    <h1 class="header-title">Rekor Yurtlar Müdürlüğü</h1>
    <?php 
    if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])): ?>
        <a href="dashboard.php" class="header-button d-flex align-items-center">
            <i class="bi bi-person-circle" style="font-size: 1.2rem; margin-right: 8px;"></i>
            Admin Paneli
        </a>
    <?php else: ?>
        <a href="login.php" class="header-button">Giriş Yap</a>
    <?php endif; ?>
</header>

<!-- Hero Section -->
<section class="hero">
    <h1>Rekor Yurtlar Yönetim Sistemi</h1>
    <p>
        Kapasiteleri takip edin, doluluk oranlarını analiz edin ve operasyonlarınızı kolaylaştırın. 
        Modern ve kullanıcı dostu arayüzümüzle, yurt yönetimi artık hiç olmadığı kadar kolay!
    </p>
</section>

<!-- Card Section -->
<div class="container my-5">
    <h2 class="text-center custom-underline" style="margin-bottom: 30px;">Yurtlarımızdaki Doluluk Oranları</h2>
    <div class="row justify-content-center">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "rekoryurdu";

        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8mb4");

        if ($conn->connect_error) {
            die("Bağlantı hatası: " . $conn->connect_error);
        }

        $sql = "
            SELECT 
                yurtlar.yurtadi, 
                yurtlar.kapasite,
                COALESCE(COUNT(ogrenciler.id), 0) AS toplam_ogrenci
            FROM 
                yurtlar
            LEFT JOIN 
                odalar ON yurtlar.tekil_yurt_kodu = odalar.tekil_yurt_kodu
            LEFT JOIN 
                ogrenciler ON odalar.numarasi = ogrenciler.oda_numarasi
                AND odalar.tekil_yurt_kodu = ogrenciler.tekil_yurt_kodu
            GROUP BY 
                yurtlar.yurtadi, yurtlar.kapasite
        ";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()):
            $percentage = $row['kapasite'] > 0 ? round(($row['toplam_ogrenci'] / $row['kapasite']) * 100) : 0;
        ?>
        <div class="col-md-4 mb-4">
            <div class="yurt-card">
                <h3 class="card-title"><?= htmlspecialchars($row['yurtadi']) ?></h3>
                <p class="card-text"><?= htmlspecialchars($row['toplam_ogrenci']) ?> / <?= htmlspecialchars($row['kapasite']) ?> dolu</p>
                <div class="progress mx-auto">
                    <div 
                        class="progress-bar bg-success" 
                        role="progressbar" 
                        style="width: <?= $percentage ?>%;" 
                        aria-valuenow="<?= $percentage ?>" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                        <?= $percentage ?>%
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; $conn->close(); ?>
    </div>
</div>

<!-- Footer -->
<footer class="text-center">
    <p>© 2024 Rekor Yurtlar Yönetim Sistemi. Tüm Hakları Saklıdır.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

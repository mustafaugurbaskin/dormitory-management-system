<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'yurtlar';

// Arama İşlemi
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : '';
$search_results = [];

if ($current_tab === 'ogrenciler' && !empty($search_query) && !empty($search_type)) {
    $sql = "
        SELECT 
            ogrenciler.tc_kimlik_no, 
            ogrenciler.adi, 
            ogrenciler.soyadi, 
            ogrenciler.telefon, 
            ogrenciler.adres, 
            ogrenciler.fotograf, 
            yurtlar.yurtadi AS yurt_adi, 
            yurtlar.tekil_yurt_kodu AS yurt_kodu, 
            odalar.numarasi AS oda_numarasi
        FROM 
            ogrenciler
        LEFT JOIN 
            yurtlar ON ogrenciler.tekil_yurt_kodu = yurtlar.tekil_yurt_kodu
        LEFT JOIN 
            odalar ON ogrenciler.oda_numarasi = odalar.numarasi AND ogrenciler.tekil_yurt_kodu = odalar.tekil_yurt_kodu
    ";
    if ($search_type === 'tc') {
        $sql .= " WHERE ogrenciler.tc_kimlik_no LIKE ?";
    } elseif ($search_type === 'name') {
        $sql .= " WHERE CONCAT(ogrenciler.adi, ' ', ogrenciler.soyadi) LIKE ?";
    }

    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
    
    body {
            font-family: 'Red Hat Display', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Navbar */
        .navbar {
            background-color: transparent !important; /* Arka planı transparan yapar */
            color: #fff;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 10;
            margin-top: 10px;
        }

        .navbar .navbar-brand {
            color: #fff;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .navbar .btn {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            border: none;
            transition: background-color 0.3s ease;
        }

        .navbar .btn:hover {
            background-color: #0056b3;
        }

 /* Sekme tasarımı */
.nav-tabs .nav-link {
    position: relative;
    display: inline-block;
    padding: 10px 20px;
    color: #28a745; /* Sekme metin rengi */
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
    overflow: hidden;
}

/* Aktif sekme */
.nav-tabs .nav-link.active {
    background-color: #28a745;
    color: #fff;
    border-radius: 5px;
}

/* Efekt için arka plan */
.nav-tabs .nav-link::before {
    content: '';
    position: absolute;
    bottom: -100%; /* Efekt aşağıdan başlar */
    left: 0;
    width: 100%;
    height: 100%;
    background: #28a745;
    z-index: -1;
    transition: bottom 0.3s ease;
}

/* Sekme üzerine gelindiğinde arka plan kayması */
.nav-tabs .nav-link:hover::before {
    bottom: 0; /* Efekt tamamlanır */
}

.nav-tabs .nav-link:hover {
    color: #fff; /* Metin rengi değişir */
}



        /* Hero Section */
        .hero {
    background: url("dashboard-bg.jpg") no-repeat center center; /* Resmi tek seferde ve ortada gösterir */
    background-size: cover; /* Resmi konteyner boyutuna uygun hale getirir */
    height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    color: #fff;
}

.hero::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* Siyah katman ve opacity */
    z-index: 1;
}


        .hero h1, .hero p {
            z-index: 2;
            position: relative;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 1rem;
        }
        
        /* Genel Tablo Stili */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }

        .table thead th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #ddd;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table tbody td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        /* Fotoğraf Kolonu */
        .table tbody td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Düğme Stili */
        .table tbody td a {
            display: inline-block;
            padding: 6px 12px;
            color: #fff;
            background-color: #007bff;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }

        .table tbody td a:hover {
            background-color: #0056b3;
        }

        /* Çıkış Yap butonu tasarımı */
        .btn-logout {
            background-color: #dc3545;
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px; /* Yuvarlak köşeler */
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px; /* Varsayılan gölge */
            transition: all 0.3s ease; /* Geçiş efektleri */
        }

        /* Hover durumunda değişiklikler */
        .btn-logout:hover {
            background-color: #c82333; /* Daha koyu bir kırmızı */
            box-shadow: rgba(149, 157, 165, 0.4) 0px 12px 30px; /* Daha yoğun gölge */
            text-decoration: none; /* Alt çizgi olmaması için */
        }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">Rekor Yurtlar Admin Paneli</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="logout.php" class="btn-logout">Çıkış Yap</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="hero">
    <h1>Rekor Yurtlar Yönetim Paneli</h1>
    <p>Öğrencileri, yurt odalarını ve yurt bilgilerini hızlıca yönetin.</p>
</section>

<div class="container mt-5">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs justify-content-center">
        <li class="nav-item">
            <a class="nav-link <?= $current_tab === 'yurtlar' ? 'active' : '' ?>" href="?tab=yurtlar">Yurtlar</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current_tab === 'odalar' ? 'active' : '' ?>" href="?tab=odalar">Odalar</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current_tab === 'ogrenciler' ? 'active' : '' ?>" href="?tab=ogrenciler">Öğrenciler</a>
        </li>
    </ul>

    <div class="tab-content mt-4">
        <?php
        if ($current_tab === 'yurtlar'): ?>
            <h2>Yurtlar</h2>
            <p>Yurtları arayın, filtreleyin, düzenleyin veya silin.</p>
            <!-- Arama ve Filtre Formu -->
            <form method="GET" action="">
                <input type="hidden" name="tab" value="yurtlar">
                <div class="row mb-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" placeholder="Yurt adı ara..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="filter">
                            <option value="">Tümü</option>
                            <option value="Kız" <?= (isset($_GET['filter']) && $_GET['filter'] === 'Kız') ? 'selected' : '' ?>>Kız</option>
                            <option value="Erkek" <?= (isset($_GET['filter']) && $_GET['filter'] === 'Erkek') ? 'selected' : '' ?>>Erkek</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Filtrele</button>
                    </div>
                    <div class="col-md-2">
                        <a href="dashboard.php?tab=yurtlar" class="btn btn-secondary w-100">Filtreleri Temizle</a>
                    </div>
                </div>
            </form>

            <a href="add_yurt.php" class="btn btn-primary mb-3">Yeni Yurt Ekle</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Yurt Adı</th>
                        <th>Adres</th>
                        <th>Telefon</th>
                        <th>Yurt Tipi</th>
                        <th>Resim</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Arama ve Filtreleme
                    $search_query = isset($_GET['search']) ? $_GET['search'] : '';
                    $filter_query = isset($_GET['filter']) ? $_GET['filter'] : '';
                    $sql = "SELECT * FROM yurtlar";

                    $conditions = [];
                    $params = [];
                    $param_types = "";

                    if (!empty($search_query)) {
                        $conditions[] = "yurtadi LIKE ?";
                        $params[] = "%" . $search_query . "%";
                        $param_types .= "s";
                    }
                    if (!empty($filter_query)) {
                        $conditions[] = "yurt_tipi = ?";
                        $params[] = $filter_query;
                        $param_types .= "s";
                    }
                    if (!empty($conditions)) {
                        $sql .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $stmt = $conn->prepare($sql);

                    if (!empty($params)) {
                        $stmt->bind_param($param_types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()):
                        $resim_json = json_decode($row['resim'], true);
                        $resim_path = $resim_json['path'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['yurtadi']) ?></td>
                            <td><?= htmlspecialchars($row['adres']) ?></td>
                            <td><?= htmlspecialchars($row['telefon']) ?></td>
                            <td><?= htmlspecialchars($row['yurt_tipi']) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($resim_path) ?>" alt="Yurt Resmi" style="width: 100px; height: auto;">
                            </td>
                            <td>
                                <a href="edit_yurt.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" style="border: none">Düzenle</a>
                                <a href="delete_yurt.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" style="background-color: #dc3545">Sil</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($current_tab === 'odalar'): ?>
            <h2>Odalar</h2>
            <p>Farklı yurtlardaki yurt odalarını hızlıca görüntüleyin veya düzenleyin.</p>
            <a href="add_oda.php" class="btn btn-primary mb-3">Yeni Oda Ekle</a>
            <div class="mb-3">
                <label for="yurt_select_odalar" class="form-label">Yurt Seç</label>
                <select id="yurt_select_odalar" class="form-select">
                    <option value="">Bir yurt seçin</option>
                    <?php
                    $yurtlar = $conn->query("SELECT tekil_yurt_kodu, yurtadi FROM yurtlar");
                    while ($yurt = $yurtlar->fetch_assoc()) {
                        echo "<option value='{$yurt['tekil_yurt_kodu']}'>{$yurt['yurtadi']} ({$yurt['tekil_yurt_kodu']})</option>";
                    }
                    ?>
                </select>
            </div>
            <table class="table table-bordered" id="odalar_table" style="display: none;">
                <thead>
                    <tr>
                        <th>Oda Numarası</th>
                        <th>Kat</th>
                        <th>Kapasite</th>
                        <th>Doluluk</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody id="odalar_table_body"></tbody>
            </table>
        <?php elseif ($current_tab === 'ogrenciler'): ?>
            <h2>Öğrenciler</h2>
            <p>Yurtlardaki öğrencileri yurtlarına ve odalarına göre hızlıca arayın, görüntüleyin, düzenleyin veya silin.</p>
            <form method="GET" action="">
                <input type="hidden" name="tab" value="ogrenciler">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" name="search_type" required>
                            <option value="tc" <?= isset($search_type) && $search_type === 'tc' ? 'selected' : '' ?>>T.C. Kimlik No</option>
                            <option value="name" <?= isset($search_type) && $search_type === 'name' ? 'selected' : '' ?>>İsim Soyisim</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search_query" placeholder="Arama..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Ara</button>
                    </div>
                    <div class="col-md-2">
                        <a href="dashboard.php?tab=ogrenciler" class="btn btn-secondary w-100">Temizle</a>
                    </div>
                </div>
            </form>
            <?php if (!empty($search_results)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>T.C. Kimlik No</th>
                            <th>Ad</th>
                            <th>Soyad</th>
                            <th>Telefon</th>
                            <th>Adres</th>
                            <th>Yurt</th>
                            <th>Yurt Kodu</th>
                            <th>Oda</th>
                            <th>Fotoğraf</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $ogrenci): ?>
                            <tr>
                                <td><?= htmlspecialchars($ogrenci['tc_kimlik_no']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['adi']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['soyadi']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['telefon']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['adres']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['yurt_adi']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['yurt_kodu']) ?></td>
                                <td><?= htmlspecialchars($ogrenci['oda_numarasi']) ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars(json_decode($ogrenci['fotograf'], true)['path']) ?>" alt="Fotoğraf" style="width: 100px;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (!empty($search_query)): ?>
                <div class="alert alert-warning">Arama sonucu bulunamadı.</div>
            <?php endif; ?>
            <a href="add_ogrenci.php" class="btn btn-primary mb-3">Yeni Öğrenci Ekle</a>
            <div class="mb-3">
                <label for="yurt_select" class="form-label">Yurt Seç</label>
                <select id="yurt_select" class="form-select">
                    <option value="">Bir yurt seçin</option>
                    <?php
                    $yurtlar = $conn->query("SELECT tekil_yurt_kodu, yurtadi FROM yurtlar");
                    while ($yurt = $yurtlar->fetch_assoc()) {
                        echo "<option value='{$yurt['tekil_yurt_kodu']}'>{$yurt['yurtadi']} ({$yurt['tekil_yurt_kodu']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="oda_select" class="form-label">Oda Seç</label>
                <select id="oda_select" class="form-select" disabled>
                    <option value="">Önce yurt seçin</option>
                </select>
            </div>
            <div id="selected_yurt_oda" class="mb-3 text-info"></div>
            <div id="ogrenci_table" style="display: none;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>T.C. Kimlik No</th>
                            <th>Ad</th>
                            <th>Soyad</th>
                            <th>Telefon</th>
                            <th>Adres</th>
                            <th>Fotoğraf</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="ogrenci_table_body"></tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p class="mb-2" style="font-size: 14px;">© 2024 Rekor Yurtlar Yönetim Paneli. Tüm Hakları Saklıdır.</p>
    </div>
</footer>


<script>
$(document).ready(function() {
    // Yurt seçimi odalar için
    $('#yurt_select_odalar').change(function() {
        const yurtKodu = $(this).val();
        if (yurtKodu) {
            $.ajax({
                url: 'get_odalar.php',
                type: 'GET',
                data: { yurt_kodu: yurtKodu },
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#odalar_table_body').html(data.table);
                    $('#odalar_table').show();
                },
                error: function() {
                    alert("Odalar yüklenirken bir hata oluştu.");
                }
            });
        } else {
            $('#odalar_table_body').empty();
            $('#odalar_table').hide();
        }
    });

    // Yurt seçimi öğrenciler için
    $('#yurt_select').change(function() {
        const yurtKodu = $(this).val();
        if (yurtKodu) {
            $.ajax({
                url: 'get_odalar.php',
                type: 'GET',
                data: { yurt_kodu: yurtKodu },
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#oda_select').html(data.dropdown).prop('disabled', false);
                    $('#ogrenci_table_body').empty();
                    $('#selected_yurt_oda').html(`Seçilen Yurt: ${$('#yurt_select option:selected').text()}`);
                },
                error: function() {
                    alert("Odalar yüklenirken bir hata oluştu.");
                }
            });
        } else {
            $('#oda_select').html('<option value="">Önce yurt seçin</option>').prop('disabled', true);
            $('#ogrenci_table_body').empty();
            $('#selected_yurt_oda').html('');
        }
    });

    // Oda seçimi öğrenciler için
    $('#oda_select').change(function() {
        const odaKodu = $(this).val();
        const yurtKodu = $('#yurt_select').val();
        if (odaKodu && yurtKodu) {
            $.ajax({
                url: 'get_ogrenciler.php',
                type: 'GET',
                data: { oda_kodu: odaKodu, tekil_yurt_kodu: yurtKodu },
                success: function(response) {
                    $('#ogrenci_table_body').html(response);
                    $('#selected_yurt_oda').html(`Seçilen Yurt: ${$('#yurt_select option:selected').text()} - Oda: ${$('#oda_select option:selected').text()}`);
                    $('#ogrenci_table').show();
                },
                error: function() {
                    alert("Öğrenciler yüklenirken bir hata oluştu.");
                }
            });
        } else {
            $('#ogrenci_table_body').empty();
            $('#selected_yurt_oda').html(`Seçilen Yurt: ${$('#yurt_select option:selected').text()}`);
            $('#ogrenci_table').hide();
        }
    });
});
</script>


</body>
</html>

<?php $conn->close(); ?>

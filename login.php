<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rekoryurdu";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// $error değişkeni başlangıçta tanımlandı
$error = "";

// Eğer kullanıcı giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    $sql = "SELECT * FROM yoneticiler WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (hash("sha256", $input_password) === $user['sifre']) {
            // Oturuma sadece kullanıcı ID'sini ekle
            $_SESSION['admin_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Hatalı şifre.";
        }
    } else {
        $error = "Kullanıcı adı bulunamadı.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Red Hat Display', sans-serif;
            background-color: #f7f8fa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .login-container h3 {
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
        }

        .login-container .form-label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .login-container .form-control {
            font-size: 14px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .login-container .btn-primary {
            background-color: #3b7aff;
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border-radius: 6px;
            border: none;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .login-container .btn-primary:hover {
            background-color: #2f64cc;
        }

        .login-container .alert {
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1 class="title">Rekor Yurtlar Müdürlüğü</h1>
    <div class="login-container">
        <h3>Admin Giriş</h3>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div>
                <p>Test amaçlı kullanıcı adı: ugurbaskin, şifre: 123 yazarak admin olarak giriş yapabilirsiniz.</p>
            </div>
            <button type="submit" class="btn btn-primary">Giriş Yap</button>
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

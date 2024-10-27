<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'pelaporan_masyarakat');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fungsi untuk SweetAlert (Notifikasi)
function sweetAlert($title, $message, $icon) {
    return "<script>
        Swal.fire({
            title: '$title',
            text: '$message',
            icon: '$icon',
            confirmButtonText: 'OK'
        });
    </script>";
}

// Proses Registrasi
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if (mysqli_query($conn, $sql)) {
        echo sweetAlert('Berhasil', 'Registrasi berhasil!', 'success');
    } else {
        echo sweetAlert('Gagal', 'Registrasi gagal.', 'error');
    }
}

// Proses Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            echo sweetAlert('Gagal', 'Password salah!', 'error');
        }
    } else {
        echo sweetAlert('Gagal', 'Username tidak ditemukan!', 'error');
    }
}

// Fungsi Pelaporan untuk User
if (isset($_POST['report'])) {
    $report_type = $_POST['report_type'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Upload foto
    $photo = $_FILES['photo']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

    $sql = "INSERT INTO reports (user_id, report_type, description, photo, status) VALUES ('$user_id', '$report_type', '$description', '$photo', 'pending')";
    if (mysqli_query($conn, $sql)) {
        echo sweetAlert('Berhasil', 'Laporan berhasil dikirim!', 'success');
    } else {
        echo sweetAlert('Gagal', 'Laporan gagal dikirim.', 'error');
    }
}

// Proses Hapus Laporan
if (isset($_POST['delete'])) {
    $report_id = $_POST['report_id'];
    $sql = "DELETE FROM reports WHERE id='$report_id'";
    if (mysqli_query($conn, $sql)) {
        echo sweetAlert('Berhasil', 'Laporan berhasil dihapus!', 'success');
    } else {
        echo sweetAlert('Gagal', 'Gagal menghapus laporan.', 'error');
    }
}

// Fungsi Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Menampilkan semua laporan untuk admin
$reports = [];
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $report_sql = "SELECT reports.*, users.username FROM reports JOIN users ON reports.user_id = users.id";
    $reports_result = mysqli_query($conn, $report_sql);
    while ($report_row = mysqli_fetch_assoc($reports_result)) {
        $reports[] = $report_row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Masyarakat</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        form { margin: 20px 0; }
        input, textarea, button, select { display: block; width: 100%; margin: 10px 0; padding: 10px; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
        .report { margin: 20px 0; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; }
        .user-list { margin: 20px 0; padding: 10px; background-color: #e9ecef; border-radius: 8px; }
        @media only screen and (max-width: 600px) { .container { padding: 10px; } }
    </style>
</head>
<body>

<div class="container">
    <h1>Pelaporan Masyarakat</h1>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Halaman Login -->
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <!-- Halaman Registrasi -->
        <h2>Registrasi</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="register">Registrasi</button>
        </form>
    <?php else: ?>
        <p>Selamat datang, <?= $_SESSION['username']; ?>! <a href="?logout">Logout</a></p>

        <?php if ($_SESSION['role'] == 'user'): ?>
            <h2>Kirim Laporan</h2>
            <form method="POST" enctype="multipart/form-data">
                <select name="report_type" required>
                    <option value="Sampah Berserakan">Sampah Berserakan</option>
                    <option value="Jalan Rusak">Jalan Rusak</option>
                    <option value="Kebakaran">Kebakaran</option>
                    <option value="Maling">Maling</option>
                    <option value="lain2">lain2</option>
                </select>
                <textarea name="description" rows="4" placeholder="Deskripsi" required></textarea>
                <input type="file" name="photo" required>
                <button type="submit" name="report">Kirim Laporan</button>
            </form>

        <?php elseif ($_SESSION['role'] == 'admin'): ?>
            <h2>Daftar Laporan</h2>
            <div class="user-list">
                <?php foreach ($reports as $report): ?>
                    <div class="report">
                        <p><strong>Jenis Laporan:</strong> <?= $report['report_type']; ?></p>
                        <p><strong>Deskripsi:</strong> <?= $report['description']; ?></p>
                        <p><strong>Status:</strong> <?= $report['status']; ?></p>
                        <p><strong>Dikirim oleh:</strong> <?= $report['username']; ?></p>
                        <p><strong>Foto:</strong> 
                            <img src="uploads/<?= $report['photo']; ?>" width="100" 
                                onclick="Swal.fire({
                                    title: 'Foto Laporan',
                                    imageUrl: 'uploads/<?= $report['photo']; ?>',
                                    imageWidth: 'auto',
                                    imageHeight: 'auto',
                                    imageAlt: 'Custom image'
                                });"
                            >
                        </p>
                        <button onclick="location.href='edit_report.php?report_id=<?= $report['id']; ?>'">Edit</button>
                        <form method="POST" action="">
                            <input type="hidden" name="report_id" value="<?= $report['id']; ?>">
                            <button type="submit" name="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini?');">Hapus</button>
                            </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

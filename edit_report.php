<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'pelaporan_masyarakat');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Cek apakah admin sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Mengambil data laporan berdasarkan ID
if (isset($_GET['report_id'])) {
    $report_id = $_GET['report_id'];
    $sql = "SELECT * FROM reports WHERE id='$report_id'";
    $result = mysqli_query($conn, $sql);
    $report = mysqli_fetch_assoc($result);
}

// Proses Edit Laporan
if (isset($_POST['edit'])) {
    $report_type = $_POST['report_type'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $sql = "UPDATE reports SET report_type='$report_type', description='$description', status='$status' WHERE id='$report_id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Laporan berhasil diubah!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah laporan.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea, button, select { display: block; width: 100%; margin: 10px 0; padding: 10px; }
        button { background-color: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Laporan</h1>
    <form method="POST">
        <select name="report_type" required>
            <option value="Sampah Berserakan" <?= $report['report_type'] == 'Sampah Berserakan' ? 'selected' : ''; ?>>Sampah Berserakan</option>
            <option value="Jalan Rusak" <?= $report['report_type'] == 'Jalan Rusak' ? 'selected' : ''; ?>>Jalan Rusak</option>
            <option value="Kebakaran" <?= $report['report_type'] == 'Kebakaran' ? 'selected' : ''; ?>>Kebakaran</option>
            <option value="Maling" <?= $report['report_type'] == 'Maling' ? 'selected' : ''; ?>>Maling</option>
            <option value="lain2" <?= $report['report_type'] == 'Maling' ? 'selected' : ''; ?>>lain2</option>
        </select>
        <textarea name="description" rows="4" placeholder="Deskripsi" required><?= $report['description']; ?></textarea>
        <select name="status" required>
            <option value="pending" <?= $report['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="selesai" <?= $report['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
        </select>
        <button type="submit" name="edit">Simpan Perubahan</button>
    </form>
</div>

</body>
</html>

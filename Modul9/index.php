<?php
$server = "localhost";
$username = "root"; // Sesuaikan dengan username database Anda
$password = ""; // Sesuaikan dengan password database Anda
$conn = new mysqli($server, $username, $password);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil daftar database
$databases = [];
$result = $conn->query("SHOW DATABASES");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $databases[] = $row['Database'];
    }
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['create_database'])) {
        // Logika untuk membuat database baru
        $newDatabase = $_POST['new_database_name'];
        $sqlCreateDb = "CREATE DATABASE IF NOT EXISTS $newDatabase";
        if ($conn->query($sqlCreateDb) === TRUE) {
            $message = "Database <strong>$newDatabase</strong> berhasil dibuat.";
            $databases[] = $newDatabase; // Tambahkan database ke daftar
        } else {
            $message = "Gagal membuat database: " . $conn->error;
        }
    } elseif (isset($_POST['create_table'])) {
        // Ambil input dari form pembuatan tabel
        $database = $_POST['database_name'];
        $tableName = $_POST['table_name'];
        $columnsArray = $_POST['columns']; // Array kolom dari input pengguna

        // Validasi input kolom
        $columns = [];
        foreach ($columnsArray as $column) {
            $columnParts = explode(" ", $column, 2); // Pisahkan nama kolom dan tipe data
            if (count($columnParts) === 2) {
                $columnName = trim($columnParts[0]);
                $columnType = trim($columnParts[1]);
                if (!empty($columnName) && !empty($columnType)) {
                    $columns[] = "$columnName $columnType";
                }
            }
        }

        if (empty($columns)) {
            die("Kolom tidak valid. Pastikan setiap kolom berformat 'nama tipe'.");
        }

        $columnsSql = implode(", ", $columns); // Gabungkan kolom untuk SQL

        // Pilih database
        $conn->select_db($database);

        // Buat tabel
        $sqlCreateTable = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY, $columnsSql)";
        if ($conn->query($sqlCreateTable) === TRUE) {
            $message = "Tabel <strong>$tableName</strong> berhasil dibuat di database <strong>$database</strong>.";
        } else {
            $message = "Gagal membuat tabel: " . $conn->error;
        }
    } elseif (isset($_POST['insert_data'])) {
        // Tambahkan data ke tabel
        $database = $_POST['database_name'];
        $tableName = $_POST['table_name'];
        $dataColumns = $_POST['data_columns'];
        $dataValues = $_POST['data_values'];

        $conn->select_db($database);

        $columnsSql = implode(", ", array_map('trim', $dataColumns));
        $valuesSql = implode(", ", array_map(function ($value) {
            return "'" . trim($value) . "'";
        }, $dataValues));

        $sqlInsert = "INSERT INTO $tableName ($columnsSql) VALUES ($valuesSql)";
        if ($conn->query($sqlInsert) === TRUE) {
            $message = "Data berhasil ditambahkan ke tabel <strong>$tableName</strong>.";
        } else {
            $message = "Gagal menambahkan data: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Pembuatan Database</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="alert">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <!-- Form untuk membuat database baru -->
    <div class="card mb-4">
        <div class="card-body">
            <h3>Buat Database Baru</h3>
            <form method="POST" id="create-db-form">
                <div class="mb-3">
                    <label for="new_database_name" class="form-label">Nama Database</label>
                    <input type="text" class="form-control" id="new_database_name" name="new_database_name" placeholder="Masukkan nama database baru" required>
                </div>
                <button type="submit" name="create_database" class="btn btn-primary w-100">Buat Database</button>
            </form>
        </div>
    </div>

    <!-- Form untuk membuat tabel -->
    <div class="card mb-4">
        <div class="card-body">
            <h3>Buat Tabel</h3>
            <form method="POST" id="create-table-form">
                <div class="mb-3">
                    <label for="database_name" class="form-label">Pilih Database</label>
                    <select id="database_name" name="database_name" class="form-select" required>
                        <?php foreach ($databases as $db): ?>
                            <option value="<?= $db; ?>"><?= $db; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="table_name" class="form-label">Nama Tabel</label>
                    <input type="text" id="table_name" name="table_name" class="form-control" placeholder="Masukkan nama tabel" required>
                </div>
                <div id="columns-container">
                    <label class="form-label">Kolom Tabel</label>
                    <div class="mb-3">
                        <input type="text" name="columns[]" class="form-control mb-2" placeholder="contoh: nama VARCHAR(255)" required>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mb-3" id="add-column">Tambah Kolom</button>
                <button type="submit" name="create_table" class="btn btn-primary w-100">Buat Tabel</button>
            </form>
        </div>
    </div>

    <!-- Form untuk menambahkan data -->
    <div class="card">
        <div class="card-body">
            <h3>Tambah Data ke Tabel</h3>
            <form method="POST" id="insert-data-form">
                <div class="mb-3">
                    <label for="database_name_data" class="form-label">Pilih Database</label>
                    <select id="database_name_data" name="database_name" class="form-select" required>
                        <?php foreach ($databases as $db): ?>
                            <option value="<?= $db; ?>"><?= $db; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="table_name_data" class="form-label">Nama Tabel</label>
                    <input type="text" id="table_name_data" name="table_name" class="form-control" placeholder="Masukkan nama tabel" required>
                </div>
                <div id="data-container">
                    <label class="form-label">Kolom dan Nilai</label>
                    <div class="mb-3 d-flex">
                        <input type="text" name="data_columns[]" class="form-control mb-2 me-2" placeholder="Kolom (contoh: nama)" required>
                        <input type="text" name="data_values[]" class="form-control mb-2" placeholder="Nilai (contoh: John Doe)" required>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mb-3" id="add-data">Tambah Data</button>
                <button type="submit" name="insert_data" class="btn btn-primary w-100">Tambah Data</button>
            </form>
            <div class="card-body">
                <a href="view.php" class="btn btn-success w-30">Lihat Data</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Tambahkan kolom baru untuk membuat tabel
    document.getElementById('add-column').addEventListener('click', function () {
        const container = document.getElementById('columns-container');
        const newField = document.createElement('div');
        newField.classList.add('mb-3', 'd-flex', 'align-items-center');
        newField.innerHTML = `
            <input type="text" name="columns[]" class="form-control mb-2 me-2" placeholder="contoh: nama VARCHAR(255)" required>
            <button type="button" class="btn btn-danger remove-column">Hapus</button>
        `;
        container.appendChild(newField);

        // Fungsi untuk menghapus kolom
        newField.querySelector('.remove-column').addEventListener('click', function () {
            container.removeChild(newField);
        });
    });

    // Tambahkan data baru untuk tabel
    document.getElementById('add-data').addEventListener('click', function () {
        const container = document.getElementById('data-container');
        const newDataRow = document.createElement('div');
        newDataRow.classList.add('mb-3', 'd-flex', 'align-items-center');
        newDataRow.innerHTML = `
            <input type="text" name="data_columns[]" class="form-control mb-2 me-2" placeholder="Kolom (contoh: nama)" required>
            <input type="text" name="data_values[]" class="form-control mb-2 me-2" placeholder="Nilai (contoh: John Doe)" required>
            <button type="button" class="btn btn-danger remove-data">Hapus</button>
        `;
        container.appendChild(newDataRow);

        // Fungsi untuk menghapus baris data
        newDataRow.querySelector('.remove-data').addEventListener('click', function () {
            container.removeChild(newDataRow);
        });
    });

    // Validasi input nama tabel
    document.getElementById('table_name').addEventListener('input', function (event) {
        const value = event.target.value;
        if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(value)) {
            event.target.setCustomValidity(
                "Nama tabel hanya boleh mengandung huruf, angka, dan underscore, dan tidak boleh diawali dengan angka."
            );
        } else {
            event.target.setCustomValidity("");
        }
    });

    // Pratinjau data sebelum dikirim
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (event) {
            const inputs = Array.from(form.querySelectorAll('input, select'));
            const previewData = inputs.map(input => 
                `${input.name}: ${input.value}`).join('\n');
            if (!confirm(`Apakah Anda yakin ingin mengirim data berikut?\n\n${previewData}`)) {
                event.preventDefault();
            }
        });
    });
</script>

</body>
</html>
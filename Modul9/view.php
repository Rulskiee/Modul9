<?php
// Koneksi ke database
$servername = "localhost";
$username = "root"; // sesuaikan dengan username database Anda
$password = ""; // sesuaikan dengan password database Anda
$dbname = "pbw_kirul"; // ganti dengan nama database yang digunakan

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Membuat tabel jika belum ada
$table_create_query = "
CREATE TABLE IF NOT EXISTS kelas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    Nama_Siswa VARCHAR(255) NOT NULL,
    Nama_Guru VARCHAR(255) NOT NULL,
    Nama_Wali VARCHAR(255) NOT NULL,
    Nilai INT(15) NOT NULL,
    Umur INT(15) NOT NULL
)";

if ($conn->query($table_create_query) === TRUE) {
    // Tabel sudah ada atau telah berhasil dibuat
} else {
    echo "Error creating table: " . $conn->error;
}

// Proses penghapusan data
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $delete_sql = "DELETE FROM kelas WHERE id = $id_to_delete"; // ganti dengan nama kolom id
    $conn->query($delete_sql);
    header("Location: view.php"); // setelah penghapusan, refresh halaman
    exit;
}

// Proses update data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama_siswa = $_POST['Nama_Siswa'];
    $nama_guru = $_POST['Nama_Guru'];
    $nama_wali = $_POST['Nama_Wali'];
    $nilai = $_POST['Nilai'];
    $umur = $_POST['Umur'];
    
    $update_sql = "UPDATE kelas SET 
                   Nama_Siswa = '$nama_siswa',
                   Nama_Guru = '$nama_guru',
                   Nama_Wali = '$nama_wali',
                   Nilai = $nilai,
                   Umur = $umur
                   WHERE id = $id";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "Data berhasil diperbarui!";
    } else {
        echo "Error: " . $update_sql . "<br>" . $conn->error;
    }
}

// Menambahkan data ke dalam database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update'])) {
    $nama_siswa = $_POST['Nama_Siswa'];
    $nama_guru = $_POST['Nama_Guru'];
    $nama_wali = $_POST['Nama_Wali'];
    $nilai = $_POST['Nilai'];
    $umur = $_POST['Umur'];

    $sql = "INSERT INTO kelas (Nama_Siswa, Nama_Guru, Nama_Wali, Nilai, Umur)
            VALUES ('$nama_siswa', '$nama_guru', '$nama_wali', $nilai, $umur)";

    if ($conn->query($sql) === TRUE) {
        echo "Data berhasil ditambahkan!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Manajemen Data</h1>

        <!-- Form untuk memasukkan data -->
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="Nama_Siswa" class="form-label">Nama Siswa</label>
                <input type="text" name="Nama_Siswa" id="Nama_Siswa" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="Nama_Guru" class="form-label">Nama Guru</label>
                <input type="text" name="Nama_Guru" id="Nama_Guru" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="Nama_Wali" class="form-label">Nama Wali</label>
                <input type="text" name="Nama_Wali" id="Nama_Wali" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="Nilai" class="form-label">Nilai</label>
                <input type="number" name="Nilai" id="Nilai" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="Umur" class="form-label">Umur</label>
                <input type="number" name="Umur" id="Umur" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Simpan Data</button>
        </form>

        <!-- Tabel untuk menampilkan data -->
        <div class="card mb-4">
            <div class="card-body">
                <h3>Daftar Data</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Nama Guru</th>
                            <th>Nama Wali</th>
                            <th>Nilai</th>
                            <th>Umur</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Mengambil data dari tabel
                        $sql = "SELECT * FROM kelas";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['Nama_Siswa'] . "</td>";
                                echo "<td>" . $row['Nama_Guru'] . "</td>";
                                echo "<td>" . $row['Nama_Wali'] . "</td>";
                                echo "<td>" . $row['Nilai'] . "</td>";
                                echo "<td>" . $row['Umur'] . "</td>";
                                echo "<td>
                                        <button class='btn btn-warning btn-sm edit-btn' data-id='" . $row['id'] . "'
                                        data-nama-siswa='" . $row['Nama_Siswa'] . "'
                                        data-nama-guru='" . $row['Nama_Guru'] . "'
                                        data-nama-wali='" . $row['Nama_Wali'] . "'
                                        data-nilai='" . $row['Nilai'] . "'
                                        data-umur='" . $row['Umur'] . "'>Edit</button>
                                        <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>Hapus</button>
                                     </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Tidak ada data ditemukan</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal untuk konfirmasi hapus -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Penghapusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Edit Data -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editNamaSiswa" class="form-label">Nama Siswa</label>
                            <input type="text" name="Nama_Siswa" id="editNamaSiswa" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNamaGuru" class="form-label">Nama Guru</label>
                            <input type="text" name="Nama_Guru" id="editNamaGuru" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNamaWali" class="form-label">Nama Wali</label>
                            <input type="text" name="Nama_Wali" id="editNamaWali" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNilai" class="form-label">Nilai</label>
                            <input type="number" name="Nilai" id="editNilai" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUmur" class="form-label">Umur</label>
                            <input type="number" name="Umur" id="editUmur" class="form-control" required>
                        </div>
                        <button type="submit" name="update" class="btn btn-primary w-100">Perbarui Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mengonfirmasi penghapusan
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const deleteLink = document.getElementById('deleteLink');
                deleteLink.href = '?delete=' + id;
                const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                confirmDeleteModal.show();
            });
        });

        // Fungsi untuk mengisi modal edit
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const namaSiswa = this.dataset.namaSiswa;
                const namaGuru = this.dataset.namaGuru;
                const namaWali = this.dataset.namaWali;
                const nilai = this.dataset.nilai;
                const umur = this.dataset.umur;

                document.getElementById('editId').value = id;
                document.getElementById('editNamaSiswa').value = namaSiswa;
                document.getElementById('editNamaGuru').value = namaGuru;
                document.getElementById('editNamaWali').value = namaWali;
                document.getElementById('editNilai').value = nilai;
                document.getElementById('editUmur').value = umur;

                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            });
        });
    </script>

</body>
</html>
<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Hapus media
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Ambil informasi file
        $stmt = $pdo->prepare("SELECT file_path FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $media = $stmt->fetch();
        
        if ($media) {
            // Hapus file fisik
            if (file_exists($media['file_path'])) {
                unlink($media['file_path']);
            }
            
            // Hapus dari database
            $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: media.php?message=deleted");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Gagal menghapus media: " . $e->getMessage();
    }
}

// Upload media
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $file = $_FILES['media'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Validasi file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $error = "Tipe file tidak didukung!";
    } elseif ($file['size'] > $max_size) {
        $error = "Ukuran file terlalu besar! Maksimal 5MB.";
    } else {
        // Buat direktori uploads jika belum ada
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate nama file unik
        $file_name = uniqid() . '_' . basename($file['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type, file_size, title, description, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $file['name'],
                    $file_path,
                    $file['type'],
                    $file['size'],
                    $title,
                    $description,
                    $_SESSION['user_id']
                ]);
                
                header("Location: media.php?message=uploaded");
                exit();
            } catch(PDOException $e) {
                $error = "Gagal menyimpan data media: " . $e->getMessage();
                // Hapus file jika gagal menyimpan ke database
                unlink($file_path);
            }
        } else {
            $error = "Gagal mengupload file!";
        }
    }
}

// Ambil semua media
try {
    $stmt = $pdo->query("
        SELECT m.*, u.username as uploaded_by_name 
        FROM media m 
        LEFT JOIN users u ON m.uploaded_by = u.id 
        ORDER BY m.created_at DESC
    ");
    $media_files = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Gagal mengambil data media: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin CMS | Media</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light">Admin CMS</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages.php" class="nav-link">
                            <i class="nav-icon fas fa-file"></i>
                            <p>Halaman</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="posts.php" class="nav-link">
                            <i class="nav-icon fas fa-newspaper"></i>
                            <p>Artikel</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="categories.php" class="nav-link">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Kategori</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="media.php" class="nav-link active">
                            <i class="nav-icon fas fa-images"></i>
                            <p>Media</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Pengguna</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Media</h1>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#uploadModal">
                            <i class="fas fa-upload"></i> Upload Media
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php
                        switch($_GET['message']) {
                            case 'deleted':
                                echo "Media berhasil dihapus!";
                                break;
                            case 'uploaded':
                                echo "Media berhasil diupload!";
                                break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($media_files as $media): ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card">
                                        <?php if (strpos($media['file_type'], 'image/') === 0): ?>
                                            <img src="<?php echo htmlspecialchars($media['file_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($media['title']); ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light text-center py-5">
                                                <i class="fas fa-file fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($media['title'] ?: $media['file_name']); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <?php echo number_format($media['file_size'] / 1024, 2); ?> KB<br>
                                                    Diupload oleh: <?php echo htmlspecialchars($media['uploaded_by_name']); ?><br>
                                                    <?php echo date('d M Y H:i', strtotime($media['created_at'])); ?>
                                                </small>
                                            </p>
                                            <div class="btn-group">
                                                <a href="<?php echo htmlspecialchars($media['file_path']); ?>" class="btn btn-sm btn-info" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="media.php?delete=<?php echo $media['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus media ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Media</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="media">File</label>
                            <input type="file" class="form-control-file" id="media" name="media" required>
                            <small class="form-text text-muted">Maksimal ukuran file: 5MB. Format yang didukung: JPG, PNG, GIF, PDF</small>
                        </div>
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" class="form-control" id="title" name="title">
                        </div>
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>Copyright &copy; 2024 <a href="#">Admin CMS</a>.</strong>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 
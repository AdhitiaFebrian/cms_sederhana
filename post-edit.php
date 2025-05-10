<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$post = [
    'id' => '',
    'title' => '',
    'content' => '',
    'status' => 'draft'
];

// Jika ada ID, ambil data artikel
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if (!$post) {
            header("Location: posts.php");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Gagal mengambil data artikel: " . $e->getMessage();
    }
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $status = $_POST['status'];
    
    try {
        if (isset($_POST['id'])) {
            // Update artikel
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, status = ? WHERE id = ?");
            $stmt->execute([$title, $content, $status, $_POST['id']]);
        } else {
            // Tambah artikel baru
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, status) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $status]);
        }
        header("Location: posts.php?message=saved");
        exit();
    } catch(PDOException $e) {
        $error = "Gagal menyimpan artikel: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin CMS | <?php echo $post['id'] ? 'Edit' : 'Tambah'; ?> Artikel</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
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
                        <a href="posts.php" class="nav-link active">
                            <i class="nav-icon fas fa-newspaper"></i>
                            <p>Artikel</p>
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
                        <h1 class="m-0"><?php echo $post['id'] ? 'Edit' : 'Tambah'; ?> Artikel</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($post['id']): ?>
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Judul</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Konten</label>
                                <textarea class="form-control" id="content" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="posts.php" class="btn btn-default">Batal</a>
                        </form>
                    </div>
                </div>
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
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    $('#content').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        lang: 'id-ID'
    });
});
</script>
</body>
</html> 
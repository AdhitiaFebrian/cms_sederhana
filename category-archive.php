<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Ambil slug kategori dari URL
$category_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Ambil data kategori
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$category_slug]);
    $category = $stmt->fetch();
    
    if (!$category) {
        header("Location: categories.php");
        exit();
    }
    
    // Ambil sub-kategori
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name ASC");
    $stmt->execute([$category['id']]);
    $sub_categories = $stmt->fetchAll();
    
    // Ambil artikel dari kategori ini dan sub-kategorinya
    $category_ids = array_merge([$category['id']], array_column($sub_categories, 'id'));
    $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id IN ($placeholders)
        ORDER BY p.created_at DESC
    ");
    $stmt->execute($category_ids);
    $posts = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Gagal mengambil data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin CMS | Arsip Kategori: <?php echo htmlspecialchars($category['name']); ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
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
                        <a href="categories.php" class="nav-link active">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Kategori</p>
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
                        <h1 class="m-0">Arsip Kategori: <?php echo htmlspecialchars($category['name']); ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <a href="categories.php" class="btn btn-default float-right">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kategori
                        </a>
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

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Artikel dalam Kategori</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($posts)): ?>
                                    <p class="text-muted">Belum ada artikel dalam kategori ini.</p>
                                <?php else: ?>
                                    <?php foreach ($posts as $post): ?>
                                        <div class="post-item mb-4">
                                            <h4>
                                                <a href="post-edit.php?id=<?php echo $post['id']; ?>">
                                                    <?php echo htmlspecialchars($post['title']); ?>
                                                </a>
                                            </h4>
                                            <div class="text-muted mb-2">
                                                <small>
                                                    <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?> |
                                                    Kategori: <?php echo htmlspecialchars($post['category_name']); ?> |
                                                    Status: <?php echo $post['status'] === 'published' ? 'Dipublikasi' : 'Draft'; ?>
                                                </small>
                                            </div>
                                            <p><?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Informasi Kategori</h3>
                            </div>
                            <div class="card-body">
                                <dl>
                                    <dt>Nama Kategori</dt>
                                    <dd><?php echo htmlspecialchars($category['name']); ?></dd>
                                    
                                    <dt>Slug</dt>
                                    <dd><?php echo htmlspecialchars($category['slug']); ?></dd>
                                    
                                    <dt>Deskripsi</dt>
                                    <dd><?php echo nl2br(htmlspecialchars($category['description'])); ?></dd>
                                    
                                    <dt>Jumlah Artikel</dt>
                                    <dd><?php echo count($posts); ?></dd>
                                    
                                    <dt>Dibuat</dt>
                                    <dd><?php echo date('d M Y H:i', strtotime($category['created_at'])); ?></dd>
                                    
                                    <dt>Diperbarui</dt>
                                    <dd><?php echo date('d M Y H:i', strtotime($category['updated_at'])); ?></dd>
                                </dl>
                                
                                <?php if (!empty($sub_categories)): ?>
                                    <h5>Sub-Kategori</h5>
                                    <ul class="list-unstyled">
                                        <?php foreach ($sub_categories as $sub): ?>
                                            <li>
                                                <a href="category-archive.php?slug=<?php echo $sub['slug']; ?>">
                                                    <?php echo htmlspecialchars($sub['name']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
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
</body>
</html> 
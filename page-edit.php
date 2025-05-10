<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page = [
    'id' => '',
    'title' => '',
    'slug' => '',
    'content' => '',
    'status' => 'draft',
    'parent_id' => null,
    'menu_order' => 0
];

// Ambil semua halaman untuk pilihan parent
try {
    $stmt = $pdo->query("SELECT id, title FROM pages WHERE id != " . (isset($_GET['id']) ? (int)$_GET['id'] : 0) . " ORDER BY title ASC");
    $parent_pages = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Gagal mengambil data halaman: " . $e->getMessage();
}

// Jika ada ID, ambil data halaman
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch();
        if (!$page) {
            header("Location: pages.php");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Gagal mengambil data halaman: " . $e->getMessage();
    }
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = trim($_POST['content']);
    $status = $_POST['status'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $menu_order = (int)$_POST['menu_order'];
    
    // Validasi
    if (empty($title)) {
        $error = "Judul halaman tidak boleh kosong!";
    } elseif (empty($slug)) {
        $error = "Slug tidak boleh kosong!";
    } else {
        try {
            // Cek slug sudah ada atau belum
            $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, isset($_POST['id']) ? $_POST['id'] : 0]);
            if ($stmt->rowCount() > 0) {
                $error = "Slug sudah digunakan!";
            } else {
                if (isset($_POST['id'])) {
                    // Update halaman
                    $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, status = ?, parent_id = ?, menu_order = ? WHERE id = ?");
                    $stmt->execute([$title, $slug, $content, $status, $parent_id, $menu_order, $_POST['id']]);
                } else {
                    // Tambah halaman baru
                    $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status, parent_id, menu_order) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $slug, $content, $status, $parent_id, $menu_order]);
                }
                header("Location: pages.php?message=saved");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Gagal menyimpan halaman: " . $e->getMessage();
        }
    }
}

// Fungsi untuk membuat slug
function createSlug($str) {
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', ' ', $str);
    $str = preg_replace('/\s/', '-', $str);
    return $str;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin CMS | <?php echo $page['id'] ? 'Edit' : 'Tambah'; ?> Halaman</title>

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
                        <a href="pages.php" class="nav-link active">
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
                        <a href="media.php" class="nav-link">
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
                        <h1 class="m-0"><?php echo $page['id'] ? 'Edit' : 'Tambah'; ?> Halaman</h1>
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

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($page['id']): ?>
                                <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Judul Halaman</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($page['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="parent_id">Halaman Induk</label>
                                <select class="form-control" id="parent_id" name="parent_id">
                                    <option value="">Tidak Ada (Halaman Utama)</option>
                                    <?php foreach ($parent_pages as $parent): ?>
                                        <option value="<?php echo $parent['id']; ?>" <?php echo ($page['parent_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($parent['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pilih halaman induk jika ini adalah sub-halaman</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($page['slug']); ?>" required>
                                <small class="form-text text-muted">Slug akan otomatis dibuat dari judul halaman jika dikosongkan</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Konten</label>
                                <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars($page['content']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft" <?php echo ($page['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($page['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="menu_order">Urutan Menu</label>
                                <input type="number" class="form-control" id="menu_order" name="menu_order" value="<?php echo $page['menu_order']; ?>" min="0">
                                <small class="form-text text-muted">Urutan halaman dalam menu (0 = tidak ditampilkan di menu)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="pages.php" class="btn btn-default">Batal</a>
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
    // Inisialisasi Summernote
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
        ]
    });
    
    // Auto-generate slug from title
    $('#title').on('input', function() {
        var slug = $('#slug');
        if (slug.val() === '') {
            slug.val(this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, ' ')
                .replace(/\s/g, '-'));
        }
    });
});
</script>
</body>
</html> 
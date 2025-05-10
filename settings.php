<?php
session_start();
require_once 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        // Proses upload file jika ada
        if (isset($_FILES['settings'])) {
            foreach ($_FILES['settings']['name'] as $key => $filename) {
                if ($_FILES['settings']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['settings']['name'][$key],
                        'type' => $_FILES['settings']['type'][$key],
                        'tmp_name' => $_FILES['settings']['tmp_name'][$key],
                        'error' => $_FILES['settings']['error'][$key],
                        'size' => $_FILES['settings']['size'][$key]
                    ];
                    
                    // Validasi file
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 2 * 1024 * 1024; // 2MB
                    
                    if (!in_array($file['type'], $allowed_types)) {
                        throw new Exception("Tipe file tidak didukung untuk " . $key);
                    }
                    
                    if ($file['size'] > $max_size) {
                        throw new Exception("Ukuran file terlalu besar untuk " . $key);
                    }
                    
                    // Upload file
                    $upload_dir = 'uploads/settings/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_name = uniqid() . '_' . basename($file['name']);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                        $stmt->execute([$file_path, $key]);
                    }
                }
            }
        }
        
        header("Location: settings.php?message=saved");
        exit();
    } catch(Exception $e) {
        $error = "Gagal menyimpan pengaturan: " . $e->getMessage();
    }
}

// Ambil semua pengaturan
try {
    $stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_group, id");
    $settings = $stmt->fetchAll();
    
    // Kelompokkan pengaturan berdasarkan group
    $grouped_settings = [];
    foreach ($settings as $setting) {
        $grouped_settings[$setting['setting_group']][] = $setting;
    }
} catch(PDOException $e) {
    $error = "Gagal mengambil data pengaturan: " . $e->getMessage();
}

// Label untuk group
$group_labels = [
    'general' => 'Pengaturan Umum',
    'media' => 'Pengaturan Media',
    'appearance' => 'Pengaturan Tampilan',
    'seo' => 'Pengaturan SEO',
    'social' => 'Pengaturan Sosial Media'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin CMS | Pengaturan</title>

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
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link active">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Pengaturan</p>
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
                        <h1 class="m-0">Pengaturan</h1>
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

                <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        Pengaturan berhasil disimpan!
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs">
                                <?php foreach ($grouped_settings as $group => $settings): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo $group === 'general' ? 'active' : ''; ?>" 
                                           data-toggle="tab" 
                                           href="#<?php echo $group; ?>">
                                            <?php echo $group_labels[$group]; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <?php foreach ($grouped_settings as $group => $settings): ?>
                                    <div class="tab-pane fade <?php echo $group === 'general' ? 'show active' : ''; ?>" 
                                         id="<?php echo $group; ?>">
                                        <?php foreach ($settings as $setting): ?>
                                            <div class="form-group">
                                                <label for="<?php echo $setting['setting_key']; ?>">
                                                    <?php echo $setting['setting_label']; ?>
                                                </label>
                                                
                                                <?php if ($setting['setting_type'] === 'textarea'): ?>
                                                    <textarea class="form-control" 
                                                              id="<?php echo $setting['setting_key']; ?>" 
                                                              name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                              rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                                
                                                <?php elseif ($setting['setting_type'] === 'select'): ?>
                                                    <select class="form-control" 
                                                            id="<?php echo $setting['setting_key']; ?>" 
                                                            name="settings[<?php echo $setting['setting_key']; ?>]">
                                                        <?php foreach (explode(',', $setting['setting_options']) as $option): ?>
                                                            <option value="<?php echo $option; ?>" 
                                                                    <?php echo $setting['setting_value'] === $option ? 'selected' : ''; ?>>
                                                                <?php echo ucfirst($option); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                
                                                <?php elseif ($setting['setting_type'] === 'checkbox'): ?>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                               class="custom-control-input" 
                                                               id="<?php echo $setting['setting_key']; ?>" 
                                                               name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                               value="1" 
                                                               <?php echo $setting['setting_value'] ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" 
                                                               for="<?php echo $setting['setting_key']; ?>">
                                                            Aktif
                                                        </label>
                                                    </div>
                                                
                                                <?php elseif ($setting['setting_type'] === 'image'): ?>
                                                    <?php if ($setting['setting_value']): ?>
                                                        <div class="mb-2">
                                                            <img src="<?php echo htmlspecialchars($setting['setting_value']); ?>" 
                                                                 alt="<?php echo $setting['setting_label']; ?>" 
                                                                 class="img-thumbnail" 
                                                                 style="max-height: 100px;">
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" 
                                                           class="form-control-file" 
                                                           id="<?php echo $setting['setting_key']; ?>" 
                                                           name="settings[<?php echo $setting['setting_key']; ?>]">
                                                    <small class="form-text text-muted">
                                                        Format yang didukung: JPG, PNG, GIF. Maksimal 2MB.
                                                    </small>
                                                
                                                <?php else: ?>
                                                    <input type="<?php echo $setting['setting_type']; ?>" 
                                                           class="form-control" 
                                                           id="<?php echo $setting['setting_key']; ?>" 
                                                           name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
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
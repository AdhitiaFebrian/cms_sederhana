<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
</head>
<body>
    <div class="jumbotron">
        <h1 class="display-4"><?= $title ?></h1>
        <p class="lead"><?= $message ?></p>
        <hr class="my-4">
        <p>Gunakan menu di atas untuk mengelola konten website Anda.</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Posts</h5>
                    <p class="card-text">Kelola artikel dan konten website Anda.</p>
                    <a href="<?= View::url('posts') ?>" class="btn btn-primary">Kelola Posts</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <p class="card-text">Kelola kategori untuk mengorganisir konten.</p>
                    <a href="<?= View::url('categories') ?>" class="btn btn-primary">Kelola Categories</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text">Kelola pengguna dan hak akses.</p>
                    <a href="<?= View::url('users') ?>" class="btn btn-primary">Kelola Users</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
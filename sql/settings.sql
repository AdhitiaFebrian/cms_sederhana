-- Buat tabel settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL,
    setting_label VARCHAR(255) NOT NULL,
    setting_type ENUM('text', 'textarea', 'number', 'email', 'url', 'image', 'select', 'checkbox') NOT NULL,
    setting_options TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert pengaturan default
INSERT INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type, setting_options) VALUES
-- Pengaturan Umum
('site_title', 'Admin CMS', 'general', 'Judul Website', 'text', NULL),
('site_description', 'Content Management System Sederhana', 'general', 'Deskripsi Website', 'textarea', NULL),
('site_logo', '', 'general', 'Logo Website', 'image', NULL),
('site_favicon', '', 'general', 'Favicon', 'image', NULL),
('site_email', 'admin@example.com', 'general', 'Email Website', 'email', NULL),
('site_phone', '', 'general', 'Nomor Telepon', 'text', NULL),
('site_address', '', 'general', 'Alamat', 'textarea', NULL),

-- Pengaturan Media
('media_max_size', '5', 'media', 'Ukuran Maksimal File (MB)', 'number', NULL),
('media_allowed_types', 'jpg,jpeg,png,gif,pdf', 'media', 'Tipe File yang Diizinkan', 'text', NULL),
('media_upload_path', 'uploads/', 'media', 'Path Upload', 'text', NULL),

-- Pengaturan Tampilan
('theme_color', 'primary', 'appearance', 'Warna Tema', 'select', 'primary,success,info,warning,danger'),
('show_breadcrumb', '1', 'appearance', 'Tampilkan Breadcrumb', 'checkbox', NULL),
('show_sidebar', '1', 'appearance', 'Tampilkan Sidebar', 'checkbox', NULL),
('footer_text', 'Copyright © 2024 Admin CMS', 'appearance', 'Teks Footer', 'text', NULL),

-- Pengaturan SEO
('meta_keywords', '', 'seo', 'Meta Keywords', 'textarea', NULL),
('meta_description', '', 'seo', 'Meta Description', 'textarea', NULL),
('google_analytics', '', 'seo', 'Google Analytics Code', 'textarea', NULL),
('robots_txt', 'User-agent: *\nAllow: /', 'seo', 'Robots.txt', 'textarea', NULL),

-- Pengaturan Sosial Media
('facebook_url', '', 'social', 'URL Facebook', 'url', NULL),
('twitter_url', '', 'social', 'URL Twitter', 'url', NULL),
('instagram_url', '', 'social', 'URL Instagram', 'url', NULL),
('youtube_url', '', 'social', 'URL YouTube', 'url', NULL); 
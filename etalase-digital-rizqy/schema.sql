CREATE DATABASE IF NOT EXISTS db_etalase_stella;
USE db_etalase_stella;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'siswa') DEFAULT 'siswa'
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_project VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    link_project VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Akun Default (Password: password123)
INSERT INTO users (nama, email, password, role) VALUES 
('Super Admin Stella', 'superadmin@stella.sch.id', '2y10$e0MYzXyjpJS7Pd0RVvHwHe1x1b1G5X5K8d.V0eQO6b3Ff9p5K9e.', 'super_admin'),
('Admin Stella', 'admin@stella.sch.id', '2y10$e0MYzXyjpJS7Pd0RVvHwHe1x1b1G5X5K8d.V0eQO6b3Ff9p5K9e.', 'admin'),
('Siswa Contoh', 'siswa@stella.sch.id', '2y10$e0MYzXyjpJS7Pd0RVvHwHe1x1b1G5X5K8d.V0eQO6b3Ff9p5K9e.', 'siswa');

-- ============================================================
-- Online Classified Ads Portal - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS classifieds_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classifieds_portal;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(120) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Mobile VARCHAR(20) NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    City VARCHAR(100) DEFAULT NULL,
    State VARCHAR(100) DEFAULT NULL,
    ProfilePhoto VARCHAR(255) DEFAULT NULL,
    Role ENUM('user','admin') NOT NULL DEFAULT 'user',
    Status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    ResetToken VARCHAR(100) DEFAULT NULL,
    ResetTokenExpiry DATETIME DEFAULT NULL,
    CreatedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Categories (supports subcategories via ParentCategoryID)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    ParentCategoryID INT DEFAULT NULL,
    DisplayOrder INT NOT NULL DEFAULT 0,
    FOREIGN KEY (ParentCategoryID) REFERENCES categories(CategoryID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Advertisements
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS advertisements (
    AdID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    CategoryID INT NOT NULL,
    Title VARCHAR(150) NOT NULL,
    Description TEXT NOT NULL,
    Price DECIMAL(12,2) NOT NULL DEFAULT 0,
    `Condition` ENUM('New','Used') NOT NULL DEFAULT 'Used',
    City VARCHAR(100) NOT NULL,
    State VARCHAR(100) NOT NULL,
    Status ENUM('Draft','Pending Approval','Active','Rejected','Sold','Expired') NOT NULL DEFAULT 'Pending Approval',
    RejectReason VARCHAR(255) DEFAULT NULL,
    ViewCount INT NOT NULL DEFAULT 0,
    PostedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ExpiryDate DATETIME DEFAULT NULL,
    FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CategoryID) REFERENCES categories(CategoryID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Advertisement Images
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS advertisement_images (
    ImageID INT AUTO_INCREMENT PRIMARY KEY,
    AdID INT NOT NULL,
    ImagePath VARCHAR(255) NOT NULL,
    SequenceNo INT NOT NULL DEFAULT 0,
    FOREIGN KEY (AdID) REFERENCES advertisements(AdID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Favorites
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    FavoriteID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    AdID INT NOT NULL,
    CreatedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (UserID, AdID),
    FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (AdID) REFERENCES advertisements(AdID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Reports
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    ReportID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    AdID INT NOT NULL,
    Reason ENUM('Spam','Fraud','Duplicate','Offensive','Wrong Category') NOT NULL,
    Comments TEXT DEFAULT NULL,
    ReportStatus ENUM('Pending','Reviewed','Dismissed') NOT NULL DEFAULT 'Pending',
    ReportDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (AdID) REFERENCES advertisements(AdID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seed Categories
-- ------------------------------------------------------------
INSERT INTO categories (Name, ParentCategoryID, DisplayOrder) VALUES
('Vehicles', NULL, 1),
('Property', NULL, 2),
('Jobs', NULL, 3),
('Electronics', NULL, 4),
('Furniture', NULL, 5),
('Fashion', NULL, 6),
('Mobiles', NULL, 7),
('Books', NULL, 8),
('Pets', NULL, 9),
('Services', NULL, 10),
('Education', NULL, 11),
('Others', NULL, 12);

-- Sample subcategories
INSERT INTO categories (Name, ParentCategoryID, DisplayOrder) VALUES
('Cars', 1, 1),
('Motorcycles', 1, 2),
('Commercial Vehicles', 1, 3),
('Apartments for Sale', 2, 1),
('Apartments for Rent', 2, 2),
('Land / Plots', 2, 3),
('Full Time', 3, 1),
('Part Time', 3, 2),
('Internship', 3, 3),
('Laptops', 4, 1),
('Cameras', 4, 2),
('Home Appliances', 4, 3),
('Sofas', 5, 1),
('Beds', 5, 2),
('Men', 6, 1),
('Women', 6, 2),
('Smartphones', 7, 1),
('Accessories', 7, 2);

-- ------------------------------------------------------------
-- Default Admin Account (password: Admin@123 -- CHANGE AFTER FIRST LOGIN)
-- Hash generated with PHP password_hash('Admin@123', PASSWORD_DEFAULT)
-- ------------------------------------------------------------
INSERT INTO users (Name, Email, Mobile, PasswordHash, City, State, Role, Status)
VALUES ('Portal Admin', 'admin@classifieds.local', '0000000000',
'$2b$10$O08trU3H/kZL3/nQ5mz62OWRx2HXDgN2FK7AtENU7cLVm0H3O4WHK', 'Hyderabad', 'Telangana', 'admin', 'active');

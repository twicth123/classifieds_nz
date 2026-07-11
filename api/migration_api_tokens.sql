-- ============================================================
-- Migration: API auth tokens (for the mobile app)
-- Run this once against your existing classifieds_portal database.
-- ============================================================

CREATE TABLE IF NOT EXISTS api_tokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Token VARCHAR(64) NOT NULL UNIQUE,
    CreatedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ExpiryDate DATETIME NOT NULL,
    FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

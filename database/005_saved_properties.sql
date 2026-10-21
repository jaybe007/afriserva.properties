-- Migration: Saved Properties / Wishlist Table
USE afriserva_properties;

CREATE TABLE IF NOT EXISTS saved_properties (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(10) UNSIGNED NOT NULL,
    property_id INT(10) UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_property (user_id, property_id),
    CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_prop FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

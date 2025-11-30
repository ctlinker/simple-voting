CREATE DATABASE voting_app;
USE voting_app;

-- Table for Candidates
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    photo VARCHAR(255) NULL -- Optional path to image
);

-- Table for Auth Tokens
CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE, -- The code printed on paper
    is_used TINYINT(1) DEFAULT 0
);

-- Table for Votes (Stores the actual vote)
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT,
    token_id INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (token_id) REFERENCES tokens(id)
);

-- Table for Admin Settings
CREATE TABLE admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    results_visible TINYINT(1) DEFAULT 0 -- 0: Hidden, 1: Visible
);

-- Table for Admin Users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL,
    password VARCHAR(255) NOT NULL
);

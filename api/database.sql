
CREATE DATABASE IF NOT EXISTS school_system;
USE school_system;

CREATE TABLE classes (
id INT AUTO_INCREMENT PRIMARY KEY,
class_name VARCHAR(50)
);

INSERT INTO classes (class_name) VALUES
('Form 1'), ('Form 2'), ('Form 3'), ('Form 4');

CREATE TABLE students (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
admission_no VARCHAR(20) UNIQUE,
parent_contact VARCHAR(20),
class_id INT
);

INSERT INTO students (name, admission_no, parent_contact, class_id) VALUES
('Christopher Njathi','1111','0799380150',1),
('Natasha Waruguru','2222','0799380150',1),
('Kevin Irungu','3333','0799380150',1);

CREATE TABLE teachers (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
unique_code VARCHAR(20) UNIQUE,
contact VARCHAR(20)
);

INSERT INTO teachers (name, unique_code, contact) VALUES
('Rodgers Gichuhi','11111','0707897017'),
('Dennis Maina','22222','0707897017');

CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) UNIQUE,
password VARCHAR(255),
role VARCHAR(20)
);

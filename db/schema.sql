 --create database
 
  create database Water_Tank_Leaked;

 --use database

  use Water_Tank_Leaked;

 --user Table

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role ENUM('admin','technician','viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Location Table

CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(150) NOT NULL,
    address TEXT,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Water Tanks Table

CREATE TABLE water_tanks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tank_name VARCHAR(100) NOT NULL,
    capacity_liters INT,
    location_id INT,
    installation_date DATE,
    status ENUM('active','maintenance','offline') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

--Sensor Table

CREATE TABLE sensors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sensor_code VARCHAR(100) UNIQUE NOT NULL,
    tank_id INT NOT NULL,
    sensor_type ENUM('leak','water_level','pressure') DEFAULT 'leak',
    status ENUM('active','inactive','faulty') DEFAULT 'active',
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tank_id) REFERENCES water_tanks(id)
);
  
--Sensor Readings Table

CREATE TABLE sensor_readings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sensor_id INT NOT NULL,
    reading_value DECIMAL(10,2),
    leak_detected BOOLEAN DEFAULT FALSE,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sensor_id) REFERENCES sensors(id)
);

--Leak Alerts Table

CREATE TABLE sensor_readings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sensor_id INT NOT NULL,
    reading_value DECIMAL(10,2),
    leak_detected BOOLEAN DEFAULT FALSE,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sensor_id) REFERENCES sensors(id)
);

--Maintenance Logs Table

CREATE TABLE maintenance_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tank_id INT,
    sensor_id INT,
    performed_by INT,
    description TEXT,
    maintenance_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tank_id) REFERENCES water_tanks(id),
    FOREIGN KEY (sensor_id) REFERENCES sensors(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);


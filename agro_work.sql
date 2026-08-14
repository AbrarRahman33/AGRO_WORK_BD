-- Agro Work PHP/XAMPP final database
-- REVIEW intentionally omitted.
-- PERSON subtypes ADMIN, FARMER, DRIVER are disjoint.

DROP DATABASE IF EXISTS agro_work_BD;
CREATE DATABASE agro_work_BD CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agro_work_BD;

CREATE TABLE DIVISION(division_id INT AUTO_INCREMENT PRIMARY KEY,division_name VARCHAR(100) NOT NULL UNIQUE) ENGINE=InnoDB;
CREATE TABLE DISTRICT(district_id INT AUTO_INCREMENT PRIMARY KEY,district_name VARCHAR(100) NOT NULL,division_id INT NOT NULL,UNIQUE(division_id,district_name),FOREIGN KEY(division_id) REFERENCES DIVISION(division_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE UPAZILA(upazila_id INT AUTO_INCREMENT PRIMARY KEY,upazila_name VARCHAR(100) NOT NULL,district_id INT NOT NULL,UNIQUE(district_id,upazila_name),FOREIGN KEY(district_id) REFERENCES DISTRICT(district_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE PERSON(person_id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150) NOT NULL,phone VARCHAR(20) NOT NULL,email VARCHAR(100),address TEXT,nid VARCHAR(20) NOT NULL UNIQUE,date_of_birth DATE,CHECK(TRIM(name)<>'')) ENGINE=InnoDB;
CREATE TABLE ADMIN(person_id INT PRIMARY KEY,FOREIGN KEY(person_id) REFERENCES PERSON(person_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE FARMER(person_id INT PRIMARY KEY,FOREIGN KEY(person_id) REFERENCES PERSON(person_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE DRIVER(person_id INT PRIMARY KEY,availability_status VARCHAR(20) NOT NULL DEFAULT 'Available',CHECK(availability_status IN('Available','Busy','Unavailable')),FOREIGN KEY(person_id) REFERENCES PERSON(person_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE AUTH_USER(auth_id INT AUTO_INCREMENT PRIMARY KEY,person_id INT NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,role VARCHAR(20) NOT NULL,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,CHECK(role IN('farmer','driver','admin')),FOREIGN KEY(person_id) REFERENCES PERSON(person_id) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE FARM(farm_id INT AUTO_INCREMENT PRIMARY KEY,farmer_id INT NOT NULL,upazila_id INT NOT NULL,farm_name VARCHAR(150),village_name VARCHAR(100) NOT NULL,total_area DECIMAL(10,2) NOT NULL,CHECK(total_area>0),FOREIGN KEY(farmer_id) REFERENCES FARMER(person_id) ON DELETE RESTRICT ON UPDATE CASCADE,FOREIGN KEY(upazila_id) REFERENCES UPAZILA(upazila_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE JOB_TYPE(job_type_id INT AUTO_INCREMENT PRIMARY KEY,job_type_name VARCHAR(100) NOT NULL UNIQUE,description TEXT) ENGINE=InnoDB;
CREATE TABLE JOB(job_id INT AUTO_INCREMENT PRIMARY KEY,farm_id INT NOT NULL,job_type_id INT NOT NULL,description TEXT,start_date DATE NOT NULL,end_date DATE,required_drivers INT NOT NULL DEFAULT 1,wage_per_day DECIMAL(10,2) NOT NULL,status VARCHAR(20) NOT NULL DEFAULT 'Pending',CHECK(required_drivers>0),CHECK(wage_per_day>0),CHECK(status IN('Pending','In Progress','Completed','Cancelled')),CHECK(end_date IS NULL OR end_date>=start_date),FOREIGN KEY(farm_id) REFERENCES FARM(farm_id) ON DELETE RESTRICT ON UPDATE CASCADE,FOREIGN KEY(job_type_id) REFERENCES JOB_TYPE(job_type_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE JOB_DRIVER(job_driver_id INT AUTO_INCREMENT PRIMARY KEY,job_id INT NOT NULL,driver_id INT NOT NULL,worker_role VARCHAR(100),agreed_wage_per_day DECIMAL(10,2) NOT NULL,assignment_start DATE NOT NULL,assignment_end DATE,assignment_status VARCHAR(20) NOT NULL DEFAULT 'Assigned',UNIQUE(job_id,driver_id),CHECK(agreed_wage_per_day>0),CHECK(assignment_end IS NULL OR assignment_end>=assignment_start),CHECK(assignment_status IN('Assigned','Working','Completed','Cancelled')),FOREIGN KEY(job_id) REFERENCES JOB(job_id) ON DELETE RESTRICT ON UPDATE CASCADE,FOREIGN KEY(driver_id) REFERENCES DRIVER(person_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;
CREATE TABLE PAYMENT(payment_id INT AUTO_INCREMENT PRIMARY KEY,job_driver_id INT NOT NULL,amount DECIMAL(10,2) NOT NULL,payment_date DATE NOT NULL,payment_method VARCHAR(50) NOT NULL,payment_status VARCHAR(20) NOT NULL DEFAULT 'Pending',transaction_reference VARCHAR(100) UNIQUE,CHECK(amount>0),CHECK(payment_method IN('Cash','Bank Transfer','Mobile Banking')),CHECK(payment_status IN('Pending','Paid','Failed')),FOREIGN KEY(job_driver_id) REFERENCES JOB_DRIVER(job_driver_id) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB;

DELIMITER $$
CREATE TRIGGER trg_admin_disjoint BEFORE INSERT ON ADMIN FOR EACH ROW BEGIN IF EXISTS(SELECT 1 FROM FARMER WHERE person_id=NEW.person_id) OR EXISTS(SELECT 1 FROM DRIVER WHERE person_id=NEW.person_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='PERSON subtype overlap is not allowed'; END IF; END$$
CREATE TRIGGER trg_farmer_disjoint BEFORE INSERT ON FARMER FOR EACH ROW BEGIN IF EXISTS(SELECT 1 FROM ADMIN WHERE person_id=NEW.person_id) OR EXISTS(SELECT 1 FROM DRIVER WHERE person_id=NEW.person_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='PERSON subtype overlap is not allowed'; END IF; END$$
CREATE TRIGGER trg_driver_disjoint BEFORE INSERT ON DRIVER FOR EACH ROW BEGIN IF EXISTS(SELECT 1 FROM ADMIN WHERE person_id=NEW.person_id) OR EXISTS(SELECT 1 FROM FARMER WHERE person_id=NEW.person_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='PERSON subtype overlap is not allowed'; END IF; END$$
DELIMITER ;

-- Location seed
INSERT INTO DIVISION(division_id,division_name) VALUES (1,'Dhaka'),(2,'Chattogram'),(3,'Rajshahi'),(4,'Khulna'),(5,'Sylhet'),(6,'Rangpur'),(7,'Mymensingh');
INSERT INTO DISTRICT(district_id,district_name,division_id) VALUES (1,'Dhaka',1),(2,'Gazipur',1),(3,'Chattogram',2),(4,'Cumilla',2),(5,'Rajshahi',3),(6,'Jashore',4),(7,'Moulvibazar',5),(8,'Rangpur',6),(9,'Mymensingh',7);
INSERT INTO UPAZILA(upazila_id,upazila_name,district_id) VALUES (1,'Savar',1),(2,'Dhamrai',1),(3,'Sreepur',2),(4,'Kapasia',2),(5,'Raozan',3),(6,'Cumilla Sadar',4),(7,'Paba',5),(8,'Manirampur',6),(9,'Sreemangal',7),(10,'Rangpur Sadar',8),(11,'Mymensingh Sadar',9);
INSERT INTO JOB_TYPE(job_type_id,job_type_name,description) VALUES (1,'Harvesting','Harvesting crops'),(2,'Plowing','Preparing land'),(3,'Irrigation','Watering agricultural fields'),(4,'Seeding','Planting seeds'),(5,'Fertilizing','Applying fertilizer'),(6,'Crop Transportation','Transporting crops'),(7,'Field Preparation','General field work');

-- Sample disjoint users
INSERT INTO PERSON(person_id,name,phone,email,address,nid,date_of_birth) VALUES
(1,'System Admin','01700000000','admin@agrowork.local','Dhaka','1111111111','2000-01-01'),
(2,'Karim Farmer','01711000001','farmer@agrowork.local','Kapasia, Gazipur','2222222222','1985-04-12'),
(3,'Rahim Worker','01811000001','driver@agrowork.local','Sreepur, Gazipur','3333333333','1995-03-14');
INSERT INTO ADMIN(person_id) VALUES(1);
INSERT INTO FARMER(person_id) VALUES(2);
INSERT INTO DRIVER(person_id,availability_status) VALUES(3,'Available');
INSERT INTO AUTH_USER(person_id,password_hash,role) VALUES
(1,'$2y$12$n7Qns1HYpSUR7R6nDmLym.p/poBN5qwspNOn0VDHwtoxkQm/3jway','admin'),(2,'$2y$12$at0fy7hrfbrwPuY4fg/sLOKU6W5x.EjJePOs5Cos0eJz5NXy1xGAe','farmer'),(3,'$2y$12$C50IwMZpC88.jHPaS4Rsne3Vj5J.MOBgMoS93Rv8k5nDmIrZ44wdm','driver');
INSERT INTO FARM(farm_id,farmer_id,upazila_id,farm_name,village_name,total_area) VALUES(1,2,4,'Karim Agro Farm','Barmi',5.50);
INSERT INTO JOB(job_id,farm_id,job_type_id,description,start_date,end_date,required_drivers,wage_per_day,status) VALUES
(1,1,1,'Rice harvesting for the eastern field','2026-08-15','2026-08-17',3,900.00,'Pending'),
(2,1,3,'Irrigation work before the next cultivation cycle','2026-08-20','2026-08-21',2,750.00,'Pending');

-- Demo login:
-- Admin  NID 1111111111 / admin123
-- Farmer NID 2222222222 / farmer123
-- Driver NID 3333333333 / driver123

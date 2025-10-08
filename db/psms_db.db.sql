CREATE DATABASE sistemagas;

CREATE TABLE customer_list (
    customer_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    status TINYINT NOT NULL DEFAULT 1
);

CREATE TABLE petrol_type_list (
    petrol_type_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1
);

CREATE TABLE user_list (
    user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    type TINYINT NOT NULL DEFAULT 1,
    status TINYINT NOT NULL DEFAULT 1,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transaction_list (
    transaction_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    receipt_no VARCHAR(50) NOT NULL,
    petrol_type_id INT,
    price DECIMAL(10,2) NOT NULL,
    liter DECIMAL(10,3) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    tendered_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    `change` DECIMAL(10,2) NOT NULL DEFAULT 0,
    type TINYINT NOT NULL DEFAULT 1,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (customer_id) REFERENCES customer_list(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (petrol_type_id) REFERENCES petrol_type_list(petrol_type_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES user_list(user_id) ON DELETE SET NULL
);

CREATE TABLE debt_list (
    debt_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transaction_list(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customer_list(customer_id) ON DELETE CASCADE
);

CREATE TABLE payment_list (
    payment_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    payment_code VARCHAR(50) NOT NULL,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer_list(customer_id) ON DELETE CASCADE
);

-- 1) Permitir que la OC NO pida contenedor (container_id NULL en purchase_items)
ALTER TABLE purchase_items
  MODIFY COLUMN container_id INT NULL;

-- Recibo
CREATE TABLE IF NOT EXISTS purchase_receipt (
  receipt_id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
  fecha DATE NOT NULL,
  doc_proveedor VARCHAR(60) NULL,
  notas TEXT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pr_po FOREIGN KEY (purchase_id) REFERENCES purchase_list(purchase_id)
);

-- Ítems del recibo (line_total normalito)
CREATE TABLE IF NOT EXISTS purchase_receipt_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_id INT NOT NULL,
  purchase_item_id INT NOT NULL,
  container_id INT NOT NULL,
  petrol_type_id INT NOT NULL,
  qty_liters DECIMAL(12,3) NOT NULL,
  unit_cost DECIMAL(12,4) NOT NULL,
  line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pri_r  FOREIGN KEY (receipt_id)       REFERENCES purchase_receipt(receipt_id)  ON DELETE CASCADE,
  CONSTRAINT fk_pri_pi FOREIGN KEY (purchase_item_id) REFERENCES purchase_items(item_id)      ON DELETE CASCADE
);


INSERT INTO user_list (user_id, fullname, username, password, type, status, date_created) VALUES
(1, 'Administrator', 'admin', '0192023a7bbd73250516f069df18b500', 1, 1, '2021-11-13 01:52:49'),
(2, 'Samantha Jane Lou', 'sjlou', '1b5b60f157e21268cf576db46c30e998', 0, 1, '2021-11-13 07:55:21'),
(3, 'Javier', 'javier', '3c9c03d6008a5adf42c2a55dd4a1a9f2', 0, 1, '2025-07-29 04:56:43');



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


INSERT INTO customer_list (customer_id, customer_code, fullname, contact, email, address, status) VALUES
(1, '202111-0001', 'Guest', '09113548798', 'guest@sample.com', 'N/A', 1),
(2, '202111-0002', 'John Smith', '09123456789', 'jsmith@sample.com', 'Sample Address', 1),
(3, '202111-0003', 'Sample Company 101', '09885546999', 'info@company101.com', 'Test Address', 1);

INSERT INTO debt_list (debt_id, transaction_id, customer_id, amount, date_added) VALUES
(1, 3, 1, 3678.0, '2021-11-13 05:53:21'),
(2, 4, 3, 5000.0, '2021-11-13 07:53:10'),
(3, 6, 2, 846.0, '2021-11-16 02:08:11'),
(4, 7, 2, 592.2, '2021-11-16 02:11:06');

INSERT INTO payment_list (payment_id, payment_code, customer_id, amount, date_added) VALUES
(1, '202111-0001', 1, 1500.0, '2021-11-13 06:58:08'),
(2, '202111-0002', 1, 178.0, '2021-11-13 07:03:11'),
(3, '202111-0003', 1, 50.0, '2021-11-13 07:04:45'),
(4, '202111-0004', 1, 50.0, '2021-11-13 07:06:41'),
(5, '202111-0005', 1, 150.0, '2021-11-13 07:07:03'),
(6, '202111-0006', 1, 50.0, '2021-11-13 07:08:09'),
(7, '202111-0007', 1, 50.0, '2021-11-13 07:08:46'),
(8, '202111-0008', 1, 123.0, '2021-11-13 07:09:23'),
(9, '202111-0009', 1, 27.0, '2021-11-13 07:18:49'),
(10, '202111-0010', 1, 1500.0, '2021-11-13 07:43:48');

INSERT INTO petrol_type_list (petrol_type_id, name, price, status) VALUES
(1, 'Standard Unleaded 91', 62.0, 1),
(2, 'Premium 95-octane unleaded', 70.0, 1),
(3, 'Premium 98-octane unleaded', 75.0, 1),
(4, 'E10', 77.0, 1),
(5, 'E85', 100.0, 1);

INSERT INTO transaction_list (transaction_id, customer_id, receipt_no, petrol_type_id, price, liter, amount, discount, total, tendered_amount, `change`, type, date_added, user_id) VALUES
(2, 1, '202111-0001', 5, 100.0, 3.5, 350.0, 2.0, 343.0, 500.0, 157.0, 1, '2021-11-13 05:24:07', 1),
(3, 1, '202111-0002', 2, 70.0, 52.5428571428571, 3678.0, 0.0, 3678.0, 0.0, 0.0, 2, '2021-11-13 05:53:21', 1),
(4, 3, '202111-0003', 5, 100.0, 50.0, 5000.0, 0.0, 5000.0, 0.0, 0.0, 2, '2021-11-13 07:53:10', 1),
(5, 1, '202111-0004', 2, 70.0, 2.14285714285714, 150.0, 0.0, 150.0, 150.0, 0.0, 1, '2021-11-13 07:55:47', 2),
(6, 2, '202111-0005', 5, 100.0, 9.0, 900.0, 6.0, 846.0, 0.0, 0.0, 2, '2021-11-16 02:08:11', 1),
(7, 2, '202111-0006', 2, 70.0, 9.0, 630.0, 6.0, 592.2, 0.0, 0.0, 2, '2021-11-16 02:11:06', 1),
(8, 2, '202111-0007', 4, 77.0, 15.0, 1155.0, 1.0, 1143.45, 5000.0, 3856.55, 1, '2021-11-16 02:13:19', 1),
(9, 2, '202111-0008', 5, 100.0, 2.0, 200.0, 0.0, 200.0, 1000.0, 800.0, 1, '2021-11-16 02:14:18', 1),
(10, 2, '202507-0001', 1, 62.0, 1.61290322580645, 100.0, 0.0, 100.0, 100.0, 0.0, 1, '2025-07-29 04:57:35', 3);

INSERT INTO user_list (user_id, fullname, username, password, type, status, date_created) VALUES
(1, 'Administrator', 'admin', '0192023a7bbd73250516f069df18b500', 1, 1, '2021-11-13 01:52:49'),
(2, 'Samantha Jane Lou', 'sjlou', '1b5b60f157e21268cf576db46c30e998', 0, 1, '2021-11-13 07:55:21'),
(3, 'Javier', 'javier', '3c9c03d6008a5adf42c2a55dd4a1a9f2', 0, 1, '2025-07-29 04:56:43');



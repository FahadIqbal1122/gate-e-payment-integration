DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS customers;

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(10, 3) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    gateway_payment_id VARCHAR(255),
    gateway_payment_code VARCHAR(255) UNIQUE NOT NULL,
    payment_url VARCHAR(255),
    status ENUM('pending', 'unpaid', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    gateway_transaction_id VARCHAR(255) NOT NULL UNIQUE,
    reference_number VARCHAR(255),
    status VARCHAR(50) NOT NULL,
    response_code VARCHAR(50),
    response_message VARCHAR(255),
    card_first_digits VARCHAR(6),
    card_last_digits VARCHAR(4),
    rrn VARCHAR(50),
    authorization_code VARCHAR(50),
    amount DECIMAL(10, 3) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    payment_date TIMESTAMP,
    raw_response JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id)
) ENGINE=InnoDB;

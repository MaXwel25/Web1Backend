CREATE TABLE applications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fio VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    bio TEXT NOT NULL,
    agreement BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE TABLE languages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE application_languages (
    app_id INT UNSIGNED NOT NULL,
    lang_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (app_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES languages(id) ON DELETE CASCADE
);

INSERT INTO languages (name) VALUES ('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'), ('Python'), ('Java'), ('Haskel'), ('Clojure'), ('Prolog'), ('Scala'), ('Go');

ALTER TABLE applications ADD COLUMN login VARCHAR(50) UNIQUE;
ALTER TABLE applications ADD COLUMN password_hash VARCHAR(255);

CREATE TABLE admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
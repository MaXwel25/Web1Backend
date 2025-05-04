<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

function setValue($field) {
    if (isset($_SESSION['auth'])) {
        return isset($_SESSION[$field]) ? htmlspecialchars($_SESSION[$field]) : '';
    }
    return isset($_COOKIE[$field]) ? htmlspecialchars($_COOKIE[$field]) : '';
}

function setChecked($field, $value) {
    if (isset($_SESSION['auth'])) {
        return (isset($_SESSION[$field]) && $_SESSION[$field] == $value) ? 'checked' : '';
    }
    return (isset($_COOKIE[$field]) && $_COOKIE[$field] == $value) ? 'checked' : '';
}

function setSelected($field, $value) {
    if (isset($_SESSION['auth'])) {
        return (isset($_SESSION[$field]) && in_array($value, (array)$_SESSION[$field])) ? 'selected' : '';
    }
    return (isset($_COOKIE[$field]) && in_array($value, (array)$_COOKIE[$field])) ? 'selected' : '';
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    
    // Показываем сообщение об успешной регистрации
    if (!empty($_COOKIE['register_success'])) {
        setcookie('register_success', '', 100000);
        $messages[] = 'Регистрация успешна! Ваш логин: '.htmlspecialchars($_COOKIE['generated_login']).', пароль: '.htmlspecialchars($_COOKIE['generated_password']);
        setcookie('generated_login', '', 100000);
        setcookie('generated_password', '', 100000);
    }
    
    // Показываем сообщение о сохранении данных
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Данные сохранены!';
    }
    
    // Показываем сообщения об ошибках
    $errors = array();
    $errors['fio'] = !empty($_COOKIE['fio_error']);
    $errors['phone'] = !empty($_COOKIE['phone_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['birth_date'] = !empty($_COOKIE['birth_date_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['languages'] = !empty($_COOKIE['languages_error']);
    $errors['bio'] = !empty($_COOKIE['bio_error']);
    $errors['agreement'] = !empty($_COOKIE['agreement_error']);
    
    if ($errors['fio']) {
        setcookie('fio_error', '', 100000);
        $messages[] = '<div class="error">Некорректное ФИО. Допустимы только буквы и пробелы.</div>';
    }
    if ($errors['phone']) {
        setcookie('phone_error', '', 100000);
        $messages[] = '<div class="error">Некорректный номер телефона. Допустимый формат: +71234567890 или 71234567890 (11-15 цифр).</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div class="error">Некорректный email. Введите email в правильном формате.</div>';
    }
    if ($errors['birth_date']) {
        setcookie('birth_date_error', '', 100000);
        $messages[] = '<div class="error">Укажите дату рождения.</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', 100000);
        $messages[] = '<div class="error">Некорректный выбор пола.</div>';
    }
    if ($errors['languages']) {
        setcookie('languages_error', '', 100000);
        $messages[] = '<div class="error">Выберите хотя бы один язык программирования.</div>';
    }
    if ($errors['bio']) {
        setcookie('bio_error', '', 100000);
        $messages[] = '<div class="error">Заполните биографию.</div>';
    }
    if ($errors['agreement']) {
        setcookie('agreement_error', '', 100000);
        $messages[] = '<div class="error">Вы должны принять условия.</div>';
    }
    
    // Проверка авторизации
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit();
    }
    
    include('form.php');
    exit();
}

// Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Обработка формы входа
    if (isset($_POST['login_action'])) {
        try {
            $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $stmt = $db->prepare("SELECT * FROM applications WHERE login = ?");
            $stmt->execute([$_POST['login']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($_POST['password'], $user['password_hash'])) {
                $_SESSION['auth'] = true;
                $_SESSION['id'] = $user['id'];
                
                // Заполняем сессию данными пользователя
                $_SESSION['fio'] = $user['fio'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['birth_date'] = $user['birth_date'];
                $_SESSION['gender'] = $user['gender'];
                $_SESSION['bio'] = $user['bio'];
                
                // Получаем языки пользователя
                $stmt = $db->prepare("SELECT lang_id FROM application_languages WHERE app_id = ?");
                $stmt->execute([$user['id']]);
                $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $_SESSION['languages'] = $languages;
                
                header('Location: index.php');
                exit();
            } else {
                setcookie('auth_error', '1', time() + 24 * 60 * 60);
                header('Location: index.php');
                exit();
            }
        } catch (PDOException $e) {
            echo 'Ошибка: ' . $e->getMessage();
            exit();
        }
    }
    
    // Обработка формы регистрации/обновления данных
    $errors = FALSE;
    if (empty($_POST['fio']) || !preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $_POST['fio'])) {
        setcookie('fio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('fio_value', $_POST['fio'], time() + 365 * 24 * 60 * 60);
    }
    
    if (empty($_POST['phone']) || !preg_match('/^\+?[0-9]{11,15}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('phone_value', $_POST['phone'], time() + 365 * 24 * 60 * 60);
    }
    
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('email_value', $_POST['email'], time() + 365 * 24 * 60 * 60);
    }
    
    if (empty($_POST['birth_date'])) {
        setcookie('birth_date_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('birth_date_value', $_POST['birth_date'], time() + 365 * 24 * 60 * 60);
    }
    
    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('gender_value', $_POST['gender'], time() + 365 * 24 * 60 * 60);
    }
    
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('languages_value', serialize($_POST['languages']), time() + 365 * 24 * 60 * 60);
    }
    
    if (empty($_POST['bio'])) {
        setcookie('bio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('bio_value', $_POST['bio'], time() + 365 * 24 * 60 * 60);
    }
    
    if (!isset($_SESSION['auth'])) {
        if (!isset($_POST['agreement'])) {
            setcookie('agreement_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
        } else {
            setcookie('agreement_value', '1', time() + 365 * 24 * 60 * 60);
        }
    }
    
    if ($errors) {
        header('Location: index.php');
        exit();
    }
    
    try {
        $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        if (isset($_SESSION['auth'])) {
            // Обновление существующей записи
            $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birth_date = ?, gender = ?, bio = ? WHERE id = ?");
            $stmt->execute([
                $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], $_SESSION['id']
            ]);
            
            // Удаляем старые языки
            $stmt = $db->prepare("DELETE FROM application_languages WHERE app_id = ?");
            $stmt->execute([$_SESSION['id']]);
            
            // Добавляем новые языки
            $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $lang) {
                $stmt->execute([$_SESSION['id'], (int)$lang]);
            }
            
            setcookie('save', '1');
            header('Location: index.php');
        } else {
            // Создание новой записи
            $login = generateRandomString();
            $password = generateRandomString();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, bio, agreement, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], 1, $login, $password_hash
            ]);
            $app_id = $db->lastInsertId();
            
            $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $lang) {
                $stmt->execute([$app_id, (int)$lang]);
            }
            
            setcookie('register_success', '1');
            setcookie('generated_login', $login, time() + 24 * 60 * 60);
            setcookie('generated_password', $password, time() + 24 * 60 * 60);
            header('Location: index.php');
        }
    } catch (PDOException $e) {
        echo 'Ошибка: ' . $e->getMessage();
        exit();
    }
}
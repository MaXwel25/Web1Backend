<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

function setValue($field) {
    return isset($_COOKIE[$field]) ? htmlspecialchars($_COOKIE[$field]) : '';
}

function setChecked($field, $value) {
    return (isset($_COOKIE[$field]) && $_COOKIE[$field] == $value) ? 'checked' : '';
}

function setSelected($field, $value) {
    return (isset($_COOKIE[$field]) && in_array($value, (array)$_COOKIE[$field])) ? 'selected' : '';
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Данные сохранены!';
    }
    
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
    
    include('form.php');
    exit();
}

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

if (!isset($_POST['agreement'])) {
    setcookie('agreement_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('agreement_value', '1', time() + 365 * 24 * 60 * 60);
}

if ($errors) {
    header('Location: index.php');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], 1
    ]);
    $app_id = $db->lastInsertId();
    
    $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
    foreach ($_POST['languages'] as $lang) {
        $stmt->execute([$app_id, (int)$lang]);
    }
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
    exit();
}

setcookie('save', '1');
header('Location: index.php');
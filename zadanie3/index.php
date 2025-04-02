<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        echo 'Данные сохранены!';
    }
    include('form.php');
    exit();
}

$errors = false;
if (empty($_POST['fio']) || !preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $_POST['fio'])) {
    echo 'Некорректное ФИО.<br>';
    $errors = true;
}
if (empty($_POST['phone']) || !preg_match('/^\+?[0-9]{11,15}$/', $_POST['phone'])) {
    echo 'Некорректный номер телефона.<br>';
    $errors = true;
}
if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo 'Некорректный email.<br>';
    $errors = true;
}
if (empty($_POST['birth_date'])) {
    echo 'Укажите дату рождения.<br>';
    $errors = true;
}
if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
    echo 'Некорректный выбор пола.<br>';
    $errors = true;
}
if (empty($_POST['languages'])) {
    echo 'Выберите хотя бы один язык программирования.<br>';
    $errors = true;
}
if (empty($_POST['bio'])) {
    echo 'Заполните биографию.<br>';
    $errors = true;
}
if (!isset($_POST['agreement'])) {
    echo 'Вы должны принять условия.<br>';
    $errors = true;
}
if ($errors) exit();

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
header('Location: ?save=1');
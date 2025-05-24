<?php
header('Content-Type: text/html; charset=UTF-8');

// HTTP-авторизация
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit;
} else {
    try {
        $db = new PDO('mysql:host=localhost;dbname=u68653', 'u68653', '7251537', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
            header('WWW-Authenticate: Basic realm="Admin Area"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Неверные логин или пароль';
            exit;
        }
    } catch (PDOException $e) {
        die('Ошибка подключения к базе данных: ' . $e->getMessage());
    }
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        // Удаление пользователя
        try {
            $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = "Пользователь успешно удален";
        } catch (PDOException $e) {
            $message = "Ошибка при удалении пользователя: " . $e->getMessage();
        }
    } elseif (isset($_POST['update'])) {
        // Обновление данных пользователя
        try {
            $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birth_date = ?, gender = ?, bio = ? WHERE id = ?");
            $stmt->execute([
                $_POST['fio'], $_POST['phone'], $_POST['email'], $_POST['birth_date'], $_POST['gender'], $_POST['bio'], $_POST['id']
            ]);
            
            // Обновление языков
            $stmt = $db->prepare("DELETE FROM application_languages WHERE app_id = ?");
            $stmt->execute([$_POST['id']]);
            
            $stmt = $db->prepare("INSERT INTO application_languages (app_id, lang_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $lang_id) {
                $stmt->execute([$_POST['id'], $lang_id]);
            }
            
            $message = "Данные пользователя успешно обновлены";
        } catch (PDOException $e) {
            $message = "Ошибка при обновлении данных: " . $e->getMessage();
        }
    }
}

// Получение списка всех пользователей
try {
    $users = $db->query("SELECT * FROM applications ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    // Получение языков для каждого пользователя
    foreach ($users as &$user) {
        $stmt = $db->prepare("SELECT lang_id FROM application_languages WHERE app_id = ?");
        $stmt->execute([$user['id']]);
        $user['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($user);
    
    // Получение статистики по языкам
    $stats = $db->query("
        SELECT l.id, l.name, COUNT(al.lang_id) as count 
        FROM languages l 
        LEFT JOIN application_languages al ON l.id = al.lang_id 
        GROUP BY l.id 
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка при получении данных: ' . $e->getMessage());
}

// Получение списка всех языков
$languages = $db->query("SELECT * FROM languages ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Административная панель</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stats-table {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Административная панель</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <h2>Список пользователей</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Языки</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr id="user-<?php echo $user['id']; ?>">
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['fio']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['birth_date']); ?></td>
                        <td><?php echo $user['gender'] == 'male' ? 'Мужской' : 'Женский'; ?></td>
                        <td>
                            <?php 
                            $user_langs = array();
                            foreach ($languages as $lang) {
                                if (in_array($lang['id'], $user['languages'])) {
                                    $user_langs[] = htmlspecialchars($lang['name']);
                                }
                            }
                            echo implode(', ', $user_langs);
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $user['id']; ?>">Редактировать</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" class="p-0">
                            <div id="edit-form-<?php echo $user['id']; ?>" class="edit-form">
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>ФИО</label>
                                            <input type="text" name="fio" class="form-control" value="<?php echo htmlspecialchars($user['fio']); ?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Телефон</label>
                                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Дата рождения</label>
                                            <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Пол</label>
                                            <select name="gender" class="form-control" required>
                                                <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                                                <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Языки программирования</label>
                                            <select name="languages[]" class="form-control" multiple required>
                                                <?php foreach ($languages as $lang): ?>
                                                    <option value="<?php echo $lang['id']; ?>" <?php echo in_array($lang['id'], $user['languages']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($lang['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Биография</label>
                                        <textarea name="bio" class="form-control" rows="3" required><?php echo htmlspecialchars($user['bio']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update" class="btn btn-primary">Сохранить</button>
                                    <button type="button" class="btn btn-secondary cancel-edit">Отмена</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2 class="stats-table">Статистика по языкам программирования</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Язык</th>
                    <th>Количество пользователей</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats as $stat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stat['name']); ?></td>
                        <td><?php echo $stat['count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Обработка кнопок редактирования
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const form = document.getElementById(`edit-form-${userId}`);
                
                // Скрыть все формы редактирования
                document.querySelectorAll('.edit-form').forEach(f => {
                    f.style.display = 'none';
                });
                
                // Показать нужную форму
                form.style.display = 'block';
                
                // Прокрутить к форме
                form.scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Обработка кнопок отмены
        document.querySelectorAll('.cancel-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.edit-form').style.display = 'none';
            });
        });
    </script>
</body>
</html>
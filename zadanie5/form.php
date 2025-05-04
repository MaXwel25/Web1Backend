<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Задание 5</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css" />
    <style>
        .error {
            color: red;
            font-weight: bold;
        }
        .error-field {
            border: 2px solid red;
        }
        .login-form {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!empty($_SESSION['auth'])): ?>
            <div class="text-right mb-3">
                <a href="?logout=1" class="btn btn-danger">Выйти</a>
            </div>
        <?php else: ?>
            <div class="login-form">
                <h4>Вход</h4>
                <form method="POST">
                    <input type="hidden" name="login_action" value="1">
                    <div class="form-group">
                        <label>Логин:</label>
                        <input type="text" name="login" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <?php if (!empty($_COOKIE['auth_error'])): ?>
                        <div class="alert alert-danger">Неверный логин или пароль</div>
                        <?php setcookie('auth_error', '', time() - 3600); ?>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Войти</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="change_forma block_forma">
            <div class="fname"><h3>HTML форма</h3></div>
            
            <?php if (!empty($messages)): ?>
                <div class="messages">
                    <?php foreach ($messages as $message): ?>
                        <?php echo $message; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="index.php" method="POST">
                <div class="otsyp">
                    <label>ФИО: 
                        <input type="text" name="fio" class="<?php echo !empty($errors['fio']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('fio_value'); ?>" required pattern="[A-Za-zА-Яа-яЁё\s]+" maxlength="150">
                    </label><br>

                    <label>Телефон: 
                        <input type="tel" name="phone" class="<?php echo !empty($errors['phone']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('phone_value'); ?>" required pattern="\+?[0-9]{11,15}">
                    </label><br>

                    <label>Email: 
                        <input type="email" name="email" class="<?php echo !empty($errors['email']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('email_value'); ?>" required>
                    </label><br>

                    <label>Дата рождения: 
                        <input type="date" name="birth_date" class="<?php echo !empty($errors['birth_date']) ? 'error-field' : ''; ?>" 
                               value="<?php echo setValue('birth_date_value'); ?>" required>
                    </label><br>

                    <p>Пол:</p>
                    <input type="radio" id="male" name="gender" value="male" <?php echo setChecked('gender_value', 'male'); ?> required>
                    <label for="male">Мужской</label><br>
                    <input type="radio" id="female" name="gender" value="female" <?php echo setChecked('gender_value', 'female'); ?>>
                    <label for="female">Женский</label><br><br>

                    <label for="language">Любимый язык программирования:</label>
                    <br>
                    <select id="language" name="languages[]" multiple required class="<?php echo !empty($errors['languages']) ? 'error-field' : ''; ?>">
                        <option value="1" <?php echo setSelected('languages_value', '1'); ?>>Pascal</option>
                        <option value="2" <?php echo setSelected('languages_value', '2'); ?>>C</option>
                        <option value="3" <?php echo setSelected('languages_value', '3'); ?>>C++</option>
                        <option value="4" <?php echo setSelected('languages_value', '4'); ?>>JavaScript</option>
                        <option value="5" <?php echo setSelected('languages_value', '5'); ?>>PHP</option>
                        <option value="6" <?php echo setSelected('languages_value', '6'); ?>>Python</option>
                        <option value="7" <?php echo setSelected('languages_value', '7'); ?>>Java</option>
                        <option value="8" <?php echo setSelected('languages_value', '8'); ?>>Haskell</option>
                        <option value="9" <?php echo setSelected('languages_value', '9'); ?>>Clojure</option>
                        <option value="10" <?php echo setSelected('languages_value', '10'); ?>>Prolog</option>
                        <option value="11" <?php echo setSelected('languages_value', '11'); ?>>Scala</option>
                        <option value="12" <?php echo setSelected('languages_value', '12'); ?>>Go</option>
                    </select>
                    <br><br />

                    <label>Биография:</label><br>
                    <textarea name="bio" rows="5" required class="<?php echo !empty($errors['bio']) ? 'error-field' : ''; ?>"><?php echo setValue('bio_value'); ?></textarea><br>
                    
                    <label>
                    <input type="checkbox" name="agreement" 
                        <?php echo (isset($_SESSION['auth']) || setValue('agreement_value')) ? 'checked' : ''; ?>
                        <?php echo empty($_SESSION['auth']) ? 'required' : ''; ?>>
                            С условиями контракта ознакомлен
                    </label><br>

                    <input type="submit" value="<?php echo isset($_SESSION['auth']) ? 'Обновить данные' : 'Сохранить'; ?>" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</body>

</html>
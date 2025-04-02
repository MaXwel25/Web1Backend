<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>Задание 3</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="styles.css" />
</head>

<body>
    <div class="change_forma block_forma">
        <div class="fname"><h3>HTML форма</h3></div>
        <form action="index.php" method="POST">
          <div class="otsyp">
          <label>ФИО: <input type="text" name="fio" required pattern="[A-Za-zА-Яа-яЁё\s]+" maxlength="150"></label><br>

          <label>Телефон: <input type="tel" name="phone" required pattern="\+?[0-9]{11,15}"></label><br>

          <label>Email: <input type="email" name="email" required></label><br>

          <label>Дата рождения: <input type="date" name="birth_date" required></label><br>

            <p>Пол:</p>
            <input type="radio" id="male" name="gender" value="male" required>
            <label for="male">Мужской</label><br>
            <input type="radio" id="female" name="gender" value="female">
            <label for="female">Женский</label><br></br>

            <label for="language">Любимый язык программирования:</label>
            <br>
            <select id="language" name="languages[]" multiple required>
                <option value="1">Pascal</option>
                <option value="2">C</option>
                <option value="3">C++</option>
                <option value="4">JavaScript</option>
                <option value="5">PHP</option>
                <option value="6">Python</option>
                <option value="7">Java</option>
                <option value="8">Haskell</option>
                <option value="9">Clojure</option>
                <option value="10">Prolog</option>
                <option value="11">Scala</option>
                <option value="12">Go</option>
            </select>
            <br><br />

            <label>Биография:</label><br>
        <textarea name="bio" rows="5" required></textarea><br>
        
        <label><input type="checkbox" name="agreement" required> С условиями контракта ознакомлен</label><br>

            <input type="submit" value="Сохранить">
            </div>
        </form>
    </div>
</body>

</html>
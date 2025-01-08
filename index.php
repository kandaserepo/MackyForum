<?php
session_start();
$chatDir = 'chats/'; 
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $_SESSION['username'] = $username;
    } elseif (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if ($password !== $confirm_password) {
            $errors[] = 'Пароли не совпадают.';
        } else {
            $_SESSION['username'] = $username; 
        }
    } elseif (isset($_POST['create_chat'])) {
        $chat_name = trim($_POST['chat_name']);
        if (!empty($chat_name)) {
            $chat_file = $chatDir . $chat_name . '.txt';
            if (!file_exists($chat_file)) {
                file_put_contents($chat_file, ""); 
            }
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$chats = glob($chatDir . '*.txt');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MackyForum</title>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script>
        let chatName = '';
        let messages = []; 

        function updateChat() {
            if (chatName) {
                fetch(`get_messages.php?chat_name=${chatName}`)
                    .then(response => response.json())
                    .then(data => {
                        const chatContainer = document.getElementById('chat-messages');
                        messages = data.slice(-10); 
                        chatContainer.innerHTML = ''; 

                        messages.forEach(message => {
                            const messageDiv = document.createElement('div');
                            messageDiv.classList.add('message');
                            messageDiv.innerHTML = `<p>${message}</p>`;
                            chatContainer.prepend(messageDiv); 
                        });

                        chatContainer.scrollTop = 0; 
                    });
            }
        }

        function setChatName(name) {
            chatName = name;
            updateChat(); 
        }

        function sendMessage(event) {
            event.preventDefault();
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value;

            if (message.trim() && chatName) {
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `chat_name=${encodeURIComponent(chatName)}&message=${encodeURIComponent(message)}`
                })
                .then(response => {
                    if (response.ok) {
                        messages.unshift(`${document.getElementById('username').value}: ${message}`); 
                        updateChat(); 
                        messageInput.value = ''; 
                    }
                });
            }
        }

        setInterval(updateChat, 6000); 
    </script>
</head>
<body>
    <div class="container">
        <h1>MackyForum</h1>
        <h4 href=u95.veliona.no>Orininal</h4>
        <?php if (isset($_SESSION['username'])): ?>
            <p>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <form action="?logout" method="post">
                <button type="submit">Выйти</button>
            </form>

            <h2>Создать чат</h2>
            <form action="index.php" method="post">
                <input type="text" name="chat_name" placeholder="Название чата" required>
                <button type="submit" name="create_chat">Создать чат</button>
            </form>

            <h2>Выберите чат</h2>
            <form action="index.php" method="post" onsubmit="setChatName(this.chat_name.value); return false;">
                <select name="chat_name" onchange="setChatName(this.value)" required>
                    <option value="">-- Выберите чат --</option>
                    <?php foreach ($chats as $chat): ?>
                        <option value="<?php echo htmlspecialchars(pathinfo($chat, PATHINFO_FILENAME)); ?>">
                            <?php echo htmlspecialchars(pathinfo($chat, PATHINFO_FILENAME)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Обновить</button>
            </form>

            <h2>Создать сообщение</h2>
            <form onsubmit="sendMessage(event);">
                <input type="hidden" id="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                <input type="hidden" name="chat_name" value="<?php echo htmlspecialchars($_POST['chat_name'] ?? ''); ?>">
                <textarea id="message-input" required placeholder="Введите ваше сообщение..."></textarea>
                <button type="submit">Отправить</button>
            </form>

            <h2>Сообщения</h2>
            <div id="chat-messages">
                <?php
                if (isset($_POST['chat_name']) && !empty($_POST['chat_name'])) {
                    $chat_file = $chatDir . $_POST['chat_name'] . '.txt';
                    if (file_exists($chat_file)) {
                        $messages = file($chat_file, FILE_IGNORE_NEW_LINES);
                        $lastMessages = array_slice($messages, -10); // Получаем последние 10 сообщений
                        foreach ($lastMessages as $message) {
                            echo '<div class="message">';
                            echo '<p>' . htmlspecialchars($message) . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Нет сообщений в этом чате.</p>';
                    }
                } else {
                    echo '<p>Пожалуйста, выберите чат для просмотра сообщений.</p>';
                }
                ?>
            </div>
        <?php else: ?>
            <h2>Вход</h2>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form action="index.php" method="post">
                <input type="text" name="username" placeholder="Имя пользователя" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" name="login">Войти</button>
            </form>
            <h2>Регистрация</h2>
            <form action="index.php" method="post">
                <input type="text" name="username" placeholder="Имя пользователя" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                <button type="submit" name="register">Зарегистрироваться</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

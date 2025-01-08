<?php
session_start();

if (isset($_POST['chat_name']) && isset($_POST['message'])) {
    $chat_name = trim($_POST['chat_name']);
    $message = trim($_POST['message']);
    
    if (!empty($chat_name) && !empty($message)) {
        $chat_file = 'chats/' . $chat_name . '.txt';
        if (file_exists($chat_file)) {
            $username = $_SESSION['username'];
            $messageData = "$username: $message\n";
            file_put_contents($chat_file, $messageData, FILE_APPEND);
            echo 'success'; // Возвращаем успешный ответ
        }
    }
}
?>

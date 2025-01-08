<?php
session_start();
$chatDir = 'chats/'; // Директория для хранения чатов

if (isset($_GET['chat_name'])) {
    $chat_name = trim($_GET['chat_name']);
    $chat_file = $chatDir . $chat_name . '.txt';

    if (file_exists($chat_file)) {
        $messages = file($chat_file, FILE_IGNORE_NEW_LINES);
        $lastMessages = array_slice($messages, -10); // Получаем последние 10 сообщений
        echo json_encode($lastMessages); // Возвращаем сообщения в формате JSON
    } else {
        echo json_encode([]); // Если файл не найден, возвращаем пустой массив
    }
} else {
    echo json_encode([]); // Если имя чата не передано, возвращаем пустой массив
}
?>

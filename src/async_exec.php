<?php
require_once __DIR__ . "/Bot.php";
require_once __DIR__ . "/PHPTelebot.php";

// Usage: php async_exec.php "COMMAND" "CHAT_ID" "TOKEN" "PRE_MESSAGE"
if ($argc < 5) {
    exit(1);
}

$command = $argv[1];
$chatId = $argv[2];
$token = $argv[3];
$preMessage = $argv[4];

// Setup Bot Token for the static Bot class
PHPTelebot::$token = $token;

// 1. Send the "Started" message
Bot::send('sendMessage', [
    'chat_id' => $chatId,
    'text' => $preMessage,
    'parse_mode' => 'html'
]);

// 2. Execute the actual command
$output = shell_exec($command);

// 3. Send the result
Bot::send('sendMessage', [
    'chat_id' => $chatId,
    'text' => "<b>Execution Result:</b>\n<code>" . $output . "</code>",
    'parse_mode' => 'html'
]);
?>

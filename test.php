<?php

include("AntiFlood.php");

$Config_Database = [
    "server"=>"localhost",
    "username"=>"MyBot",
    "password"=>'123456',
    "database"=>"Bot"
];

$AntiFlood = new AntiFLood($Config_Database);
$AntiFlood->setting([
    'spam_time'=>10, /** The interval when users must observe in sending the message */
    'safe_time'=>0.8, /** Time interval to clear doubts and be known as safe-user */
    'flood_assurance'=>3, /** Repeat spam and be identified as spam! */
    'alert_function'=>function ($data){ /** Introduce the function to send reports */
        Telegram("SendMessage",[
            "chat_id"=>$data['chat_id'],
            "text"=>"Click the buttons more slowly! Thankful :)",
        ]);
    }
]);

$U = JSON_DECODE(file_get_contents("php://input"),1);
$from = isset($U["message"]["from"]["id"])?$U["message"]["from"]["id"]:$U["callback_query"]["from"]["id"];

$AntiFlood->run($from,$U["update_id"]);

/* bot command code */
?>

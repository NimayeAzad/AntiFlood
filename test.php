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


$AntiFlood->GetReportSpam();
/*
View all reports of current user
[
    {
        "id": "1",
        "user_id": "123456789",
        "time": "1624961634.6276",
        "update_id": "0"
    },
    {
        "id": "2",
        "user_id": "123456789",
        "time": "1624961634.6633",
        "update_id": "0"
    },
    {
        "id": "3",
        "user_id": "123456789",
        "time": "1624961634.72",
        "update_id": "0"
    },
*/

$AntiFlood->GetAllReportSpam();
/*
View spam reports from all users
{
    "123456789": [
        {
            "id": "1",
            "user_id": "123456789",
            "time": "1624961634.6276",
            "update_id": "0"
        },
        {
            "id": "2",
            "user_id": "123456789",
            "time": "1624961634.6633",
            "update_id": "0"
        }
    ],
    "987654321": [
        {
            "id": "9",
            "user_id": "987654321",
            "time": "1624961819.3238",
            "update_id": "0"
        },
        {
            "id": "10",
            "user_id": "987654321",
            "time": "1624961819.4981",
            "update_id": "0"
        }
    ]
}
*/

/* bot command code */
?>

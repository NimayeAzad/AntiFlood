<?php
    class AntiFLood{
        /**
         * Written by: NimayeAzad (MonsterTeam) - 14 June 2021
         * Version : 1.0
         * Join our channel to get new updates : @NimaAzadDev
         * You must be ethical in using this source (Do not edit or delete written information)
         */
        private $user_id;
        private $Database;
        private $update_id = 0;

        private $SusTime = 0.9;
        private $Sus_Safe = 30;
        private $Sus_Flood = 3;
        private $AlertFunction;

        public function __construct($DataBase){
            $this->Database = new mysqli($DataBase['server'], $DataBase['username'], $DataBase['password'], $DataBase['database']);
            if (mysqli_connect_error()) {
                error_log("Library/Database/Connect: ".mysqli_connect_error());
                die("Library: Unable to connect to the database. Please check the entered config data and error_log");
            }
            $this->CreateTables();
        }
        public function run($user,$uid=0){
            $this->update_id = $uid;
            $this->user_id = $user;
            $this->AddUser();
            $this->AnalysisData();
            if($this->update_id != 0){
                $this->SetUpdateID();
            }
        }
        public function setting($config){
            if($config['spam_time']){
                $this->SusTime = $config['spam_time'];
            }
            if($config['safe_time']){
                $this->Sus_Safe = $config['safe_time'];
            }
            if($config['flood_assurance']){
                $this->Sus_Flood = $config['flood_assurance'];
            }
            if($config['alert_function']){
                $this->AlertFunction = $config['alert_function'];
            }
            return true;
        }
        private function CreateTables(){
            $sql = "CREATE TABLE IF NOT EXISTS `suspicious_messages_data` (
                `user_id` INT PRIMARY KEY,
                `last_message` DOUBLE NOT NULL,
                `count_sus_message` INT NOT NULL,
                `distance_previous_message` FLOAT NOT NULL,
                `update_id` INT NOT NULL
            )";
            if (!$this->Database->query($sql)){
                error_log("Library/Database/CreateTable/suspicious_messages_data: ".$this->Database->error);
                return false;
            }
            $sql = "CREATE TABLE IF NOT EXISTS `report_users_spam` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `time` DOUBLE NOT NULL,
                `update_id` INT NOT NULL
            )";
            if (!$this->Database->query($sql)){
                error_log("Library/Database/CreateTable/report_users_spam: ".$this->Database->error);
                return false;
            }
            return true;
        }
        private function AddUser(){
            $sql = "SELECT * FROM `suspicious_messages_data` WHERE `user_id`='".$this->user_id."'";
            $user_exists = false;
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/AddUser/CheckUserExist: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    $user_exists = isset($row["user_id"])?true:false;
                }
            }
            if($user_exists == false){
                $sql = "
                INSERT INTO `suspicious_messages_data`
                (user_id,last_message,count_sus_message,distance_previous_message,update_id)
                     VALUES
                ('".$this->user_id."','".microtime(true)."','0','0.0','".$this->update_id."')
                ";
                $Result = $this->Database->query($sql);
                if (!$Result) {
                    error_log("Library/Database/AddUser/InsertUser: ".$this->Database->error);
                    return false;
                } else {
                    return true;
                }
            }
        }
        private function AddCountSus(){
            $new_value = $this->GetCountSus() + 1;
            $sql = "
                UPDATE `suspicious_messages_data`
                    SET
                count_sus_message = '".$new_value."'
                    WHERE
                user_id = '".$this->user_id."';
            ";
            $Result = $this->Database->query($sql);
            if (!$Result) {
                error_log("Library/Database/AddCountSus: ".$this->Database->error);
                return false;
            } else {
                return true;
            }

        }
        private function RestCountSus(){
            $sql = "
            UPDATE `suspicious_messages_data`
                SET
            count_sus_message = '0'
                WHERE
            user_id = '".$this->user_id."';
            ";
            $Result = $this->Database->query($sql);
            if (!$Result) {
                error_log("Library/Database/RestCountSus: ".$this->Database->error);
                return false;
            } else {
                return true;
            }
        }
        private function GetCountSus(){
            $sql = "SELECT * FROM `suspicious_messages_data` WHERE `user_id`='" . $this->user_id . "'";
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/GetCountSus: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    return $row['count_sus_message'];
                }
            }
        }

        private function GetLastUpdateID(){
            $sql = "SELECT * FROM `suspicious_messages_data` WHERE `user_id`='" . $this->user_id . "'";
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/GetLastUpdateID: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    return $row['update_id'];
                }
            }
        }
        private function SetUpdateID(){
            $sql = "
            UPDATE `suspicious_messages_data`
                SET
            update_id = '".$this->update_id."'
                WHERE
            user_id = '".$this->user_id."';
            ";
            $Result = $this->Database->query($sql);
            if (!$Result) {
                error_log("Library/Database/SetUpdateID: ".$this->Database->error);
                return false;
            } else {
                return true;
            }
        }

        private function GetLastDistance(){
            $sql = "SELECT * FROM `suspicious_messages_data` WHERE `user_id`='" . $this->user_id . "'";
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/GetLastDistance: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    return $row['distance_previous_message'];
                }
            }
        }
        private function SetLastDistance($value){
            if($this->update_id != 0){
                if($this->GetLastUpdateID() != $this->update_id){
                    $this->SetUpdateID();
                }else{
                    return false;
                }
            }
            $sql = "
            UPDATE `suspicious_messages_data`
                SET
            distance_previous_message = '".$value."'
                WHERE
            user_id = '".$this->user_id."';
            ";
            $Result = $this->Database->query($sql);
            if (!$Result) {
                error_log("Library/Database/SetLastDistance: ".$this->Database->error);
                return false;
            } else {
                return true;
            }
        }

        private function SetLastTimeMessage(){
            if($this->update_id != 0){
                if($this->GetLastUpdateID() == $this->update_id){
                    return true;
                }
            }
            $sql = "
            UPDATE `suspicious_messages_data`
                SET
            last_message = '".microtime(1)."'
                WHERE
            user_id = '".$this->user_id."';
            ";
            $Result = $this->Database->query($sql);
            if (!$Result) {
                error_log("Library/Database/SetLastTimeMessage: ".$this->Database->error);
                return false;
            } else {
                return true;
            }
        }
        private function GetLastTimeMessage(){
            $sql = "SELECT * FROM `suspicious_messages_data` WHERE `user_id`='" . $this->user_id . "'";
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/GetLastTimeMessage: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    return $row['last_message'];
                }
            }
        }

        private function Report(){
            $time = microtime(true);
            $sql = "
                INSERT INTO `report_users_spam`
                (user_id,time,update_id)
                     VALUES
                ('".$this->user_id."','".$time."','".$this->update_id."')
            ";
            $Result = $this->Database->query($sql);
            if (!$Result) {
                error_log("Library/Database/Report: ".$this->Database->error);
                return false;
            } else {
                if(isset($this->AlertFunction)){
                    $data = [
                        'chat_id'=>$this->user_id,
                        'time'=>$time,
                        'update_id'=>$this->update_id
                    ];
                    $function = $this->AlertFunction;
                    $function($data);
                }
                return true;
            }
        }

        private function AnalysisData(){
            if(microtime(1) - $this->GetLastTimeMessage() >= $this->Sus_Safe){
                $this->RestCountSus();
            }
            $PreviousDistance = $this->GetLastDistance();
            $Distance = microtime(1) - $this->GetLastTimeMessage();
            $this->SetLastTimeMessage();
            $this->SetLastDistance($Distance);
            if($PreviousDistance <= $this->SusTime and $Distance <= $this->SusTime){
                $this->AddCountSus();
            }
            if($this->GetCountSus() >= $this->Sus_Flood){
                $this->Report();
                $this->RestCountSus();
            }
            return true;
        }
        
        public function GetReportSpam(){
            /**
            * View user spam report
            */
            $Result = [];
            $sql = "SELECT * FROM `report_users_spam` WHERE `user_id`='" . $this->user_id . "'";
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/GetReportSpam: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    $Result[] = $row;
                }
            }
            return $Result;
        }
        public function GetAllReportSpam(){
            /**
            * View spam reports of all users
            */
            $Result = [];
            $sql = "SELECT * FROM `report_users_spam`";
            $Search = $this->Database->query($sql);
            if (!$Search) {
                error_log("Library/Database/GetAllReportSpam: ".$this->Database->error);
                return false;
            } else {
                while ($row = mysqli_fetch_array($Search, MYSQLI_ASSOC)) {
                    $Result[] = $row;
                }
            }
            return $Result;
        }

        public function __destruct(){ /** close mysql connection */
            mysqli_close($this->Database);
        }
    }
?>

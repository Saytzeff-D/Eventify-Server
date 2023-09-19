<?php
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Headers:Content-Type");
    class Connection{
        protected $server = 'localhost';
        protected $username = 'root';
        protected $password = '';
        protected $dbName = 'stockevent';
        public $connect;
        public function __construct()
        {
            $this->connect = new mysqli($this->server, $this->username, $this->password, $this->dbName);
        }
    }

    class InsertData extends Connection{
        public function notify($userId, $notification)
        {
            date_default_timezone_set('Africa/Lagos');
            $myTime = date('l'). ', ' .date('d/m/Y'). ',' .date('h:ia');
            $insertNotification = "INSERT INTO notifications (user_id, notification, notifiedTime) VALUES (?, ?, ?)";
            $bindParams = $this->connect->prepare($insertNotification);
            $bindParams->bind_param("sss", $userId, $notification, $myTime);
            $postQuery = $bindParams->execute();
        }
        public function signUp($fname, $lname, $phoneNum, $email, $resident, $occupation, $pword)
        {
            $verifyEmail = "SELECT * FROM users WHERE email = '$email'";
            $queryDB = $this->connect->query($verifyEmail);
            if ($queryDB->num_rows>0) {
                echo json_encode('Email Already exists');
            }
            else{
                $querySql = "INSERT INTO users (fname, lname, phoneNum, email, resident, occupation, pword) VALUES (?,?,?,?,?,?,?)";
                $prepareIntoDb = $this->connect->prepare($querySql);
                $prepareIntoDb->bind_param("sssssss", $fname, $lname, $phoneNum, $email, $resident, $occupation, $pword);
                $insertIntoDb = $prepareIntoDb->execute();
                if($insertIntoDb){
                    $myNotification =  "News!! A user has been added, $fname $lname signup as a user on this platform";
                    $this->notify(1, $myNotification);
                    echo json_encode('True');
                }
                else{
                    echo json_encode('Network Error');
                }
            }
        }
        public function bookEvent($userId, $startDate, $endDate, $occasion, $eventDesc)
        {
            $queryFname = "SELECT fname, lname FROM users WHERE user_id = '$userId'";
            $userFname = $this->connect->query($queryFname);
            $userName = $userFname->fetch_assoc();
            $checkEventDate = "SELECT * FROM events WHERE startDate = '$startDate' OR endDate = '$endDate' OR startDate = '$endDate' OR endDate = '$startDate'";
            $queryEventTb = $this->connect->query($checkEventDate);
            $myUserName = $userName['fname'];
            if($queryEventTb->num_rows>0){
                echo json_encode('Unsuccessful, the date has been booked already');
            }
            else{
                $insertSql = "INSERT INTO events (user_id, startDate, endDate, occasion, eventDesc) VALUES (?, ?, ?, ?, ?)";
                $queryMyInsert = $this->connect->prepare($insertSql);
                $queryMyInsert->bind_param("sssss", $userId, $startDate, $endDate, $occasion, $eventDesc);
                $executeMyInsert = $queryMyInsert->execute();
                if($executeMyInsert){
                    $user = $userName['fname'] . ' '. $userName['lname'];
                    $userNotice = "Dear $myUserName, the Admins has received your request and it will be approved soon";
                    $adminNotice = "$user booked an event-$occasion. Kindly review their request as they await your approval";
                    $this->notify(1, $adminNotice);
                    $this->notify($userId, $userNotice);
                    echo json_encode('Event Booked Successfully');
                }
                else{
                    echo json_encode('Network Error');
                }
            }
        }
        public function updateUser($userId, $occupation, $phoneNum, $resident)
        {
            $query = "UPDATE users SET occupation = ?, phoneNum = ?, resident = ? WHERE user_id = '$userId'";
            $prepQuery = $this->connect->prepare($query);
            $prepQuery->bind_param("sss", $occupation, $phoneNum, $resident);
            $prepQuery->execute();
            if($prepQuery){
                echo json_encode('True');
            }
            else{
                echo json_encode('False');
            }
        }
        public function updateProfilePic($user_id, $img)
        {
            $imgQuery = "UPDATE users SET profilepic = ? WHERE user_id = '$user_id'";
            $prepImgQuery = $this->connect->prepare($imgQuery);
            $prepImgQuery->bind_param("s", $img);
            $prepImgQuery->execute();
            if($prepImgQuery){
                echo json_encode('True');
            }
            else{
                echo json_encode('False');
            }
        }
        public function changeApprovalNotice($id, $approvalNotif)
        {
            $queryNotiif = "UPDATE events SET approvalNotice = '$approvalNotif' WHERE event_id = '$id'";
            $updateQuery = $this->connect->query($queryNotiif);
            if($updateQuery){
                echo json_encode('True');
            }
            else{
                echo json_encode('False');
            }
        }
    }

    class Login extends Connection{
        public function checkMyUser($email, $pword)
        {
            $sql = "SELECT user_id, fname, pword FROM users where email = ?";
            $fetchFromDb = $this->connect->prepare($sql);
            $fetchFromDb->bind_param("s", $email);
            $fetchFromDb->execute();
            $myArr = $fetchFromDb->get_result();
            $arrDb = $myArr->fetch_assoc();
            $verify = password_verify($pword, $arrDb['pword']);
            if($verify){
                if($arrDb['fname'] == 'Admin'){
                    echo json_encode([$arrDb['user_id'], 'Admin']);
                }
                else{
                    echo json_encode([$arrDb['user_id'], 'User']);
                }
            }
            else{
                echo json_encode('False');
            }
        }
    }

    class FetchAllData extends Connection{
        public function userDetails($myId)
        {
            $myQuery = "SELECT * FROM users WHERE user_id = '$myId'";
            $fetchUser = $this->connect->query($myQuery);
            echo json_encode($fetchUser->fetch_assoc());
        }
        public function eventDetails($id)
        {
            $eventQuery = "SELECT * FROM events WHERE user_id = '$id' AND discard = false";
            $fetchEvent = $this->connect->query($eventQuery);
            echo json_encode($fetchEvent->fetch_all(MYSQLI_ASSOC));
        }
        public function profileDetails($user_id)
        {
            $profileQuery = "SELECT * FROM users WHERE user_id = '$user_id'";
            $notifQuery = "SELECT status FROM notifications WHERE user_id = '$user_id' and status = 'unread'";
            $fetchProfile = $this->connect->query($profileQuery);
            $fetchNotifStatus = $this->connect->query($notifQuery);
            echo json_encode([$fetchProfile->fetch_assoc(), $fetchNotifStatus->num_rows]);
        }
        public function userNotification($userId)
        {
            $query = "SELECT * FROM notifications WHERE user_id = '$userId' ORDER BY notification_id DESC";
            $noticeStatus = "UPDATE notifications SET status = 'read' WHERE user_id  = '$userId'";
            $fetchQuery = $this->connect->query($query);
            $this->connect->query($noticeStatus);
            echo json_encode($fetchQuery->fetch_all(MYSQLI_ASSOC));
        }
        public function allEventRequest()
        {
            $requestQuery = "SELECT * FROM events JOIN users using(user_id) WHERE approvalNotice = 'Pending Approval'";
            $fetchRequest = $this->connect->query($requestQuery);
            echo json_encode($fetchRequest->fetch_all(MYSQLI_ASSOC));
        }
    }
?>
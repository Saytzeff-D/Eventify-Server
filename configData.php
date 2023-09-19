<?php
    require_once 'index.php';
    $_POST = json_decode(file_get_contents('php://input'));
    if($_POST->type == 'signUp'){
        $fname = $_POST->fname;
        $lname = $_POST->lname;
        $phoneNum = $_POST->phoneNum;
        $email = $_POST->email;
        $resident = $_POST->resident;
        $occupation = $_POST->occupation;
        $pword = password_hash($_POST->confPword, PASSWORD_DEFAULT);
        $signUser = new InsertData;
        $signUser->signUp($fname, $lname, $phoneNum, $email, $resident, $occupation, $pword);
    }

    elseif($_POST->type == 'login'){
        $email = $_POST->email;
        $pword = $_POST->pword;
        $myLogin = new Login;
        $myLogin->checkMyUser($email, $pword);
    }

    elseif($_POST->type == 'fetchUser'){
        $myId = $_POST->id;
        $myUser = new FetchAllData;
        $myUser->userDetails($myId);
    }

    elseif($_POST->type == 'bookEvent'){
        $userId = $_POST->userId;
        $startDate = $_POST->startDate;
        $endDate = $_POST->endDate;
        $occasion = $_POST->occasion;
        $eventDesc = $_POST->eventDesc;
        $myEvent = new InsertData;
        $myEvent->bookEvent($userId, $startDate, $endDate, $occasion, $eventDesc);
    }

    elseif($_POST->type == 'fetchEvent'){
        $id = $_POST->id;
        $myUser = new FetchAllData;
        $myUser->eventDetails($id);
    }

    elseif($_POST->type == 'userProfile'){
        $user_id = $_POST->user_id;
        $myProfile = new FetchAllData;
        $myProfile->profileDetails($user_id);
    }

    elseif($_POST->type == 'userNotif'){
        $userId = $_POST->userId;
        $myNotice = new FetchAllData;
        $myNotice->userNotification($userId);
    }

    elseif($_POST->type == 'updateProfile'){
        $userId = $_POST->user_id;
        $occupation = $_POST->occup;
        $phoneNum = $_POST->phoneNum;
        $resident = $_POST->resident;
        $myUpdate = new InsertData;
        $myUpdate->updateUser($userId, $occupation, $phoneNum, $resident);
    }

    elseif($_POST->type == 'updateProfilePic'){
        $user_id = $_POST->userId;
        $img = $_POST->img;
        $myImage = new InsertData;
        $myImage->updateProfilePic($user_id, $img);
    }

    elseif($_POST->type == 'discardEvent'){
        echo json_encode('True');
    }

    elseif($_POST->type == 'eventRequest'){
        $fetchEvent = new FetchAllData;
        $fetchEvent->allEventRequest();
    }

    elseif($_POST->type == 'approvalStatus'){
        $myfname = $_POST->fname;
        $myuser = $_POST->user;
        $myoccasion = $_POST->occasion;
        $start = $_POST->startDate;
        $end = $_POST->endDate;
        $notifUser = "Dear $myfname, the admin of this event center wishes to inform you that the event ($myoccasion scheduled for $start) you booked for has been $_POST->approvalNotice. Regards!";
        $notifAdmin = "You have sucessfully approved the request booked by $myuser to hold a $myoccasion, commencing on $start till $end";
        $id = $_POST->event_id;
        $approvalNotif = $_POST->approvalNotice;
        $changeStatus = new InsertData;
        $changeStatus->changeApprovalNotice($id, $approvalNotif);
        $changeStatus->notify(1, $notifAdmin);
        $changeStatus->notify($_POST->user_id, $notifUser);
    }

    elseif($_POST->type == 'sendMsgToUser'){
        $sendMsg = new InsertData;
        $sendMsg->notify($_POST->user_id, $_POST->msg);
    }

?>
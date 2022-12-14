<?php

Class fund{

    //status 
    // active fund = 0
    // closed fund = 1
    // settled fund = 2

    function create_fund($POST,$FILES,$data){

        $DB = new Database();
        $_SESSION['error'] = '';
        $table = $data['table'];

        if($_POST)
        {
            if($FILES['file']['name']!="" && $FILES['file']['error']== 0)
            {
                $folder = $data['table']."/";
                $photoname = clean(date("Y-m-d H:i:s"));
                $destination = "../public/assets/images/mainPages/".strtolower($folder).$photoname;
                move_uploaded_file($FILES['file']['tmp_name'],$destination);
            }
            else 
            {
                $_SESSION['error'] = "This file could not be uploaded";
            }

            if($_SESSION['error'] == "")
            {
                $arr['member_ID1']=$arr['member_ID2']=$arr['member_ID3'] = 0;
                $arr['amount'] = $POST['amount'];
                $arr['keywords'] = $POST['keywords'];
                $arr['town'] = $POST['town'];
                $arr['district'] = $POST['District'];
                $arr['title'] = $POST['Title'];
                $arr['description'] = $POST['description'];
                $arr['accNo'] = $POST['accNo'];
                $arr['bankName'] = $POST['bankName'];
                $arr['branchName'] = $POST['branchName'];
                $arr['accountHolder'] = $POST['accountHolder'];
                $arr['image'] = $photoname;
                $arr['date'] = date("Y-m-d");
                $user = $arr['user'] = $_SESSION['user_id'];

                if(array_key_exists('member1',$data)){
                    $arr['member_ID1'] = $data['member1'];
                }

                if(array_key_exists('member2',$data)){
                    $arr['member_ID2'] = $data['member2'];
                }

                if(array_key_exists('member3',$data)){
                    $arr['member_ID3'] = $data['member3'];
                }                
                
                $query = "INSERT INTO $table  
                (picture,town,district,title,content,amount,keywords,accountnumber,accountholder,bankname,branchname,create_date,user_ID,member_ID1,member_ID2,member_ID3) 
                VALUES 
                (:image,:town,:district,:title,:description,:amount,:keywords,:accNo,:accountHolder,:bankName,:branchName,:date,:user,:member_ID1,:member_ID2,:member_ID3)";

                $result = $DB->write($query,$arr);

                if ($result) {
                    $query2 = "UPDATE registered_user SET fundCount = fundCount + 1 WHERE user_ID = $user";
                    $DB->write($query2);
                    header("Location:".ROOT."funds/".$data['category']);
                    // die;
                }else{
                    $_SESSION['error']="wrong username or password";                    
                }
            }
            

        }
        
    }

    function view_all_funds($cat){
        // $page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        // $limit = 12;
        // $offset = ($page_number - 1) * $limit;
        $tablename = strtolower($cat."fund");
        $query = "SELECT * FROM $tablename WHERE amount!=filled and status=0 ORDER BY id DESC";
        $DB = new Database();
        $result = $DB->read($query);
        if(is_array($result))
        {
            return $result;
        }
        return false;
    }

    function view_fund($data){
         
        $tablename = strtolower($data['table']);
        $id = $data['id'];

        $query = "SELECT * FROM $tablename WHERE ID = $id ";

        $DB = new Database();
        $result = $DB->read($query);

        if(is_array($result))
        {
            return $result[0];
        }
        return false;
    }

    function donate($data){

        $arr['date']= date("Y-m-d");
        $arr['visibility'] = $data['visibility'];
        $arr['tip'] = $data['tip'];
        $fund=$arr['fund'] = $data['id'];
        $user = $arr['user'] = $data['user_id'];
        $arr['time'] = date("H:i:s");
        $arr['amount'] = $data['amount'];
        $amount = $arr['amount'];
        $total = $arr['amount']+$arr['tip'];
        $table = $data['table']."_donate";
        $table2 = $data['table'];

        $query = "INSERT INTO $table (date,visibility,tip,fund_ID,user_ID,time,amount) VALUES (:date,:visibility,:tip,:fund,:user,:time,:amount)";
        $query2 = "UPDATE $table2 set filled = filled + $amount WHERE ID = $fund";
        $query3 = "UPDATE registered_user SET donateCount = donateCount + 1 WHERE user_ID = $user";
        $query4 = "UPDATE registered_user SET donateAmount = donateAmount + $amount WHERE user_ID = $user";

        $DB = new Database();
        $DB->write($query, $arr);
        $DB->write($query2);
        $DB->write($query3);
        $DB->write($query4);

        if ($data['balance'] ==1) {
            $query4 = "UPDATE registered_user SET balance=balance-$total WHERE user_ID = $user";
            $DB->write($query4);
        }
    }

    function DonationStat($table,$id){

        $table = strtolower($table)."_donate";
        $DB = new Database();
        $_SESSION['error'] = '';
        $date = '\''.date("Y-m-d").'\'';
        // echo $date;

        //donation count today
        $query = "SELECT COUNT(ID) as donation FROM $table WHERE date=$date AND fund_ID =$id";
        $count1 = $DB->read($query);
        $arr[0] = $count1[0]->donation;

        //total donation count
        $query2 = "SELECT COUNT(ID) as donation FROM $table WHERE fund_ID =$id";
        $count2 = $DB->read($query2);
        $arr[1] = $count2[0]->donation;

        //recent donation
        $query3 = "SELECT first_name,last_name,visibility,amount FROM registered_user INNER JOIN $table ON registered_user.user_id = $table.user_id WHERE $table.fund_ID=$id AND $table.user_id>0 ORDER BY $table.date DESC, $table.time DESC";
        $count3 = $DB->read($query3);
        if ($count3){
            $visibility = $count3[0]->visibility;
            if($visibility){
                $name = "Anonymous";
            }
            else {
                $name = $count3[0]->first_name." ".$count3[0]->last_name;
            }
            $arr[2][0] = $name;
            $arr[2][1] = $count3[0]->amount;
        } else {
            $queryq = "SELECT user_id,amount FROM  $table WHERE fund_ID=$id ORDER BY date DESC, time DESC";
            $resultq = $DB->read($queryq);
            if($resultq){
                if($resultq[0]->user_id == 0){
                    $arr[2][0] = "Unregistered user";
                    $arr[2][1] = $resultq[0]->amount;
                }else{
                    $arr[2][0] = "No donations yet";
                    $arr[2][1] = 0;
                }
            }else{
                $arr[2][0] = "No donations yet";
                $arr[2][1] = 0;
            }
        }

        //top donation
        $query4 = "SELECT first_name,last_name,visibility,amount FROM registered_USER INNER JOIN $table ON registered_user.user_id = $table.user_id WHERE $table.fund_ID=$id AND $table.user_id>0 ORDER BY amount DESC LIMIT 1";
        $count4 = $DB->read($query4);
        if($count4){
            $visibility = $count4[0]->visibility;
            if($visibility){
                $name = "Anonymous";
            }
            else {
                $name = $count4[0]->first_name." ".$count4[0]->last_name;
            }
            $arr[3][0] = $name;
            $arr[3][1] = $count4[0]->amount;
        }else{
            $queryq = "SELECT user_id,amount FROM  $table WHERE fund_ID=$id ORDER BY date DESC, time DESC";
            $resultq = $DB->read($queryq);
            if($resultq){
                if($resultq[0]->user_id == 0){
                    $arr[3][0] = "Unregistered user";
                    $arr[3][1] = $resultq[0]->amount;
                }else{
                    $arr[3][0] = "No donations yet";
                    $arr[3][1] = 0;
                }
            }else{
                $arr[3][0] = "No donations yet";
                $arr[3][1] = 0;
            }
        }
        return $arr;
    }

    function report($data){

        $DB = new Database();
        $_SESSION['error'] = '';

        $tablename = $data['table'];
        $arr['fund'] = $data['fund_id'];
        $arr['feedback'] = $data['feedback']['feedback'];
        $arr['user'] = $data['user_id'];
        $arr['date'] = date("Y-m-d H:i:s");
        $allowed = array("image/jpeg","image/png");

        //entering report photos to the filesystem and database
        for ($i=0; $i < count($data['photos']['photo']['name']); $i++) { 
            if($data['photos']['photo']['name'][$i]!="" && $data['photos']['photo']['error'][$i]== 0 && in_array($data['photos']['photo']['type'][$i],$allowed))
            {
                $folder1 = "assets/uploads/reports/".$tablename;
                $folder2 = $folder1."/".$arr['fund'];
                $folder3 = $folder2."/".$arr['user'];

                if (!file_exists($folder1)) {
                    mkdir($folder1, 0777, true);
                }
                if (!file_exists($folder2)) {
                    mkdir($folder2, 0777, true);
                }
                if (!file_exists($folder3)) {
                    mkdir($folder3, 0777, true);
                    $desination = $folder3."/".$i;
                }
                else{ 
                    $desination = $folder3."/".$i;
                }
                move_uploaded_file($data['photos']['photo']['tmp_name'][$i],$desination);
            }else{
                $_SESSION['error'] = "This file could not be uploaded";

            }
        }

        echo $_SESSION['error'];

        if (!$_SESSION['error']) {
            $query = "INSERT INTO $tablename (fund_ID,user_ID,date,feedback) VALUES (:fund,:user,:date,:feedback)";

            $result = $DB->write($query,$arr);

            if ($result) {
                header("Location:".ROOT."funds/Medical");
                die;
            }
        }

    }

    function get_search_and_sort($data,$table){
        $DB = new Database();
        $_SESSION['error']="";
        
        $k = $data['keyword'];
        $l = $data['location'];
        $s = $data['sort'];
        $sign = "%";
        $keywords = $sign.$k.$sign;
        $location = $sign.$l.$sign;
        
        switch ($s){
            case 1:
                $order = 'create_date DESC';
                break;
            case 2:
                $order = 'create_date ASC';
                break;
            case 3:
                $order = '(amount-filled) DESC';
                break;
            case 4:
                $order = '(amount-filled) ASC';
                break;
            default:
                $order = 'id ASC';   
        }

        $query = "SELECT * FROM $table WHERE (town LIKE '$location' OR district LIKE '$location') AND keywords LIKE '$keywords' AND amount!= filled AND status = 0 ORDER BY $order";
        $data = $DB->read($query); 
        
        if ($data){
            return $data;
        }
        return false;
    }

    function delete_fund_user($table, $id)
    {
        $DB = new Database();
        $_SESSION['error']="";
        $query = "UPDATE $table SET status=1 WHERE ID=$id";
        $DB->write($query); 
    }

    function delete_fund_admin($table, $id)
    {
        $DB = new Database();
        $_SESSION['error']="";
        $donateTable = $table."_donate";

        $query0 = "SELECT * FROM $donateTable WHERE fund_ID=$id";
        $donations=$DB->read($query0);

        $query3 = "SELECT * FROM $table WHERE ID=$id";
        echo $query3;
        $fund=$DB->read($query3);
        $user = $fund[0]->user_ID;

        if($fund[0]->status<2){
            for ($i=0;$i<count($donations);$i++){
                $user=$donations[$i]->user_ID;
                $amount=$donations[$i]->amount;
                if($user>0){
                    $query = "UPDATE registered_user SET donateCount = donateCount -1,donateAmount = donateAmount - $amount,balance = balance + $amount WHERE user_ID=$user";
                    $DB->write($query);
                }
            }
        }

        $query1 = "DELETE FROM $donateTable WHERE fund_ID=$id";
        $DB->write($query1);


        $query4 = "UPDATE registered_user SET removed_count = removed_count + 1 WHERE user_ID = $user";
        $DB->write($query4);
    
        $query2 = "DELETE FROM $table WHERE ID=$id";
        $DB->write($query2);


    }

    function settle_fund($table,$id){
        $DB = new Database();
        $_SESSION['error']="";
        $query = "UPDATE $table SET status=2 WHERE ID=$id";
        $DB->write($query); 
    }

    function get_leaderboard(){
        $DB = new Database();
        $_SESSION['error']="";
        $query = "SELECT * FROM registered_user ORDER BY donateAmount DESC LIMIT 5 ";
        $data = $DB->read($query); 
        
        if ($data){
            return $data;
        }
        return false;

    }

    function get_monthlyleaderboard(){
        $DB = new Database();
        $_SESSION['error']="";  
        $query = "SELECT * FROM animalcarefund_donate where user_ID != 0 UNION SELECT * FROM childrenfund_donate where user_ID != 0 UNION SELECT * FROM educationfund_donate where user_ID != 0 UNION SELECT * FROM medicalfund_donate where user_ID != 0 UNION SELECT * FROM otherfund_donate where user_ID != 0 UNION SELECT * FROM seniorcarefund_donate where user_ID != 0 ORDER BY amount DESC LIMIT 3 ";
        
        $data = $DB->read($query); 
        if(isset($data))
        {
            return $data;
        }
        return false;

    }
    function get_rankers($arr){
        $DB = new Database();
        $_SESSION['error']="";  
        $query = "SELECT * FROM registered_user where user_ID = '$arr '";
        $data = $DB->read($query); 

            if(isset($data))
            {
                return $data;
            }
            return false;
    }

    function enter_comment($table,$fundId,$comment,$user){
        $DB = new Database();
        $_SESSION['error']=""; 
        $table = strtolower($table."_comment");
        $arr['date'] = date("Y-m-d");
        $arr['time'] = date("H:i:s");
        $arr['comment'] = $comment;
        $arr['user'] = $user;
        $arr['fundId'] = $fundId;

        if(!$user){
            return false;
        }

        $query = "INSERT INTO $table (fund_ID,user_ID,date,time,comment) VALUES (:fundId,:user,:date,:time,:comment)";
        $result = $DB->write($query,$arr);

        if($result){
            return true;
        }
        else {
            return false;
        }

    }

    function load_comments($table, $fundId){
        $DB = new Database();
        $_SESSION['error']=""; 
        $table = $table."_comment";
        $query = "SELECT * FROM $table INNER JOIN registered_user ON $table.user_ID = registered_user.user_ID WHERE fund_ID = $fundId ORDER BY date AND time LIMIT 20";
        $result = $DB->read($query);

        if($result){
            return $result;
        }
        else {
            return false;
        }

    }
}

?>

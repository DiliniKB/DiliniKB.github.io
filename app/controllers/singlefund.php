<?php
Class singlefund extends Controller
{

    function index($category, $id)
    {
        $data['category'] = $category;
        $data['type'] = "fund";
        $data['table'] = $category."fund";
        $data['page_title'] = "View ".$data['category']." ".$data['type'];
        $data['id'] = $id;

        $fund = $this->loadModel("fund");
        $results = $fund->view_fund($data);
        $data['fund'] = $results;

        $creaters = $this->loadModel("user");
        $data['creaters'][0] = $creaters->search_user_by_id($data['fund']->user_ID);

        for ($i=1; $i <4 ; $i++) { 
            $member = 'member_ID'.$i;
            if($data['fund']->$member){
                $data['creaters'][$i] = $creaters->search_user_by_id($data['fund']->$member);
            }
        }

        $today = date_create(date('Y-m-d'));
        $create = date_create($data['fund']->create_date);
        $dategap = date_diff($create,$today);
        $data['dategap'] = '';
        if($dategap->y){
            $data['dategap'] =+ $dategap->y." Years ";
        }
        if($dategap->m){
            $data['dategap'] =+ $dategap->m." Months ";
        }
        if($dategap->d){
            $data['dategap'] =+ $dategap->d." Days";
        }
        if($today == $create){
            $data['dategap'] = 'Today';
        }else{
            $data['dategap'] = $data['dategap']." ago";
        }

        $NDonations = $fund->DonationStat($data['table'],$data['id']);
        $data['numberofdonationsToday'] = $NDonations[0];
        $data['numberofdonations'] = $NDonations[1];
        $data['recentDonor'] = $NDonations[2][0];
        $data['recentDonation'] = $NDonations[2][1];
        $data['topDonor'] = $NDonations[3][0];
        $data['topDonation'] = $NDonations[3][1];

        $comments = $fund->load_comments($data['table'],$data['id']);
        $data['comments'] = $comments;

        if ($_POST) {
            // show($_POST);
            if(empty($_SESSION['user_id'])){
                header("Location:".ROOT."home/login");
            }
            else{
                $result = $fund->enter_comment($data['table'],$data['id'],$_POST['comment'],$_SESSION['user_id']);
            }
            
            unset($_POST); 
            if ($result){
                header("Refresh:0"); 
            }
        }

        $this->view("viewfund",$data);
    }

    function report($table="",$id=""){
        $data['table'] = $table."_report";
        $data['fund_id'] = $id;
        $data['page_title'] = "Report fund";
        if(empty($_SESSION['user_id'])){
            header("Location:".ROOT."home/login");
        }
        $data['user_id'] = $_SESSION['user_id'];
        
        if ($_POST) {
            $data['feedback'] = $_POST;
            $data['photos'] = $_FILES;
            $fund = $this->loadModel("fund");
            $fund->report($data);
            // show($data);
        }
        $this->view("report",$data);

    }

    function donate($category,$id){
        $data['page_title'] = "Donate";
        $data['category'] = $category;
        $data['type'] = "fund";
        $data['table'] = $category."fund";
        $data['page_title'] = "View ".$data['category']." ".$data['type'];
        $data['id'] = $id;

        $fund = $this->loadModel("fund");
        $results = $fund->view_fund($data);
        $data['fund'] = $results;

        if(isset($_SESSION['user_id'])){
            $data['user_id'] = $_SESSION['user_id'];
            $users = $this->loadModel("user");
            $user = $users->search_user_by_id($data['user_id']);
            // show($user);
            $data['balance'] = $user->balance;
        }else{
            $data['user_id'] = 0;
        }

        // show($data);

        if ($_POST) {
            // show($_POST);
            $data['amount'] = $_POST['donation'];
            $data['tip'] = $_POST['tip'];
            $data['visibility'] = $_POST['anonymous'];
            $data['balance'] = $_POST['balance'];
            $fund->donate($data);
            unset($_POST,$data);
            header("Location:".ROOT."singlefund/".$category."/".$id);
            die;
        }

        $this->view("fundpayment",$data);
        
    }

    
}
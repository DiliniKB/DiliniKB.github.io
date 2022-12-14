<?php

    Class CreatePosts extends Controller
    {
        function index($category){
            $data['category'] = $category;
            // show($data);
            if($_SESSION['user_id']){
                header("Location:".ROOT."createposts/create/".$category."");
            }else{
                header("Location:".ROOT."home/login");
            }
		    die;
        }
        
        function create($category)
        {
            $data['category'] = $category;
            $data['type'] = "post";
            $data['table'] = $data['category']."post";

            $data['page_title'] = "Start a ".$data['type'];

            $user = $this->loadModel("user");
            $user0 = $user->search_user_by_id($_SESSION["user_id"]);

            if($_POST){
                for ($i=1; $i <4 ; $i++) { 
                    $member = 'member'.$i;
                    if($_POST[$member]){
                        $result= $user->search_user_by_email($_POST[$member]);
                        $data[$member] = $result->user_ID;
                        if(!$data[$member]){
                            $_SESSION['error'] = "Please enter a registered email for".$member;
                        }
                    }
                }

                $uploader = $this->loadModel("post");
                $uploader->create_post($_POST,$_FILES,$data);

            }

            $data['user'] = $user0;

            $this->view("create".$data['type'],$data);
        }

    }

?>

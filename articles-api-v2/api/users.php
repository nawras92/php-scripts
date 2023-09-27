<?php

// Include Database Configuration
include_once '../db-config.php';

// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// GET an article
if($method === 'GET'){
	if(isset($_GET['id'])){
		$userId = $_GET['id']; 
	  // To do: Validate/Sanitize id query $userId 
	  $sql_query = "SELECT * FROM `nx_users` WHERE id = $userId";
		$result = $conn->query($sql_query);
		if($result->num_rows === 1){
			$user = $result->fetch_assoc();
			echo json_encode($user);
			return;
		}else{
		  echo json_encode(array('message'=> 'No User Found'));
			return;
		}
	}
	exit;
}

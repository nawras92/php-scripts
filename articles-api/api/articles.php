<?php

// Include Database Configuration
include_once '../db-config.php';


// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// GET an article
if($method === 'GET'){
	if(isset($_GET['id'])){
		$articleId = $_GET['id']; 
	  // To do: Validate/Sanitize id query $articleId 
	  $sql_query = "SELECT * FROM `nx_articles` WHERE id = $articleId";
		$result = $conn->query($sql_query);
		if($result->num_rows === 1){
			$article = $result->fetch_assoc();
			echo json_encode($article);
			return;
		}else{
		  echo json_encode(array('message'=> 'No Article Found'));
			return;
		}
	}
	$articles = array();
	$sql_query = "SELECT * FROM `nx_articles`";
	$result = $conn->query($sql_query);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$articles[] = $row;
		}
		echo json_encode($articles);
	}else{
		echo json_encode(array('message'=> 'No Articles Found'));
	}
	exit;
}

// POST an Article
if($method === 'POST'){
	// Get Data
	$data = json_decode(file_get_contents("php://input"), true);
	// To do: Validate/Sanitize Input Data $data
	$title = $data['title'];
	$description = $data['description'];
	$content = $data['content'];
	$author_id = $data['author_id'];
	$category = $data['category'];
	$keywords = $data['keywords'];

	$query_sql = "INSERT INTO `nx_articles` (`title`, `description`, `content`, `author_id`, `keywords`, `category`) VALUES ('$title', '$description', '$content', '$author_id', '$keywords', '$category');";
	if($conn->query($query_sql) === true){
		echo json_encode(array("message"=> "An article has been added"));
	}else{
		echo json_encode(array("message"=> "An error occured" . $conn->error));
	}
	exit;
}


// Delete an article
if($method === 'DELETE'){
	if(isset($_GET['id'])){
		$articleId = $_GET['id'];
		$sql_query = "DELETE FROM nx_articles WHERE `nx_articles`.`id` = '$articleId'";
		if($conn->query($sql_query) === true){
		  echo json_encode(array("message" => "The article has been deleted."));
		}else{
		  echo json_encode(array("message" => "Error deleting the article: " . $conn->error));
		}

	}else{
		echo json_encode(array("message" => "ID not provided"));
	}
	
	exit;
}

// Update an article
if($method === 'PUT'){
	if(isset($_GET['id'])){
		$articleId = $_GET['id']; 
	  // To do: Validate/Sanitize id query $articleId 
		// Get Data
		$data = json_decode(file_get_contents("php://input"), true);
		// To do: Validate/Sanitize Input Data $data
		// To do: Just Edit the fields provided by the user.
		// .... And neglect other fields.
		$title = $data['title'];
		$description = $data['description'];
		$content = $data['content'];
		$author_id = $data['author_id'];
		$category = $data['category'];
		$keywords = $data['keywords'];
		$query_sql = "UPDATE `nx_articles` SET `title` = '$title', `description` = '$description', `content` = '$content', `author_id` = '$author_id', `keywords` = '$keywords', `category` = '$category' WHERE `nx_articles`.`id` = $articleId; ";
		if($conn->query($query_sql) === true){
			echo json_encode(array("message"=> "Article has been edited successfully."));
		}else{
			echo json_encode(array("message"=> "An error occured" . $conn->error));
		}
	}else{
			echo json_encode(array("message"=> "Article ID not provided"));
	}

	exit;
}



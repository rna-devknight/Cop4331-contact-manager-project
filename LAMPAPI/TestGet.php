<?php
	//By Peyton Ryan
	// NOT TESTED YET

	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	/*$contactId = 0;
	$userId = $inData["user_id"];
	$firstName = $inData["first_name"];
	$lastName = $inData["last_name"];
	$phoneNumber = $inData["phone_num"];
	$email = $inData["email"];
	$dateRecord = date("Y.m.d");*/

	/*
	$conn = new mysqli("contactus27.ccfnvwijsws5.us-east-2.rds.amazonaws.com",
	 									 "root",
										 "Selcouth",
										 "contactus27");
	if ($conn->connect_error)
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$add = $conn->prepare("INSERT into Contact (first_name, last_name, phone_num, email, date_record, user_id) VALUES(?,?,?,?,?)");
		$add->bind_param("ssssss", $firstName, $lastName, $phoneNumber, $email, $dateRecord, $userId);
		$add->execute();
		$add->close();

		$conn->close();
		returnWithError("");
	}*/

	$myObj->firstName = "John";
	$myObj->lastName = "Doe";

	$myJSON = json_encode($myObj);

	sendResultInfoAsJson($myJSON);

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
		return $obj;
	}

	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>

<?php
	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	$userId = $inData["user_id"];
	$contactId = $inData["contact_id"];
	$firstName = $inData["first_name"];
	$lastName = $inData["last_name"];
	$phoneNumber = $inData["phone_num"];
	$email = $inData["email"];

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
		$add = $conn->prepare("UPDATE Contact set
			first_name = ?,
			last_name = ?,
			phone_num = ?,
			email = ? where
			user_id = ? AND
			contact_id = ?");
		$add->bind_param("ssssss", $firstName, $lastName, $phoneNumber, $email, $userId, $contactId);
		$add->execute();
		$add->close();

		$conn->close();
		returnWithError("");
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>

<?php
	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	$contactId = $inData["contact_id"];
	$userId = $inData["user_id"];
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
			first_name = $firstName,
			last_name = $lastName,
			phone_num = $phoneNumer,
			email = $email where
			user_id = $userId AND
			contact_id = $contactId");
		$add->bind_param("sssssss", $index, $firstName, $phoneNumber, $email, $dateRecord, $userId);
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

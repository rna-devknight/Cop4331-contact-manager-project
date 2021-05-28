<?php
	//By Peyton Ryan
	// NOT TESTED YET

	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	$contactId = 0;
	$userId = $inData["user_id"];
	$firstName = $inData["first_name"];
	$phoneNumber = $inData["phone_num"];
	$email = $inData["email"];
	$dateRecord = date("Y.m.d");

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
		$id = $conn->prepare("SELECT MAX(contact_id) AS max_val FROM Contact WHERE user_id=?");
		$id->bind_param("s", $userId);
		$id->execute();
		$index = $id->get_result()->$max_val;
		$id->close();

		if($index == null)
		{
			$index = -1;
		}
		$index = $index + 1;

		$add = $conn->prepare("INSERT into Contact (contact_id, first_name, phone_num, email, date_record, user_id) VALUES(?,?,?,?,?,?)");
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

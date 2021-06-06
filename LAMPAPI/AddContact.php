<?php
	//By Peyton Ryan
	// NOT TESTED YET

	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	$contactId = 0;
	$userId = $inData["user_id"];
	$firstName = $inData["first_name"];
	$lastName = $inData["last_name"];
	$phoneNumber = $inData["phone_num"];
	$email = $inData["email"];
	date_default_timezone_set('UTC');
	$dateRecord = date("m-d-Y");

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
		$add = "INSERT into Contacts (first_name, last_name, user_id, phone_num, email, date_record)
		VALUES('$firstName','$lastName','$userId','$phone_num'),'$email','$dateRecord'";
		if(mysqli_query($conn, $add)){
    echo "Records added successfully.";
} else{
    echo "ERROR: Could not able to execute $add. " . mysqli_error($conn);
}


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

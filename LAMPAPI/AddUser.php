<?php
	//By Peyton Ryan
	// NOT TESTED YET

	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	$contactId = 0;
	$firstName = $inData["first_name"];
	$lastName = $inData["last_name"];
	$username = $inData["username"];
	$password = $inData["password"];

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
		/*$add = $conn->prepare("INSERT into Users (first_name, last_name, username, password) VALUES(?,?,?,?)");
		$add->bind_param("ssss", $firstName, $lastName, $username, $password);
		$conn->query($add);
		$add->close();*/

		$add = "INSERT into Users (first_name, last_name, username, password) VALUES('$firstName','$lastName','$usernmae','$password')";
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

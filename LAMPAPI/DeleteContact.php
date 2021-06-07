<?php
	$inData = getRequestInfo();

	// I believe in Cammel Case Supperiority
	$contactId = $inData["contact_id"];
	$userId = $inData["user_id"];

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
		$add = $conn->prepare("DELETE FROM Contact WHERE contact_id = ? AND user_id = ?");
		$add->bind_param("ii", $contactId, $userId);
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

<?php

	$inData = getRequestInfo();

	$searchResults = "";
	$searchCount = 0;

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
		$stmt = $conn->prepare("SELECT first_name, last_name, contact_id from Contact where
			(first_name like ? or
			last_name like ? or
			first_name + ' ' + last_name like ? or
			last_name + ' ' + first_name like ?') and user_id=?");
		$contactName = "%" . $inData["search"] . "%";
		$stmt->bind_param("ssssi", $contactName, $contactName, $contactName, $contactName, $inData["userId"]);

		$stmt->execute();

		$result = $stmt->get_result();

		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			$searchResults .= '"' . $row["first_name"] . ', ' . $row["last_name"] . ', ' . $row["user_id"] . '"';
		}

		if( $searchCount == 0 )
		{
			returnWithError( "No Records Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
		}

		$stmt->close();
		$conn->close();
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
		$retValue = '{"results":[],"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}

?>

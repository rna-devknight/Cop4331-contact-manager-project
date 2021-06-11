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
		if($inData["search"] == "")
		{
			$stmt = $conn->prepare("SELECT first_name, last_name, contact_id, phone_num, email from Contact where
				 user_id=?");
			$stmt->bind_param("i", $inData["user_id"]);
		}
		else
		{
			$stmt = $conn->prepare("SELECT first_name, last_name, contact_id, phone_num, email from Contact where
				(first_name like ? or
				last_name like ? or
				first_name + ' ' + last_name like ? or
				last_name + ' ' + first_name like ?) and user_id=?");
			$contactName = "%" . $inData["search"] . "%";
			$stmt->bind_param("ssssi", $contactName, $contactName, $contactName, $contactName, $inData["user_id"]);
		}

		$stmt->execute();

		$result = $stmt->get_result();

		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			$searchResults .= '"' . "<div class = 'contactBox' id = 'contactBox" .
			$row["contact_id"] .
			"'><div class = 'contactId'>Contact ID: " .
			$row["contact_id"] .
			"</div><input type='text' id='firstName" .
			$row["contact_id"] .
			"' value='" .
			$row["first_name"] .
			"'> <input type='text' id='lastName" .
			$row["contact_id"] .
			"' value='" .
			$row["last_name"] .
			"'> <br/><input type='text' id='phone" .
			$row["contact_id"] .
			"' value='" .
			$row["phone_num"] .
			"'> <br/><input type='text' id='email" .
			$row["contact_id"] .
			"' value='" .
			$row["email"] .
			"'> <br/><button type='button' class='button-small' id='submit" .
			$row["contact_id"] .
			"' onclick='updateContact(" .
			$row["contact_id"] .
			")'>Update</button><button type='button' class='button-small' id='delete" .
			$row["contact_id"] .
			"' onclick='deletePopup(" .
			$row["contact_id"] .
			")'>Delete</button></div>" .
			'"';
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

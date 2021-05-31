<?php

  // COMPOSED BY: Charlie Tan
  // IF YOU FIND ANY PROBLEMS, LET ME KNOW IN THE SMALL PROJECT DISCORD!

  /*********************
  |  SCRIPT CONSTANTS  |
  *********************/

  // DATABASE INFORMATION
  define('DB_HOSTNAME', 'contactus27.ccfnvwijsws5.us-east-2.rds.amazonaws.com');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', 'Selcouth');
  define('DATABASE_NAME', 'contactus27');

  // DATABASE INFORMATION
  define('PRIMARY_TABLE_NAME', 'Users');
  define('SECONDARY_TABLE_NAME', 'Contact');

  // PRIMARY TABLE INFORMATION
  define('USERID_COLUMN_NAME', 'user_id');
  define('FIRSTNAME_COLUMN_NAME', 'first_name');
  define('LASTNAME_COLUMN_NAME', 'last_name');
  define('USERNAME_COLUMN_NAME', 'username');
  define('PASSWORD_COLUMN_NAME', 'password');

  // ASSOCIATIVE ATTRIBUTE NAMES FROM FRONTEND'S JSON REQUEST PACKET
  define('FIRST_NAME_ASSOC_NAME', 'first_name');
  define('LAST_NAME_ASSOC_NAME', 'last_name');
  define('DESIRED_USERNAME_ASSOC_NAME', 'username');
  define('DESIRED_PASSWORD_ASSOC_NAME', 'password');

  // ERROR MESSAGES
  define('CONNECTION_ERROR_MSG', 'Connection to database failed');
  define('ASSOCIATIVE_ARRAY_ERROR_MSG', 'Input data values could not be accessed '.
    'from internal associative array.');
  define('DATABASE_QUERY_ERROR_MSG', 'Database query failed');
  define('INSERT_QUERY_ERROR_MSG', 'Insert query into database failed');
  define('ID_RETRIEVAL_FAIL_MSG1', "Registration was successful but userID retrieval failed".
    " upon database query");
  define('ID_RETRIEVAL_FAIL_MSG2', "Registration was successful but userID retrieval failed".
    " upon accessing the corresponding table row");
  define('REGISTRATION_ERROR_MSG', "Registration failed: desired username and password ".
    "has already been taken");

  /*********************************** END SCRIPT CONSTANTS **************************************/

  /*******************
  |  HELPER CLASSES  |
  *******************/

  class registration_response_packet
  {
    public $success_boolean; // 'true' FOR SUCCESSFUL REGISTRATION, 'false' OTHERWISE
    public $user_id_int; // CLIENT'S USER ID COLUMN IN THE DATABASE
    public $error_msg_str; // DESCRIBE FAILURE WHEN 'success_boolean' IS FALSE

    function __construct($success_bool, $userid_int, $error_str)
    {
      $this->success_boolean = $success_bool;
      $this->user_id_int = $userid_int;
      $this->error_msg_str = $error_str;
    }
  }

  /************************************* END HELPER CLASSES **************************************/

  /*********************
  |  SCRIPT EXECUTION  |
  **********************/

  // ASSOCIATIVE ARRAY CONTAINING INPUT DATA FROM REGISTRATION PAGE
  $inputdata_assoc_array = json_decode(file_get_contents('php://input'), true);

  // ESTABLISH CONNECTION TO EXTERNAL DATABASE
  $database = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE_NAME);

  // IF CONNECTION COULD NOT BE MADE
  if($database->connect_errno != 0)
  {
    send_json_response_packet(false, NULL, CONNECTION_ERROR_MSG);
    exit;
  }

  // AT THIS POINT, DATABASE RESOURCE CAN BE ASSUMED TO BE SAFE TO USE

  $firstname_input_str = $inputdata_assoc_array[FIRST_NAME_ASSOC_NAME];
  $lastname_input_str = $inputdata_assoc_array[LAST_NAME_ASSOC_NAME];
  $username_input_str = $inputdata_assoc_array[DESIRED_USERNAME_ASSOC_NAME];
  $password_input_str = $inputdata_assoc_array[DESIRED_PASSWORD_ASSOC_NAME];

  // IF FIRSTNAME/LASTNAME/USERNAME/PASSWORD VALUES COULD NOT BE ACCESSED FROM ASSOCIATIVE ARRAY
  if($username_input_str == NULL || $password_input_str == NULL ||
    $firstname_input_str == NULL ||  $lastname_input_str == NULL)
  {
    send_json_response_packet(false, NULL, ASSOCIATIVE_ARRAY_ERROR_MSG);
    $database->close();
    exit;
  }

  $mysql_select_query_str = "SELECT ".USERID_COLUMN_NAME." FROM ".PRIMARY_TABLE_NAME.
    " WHERE ".USERNAME_COLUMN_NAME." = ? AND ".PASSWORD_COLUMN_NAME." = ?";
  $query_statement = $database->prepare($mysql_select_query_str);
  $query_statement->bind_param('ss', $username_input_str, $password_input_str);

  // IF DATABASE QUERY WAS NOT EXECUTED SUCCESSFULLY
  if( !($query_statement->execute()) )
  {
    send_json_response_packet(false, NULL, DATABASE_QUERY_ERROR_MSG);
    $query_statement->close();
    $database->close();
    exit;
  }

  // AT THIS POINT, IT CAN BE ASSUMED THAT THE DATABASE QUERY WAS EXECUTED SUCCESSFULLY

  $result_set_obj = $query_statement->get_result();

  // KEYS FOR THIS ASSOCIATIVE ARRAY CORRESPOND TO THE NAMES OF THE DATABASE COLUMNS
  $row_assoc_array = $result_set_obj->fetch_assoc();

  // IF NO ROW IN THE DATABASE TABLE MATCHED THE SELECT QUERY
  if($row_assoc_array == NULL)
  {
    $result_set_obj->close();
    $query_statement->close();

    $new_userid = enter_client_into_database($firstname_input_str, $lastname_input_str,
      $username_input_str, $password_input_str, $database);

    send_json_response_packet(true, $new_userid, 'No error');
    $database->close();
    exit;
  }

  // AT THIS POINT, IT CAN BE ASSUMED THAT THE DATABASE ALREADY HAS AN ENTRY WITH THE...
  // ...SAME USERNAME AND PASSWORD IN THE PRIMARY USERS TABLE

  send_json_response_packet(false, -1234, REGISTRATION_ERROR_MSG);

  $result_set_obj->close();
  $query_statement->close();
  $database->close();

  /************************************ END SCRIPT EXECUTION *************************************/

  /***************************
  |  USER-DEFINED FUNCTIONS  |
  ***************************/

  function send_json_response_packet($success_bool, $userid_int, $error_str)
  {
    // SEND HTML HEADER FIRST BEFORE CONTENT
    header('Content-type: application/json');

    $response_packet_obj = new registration_response_packet($success_bool, $userid_int,
      $error_str);

    $json_response_str = json_encode($response_packet_obj);

    // SEND CONTENT
    echo $json_response_str;
  }

  /************************************** NEXT FUNCTION ******************************************/

  // RETURNS INT OF NEWLY CREATED USERID FOR THE CLIENT
  function enter_client_into_database($fname_str, $lname_str, $usrname_str, $pwd_str, &$db)
  {
    $mysql_insert_query_str = "INSERT INTO ".PRIMARY_TABLE_NAME." SET ".FIRSTNAME_COLUMN_NAME.
      " = ?, ".LASTNAME_COLUMN_NAME." = ?, ".USERNAME_COLUMN_NAME." = ?, ".PASSWORD_COLUMN_NAME.
      " = ?";

    $query_statement = $db->prepare($mysql_insert_query_str);
    $query_statement->bind_param('ssss', $fname_str, $lname_str, $usrname_str, $pwd_str);

    // IF DATABASE QUERY WAS NOT EXECUTED SUCCESSFULLY
    if( !($query_statement->execute()) )
    {
      send_json_response_packet(false, NULL, INSERT_QUERY_ERROR_MSG);
      $query_statement->close();
      $db->close();
      exit;
    }

    // AT THIS POINT, IT CAN BE ASSUMED THAT THE DATABASE QUERY WAS EXECUTED SUCCESSFULLY

    $query_statement->close();
    $new_userid_int = get_userid_from_database($usrname_str, $pwd_str, $db);

    return $new_userid_int;
  }

  /************************************** NEXT FUNCTION ******************************************/

  // RETURNS INT
  // FUNCTION ASSUMES THAT A PRIOR INSERT QUERY WAS SUCCESSFUL FOR THE CORRESPONDING USERID
  function get_userid_from_database($usrname_str, $pwd_str, &$db)
  {
    $mysql_select_query_str = "SELECT ".USERID_COLUMN_NAME." FROM ".PRIMARY_TABLE_NAME.
      " WHERE ".USERNAME_COLUMN_NAME." = ? AND ".PASSWORD_COLUMN_NAME." = ?";
    $query_statement = $db->prepare($mysql_select_query_str);
    $query_statement->bind_param('ss', $usrname_str, $pwd_str);

    // IF DATABASE QUERY WAS NOT EXECUTED SUCCESSFULLY
    if( !($query_statement->execute()) )
    {
      send_json_response_packet(false, NULL, ID_RETRIEVAL_FAIL_MSG1);
      $query_statement->close();
      $db->close();
      exit;
    }

    // AT THIS POINT, IT CAN BE ASSUMED THAT THE DATABASE QUERY WAS EXECUTED SUCCESSFULLY

    $result_set_obj = $query_statement->get_result();

    // KEYS FOR THIS ASSOCIATIVE ARRAY CORRESPOND TO THE NAMES OF THE DATABASE COLUMNS
    $row_assoc_array = $result_set_obj->fetch_assoc();

    // IF NO ROW IN THE DATABASE TABLE MATCHED THE SELECT QUERY  
    if($row_assoc_array == NULL)
    {
      send_json_response_packet(false, NULL, ID_RETRIEVAL_FAIL_MSG2);
      $result_set_obj->close();
      $query_statement->close();
      $db->close();
      exit;
    }

    // AT THIS POINT, IT CAN BE ASSUMED THAT THE CORRESPONDING TABLE ROW CONTAINING THE...
    // ...DESIRED USERID WAS SUCCESSFULLY ACCESSED

    $userid_int = $row_assoc_array[USERID_COLUMN_NAME];

    $result_set_obj->close();
    $query_statement->close();

    return $userid_int;
  }

?>
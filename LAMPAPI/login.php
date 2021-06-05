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

  // DATABASE TABLE INFORMATION
  define('PRIMARY_TABLE_NAME', 'Users');
  define('SECONDARY_TABLE_NAME', 'Contact');

  // PRIMARY TABLE INFORMATION
  define('USERID_COLUMN_NAME', 'user_id');
  define('FIRSTNAME_COLUMN_NAME', 'first_name');
  define('LASTNAME_COLUMN_NAME', 'last_name');
  define('USERNAME_COLUMN_NAME', 'username');
  define('PASSWORD_COLUMN_NAME', 'password');

  // FRONTEND LOGIN PAGE INFO
  define('USERNAME_FIELD_ID', 'username');
  define('PASSWORD_FIELD_ID', 'userpw');

  // ASSOCIATIVE ATTRIBUTE NAMES FROM FRONTEND'S JSON REQUEST PACKET
  // EXPECTING: {"username" : "some_username", "password" : "some_password"}
  define('USERNAME_ASSOCIATIVE_NAME', 'username');
  define('PASSWORD_ASSOCIATIVE_NAME', 'password');

  // ERROR MESSAGES
  define('CONNECTION_ERROR_MSG', 'Connection to database failed');
  define('ASSOCIATIVE_ARRAY_ERROR_MSG', 'Username and Password values could not be accessed '.
    'from internal associative array. Must change Script Constants USERNAME_ASSOCIATIVE_NAME '.
    'and PASSWORD_ASSOCIATIVE_NAME to proper values.');
  define('DATABASE_QUERY_ERROR_MSG', 'Database query failed');
  define('NO_MATCH_ERROR_MSG', 'Username and/or password did not match');

  /*********************************** END SCRIPT CONSTANTS **************************************/

  /*******************
  |  HELPER CLASSES  |
  *******************/

  class login_response_packet
  {
    public $success_boolean; // 'true' FOR SUCCESSFUL LOGIN, 'false' OTHERWISE
    public $user_id_int; // CLIENT'S USER ID COLUMN IN THE DATABASE
    public $first_name_str; // CLIENT'S FIRST NAME COLUMN IN THE DATABASE
    public $last_name_str; // CLIENT'S LAST NAME COLUMN IN THE DATABASE
    public $error_msg_str; // DESCRIBE FAILURE WHEN 'success_boolean' IS FALSE

    function __construct($success_bool, $userid_int, $firstname_str, $lastname_str, $error_str)
    {
      $this->success_boolean = $success_bool;
      $this->user_id_int = $userid_int;
      $this->first_name_str = $firstname_str;
      $this->last_name_str = $lastname_str;
      $this->error_msg_str = $error_str;
    }
  }

  /************************************* END HELPER CLASSES **************************************/

  /*********************
  |  SCRIPT EXECUTION  |
  **********************/

  // ASSOCIATIVE ARRAY CONTAINING INPUT DATA FROM LOGIN PAGE
  $inputdata_assoc_array = json_decode(file_get_contents('php://input'), true);

  // ESTABLISH CONNECTION TO EXTERNAL DATABASE
  $database = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE_NAME);

  // IF CONNECTION COULD NOT BE MADE
  if($database->connect_errno != 0)
  {
    send_json_response_packet(false, NULL, NULL, NULL, CONNECTION_ERROR_MSG);
    exit;
  }

  // AT THIS POINT, DATABASE RESOURCE CAN BE ASSUMED TO BE SAFE TO USE

  $username_input_str = $inputdata_assoc_array[USERNAME_ASSOCIATIVE_NAME];
  $password_input_str = $inputdata_assoc_array[PASSWORD_ASSOCIATIVE_NAME];

  // IF USERNAME AND PASSWORD VALUES COULD NOT BE ACCESSED FROM ASSOCIATIVE ARRAY
  if($username_input_str == NULL || $password_input_str == NULL)
  {
    send_json_response_packet(false, NULL, NULL, NULL, ASSOCIATIVE_ARRAY_ERROR_MSG);
    $database->close();
    exit;
  }

  $mysql_select_query_str = "SELECT ".USERID_COLUMN_NAME.", ".FIRSTNAME_COLUMN_NAME.", ".
    LASTNAME_COLUMN_NAME." FROM ".PRIMARY_TABLE_NAME." WHERE ".
    USERNAME_COLUMN_NAME." = ? AND ".PASSWORD_COLUMN_NAME." = ?";
  $query_statement = $database->prepare($mysql_select_query_str);
  $query_statement->bind_param('ss', $username_input_str, $password_input_str);

  // IF DATABASE QUERY WAS NOT EXECUTED SUCCESSFULLY
  if( !($query_statement->execute()) )
  {
    send_json_response_packet(false, NULL, NULL, NULL, DATABASE_QUERY_ERROR_MSG);
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
    send_json_response_packet(false, -1234, "", "", NO_MATCH_ERROR_MSG);
    $result_set_obj->close();
    $query_statement->close();
    $database->close();
    exit;
  }

  // AT THIS POINT, IT CAN BE ASSUMED THAT THE PROVIDED USERNAME AND PASSWORD IS VALID

  $userid_int = $row_assoc_array[USERID_COLUMN_NAME];
  $firstname_str = $row_assoc_array[FIRSTNAME_COLUMN_NAME];
  $lastname_str = $row_assoc_array[LASTNAME_COLUMN_NAME];

  send_json_response_packet(true, $userid_int, $firstname_str, $lastname_str, 'No error');

  $result_set_obj->close();
  $query_statement->close();
  $database->close();

  /************************************ END SCRIPT EXECUTION *************************************/

  /***************************
  |  USER-DEFINED FUNCTIONS  |
  ***************************/

  function send_json_response_packet($success_bool, $userid_int, $firstname_str,
    $lastname_str, $error_str)
  {
    // SEND HTML HEADER FIRST BEFORE CONTENT
    header('Content-type: application/json');

    $response_packet_obj = new login_response_packet($success_bool, $userid_int,
      $firstname_str, $lastname_str, $error_str);

    $json_response_str = json_encode($response_packet_obj);

    // SEND CONTENT
    echo $json_response_str;
  }

?>
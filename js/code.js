// Easily modifiable variables for the url
var address = 'http://contactus27.xyz/LAMPAPI';
var extension = '.php';
var loginAddress = '/login';
var contactAddress = '/AddContact';
var searchAddress = '/SearchContacts';
var registerAddress = '/registration';

// Variables regarding the user
var userID = 0;
var firstName = "";
var lastName = "";
var username = "";

// Function to log in
function login() {
    // Initializes variables
    userID = 0;
    firstName = "";
    lastName = "";

    // Obtains username and password from approriate id tag
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    
    // Initializes result
    document.getElementById("result").innerHTML = "Processing, please wait...";

    // Initializes the json payload and loads the url
    var jsonPayload = '{"login" : "' + username + '", "password" : "' + password + '"}';
    var url = address + loginAddress + extension;

    // xhr = XMLHttpRequest
    var xhr = new XMLHttpRequest();
    // Initializes the newly created request
    xhr.open("POST", url, true);
    // Sets the value of the HTTP header (header, value)
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        xhr.onreadystatechange = function() {
            // If request is finished and response is correct
            if(this.readyState == 4 && this.status == 200) {
                // Processes the JSON string and assigns server response
                var jsonObject = JSON.parse(xhr.responseText);
                // Assigns the id from the obtained server response to the user id
                userID = jsonObject.user_id_int;

                // If id invalid, return
                if(userID < 1) {
                    document.getElementById("result").innerHTML = "Username or Password is invalid";
                    return;
                }

                // Assigns first name and last name from server response
                firstName = jsonObject.first_name_str;
                lastName = jsonObject.last_name_str;

                // Executes save cookie function to store user session
                saveCookie(username);

				// Displays confirmation message
				document.getElementById("result").innerHTML = "Success!";
				
                // Redirects to home.html
                window.location.href = "home.html";
            }
        };

        // Sends the request to the server
        xhr.send(jsonPayload);
    }
    catch(err) {
        // Throws error message in id result
        document.getElementById("result").innerHTML = err.meessage;
    }
}

// Function to provide immediate password confirmation
function check() {
	// Obtains the two passwords
	var pw1 = document.getElementById("password").value;
	var pw2 = document.getElementById("passwordConfirmation").value;

	if(pw1 != pw2) {
		document.getElementById("result").innerHTML = "Passwords do not match.";
	}
	else {
		document.getElementById("result").innerHTML = "Passwords match.";
	}
}


// Function to validate registration fields
function validate() {
	// Obtains the two passwords
    var pw1 = document.getElementById("password").value;
	var pw2 = document.getElementById("passwordConfirmation").value;

	if(pw1 != pw2) {
		document.getElementById("result").innerHTML = "Passwords do not match.";
	}
	else {
		register();
	}
}

// Function to save cookie from server info
function saveCookie(username) {
    var minutes = 20;
    var date = new Date();

    date.setTime(date.getTime()+(minutes*60*1000));

	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",username=" + username + ",userID=" + userID +  ";expires=" + date.toGMTString();
}

// Reads the cookie to verify user
function readCookie()
{
	var message = "Welcome ";
	var messageEnd = "!";
	userID = -1;
	var data = document.cookie;
	var splits = data.split(",");

	for(var i = 0; i < splits.length; i++) 
	{
		var thisOne = splits[i].trim();
		var tokens = thisOne.split("=");

		if(tokens[0] == "firstName") {
			firstName = tokens[1];
		}

		else if(tokens[0] == "lastName") {
			lastName = tokens[1];
		}
		else if(tokens[0] == "username") {
			username = tokens[1];
		}
		else if( tokens[0] == "userID" ) {
			userID = parseInt( tokens[1].trim() );
		}
	}
	
	if( userID < 0 ) {
		window.location.href = "index.html";
	}
	else {
		document.getElementById("welcome").innerHTML = message + firstName + " " + lastName + messageEnd;
		document.getElementById("displayUsername").innerHTML = username;
	}
}

// Function to log the user out
function logout() {
    userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

// Function to add a contact
function addContact() {
    var newContact = document.getElementById("contactText").value;
    document.getElementById("contactAddResult").innerHTML = "";

    var jsonPayload = '{"contact" : "' + newContact + '", "userID" : ' + userID + '}';
	var url = address + contactAddress + extension;

	var xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try	{
		xhr.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("addResult").innerHTML = "Contact has been added";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err) {
		document.getElementById("addResult").innerHTML = err.message;
	}
}

// Function to search contacts
function searchContacts() {
    var search = document.getElementById("searchText").value;
    document.getElementById("searchResult").innerHTML = "";

    var contactList = "";

    var jsonPayload = '{"search" : "' + search + '","userID" : ' + userID + '}';
	var url = address + '/SearchContacts' + extension;

    var xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
		xhr.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("contactSearchResult").innerHTML = "Contact(s) has been retrieved";
				var jsonObject = JSON.parse( xhr.responseText );
				
				for( var i=0; i<jsonObject.results.length; i++ ) {
					contactList += jsonObject.results[i];
					if( i < jsonObject.results.length - 1 )	{
						contactList += "<br />\r\n";
					}
				}
				
				document.getElementsByTagName("p")[0].innerHTML = contactList;
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err) {
		document.getElementById("contactSearchResult").innerHTML = err.message;
	}

}

// Function to register
function register() {
	// Obtain values from the form
	var username = document.getElementById("username").value;
	var password = document.getElementById("password").value;
	var firstName = document.getElementById("firstName").value;
	var lastName = document.getElementById("lastName").value;

	// Initializes result
    document.getElementById("result").innerHTML = "";

	// Initializes the json payload and loads the url
    var jsonPayload = '{"login" : ' + username + '", "password" : "' + password + '", "firstName" : "' + firstName + '", "lastName" : "' + lastName + '"}';
    var url = address + registerAddress + extension;

	// xhr = XMLHttpRequest
	var xhr = new XMLHttpRequest();
	// Initializes the newly created request
	xhr.open("POST", url, true);
	// Sets the value of the HTTP header (header, value)
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

	try {
        xhr.onreadystatechange = function() {
            // If request is finished and response is correct
            if(this.readyState == 4 && this.status == 200) {
                // Processes the JSON string and assigns server response
                // var jsonObject = JSON.parse(xhr.responseText);
                // Assigns the id from the obtained server response to the user id
                // userID = jsonObject.id;

				// Delete this
                // If id invalid, return
                // if(userID < 1) {
                //     document.getElementById("result").innerHTML = "Username or Password is invalid";
                //     return;
                // }

                // Redirects to index.html
                window.location.href = "index.html";
            }
        };

        // Sends the request to the server
        xhr.send(jsonPayload);
    }
    catch(err) {
        // Throws error message in id result
        document.getElementById("result").innerHTML = err.meessage;
    }
}
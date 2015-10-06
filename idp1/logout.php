<!--
Iraklis Symeonidis
Logout v 1.00: Logout the user from the IdP side

For the purpose of iMinds Project: Media ID

Developed in COSIC/ESAT, KU Leuven 2014
-->
<!DOCTYPE html>
<html lang="en">
<HEAD>
<meta http-equiv="Expires" content="Tue, 01 Jan 2013 12:12:12 GMT">
<meta http-equiv="Pragma" content="no-cache">
<title>Session Logout</title>

<BODY BGCOLOR="FFFFFF">
<?php
//Force the validation time of the cookie stored on the IdP side to 0 sec
//Due to the cookie perimission restrictions only the cookie that have been assigned to the particular user will expire
setcookie("_idp_session", 0, 0, "/idp", "", TRUE);
header("Content-type: text/html");
?>
    
    <h1>Session Logout</h1>
    <p>You have logged out of your application session.</p>
  </body>
</html>
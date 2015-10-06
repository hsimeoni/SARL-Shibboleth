<!--
Iraklis Symeonidis
SAML Revoked Assertions v 1.00:
Provides an overview of the revoked assertions
It displays the SAML id's, user names and the status of the assertions

For the purpose of iMinds Project: Media ID

Developed in COSIC/ESAT, KU Leuven 2014
-->
<html>
<head>

<title>Revoked Assertions List</title>
</head>

<body>
<h1>Revoked Assertions List</h1>
<p>SAML Revocation List for Shibboleth Assertions v_1.00</p>
<hr>

<?php
define("sar_list_debug",0, true); //Debug mode On in case of 1 and off when 0

shell_exec('./sarl_url_script_v1'); // Execute the Bash script. Is used to parse the log file and collect the necessary attributes
$file = file('sarl_url_script_file_v1'); // Save the attributes in a file

//Just the title
print "<h2>List of <font color='#006cb5'>Users</font> with their Revoked SAML assertions #ID's indicated by the Revocation Status (<font color=\"red\">Revoked</font>).</h2>";

$i = 0;
//Collect all the revocation destinations stored in the file
//This loop is searching for variant DB destinations and store only the different values in the $sarl_urls[] array -- memory space saving
foreach ($file as $key=>$value){
    if ($i==0){
        $sarl_urls[$i] = trim($value);
        $i++;
    }elseif (in_array(trim($value), $sarl_urls) != 1){ //Search whether a url already exists in the $salr_urls[] array
        $sarl_urls[$i] = trim($value); //Remove the character noise, stripping whitespace (or other characters) from the beginning and end of a string
        $i++;
    }
}
 if(sar_list_debug) print ("</br> saml id = ".$sarl_urls)."</br>";

//Make in each alteration a connetion to the individual revocation DB and print the content.
//The connection is closed in the end of each alteration
foreach($sarl_urls as $key=>$value){
    //Connect to the particular url where the Revocation DB is located
    //The login credentials should be stored once and not tranfered every time via the assertions. Also there shouldn't be available on LDAP
    mysql_connect($value,'root','cosic')or die(mysql_error());
    //The name of the DB should be sarl_db. It is good to have consistency. However, we can support several DB names with simple code improments
    mysql_select_db("sarl_db") or die(mysql_error());
    $results = mysql_query("SELECT uid,saml_id FROM sarl_id"); //Collect all the tupples store in the sarl_db
    
    $j=0;
    //mysql_fetch_array: Returns an array that corresponds to the fetched row and moves the internal data pointer ahead. 
    while ($row = mysql_fetch_array($results)){
        //print the results row by rows
        print "</br>[".$j."] User: <b><font color = '#006cb5'>".$row['uid']."</font></b> Saml id: <font color = 'red'>".$row['saml_id']."</font>";
        $j++;
    }
    mysql_close(); // Close the connection
}
?>
</body>
</html> 
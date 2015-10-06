<!--
Iraklis Symeonidis
SAML Revocation List Management v 1.00: 
Is an assertion revocation manager. 
It displays the SAML id's, user names and the status of the assertions
providing with the action of revocation

For the purpose of iMinds Project: Media ID

Developed in COSIC/ESAT, KU Leuven 2014
-->
<html>
<head>

<title>Sarl Manager</title>
</head>

<body>
<h1>Sarl Manager</h1>
<p>SAML Revocation Manager for Shibboleth Assertions v_1.00</p>
<hr>

<?php
define("sarl_mamager_debug",0, true); //Debug mode On in case of 1 and Off when 0

shell_exec('./saml_attr_script_v2'); // Execute the Bash script. Is used to parse the log file and collect the necessary attributes
$file = file('saml_attr_script_file_v2'); // Save the attributes in a file

//Just the title
print "<h2>List of <font color='#006cb5'>Users</font> with their SAML assertions #ID's indicated by the revocation Status (<font color=\"green\">Valid</font>/<font color=\"red\">Revoked</font>).</h2>";

print "<form method=\"post\">";

//$trimmed_values[] is a n*4 dimensional array.
//In each alteration each one of the tuples stored in the saml_attr_script_file_v2 folder are moved in a j*4 array where j is the number of the particular tuple (row)
//For each tuple the explode() function separates the attributes based on the column delimiter (':') and store the attribute in the j*i position 
//where j is the number of the particular tuple and i the position of the related attribute
$i = 0;
foreach ($file as $key=>$value){
    $trimmed_values[$i] = explode(":",$value); //explode — Split a string by the indicated column character (:)
    $i++;
}
if(sarl_mamager_debug){var_dump($trimmed_values);} //Debug mode: print the array contents of $trimmed_values

for($j=0;$j < count($trimmed_values);$j++){
    $uid = trim($trimmed_values[$j][0]);//Value j*0 from the $trimmed_values array (where j it the current tupple)is assigned to $uid variable
    if(sarl_mamager_debug) print ("</br> uid= ".$uid)."</br>";
    $saml_id = trim($trimmed_values[$j][1]);//Value j*1 from the $trimmed_values array is assigned to $uid variable
                                            //Trim: removes the character noise stripping  whitespace (or other characters) from the beginning and end of a string
    if(sarl_mamager_debug) print ("</br> saml id = ".$saml_id)."</br>";
    if(sarl_mamager_debug) print ("</br> http_url = ".trim($trimmed_values[$j][2])."</br>");
    
    //strpos — Find the position of the first occurrence of a substring (http) in a string
    if(strpos(trim($trimmed_values[$j][2]),"http") === FALSE){
        $sarl_url = trim($trimmed_values[$j][2]); //String "http" NOT found in a haystack. E.g., url format sarl.example.org
        print "*";
    }else{
        $sarl_url = explode("//",trim($trimmed_values[$j][3])); //String "http" found in a haystack. E.g., url format http://sarl.example.org.
                                                                //The explode function removes the "//" from the fourth attribute (//sarl.example.org)
                                                                //explode — Split a string by the indicated column string (//). The sarl.example.org is stored in the postion $sarl_url[1]
    }    
    //Connect to the DB indicated by the $sarl_url
    //The current version of the application supports various DB locations and is not limited to only one revocation DB location
    mysql_connect($sarl_url[1],'root','cosic')or die(mysql_error());
    mysql_select_db("sarl_db") or die(mysql_error());
    if(mysql_num_rows(mysql_query("select uid,saml_id from sarl_id where saml_id = '".$saml_id."'"))){//The SQL Query based on the SAM id value ($saml_id variable)
        //Creates a checkbox printing the revoked saml_ids
        print "<input type=\"checkbox\" name=\"options[1]\" value=\"".$uid.":".$saml_id.":".$sarl_url[1]."\"/> "."User <b><font color = '#006cb5'>".$uid."</font></b> Saml id: <font color = 'red'>".$saml_id."</font></br>";
    }  else {
        //Creates a checkbox printing the valid saml_ids
        print "<input type=\"checkbox\" name=\"options[2]\" value=\"".$uid.":".$saml_id.":".$sarl_url[1]."\"/> "."User <b><font color = '#006cb5'>".$uid."</font></b> Saml id: <font color = 'green'>".$saml_id."</font></br>";
    }
    mysql_close(); //Close the connection
 
}
//Print a submit value displaying "Revoked"
print "<hr></br><input type=\"submit\" value=\"Revoke\" name=\"submit\" />";
print "</form>";

print("<h2>Feedback</h2>");

if(isset($_POST['submit'])){ //When the submit button is pressed it excecute the following code
$checked = $_POST['options'];
    foreach ($checked as $key => $value){
        //The values are sepparated by the column (:) delimiter
        $checked_trimmed = explode(":", $value);
        $uid_revoke= $checked_trimmed[0];
        $saml_id_revoke= $checked_trimmed[1];
        $saml_url_revoke = $checked_trimmed[2];
        
        if(sarl_mamager_debug){print("saml_url_revoke = ".$saml_url_revoke);}
        //Connect to the particular url where the Revocation DB is located
        //The login credentials should be stored once and not tranfered every time via the assertions. Also there shouldn't be available on LDAP
        $connect_db=mysql_connect($saml_url_revoke,'root','cosic')or die(mysql_error());
        //The name of the DB should be sarl_db. It is good to have consistency. However, we can support several DB names with simple code improments
        mysql_select_db("sarl_db") or die(mysql_error());
               
        if(mysql_num_rows(mysql_query("select * from sarl_id where saml_id = '".$saml_id_revoke."'")) == 0 ){//Check wheter the SAML id that should be revoked existis in the database
            //Add the tupple uid and saml_id in the database with the status 1 at the field Revoked
            //Here we can add more cases for the status of the saml_id such as 2 for the suspended assertions extending this condition
            mysql_query("insert into sarl_id (uid,saml_id,Revoked) values ('".$uid_revoke."','".$saml_id_revoke."'".",1)") or die(mysql_error());
            //Inform that the actual action is finished
            print "The Saml ID: <b>".$saml_id_revoke."</b> for the user: <b><font color = '#006cb5'>".$uid_revoke."</font></b> has now been <font color ='red'>revoked</font>.</br>";
            header("Refresh:3");//Refresh the webpage after the 3 seconds       
        }else{
            //Inform that the actual action had already been performed
            print "The Saml ID: <b>".$saml_id_revoke."</b> for the user: <b><font color = '#006cb5'>".$uid_revoke."</font></b> has already been <font color ='red'>revoked</font>.</br>";
            header("Refresh:3");//The same
            
        }
        
    } 
}
?>
</body>
</html> 
<?php /*

 Torqace : Torque Interface

 Copyright (C) 2013, Ardhi Putra Pratama

 Torqace is based on the PBSWeb-Lite code written by Yuan-Chung Cheng.
 PBSWeb-Lite is based on the PBSWeb code written by Paul Lu et al.
 See History for more details.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 */
?>
<?php
	include_once ("config.php");
	include_once ("constant.php");
	
	if(isset($_POST['submit'])){
	    $uname  = $_REQUEST['uname'];
	    $pass = $_REQUEST['pass'];
	    $email = $_REQUEST['mail'];
		
		
		//echo $uname."\n";
		//echo $pass."\n";
		//echo $email."\n";
		$x = file_put_contents('/var/www/html/data/newuser', "$uname $pass $email".PHP_EOL, FILE_APPEND);
		if ($x) {
			echo "<h1>Registrasion Successful</h1>";
			echo "<h2>Please wait for email confirmation..</h2>";
		} else {
			echo "<h1>Registrasion Unsuccessful</h1>";
		}		
		echo "<a href='index.php'>Back to home</a>";
		//echo '<META HTTP-EQUIV=Refresh CONTENT="2; URL=index.php">';  
	} else {
		$daptar = shell_exec("/usr/local/sbin/listusers");
		$arr =  explode("\n", $daptar);
		//print_r($arr);
?>
<html>
	<head>
		<script language="JavaScript">
		
		<?php		
		if (sizeof($arr) > 0) {
			echo "var existing_user=new Array(" . sizeof($arr) . ");\n";
			$i = 0;
			foreach ($arr as $element) {
				echo "existing_user[$i]='$element';\n";
				$i = $i + 1;
			}
		}
		?>
			function validateForm(){
				var uname=document.forms["registform"]["uname"].value;
				var pass=document.forms["registform"]["pass"].value;
				var pass2=document.forms["registform"]["pass2"].value;
				var x=document.forms["registform"]["mail"].value;
				var invalidchars = " /\t\n()[]\\|*?$&`<>{}";
				if (uname==null || uname=="") {
  					alert("Username must be filled out");
  					return false;
  				}
  				for (var i=0; i < existing_user.length; i++) {
					if (existing_user[i] == uname) {
						alert("Username "+uname+" already exists, please use another one");
						return false;
					}
				}
				for (var i = 0; i < uname.length; i++) {
					var letter = uname.charAt(i);
					if (invalidchars.indexOf(letter) != -1) {
						alert("Username \""+uname+"\" contains invalid characters.");
						return false;
					}
				}
  				if (pass==null || pass=="") {
  					alert("Password must be filled out");
  					return false;
  				}
  				if (pass != pass2) {
  					alert("Password not match!")
  					return false;
  				}
  				
  				var atpos=x.indexOf("@");
				var dotpos=x.lastIndexOf(".");
				if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
  					alert("Not a valid e-mail address");
  					return false;
  				}
  				var domainName="ui.ac.id"
  				if(x.indexOf(domainName) > 0) {
                	if((x.indexOf(domainName) + domainName.length) == x.length){
                    	return true;
                	}
                }else {
                	alert('Please enter a valid email for : '+ domainName)
            		return false;
                }

            	return true;
            
			}
		</script>
	</head>
<body bgcolor="white">
<h1>
<img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_REGISTER; ?></h1>
<hr/>

<form method="post" action="" name="registform" onsubmit="return validateForm()" >
	Username: <input type="text" name="uname" /> <br/>
	Password: <input type="password" name="pass" /> <br/>
	Password (Again): <input type="password" name="pass2" /> <br/>
	Mail: <input type="email" name="mail" /> <br/>
	<input type="submit" name="submit" value="Submit"/> 
	<input type="reset" value="Clear" />
</form>
<hr/>
<?php include_once("footer.php"); 
echo "Only support email with *.ui.ac.id domain right now.";
echo "Contact <a href=\"mailto:" . $PBSWEBMAIL . "\">".$PBSWEBMAIL."</a> to create account." 
?>
</body>
</html>
<?php } ?>
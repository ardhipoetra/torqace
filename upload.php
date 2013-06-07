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

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");
include_once ("error.php");

auth_page();
$username = $_SESSION['username'];

$upload_ok = 1;
$has_plabel = 0;
$plabel_exist = 0;
$err_invalid_plabel = 0;
$err_conflict_plabel = 0;
$err_nofile = 0;

$err_fileNotMain = 0;

// Now we obtain $host,$plabel,$domake,$userfile
if (!isset($_POST['host']) || $_POST['host'] == "") {
	$host = $PBSWEBDEFAULTHOST;
} else {
	$host = $_POST['host'];
}

if (!isset($_POST['plabel']) || $_POST['plabel'] == "") {
	$upload_ok = 0;
	$has_plabel = 0;
} else {
	$has_plabel = 1;
	$plabel = str_replace("/", "-", $_POST['plabel']);
	$plabel = str_replace("*", "", $plabel);
	$plabel = str_replace("?", "", $plabel);
	$plabel = preg_replace("/\s/", "", $plabel);
	// white space is not welcome!
	// test again
	if ($plabel == "") {
		$upload_ok = 0;
		$err_invalid_plabel = 1;
	}
}

// prepare a list of existing labels
$ls_cmd = "ls ~/" . $PBSWEBUSERDIR;
$ls_result = `ssh -l $username $host '$ls_cmd; exit' 2>&1`;
$tmparray = explode("\n", $ls_result);
$i = 0;
$existing_plabels = array();
foreach ($tmparray as $element) {
	$element = trim($element);
	if ($element != "") {
		$existing_plabels[$i] = $element;
		$i = $i + 1;
		if ($plabel == $element) {
			$plabel_exist = 1;
			// file exists, test if confirmed
			if (!isset($_POST['overwrite']) || $_POST['overwrite'] != "Yes") {
				$upload_ok = 0;
				$err_conflict_plabel = 1;
			}
		}
	}
}

if (!isset($_FILES['userfile']['tmp_name']) || $_FILES['userfile']['tmp_name'] == "none") {
	$upload_ok = 0;
	if ($has_plabel == 1) {
		$err_nofile = 1;
	}
}

if (isset($_FILES['userfile']['name']) && strcmp($_FILES['userfile']['name'], "main.c") != 0 &&
	ereg(".c$",$_FILES['userfile']['name'])) {
	$upload_ok = 0;
	$err_fileNotMain = 1;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title> <?php echo $TITLE_UPLOAD; ?> </title>
</head>

<script language="JavaScript">
<!-- Hide from older browsers

<?php
if (sizeof($existing_plabels) > 0) {
	echo "var existing_plabels=new Array(" . sizeof($existing_plabels) . ");\n";
	$i = 0;
	foreach ($existing_plabels as $element) {
		echo "existing_plabels[$i]='$element';\n";
		$i = $i + 1;
	}
}
?>

	function FormSubmit() {
		var invalidchars = " /\t\n()[]\\|*?$&`<>{}";
		var plabelval='';
		<?php
			if (sizeof($existing_plabels) > 0) {
				echo "  var num_plabels=" . sizeof($existing_plabels) . ";\n";
				echo "  overwriteval=document.mainform.overwrite.checked;\n";
			}
		?>
		plabelval = document.mainform.plabel.value;

		if (plabelval == '') {
			alert('Program label is a required field, please try again.');
			return false;
		}
		for (var i = 0; i < plabelval.length; i++) {
			var letter = plabelval.charAt(i);
			if (invalidchars.indexOf(letter) != -1) {
				alert("Program label \""+plabelval+"\" contains invalid characters.");
				return false;
			}
		}

<?php
	if(sizeof($existing_plabels) > 0) {
?>
	if (!overwriteval) {
		for (var i = 0; i < num_plabels; i++) {
			if (plabelval == existing_plabels[i]) {
				alert("Program label \""+plabelval+"\" already exists, please use another label or submit with the overwrite box checked."); 
				return false;
			}
		}
	}
<?php } // PHP: if(sizeof($existing_plabels) > 0) ?>
	return true;
	}
	
	function ChangeFile() {
		var id = document.mainform.tipefile.selectedIndex;
		if (id == 0) {
			//jika berkas
			document.getElementById("makerow").style.display = "none";
			document.getElementById("argrow").style.display = "";
			document.getElementById("argprogtxt").innerHTML = "Program Argument(Not Compressed)";
			document.getElementById("infoCompress").style.display = "none";
			document.getElementById("infoArr").style.display = "none";
			// alert('hai');
		}
		else if(id == 1) {
			//jika compressed
			document.getElementById("makerow").style.display = "";
			document.getElementById("argrow").style.display = "none";
			document.getElementById("argprogtxt").innerHTML = "Program Argument";
			document.getElementById("infoCompress").style.display = "";
			document.getElementById("infoArr").style.display = "none";
			// alert('hai2');
		} else if(id == 2) {
			document.getElementById("makerow").style.display = "";
			document.getElementById("argrow").style.display = "";
			document.getElementById("argprogtxt").innerHTML = "Program Argument";
			document.getElementById("infoCompress").style.display = "";
			document.getElementById("infoArr").style.display = "";
			
		}
	}
	// Stop hiding -->
</script>

<body bgcolor="white">
<h1>
<img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_UPLOAD; ?></h1>

<?php /* Put standard navigation menu */
include ("navbar.php");
?>
<hr>
<?php
# phpinfo();
if($upload_ok == 0) {
// print the upload form
	if($err_invalid_plabel==1) {
		print("<p><font color=\"red\">");
		print("You must provide a continuous alpha-numeric string as the program label!");
		print("</font></p>\n");
	}
	if($err_conflict_plabel==1) {
		print("<p><font color=\"red\">");
		print("Program label exists, please provide another label or submit again with overwrite box checked to ");
		print("save the uploaded files under an old label (might overwrite files with the same name).");
		print("</font></p>\n");
	}
	if($err_nofile==1) {
		print("<p><font color=\"red\">");
		print("File upload error, maybe your file is too big.");
		print("</font></p>\n");
	}
	if($err_fileNotMain==1) {
		print("<p><font color=\"red\">");
		print("For MPI in C, the name of program must be main.c");
		print("</font></p>\n");
	}
?>
<FORM ENCTYPE="multipart/form-data" METHOD="POST" name="mainform" onSubmit="return FormSubmit();">
<select name="tipefile" onChange="javascript:ChangeFile()">
	<option value="file">Single File</option>
	<option value="compressed">Compressed Files</option>
	<option value="array">Array</option>
</select>	
<table><tr align="left"><th><?php echo $TITLE_UPLOAD; ?>:
<br>Warning:  Must be less than 50 MB!<br></th><td>
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="52428800"/>
<INPUT TYPE="hidden" name="host" value="<?php echo $PBSWEBDEFAULTHOST; ?>"/>
<INPUT NAME="userfile" TYPE="file"></td></tr>
<TR ALIGN="LEFT" id="argrow"><TH id="argprogtxt">Program Argument(Not Compressed)</TH>
	<TD><INPUT TYPE="file" NAME="argument"/>
		<div id="infoArr" style="display: none">Read about array arguments 
			<a href="help/help-arrInput.html">here</a></div></TD></TR>
	<TR ALIGN="LEFT"><TH>Enter a Label:<br></TH>
	<TD><INPUT TYPE="text" NAME="plabel">  (continuous alpha-numeric string)</TD></TR>
<TR ALIGN="LEFT" id="makerow" style="display: none"><TH>Make?<br></TH>
	<TD><INPUT TYPE="checkbox" NAME="domake" VALUE="Yes">Read about how to create Makefile  
		<a href="help/help-make.html">here</a>
	</td></tr>
<TR ALIGN="LEFT"><TH>Overwrite?<br></TH>
<TD><INPUT TYPE="checkbox" NAME="overwrite" VALUE="Yes">
<tr><td>
<br><INPUT TYPE="submit" name="operation" VALUE="Submit"></td></tr>
</table>
</FORM>
<p id="infoCompress" style="display: none">Note: archive in the format of .tgz/.tar.gz/.tar/.zip is supported.</p>

<?php
} else {
// upload_ok==1, we got the upload file!

$domake = $_POST['domake']; 
$tipe = $_POST['tipefile']; 
$userfile_name = $_FILES['userfile']['name'];
$argument_name = ($tipe != "compressed") ? $_FILES['argument']['name'] : "" ;
$uploadfile = $PBSWEBTEMPUPLOADDIR . "/" . $username . "/" . $userfile_name;
$argumentfile = ($tipe != "compressed") ? $PBSWEBTEMPUPLOADDIR . "/" . $username . "/" . $argument_name 
									: "" ;
$dest_dir = $PBSWEBUSERDIR . "/" . $plabel;
$dest_file = $dest_dir . "/" . $userfile_name;
$dest_address = $username . "@" . $host . ":~/" . $dest_file;
$arg_address = $username . "@" . $host . ":~/" . $dest_dir . "/" . $argument_name;

if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
	print "Possible file upload attack!  Here's some debugging info:\n";
	print_r($_FILES);
} else {
	$contents = file_get_contents($uploadfile);
	
	//hindari pemanggilan syscall exec dan system (pada C)
	$pattern = "/\bexec\(|\bsystem\(/i";
	if (preg_match($pattern, $contents)) {
		echo "contains exec or system call";
		$restrict = true;
	}
	
}
if (!empty($argument_name) && !move_uploaded_file($_FILES['argument']['tmp_name'], $argumentfile)) {
	print "Error in Uploading Argument:\n";
	print_r($_FILES);
}

echo "<h2>Upload Results</h2>";
echo "<table border=\x220\x22>";
echo "<tr><th align=\x22LEFT\x22>Filename:</th><td>$userfile_name</td></tr>";
echo "<tr><th align=\x22LEFT\x22>Label:</th><td>$plabel</td></tr>";
echo "<tr><th align=\x22LEFT\x22>Project Type:</th><td>$tipe</td></tr>";
echo "<tr><th align=\x22LEFT\x22>Host:</th><td>$host</td></tr></table>";

chmod($uploadfile,0644);
if (!$restrict) {
	if($plabel_exist == 1) {
	// nothing to do for a pre-existing dir, but
	// we will need to overwrite a regular file
		if($first=="-") {
			$mkdir = `ssh -l "$username" "$host" 'rm -f ~/$dest_dir; mkdir ~/$dest_dir; exit' 2>&1`;
		}
	} else {
		$mkdir = `ssh -l "$username" "$host" 'mkdir -p ~/$dest_dir/input; exit' 2>&1`;
	}
	echo "<pre>$mkdir</pre>";
	if ($tipe != "compressed" && !empty($argument_name)) {
		$securecopy = `scp "$argumentfile" "$arg_address" 2>&1`;
	}
	$securecopy = `scp "$uploadfile" "$dest_address" 2>&1`;
	echo "<pre>$securecopy</pre>";
	// copy finished, remove the temporary file
	unlink($uploadfile);
	
	# add properties
	$checkgrep = "grep \"^".$plabel."\\s\" .torqace";
	$checkgrep = `ssh -l "$username" "$host" 'cd ~/$PBSWEBUSERDIR; $checkgrep ;exit' 2>&1`;
	$checkgrep = trim($checkgrep);
	if ($checkgrep == "") {
		$comm = 'echo "'.$plabel.' '.$tipe.'"';
		$comm = `ssh -l "$username" "$host" 'cd ~/$PBSWEBUSERDIR; $comm >> .torqace ;exit' 2>&1`;
	} else {
		$comm = "sed \"s/$checkgrep/$plabel $tipe/g\" .torqace";
		$comm = `ssh -l "$username" "$host" 'cd ~/$PBSWEBUSERDIR; $comm > .torqace_;rm .torqace;mv .torqace_ .torqace; exit' 2>&1`;
	}
} else {
	echo "<pre>Upload Failed</pre>";
}

# By JYJ 20040105: handle the uploaded file more gracefully; we
# now handle it based on .tar/.tgz/.tar.gz/.zip
if (ereg(".tar.gz$",$userfile_name) || ereg(".tgz$",$userfile_name)) {
	$untarlist = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -ztf $userfile_name;exit' 2>&1`;
	$untar = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -zxvf $userfile_name;exit' 2>&1`;
	$remove_userfile =`ssh -l "$username" "$host" 'cd ~/$dest_dir; rm -f $userfile_name;exit' 2>&1`;
} elseif (ereg(".tar$",$userfile_name)) {
	$untarlist = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -tf $userfile_name;exit' 2>&1`;
	$untar = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -xvf $userfile_name;exit' 2>&1`;
	$remove_userfile =`ssh -l "$username" "$host" 'cd ~/$dest_dir; rm -f $userfile_name;exit' 2>&1`;
} elseif (ereg(".zip$",$userfile_name)) {
	$untarlist = `ssh -l "$username" "$host" 'cd ~/$dest_dir; zipinfo -1 $userfile_name;exit' 2>&1`;
	$untar = `ssh -l "$username" "$host" 'cd ~/$dest_dir; unzip -o $userfile_name;exit' 2>&1`;
	$remove_userfile =`ssh -l "$username" "$host" 'cd ~/$dest_dir; rm -f $userfile_name;exit' 2>&1`;
} else {
	// don't know how to decompress the file, keep it untouched
	$untarlist = $userfile_name;
	$untar = "None";
}


echo "<p><b>Files extracted:</b><br><pre>$untar</pre></p>";

$tarfiles = explode("\n",$untarlist);
$plabel_path=$tarfiles[0];
if ((!(ereg("/$",$plabel_path)))||($plabel_path=="./")) {
	$plabel_path="";
}
echo "<p>$plabel_path</p>";
// arguments zipped (array only)
if ($tipe == "array") {
	if (ereg(".tar.gz$",$argument_name) || ereg(".tgz$",$argument_name)) {
		$untarlist_a = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -ztf $argument_name;exit' 2>&1`;
		$untar_a = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -zxvf $argument_name -C input;exit' 2>&1`;
		$remove_userfile =`ssh -l "$username" "$host" 'cd ~/$dest_dir; rm -f $argument_name;exit' 2>&1`;
	} elseif (ereg(".tar$",$argument_name)) {
		$untarlist_a = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -tf $argument_name;exit' 2>&1`;
		$untar_a = `ssh -l "$username" "$host" 'cd ~/$dest_dir; tar -xvf $argument_name -C inputl;exit' 2>&1`;
		$remove_userfile =`ssh -l "$username" "$host" 'cd ~/$dest_dir; rm -f $argument_name;exit' 2>&1`;
	} elseif (ereg(".zip$",$argument_name)) {
		$untarlist_a = `ssh -l "$username" "$host" 'cd ~/$dest_dir; zipinfo -1 $argument_name;exit' 2>&1`;
		$untar_a = `ssh -l "$username" "$host" 'cd ~/$dest_dir; unzip -d input -o $argument_name;exit' 2>&1`;
		$remove_userfile =`ssh -l "$username" "$host" 'cd ~/$dest_dir; rm -f $argument_name;exit' 2>&1`;
	}
	echo "<p><b>Arguments extracted:</b><br><pre>$untar_a</pre></p>";
} 


$tarfiles = explode("\n",$untarlist_a);
$plabel_path=$tarfiles[0];
if ((!(ereg("/$",$plabel_path)))||($plabel_path=="./")) {
	$plabel_path="";
}

echo "<p>$plabel_path</p>";

if ($domake == "Yes") {
	$make = `ssh -l "$username" "$host" 'cd ~/$dest_dir; make;exit' 2>&1`;
	echo "<p><b>make output:</b><br><pre>$make</pre></p>";
}

echo "<p>\n";
echo "<b>Suggested Next Step :</b>\n";
if (!$restrict) {
	echo " <a href=\"scriptcreate.php?directory=/$plabel&host=$host\">".$TITLE_SCRIPTGEN."</a> | ";
}
echo "<a href=\"mainmenu.php\">". $TITLE_MAINMENU . "</a>\n";
echo "</p>\n";

} // end if(upload...)
?>

<hr>
<?php include_once("footer.php"); ?>
<!-- $Id: upload.php,v 1.20 2004/03/18 21:04:19 platin Exp $ -->
</body>
</html>

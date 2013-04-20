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

include_once("config.php");

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME,$PBSWEBPATH);
session_start();
setcookie(session_name(),session_id(), time()+$PBSWEBEXPTIME, $PBSWEBPATH);

include_once("auth.php");

auth_page();

$username=$_SESSION['username'];

$host=$_GET['host'];
$filename=str_replace("/../","/","/" . $_GET['filename']);
$filename=str_replace("*","", $filename);
$filename=str_replace("?","", $filename);
$filename=preg_replace("/\s/", "", $filename); // white space is not welcome!
$filename=preg_replace("/\/+$/", "", $filename);
$filename=preg_replace("/^\/+/", "", $filename);
if(strpos($filename,"/")) {
	$dirup=preg_replace("/\/.*$/", "", $filename);
} else {
	$dirup="";
}

if(!isset($_GET['confirmed']) || $_GET['confirmed'] != "YES") {
// ask user to confirm this action
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head></head>
	<body bgcolor="white">
		<script language="JavaScript">
			if (confirm("Are you absolutely sure you want to delete <?php print("$filename on $host"); ?> ?"))
				location.replace(' <?php print("filedel.php?confirmed=YES&filename=$filename&host=$host"); ?> ');
			else
				location.replace('<?php print("dirview.php?host=$host&dir=$dirup"); ?>');
		</script>
	</body>
</html>
<?php
exit();
} // end the confirm if

// The action has been confirmed, proceed to delete the file
$filedel_cmd="rm -rf ~/" . $PBSWEBUSERDIR . "/" . $filename;
$filedel_result = `ssh -l $username $host "$filedel_cmd; exit" 2>&1`;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>File Deleted</title>
	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php print("$host: $filename"); ?>
		has been deleted</h1>
		</hr>
		<?php
		include ("navbar.php");
	?>
		<hr>
	<?php 
		print("<pre>$qdel_result</pre>");

		echo "<p>\n";
		echo "<a href=\"dirview.php?host=$host&dir=$dirup\">Back</a>";
		echo "</p>\n";
	?>

		<hr>
		<p>Send questions and comments to
	<?php 
		echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
		echo $PBSWEBMAIL . "</a>\n";
	?>
		You can find <a href='help.html'>help here </a>.</p>
		<script language="JavaScript">
			location.replace("<?php print("dirview.php?host=$host&dir=$dirup"); ?> ");
		</script>
		<!-- $Id: filedel.php,v 1.9 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>

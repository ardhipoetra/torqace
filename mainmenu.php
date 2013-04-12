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
if (isset($_POST['loginnow']) && $_POST['loginnow'] == "true") {
	# from the login page, renew the session; i.e. logout!
	session_start();
	setcookie($PBSWEBNAME, '', time() - 86400, $PBSWEBPATH);
	unset($_COOKIE[session_name()]);
	$_SESSION = array();
	session_destroy();
}
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");

if (!session_is_vaild()) {
	// login!
	auth_login($_POST['username'], $_POST['password']);
} else {
	$username = $_SESSION['username'];
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title><?php echo $TITLE_MAINMENU; ?></title>
	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_MAINMENU; ?></h1>
		<hr>
		<table width="100%">
			<tr>
				<td>
				<table border="5" cellpadding="6">
					<tr>
						<td height="400" width="400" valign="top"><font size="+2"> <img src="img/upload.png" height="32" width="32" alt="Upload Berkas"> <a href="upload.php"><?php echo $TITLE_UPLOAD; ?></a>
							<br>
							<br>
							<!--
							<img src="img/compile.gif" height="32" width="32" alt="Compile Files">
							<a href="dirselect.php?action=compile">Compile Uploaded Files</a><br><br>
							<img src="img/script.gif" height="32" width="32" alt="Script Generation and Job Submission">
							<a href="dirselect.php?action=script">Script Generation and Submission</a><br><br>
							--> 
							<img src="img/info.png" height="32" width="32" alt="Info Queue">
							<a href="qstat.php"><?php echo $TITLE_QSTAT; ?></a><br><br>
							<img src="img/folder.png" height="32" width="32" alt="Lihat Berkas"> <a href="dirview.php"><?php echo $TITLE_VIEWDIR; ?></a>
							<br>
							<br>
							<img src="img/exit.png" height="32" width="32" alt="Logout"> <a href="logout.php">Logout</a>
							<br>
							<br>
						</font></td>
					</tr>
				</table></td>
				<td>
				<table border="5" cellpadding="6" >
					<tr>
						<td height="460" width="400">
						<center>
							<img src="<?php print($PBSWEBMAINLOGO); ?>" border="0" height="450" width="390" alt="Main Page Logo">
						</center></td>
					</tr>
				</table></td>
			</tr>
		</table>
		<hr>
		<? include_once("footer.php"); ?>
		<!-- $Id: mainmenu.php,v 1.22 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>

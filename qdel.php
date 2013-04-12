<?php /*

 PBSWeb-Lite: A Simple Web-based Interface to PBS

 Copyright (C) 2003, 2004 Yuan-Chung Cheng

 PBSWeb-Lite is based on the PBSWeb code written by Paul Lu et al.
 See History for more detailes.

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

// GET interface
$jobid=$_GET['jobid'];
$host=$_GET['host'];

if(!isset($_GET['confirmed']) || $_GET['confirmed'] != "YES") {
// ask the user to confirm this action
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head></head>
	<body bgcolor="white">
		<script language="JavaScript">
			if (confirm("Are you absolutely sure you want to cancel job <?php print($jobid); ?> ?"))

			// Passing PHPSESSID on the URL is necessary here because the session
			// does not follow a location.replace or an assignment to location
			// when cookies are turned off.  It's dirty, but it works :-)
			location.replace("<?php print("qdel.php?confirmed=YES&jobid=$jobid&host=$host"); ?>");
	else
	location.replace("<?php print('qstat.php'); ?> ");
		</script>
	</body>
</html>
<?php
exit();
} // end the confirm if

if (!isset($PBSWEBHOSTLIST[$host]["qdel"]) || $PBSWEBHOSTLIST[$host]["qdel"] == "") {
	error_page("Failed retrieving qdel command");
	exit();
} else {
	$qdel_cmd = $PBSWEBHOSTLIST[$host]["qdel"];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Job Dequeued</title>
	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo">Job <?php print($jobid); ?> has been removed from the queue </h1>
		</hr>
		<?php
		include ("navbar.php");
		?>
		<hr>
		<?php
		$qdel_result = `ssh -l $username $host "$qdel_cmd $jobid; exit" 2>&1`;
		print(" 		<pre>$qdel_result</pre>");
	?>


		<p>
			<a href="qstat.php">Back to Queue Status</a>
		</p>
		<hr>
		<p>
			Send questions and comments to
			<?php
				echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
				echo $PBSWEBMAIL . "</a>\n";
			?> You can find <a href='help.html'>help here </a>.
		</p>
		<!-- $Id: qdel.php,v 1.9 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>

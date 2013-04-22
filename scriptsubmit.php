<?php
/*

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
include_once ("pbsutils.php");

auth_page();
$username = $_SESSION['username'];

// need to get host and directory from POST
if (!(isset($_POST['host']) && isset($_POST['directory']))) {
	error_page("Error: no host and directory specified.");
	exit();
} else {
	$host = $_REQUEST['host'];
	$directory = $_REQUEST['directory'];
}

// setup the $jobinfo array, see pbsutils.php for format
$jobinfo = array();
$jobinfo['mail'] = $username . "@" . "$host";
$jobinfo['maxtime'] = "00:00:00";

// collect data from $_POST
pbsutils_collect($jobinfo, $_POST);

// create local job file, then copy to server
$jobfile = $jobinfo['name'] . ".pbs";
$localfile = $PBSWEBTEMPUPLOADDIR . "/" . $username . "/" . $jobfile;
pbsutils_save($localfile, $jobinfo);
$remote_file = $username . "@" . $host . ":~/";
$remote_file .= $PBSWEBUSERDIR . "/" . $directory . "/" . $jobfile;
$scp_result = `scp "$localfile" "$remote_file" 2>&1`;

$jobscript_str = pbsutils_script($jobinfo);

// submit the job and get the jobid
$qsub_cmd = $PBSWEBHOSTLIST[$host]['qsub'];
if ($qsub_cmd == "") {
	error_page("Error: can't retrieve command for qsub.");
	exit();
}

if ($jobinfo['type'] == "array") {
	$qsub_cmd .= " -t ".$_POST['arroption'];
}

$qsub_result = `ssh -l $username $host 'cd ~/$PBSWEBUSERDIR/$directory; $qsub_cmd $jobfile; exit' 2>&1`;
$tmparray = explode(".", trim($qsub_result));
$jobid = $tmparray[0];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title><?php echo $TITLE_SCRIPTVIEW; ?></title>
	</head>

	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_SCRIPTVIEW; ?></h1>
		<?php
		include ("navbar.php");
	?>
		<hr>

		<?php 
		//echo "<pre>";
		//print_r($jobinfo);
		//print_r($_POST);
		//echo "</pre>";

		echo "<p><b>Job Name:</b> " . $jobinfo['name'] . "</p>\n";
		echo "<p><b>Server:</b> $host</p>\n";
		echo "<p><b>Directory:</b> $directory</p>\n";
		echo "<p><b>Torque Job ID:</b> $jobid</p>\n";
		echo "<p><b>Job Script Filename:</b> $jobfile</p>\n";
		echo "<p><b>Job Script Contents:</b>\n";
		echo "<pre>\n";
		echo "$jobscript_str\n";
		echo "</pre>\n";
		echo "<br><br>\n";
		echo "<p>\n";
		echo "<b>Suggested Next Step:</b>  <a href=qstat.php?host=$host>View Queue Status</a>";
		echo "</p>\n";
		?>
		<hr>
		<?php
			include ("footer.php");
		?>
		<!-- $Id: scriptsubmit.php,v 1.6 2004/03/18 21:04:19 platin Exp $ -->

	</body>
</html>

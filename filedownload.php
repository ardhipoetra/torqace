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
include_once ("config.php");

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");
include_once ("error.php");

auth_page();

$username = $_SESSION['username'];

// GET interface
$host = $_GET['host'];
$filename = str_replace("/../", "/", "/" . $_GET['filename']);
$filename = str_replace("*", "", $filename);
$filename = str_replace("?", "", $filename);
$filename = preg_replace("/\s/", "", $filename);
// white space is not welcome!
$filename = preg_replace("/\/+$/", "", $filename);
$filename = preg_replace("/^\/+/", "", $filename);
$filebasename = basename($filename);
if (strpos($filename, "/")) {
	$dirup = preg_replace("/\/.*$/", "", $filename);
} else {
	$dirup = "";
}

// check the type of the file, and prepare the download link.
$ls_cmd = "ls -ld ~/" . $PBSWEBUSERDIR . "/" . $filename;
$ls_result = `ssh -l $username $host '$ls_cmd; exit' 2>&1`;
$first = $ls_result{0};

if ($first != "-" && $first != "d") {
	// not a regular file or directory, exit
	error_page("File \"$filename\" not found: $ls_result");
	exit();
}

// scp the file/dir to the temp directory
$source = $username . "@" . $host . ":~/" . $PBSWEBUSERDIR . "/" . $filename;
$dest = $PBSWEBTEMPDOWNLOADDIR . "/" . $username;
$localfile = $dest . "/" . $filebasename;
clearstatcache();
if (!file_exists($PBSWEBTEMPDOWNLOADDIR)) {
	mkdir($PBSWEBTEMPDOWNLOADDIR, 0755);
}
if (!file_exists($dest)) {
	mkdir($dest, 0755);
}
$scp_result = `rm -rf $localfile; scp -r "$source" "$localfile" 2>&1`;

if (!file_exists($localfile)) {
	error_page("File copy error: $scp_result.");
	exit();
}
if ($first == "-") {
	// regular file, simple
	$filelink = $localfile;
} else {
	// directory, we tar it and them provide the link for .tar.gz file
	$tar_result = `cd $dest; rm -f "$filebasename.tar.gz"; tar cfz "$filebasename.tar.gz" $filebasename`;
	$filelink = $dest . "/" . $filebasename . ".tar.gz";
	if (!file_exists($filelink)) {
		error_page("Error when preparing the package: $tar_result.");
		exit();
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
	<HEAD>
		<!-- Let JavaScript do the automatic location change for us -->
		<!-- <META http-equiv="Refresh" content="0; URL=/"> -->
	</HEAD>
	<BODY>
		<CENTER>
			<H2>Downloading file ... </H2>

			<BR>
			<BR>
			<SCRIPT language="JavaScript">
location.replace("<?php print($filelink); ?>
				");
			</SCRIPT>
			<A href="<?php print($filelink); ?>">Click here to download manually</A>
		</CENTER>
	</BODY>
</HTML>

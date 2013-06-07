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

// Functions used to auth login and page in the session.

include_once ("error.php");

// test username and password using spasswd program.
function auth_login($username, $password) {
	include ("config.php");

	if (($fh = popen("/usr/sbin/spasswd", "w"))) {
		fputs($fh, "$username $password");
		$r = pclose($fh);
		if ($r == 0) {
			// success, register the session variable
			$_SESSION['username'] = $username;

			// also initialize pbsweb tmp dir and so on..
			$tmpdir = $PBSWEBTEMPDOWNLOADDIR . "/" . $username;
			clearstatcache();
			if (file_exists($tmpdir)) {
				$run = `rm -rf $tmpdir`;
			}
			if (!file_exists($PBSWEBTEMPDOWNLOADDIR)) {
				mkdir($PBSWEBTEMPDOWNLOADDIR, 0755);
			}
			if (!file_exists($tmpdir)) {
				mkdir($tmpdir, 0755);
			}

			$tmpdir = $PBSWEBTEMPUPLOADDIR . "/" . $username;
			clearstatcache();
			if (file_exists($tmpdir)) {
				$run = `rm -rf $tmpdir`;
			}
			if (!file_exists($PBSWEBTEMPUPLOADDIR)) {
				mkdir($PBSWEBTEMPUPLOADDIR, 0700);
			}
			if (!file_exists($tmpdir)) {
				mkdir($tmpdir, 0700);
			}
			
			$gr = shell_exec("id -G $username | grep 504");
			if (isset($gr)) {
				$_SESSION['isadmin'] = 1;
			} else {
				$_SESSION['isadmin'] = 0;
			}

			return 0;
		} else {
			// failed!
			error_page("Permission denied, please try again.");
			exit();
		}
	} else {
		// error on calling spasswd...
		error_page("Internal program error.");
		;
		exit();
	}
}

// test if the session is vaild
function session_is_vaild() {
	// FIXME: only test $_SESSION['username'] now
	return (isset($_SESSION['username']) && $_SESSION['username'] != "");
}

function auth_page() {
	if (!session_is_vaild()) {
		// failed!
		error_page("Session non-exist or expired, please login again and make sure to turn on the cookie support.");
		exit();
	}
	return 0;
}

// $Id: auth.php,v 1.5 2004/03/18 21:04:19 platin Exp $
?>

<?php
/*

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
  session_start();
  setcookie(session_name(),session_id(), time()-86400, $PBSWEBPATH);

  unset($_COOKIE[session_name()]);
  // Unset all of the session variables.
  $_SESSION = array();
  // Finally, destroy the session.
  session_destroy();

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<!-- Let JavaScript do the automatic location change for us -->
<!-- <META http-equiv="Refresh" content="0; URL=/"> -->
</HEAD>
<BODY>
<CENTER>
<H2>Logging you out ... </H2>

<BR>
<BR>
<SCRIPT language="JavaScript">
	location.replace("<?php print($PBSWEBPATH); ?>");
</SCRIPT>
<A href="<?php print($PBSWEBPATH); ?>">Click here to continue</A>
</CENTER>
</BODY>
</HTML>

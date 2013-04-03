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

function error_page($string)
{
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
<title>PBSWeb-Lite Error Page</title>
</head>
<body bgcolor="white">
<h1> </h1>
<p><br>
</p>
<table border="5"
 style="text-align: left; margin-left: auto; margin-right: auto; width: 80%; height: 80%;">
  <tbody>
    <tr>
      <td style="vertical-align: top;">
      <table
 style="text-align: left; height: 100%; width: 90%; margin-left: auto; margin-right: auto;">
        <tbody>
          <tr>
            <td
 style="text-align: center; vertical-align: top; height: 20%;"><br><big><big><big><big><span
 style="color: rgb(153, 0, 0);">An Error Just Happened.<br>
            </span></big></big></big></big></td>
          </tr>
          <tr>
            <td style="vertical-align: top; height: 65%;">
            <div style="margin-left: 80px;"><big><big><small><br>
<?php print($string); ?>
</small><br>
            </big></big></div>
            </td>
          </tr>
          <tr>
            <td style="vertical-align: middle;">
Send questions and comments to 
<?php
echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
echo $PBSWEBMAIL . "</a>\n";
?>
</td>
          </tr>
        </tbody>
      </table>
      <br>
      </td>
    </tr>
  </tbody>
</table>
<p><br>
</p>
</body>
</html>
<?php
}
?>


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

############################################################
#
# Configurations, please modify the following constants
# according to your system configurations.
#
############################################################

# Hostname of the default resource provider for Torqace to access
$PBSWEBDEFAULTHOST = "hastinapura";

# List of all resourse providers and corresponding
# PBS commands, the format is
#  "hostname" => array ("qstat"=>"qstat_command" ,
#                       "qsub"=>"qsub_command",
#                       "qdel"=>"qdel_command"......
$PBSWEBHOSTLIST = array("hastinapura" => array("qstat" => "/opt/torque/bin/qstat", 
										"qsub" => "/opt/torque/bin/qsub", "
										 qdel" => "/opt/torque/bin/qdel", 
										 "max_nodes" => 14, "max_ppn" => 2));

# List of all queues on each resourse provider; use the
# name of queues as the keys and the corresponding max_walltime
# as the contents.
$PBSWEBQUEUELIST = array("hastinapura" => array("serial"  =>	"48:00:00",
						"medium"  =>	"18:00:00",
						"short"	  =>	"06:00:00",
						"long"    =>	"30:00:00",
						"riset"	  =>	"9999:00:00"));

# List of PBS Jobscript template files; label => template file
# Remember to modify those template files according to your PBS
# and system configurations.
$PBSWEBTEMPLATELIST = array("plain" => "template/template_plain.pbs", 
										"mpich" => "template/template_mpich.pbs", 
										"openmpi" => "template/template_openmpi.pbs");

# Directory on the system where Torqace is installed.
$PBSWEBDIR = "/var/www/html/torqace";

# Directory used to stage the file uploaded using PBSWeb.
# The directory should be writable by httpd; we suggest
# to set the permission of the directory as 700.
$PBSWEBTEMPUPLOADDIR = "/var/www/html/torqace/upload";

# The path to the root of the Torqace directory on URL,
# the tailing '/' is necessary.
$PBSWEBPATH = "/torqace/";

# Who to contact when there is a problem...
$PBSWEBMAIL = "chpc@cs.ui.ac.id";

# Logo used in the webpage
$PBSWEBHEADERLOGO = "img/japan.gif";
$PBSWEBMAINLOGO = "img/fasilkom.jpg";

############################################################
# Global Constants for Torqace, you don't really need
# to modify the following constants.
############################################################

# Directory used by Torqace at the Resource Provider
# For example, if user "george" wants to use Torqace with their
# account at machine "aurora.nic.ualberta.ca", then george must
# have a directory called "~/pbsweb" (a symbolic link is also OK).
# This directory has to be created beforehand.
$PBSWEBUSERDIR = "pbsweb";

# about the session cookie
$PBSWEBNAME = "PBSWeb";
$PBSWEBEXPTIME = 3600;

# A relative path to $PBSWEBDIR where files-to-be-downloaded will
# be staged.
$PBSWEBTEMPDOWNLOADDIR = "download";

// $Id: config.php,v 1.14 2004/03/19 03:42:21 platin Exp $
?>

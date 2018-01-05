<?php
/*
    dpi_admin.php
    (C) 2018 by Jose Solares (jsolares@codevoz.com)

    This file is part of numerocentral.

    numerocentral is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    NumeroCentral is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with NumeroCentral.  If not, see <http://www.gnu.org/licenses/>.

    Download the ID on file for the user.
*/

include 'prepend_admin.php';

$userid = $user->requireAuthentication( "displayLogin" );

getpost_ifset(("file"));
$path = "/var/spool/asterisk/dpi";

if ( file_exists("$path/$file.pdf") ) {

$filename = str_replace("/","-",$file) .".pdf";

header('Vary: User-Agent');
header("Content-Type: application/pdf"); 
header("Content-Disposition: attachment; filename=\"" . $filename. "\";"); 
header("Content-Length: ".filesize("$path/$file.pdf")); 
header("Pragma: cache"); 
header("Expires: 0"); 
header("Cache-Control: private");
header("Content-Transfer-Encoding: binary\n");

$fp=fopen("$path/$file.pdf","r"); 
print fread($fp,filesize("$path/$file.pdf")); 
fclose($fp); 
}
exit();
?>

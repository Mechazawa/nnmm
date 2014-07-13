<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <Mega{at}IOException.at> wrote this file. As long as you retain this notice
 * you can do whatever you want with this stuff. If we meet some day, and you
 * think this stuff is worth it, you can buy me a beer in return
 * ----------------------------------------------------------------------------
 */

/* Variables */
$user = "username"; //Database username
$pass = "password"; //Database password
$dbname = "nnmm"; //Database name
$idlen = 3; //Length of the id's


header("content-type: text/plain; charset=UTF-8; imeanit=yes");
header("X-Content-Type-Options: nosniff");

$protocol=empty($_SERVER['HTTPS'])?"http":"https";
$queryStr=$_SERVER['QUERY_STRING'];
$pasteid=preg_replace("/[^a-zA-Z0-9]/", "", $queryStr);
$pastedata=$_SERVER["REQUEST_METHOD"] == "POST" ? 
                urldecode(str_replace("+", "%2B", file_get_contents('php://input'))) : false;

$db = new PDO("mysql:dbname=$dbname;host=127.0.0.1", $user, $pass);

$reUrl='/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\]])|(?:%[\da-fA-F]{2}))*$/';
$validchars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
$sql = "SELECT data from pastes where id = ?";

if ($pasteid) {
    if(strlen($pasteid) > $idlen || strlen($pasteid) <= 1)
        die(header("HTTP/1.0 414 Request-URI Too Long"));
    
    $st = $db->prepare($sql);
    $st->execute(array($pasteid));
    $res = $st->fetchAll();
    
    if (count($res) == 1) {
        if(preg_match($reUrl, $res[0][0]) == 1 && substr($queryStr, -1) != "!") {	
            header("location: ".trim($res[0][0]));
        } else {
            print $res[0][0];
        }
    } else {
        die(header("HTTP/1.0 404 Not Found"));
    }

} elseif($_SERVER["REQUEST_METHOD"] == "POST") {
    $st = $db->prepare($sql);
    $valid = False;
    while(!$valid) {
        $str = substr(str_shuffle($validchars), 0, 5);
        $st->execute(array($str));
        $valid = count($st->fetchAll()) == 0;
    }
    $ins = $db->prepare("INSERT INTO `pastes` (`id`, `data`) VALUES (?, ?)");
    $ins->execute(array($str, $pastedata));
    print "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?$str";

} else { ?>
NAME
    nnmm - nnmm stands for nothing

SYNOPSIS 
    Python
        pasteurl = urllib2.urlopen("http://nnmm.nl/", <data>).read()
    Bash
        <command> | curl --data-urlencode @- nnmm.nl 

DESCRIPTION
    Just post any data to this server and it'll give you a "paste" link.
    If the data is an url (ex "http://example.com/") it will return the
    same kind of url but it will instead be a shortened url. This means
    that it will redirect instead of show the data. This can be stopped 
    by adding an ! at the end of the url.
    
SEE ALSO
    The current source code can be found at http://nnmm.nl/s.php
    The git repo can be found at https://github.com/Mechazawa/nnmm 
<?php
}


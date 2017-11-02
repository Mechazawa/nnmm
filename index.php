<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <Mega{at}IOException.at> wrote this file. As long as you retain this notice
 * you can do whatever you want with this stuff. If we meet some day, and you
 * think this stuff is worth it, you can buy me a beer in return
 * ----------------------------------------------------------------------------
 */

/* Start config */
$user = "username"; //Database username
$pass = "password"; //Database password
$database = "nnmm"; //Database name
$table = "pastes";  //Table name
$idlen = 3; //Length of the id's
/* End config */

// All the data sent is plain text, imeanit=yes is a Firefox quirk
header("content-type: text/plain; charset=UTF-8; imeanit=yes");
header("X-Content-Type-Options: nosniff");
header("Content-Disposition: inline");

// CORS headers
header("Access-Control-Allow-Origin: *")
header("Access-Control-Allow-Methods: GET, POST")
header("Access-Control-Allow-Headers: Content-Type")
header("Access-Control-Expose-Headers: Content-Type")
header("Access-Control-Max-Age: 600")

$protocol = empty($_SERVER['HTTPS']) ? "http" : "https";
$queryStr = $_SERVER['QUERY_STRING'];
$pasteid = preg_replace("/[^a-zA-Z0-9]/", "", $queryStr);
$pastedata = $_SERVER["REQUEST_METHOD"] == "POST" ?
                urldecode(str_replace("+", "%2B", file_get_contents('php://input'))) : false;

// Change this if you do not wish to use MySQL. SQLite should work just fine
$db = new PDO("mysql:dbname=$database;host=127.0.0.1", $user, $pass);

// The variable validchars and the regex used in pasteid should be 
// changed if you want to add support for other characters.
$validchars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
// Use this version of validchars if you only want characters that do notb "visually overlap". 
//$validchars = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";

$reUrl = '/^https?:\/\/([a-zA-Z0-9\-]+\.)+[a-zA-Z0-9\-]+(\/[^\s]*)?$/';
$sql = "SELECT data from `$table` where `id` = ?";
$baseUrl = "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if ($pasteid) {
    if(strlen($pasteid) > $idlen || strlen($pasteid) <= 1) {
        die(header("HTTP/1.0 414 Request-URI Too Long"));
    }

    $st = $db->prepare($sql);
    $st->execute(array($pasteid));
    $res = $st->fetchAll();

    if (count($res) == 1) {
        if(preg_match($reUrl, $res[0][0]) == 1 && substr($queryStr, -1) != "!") {
            header("location: ".trim($res[0][0]));
        } else {
            header("Content-Length: ".strlen($res[0][0]));
            print($res[0][0]);
        }
    } else {
        header("HTTP/1.0 404 Not Found");
    }
} elseif($_SERVER["REQUEST_METHOD"] == "POST") {
    $st = $db->prepare($sql);
    $valid = False;

    while(!$valid) {
        $str = substr(str_shuffle(str_repeat($validchars, $idlen)), 0, $idlen);
        $st->execute(array($str));
        $valid = count($st->fetchAll()) == 0;
    }
    $ins = $db->prepare("INSERT INTO `$table` (`id`, `data`) VALUES (?, ?)");
    $ins->execute(array($str, $pastedata));

    $out = "$baseUrl?$str";
    header("Content-Length: ".strlen($out));
    print($out);
} else { ?>
NAME
    nnmm - nnmm stands for nothing

SYNOPSIS
    Python
        pasteurl = urllib2.urlopen("<?php echo $baseUrl;?>", <data>).read()
    Bash
        <command> | curl --data-urlencode @- "<?php echo $baseUrl;?>"
        curl --data-urlencode @- "<?php echo $baseUrl;?>" < <file>

DESCRIPTION
    Just post any data to this server and it'll give you a "paste" link.
    If the data is an url (ex "http://example.com/") it will return the
    same kind of url but it will instead be a shortened url. This means
    that it will redirect instead of show the data. This can be stopped 
    by adding an ! at the end of the url.

SEE ALSO
    The current source code can be found at http://nnmm.nl/s.php
    Command-line tool: https://nnmm.nl/nnmm
    The git repo can be found at https://github.com/Mechazawa/nnmm 
<?php }

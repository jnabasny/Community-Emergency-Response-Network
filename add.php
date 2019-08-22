<html><body>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<center><h1><BR><BR><BR><BR>
<?php

$to = $_POST["to"];
$oper = $_POST["oper"];
$address = $to.$oper;

$servername = "localhost";
$username = "user";
$password = "password";
$dbname = "cern";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "INSERT INTO Members (Address, Phone_Number)
VALUES ('$address', '$to')";

if ($conn->query($sql) === TRUE) {
    echo $to . " has joined the Community Emergency Response Network.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}


$conn->close();

$sent=mail($address, "", "Welcome to the Community Emergency Response Network! To send a message, text email@gmail.com, or simply reply to this message.", "");
$sent2=mail($address, "", "To unsubscribe, simply text \"unsubscribe\" (without quotes) to the network. Email admin@gmx.com regarding any questions.", "");
if($sent)
{
//sent
}
else
{
error_log(print_r("Welcome message failed to send to " . $address, true));
}
if($sent2)
{
//sent
}
else
{
}

?>
</h1></center></body></html>

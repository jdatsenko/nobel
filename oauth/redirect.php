<?php
require_once '../vendor/autoload.php'; 
include "../config.php"; 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$conn = new mysqli($dbhost, $dbuser, $dbpass, $db); 

try {
    $client = new Google\Client(); 
    $client->setAuthConfig('../client_secret.json'); 
    $client->setRedirectUri('https://node27.webte.fei.stuba.sk/oauth/redirect.php'); 
    $client->addScope("email"); 
    $client->addScope("profile");
    


    if (!isset($_GET['code'])) {
        $auth_url = $client->createAuthUrl(); 
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL)); 
    } else {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']); 
        $client->setAccessToken($token);
        $oauth = new Google\Service\Oauth2($client);
        $userInfo = $oauth->userinfo->get(); 
        $email = $userInfo['email']; 
        $username = $userInfo['name']; 

        $sql = "SELECT * FROM users WHERE email = '$email' AND username = '$username'"; 
        $result = $conn->query($sql); 
        if ($result->num_rows > 0) {
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["email"] = $email;
            header("Location: ../../index.php");
            exit;
        }

        $addUser = "INSERT INTO users (username, email, password, 2fa_code) VALUES ('$username', '$email', '', '')"; // Prepare a SQL statement
        $conn->query($addUser); 
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        header("Location: ../../index.php");
        exit;
    } 
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>

    







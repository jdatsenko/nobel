<?php
require_once '../../vendor/autoload.php';
include "../../config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: /users/login/userInfo.php");
    exit;
}

function checkGmail($email) {
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function checkLength($field, $min, $max) {
    $string = trim($field); 
    $length = strlen($string); 
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}

function checkUsername($username) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}

$conn = new mysqli($dbhost, $dbuser, $dbpass, $db); 

$ga = new PHPGangsta_GoogleAuthenticator();
$secret;

if(isset($_SESSION['secret'])) {
    $secret = $_SESSION['secret'];
} else {
    $secret = $ga->createSecret();
    $_SESSION['secret'] = $secret;
}
$qrCodeUrl = $ga->getQRCodeGoogleUrl('Datsenko_webte', $secret);

$errmsg = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['repeat_password']) || empty($_POST['2fa_code'])) {
        $errmsg = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
        $errmsg .= '<strong>Error:</strong> Fill all the fields';
        $errmsg .= '</div>';
    } 
    elseif (checkLength($_POST['username'], 6, 32) === false) {
        $errmsg .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4' role='alert'>";
        $errmsg .= "<strong>Error:</strong> Username must be between 6 and 32 characters.";
        $errmsg .= "</div>";
    } elseif (checkUsername($_POST['username']) === false) {
        $errmsg .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4' role='alert'>";
        $errmsg .= "<strong>Error:</strong> Username can only contain letters, numbers, and underscores.";
        $errmsg .= "</div>";
    } else {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $repeatPassword = $_POST['repeat_password'];
        $twofaCode = $_POST['2fa_code'];

        if ($password !== $repeatPassword) {
            $errmsg .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
            $errmsg .= '<strong>Error:</strong> Passwords do not match';
            $errmsg .= '</div>';
        } elseif (!checkGmail($email)) {
            $errmsg .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
            $errmsg .= '<strong>Error:</strong> Email must be a valid Gmail address';
            $errmsg .= '</div>';
        } else {
            
            $is2FACodeValid = $ga->verifyCode($secret, $twofaCode, 2);

            if (!$is2FACodeValid) {
                $errmsg .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
                $errmsg .= '<strong>Error:</strong> Invalid 2FA code';
                $errmsg .= '</div>';
            } else {
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt_check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt_check_username->bind_param("s", $username);
                $stmt_check_username->execute();
                $result_username = $stmt_check_username->get_result();

                if ($result_username->num_rows > 0) {
                    $errmsg .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
                    $errmsg .= '<strong>Error:</strong> This username is already taken';
                    $errmsg .= '</div>';
                } else {
                    $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt_check_email->bind_param("s", $email);
                    $stmt_check_email->execute();
                    $result_email = $stmt_check_email->get_result();
                    
                    if ($result_email->num_rows > 0) {
                        $errmsg .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
                        $errmsg .= '<strong>Error:</strong> This email is already registered';
                        $errmsg .= '</div>';
                    } else {
                        $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, `2fa_code`) VALUES (?, ?, ?, ?)");
                        $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $secret);

                        if ($stmt_insert->execute()) {
                            session_unset();
                            header("Location: ../login/index.php");
                            exit(); 
                        } else {
                            $errmsg .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4" role="alert">';
                            $errmsg .= '<strong>Error:</strong> Unable to register user';
                            $errmsg .= '</div>';
                        }
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex flex-col justify-center items-center">
<header class="mt-8">
    <h1 class="text-4xl font-bold mb-2 text-center">Registration</h1>
</header>
<div class="container m-auto my-8 flex justify-center items-center">
    <form class="bg-white rounded-md shadow-md p-8" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" id="registrationForm">
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text" id="username" name="username" value="<?php if(isset($_POST['username'])) echo htmlspecialchars($_POST['username']); ?>" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div class="mb-6">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div class="mb-6">
            <label for="repeat_password" class="block text-sm font-medium text-gray-700 mb-2">Repeat Password</label>
            <input type="password" id="repeat_password" name="repeat_password" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div class="mb-6">
            <label for="2fa_code" class="block text-sm font-medium text-gray-700 mb-2">2FA Code</label>
            <input type="text" id="2fa_code" name="2fa_code" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <img src="<?php echo $qrCodeUrl; ?>" alt="" class="mx-auto">

        <div class="container flex justify-center items-center pt-4">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-2 rounded" type="submit">Register</button>
            <a href="../../oauth/redirect.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-4">Login with Google</a>
        </div>

        <?php
        if(!empty($errmsg)) {
            echo $errmsg;
        }
        ?>
    </form>
</div>
</body>
</html>














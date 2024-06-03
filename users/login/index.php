<?php
require_once '../../vendor/autoload.php';
include "../../config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../../index.php");
    exit;
}


function checkGmail($email) {
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ga = new PHPGangsta_GoogleAuthenticator();

$secret = $ga->createSecret();

$errmsg = ""; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if(empty($_POST["username_email"]) || empty($_POST["password"]) || empty($_POST["2fa"])) {
        echo "Please provide username/email, password, and 2FA code.";
        exit;
    }

    $username_email = $_POST["username_email"];
    $password = $_POST["password"];
    $twofaCode = $_POST["2fa"];

    $sql = "SELECT username, email, password, 2fa_code FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username_email, $username_email);

    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($username, $email, $hashed_password, $stored_2fa_code);
            $stmt->fetch();

            if (password_verify($password, $hashed_password) && $ga->verifyCode($stored_2fa_code, $twofaCode, 2)) {
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                $_SESSION["loggedin"] = true;
                header("location: ../../index.php");
                exit;
            } else {
                echo "<p class='text-red-500 font-bold mb-4'>Incorrect username/email, password, or 2FA code.</p>";
            }
            
        } else {
            echo "Incorrect username/email, password, or 2FA code.";
        }
    } else {
        echo "Oops! Something went wrong.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-center items-center">

<header class="mb-8">
    <h1 class="text-4xl font-bold mb-2 text-center">Login</h1>
</header>

<main class="bg-white p-8 rounded-md shadow-md">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="space-y-4">
        <div>
            <label for="username_email" class="block text-sm font-medium text-gray-700">Username/Email:</label>
            <input type="text" name="username_email" id="username_email" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
            <input type="password" name="password" id="password" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>

        <div>
            <label for="2fa" class="block text-sm font-medium text-gray-700">2FA code:</label>
            <input type="number" name="2fa" id="2fa" class="border border-gray-300 rounded-md py-2 px-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>
        <div class="flex justify-center items-center">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Login</button>
          <a href="../../oauth/redirect.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-4">Login with Google</a>

        </div>

    </form>

    <p class="mt-4 text-sm text-gray-600">Haven't registered yet? <a href="../register/index.php" class="text-blue-500 hover:underline">Registration.</a></p>
</main>
<script>
    function redirectToMain() {
        window.location.href = "../../index.php";

    }
</script>
</body>
</html>







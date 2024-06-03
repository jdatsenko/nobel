<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nobel Prize Winner Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="max-w-md mx-auto my-10 bg-white p-5 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-5">Nobel Prize Winner Form</h2>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="mb-4">
            <label for="first_name" class="block mb-1">First Name:</label>
            <input type="text" id="first_name" name="first_name" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="last_name" class="block mb-1">Last Name:</label>
            <input type="text" id="last_name" name="last_name" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="birth_year" class="block mb-1">Birth Year:</label>
            <input type="text" id="birth_year" name="birth_year" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="death_year" class="block mb-1">Death Year:</label>
            <input type="text" id="death_year" name="death_year" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
    <label for="country" class="block mb-1">Country:</label>
    <select id="country" name="country" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        <?php
        include "../../config.php";
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
        $sql = "SELECT id, country_name FROM countries";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['country_name'] . "</option>";
            }
        } else {
            echo "<option value=''>No categories found</option>";
        }
        $conn->close();
        ?>
    </select>
</div>

        <div class="mb-4">
            <label for="sex" class="block mb-1">Sex:</label>
            <select id="sex" name="sex" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="year" class="block mb-1">Year:</label>
            <input type="text" id="year" name="year" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
    <label for="category" class="block mb-1">Category:</label>
    <select id="category" name="category" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        <?php
        include "../../config.php";
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
        $sql = "SELECT id, category FROM categories";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['category'] . "</option>";
            }
        } else {
            echo "<option value=''>No categories found</option>";
        }
        $conn->close();
        ?>
    </select>
</div>


        <div class="mb-4">
            <label for="contribution_sk" class="block mb-1">Contribution (SK):</label>
            <textarea id="contribution_sk" name="contribution_sk" rows="4" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500" ></textarea>
        </div>

        <div class="mb-6">
            <label for="contribution_en" class="block mb-1">Contribution (EN):</label>
            <textarea id="contribution_en" name="contribution_en" rows="4" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500" ></textarea>
        </div>
        <div class="flex items-center justify-center">
            <button type="submit" name="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">Submit</button>
        </div>
    </form>
    
    
    <?php
include "../../config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $required_fields = array("first_name", "last_name", "birth_year", "death_year", "country", "sex", "year", "category", "contribution_sk", "contribution_en");
    $errors = array();
    $errorShown = false;

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Fill all the fields.";
            $errorShown = true;
            break;
        }
    }

    if (!$errorShown) {
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $name = $_POST["first_name"];
        $surname = $_POST["last_name"];

        $check_sql = "SELECT * FROM person WHERE name = '$name' AND surname = '$surname'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            echo "ERROR: This person already exists";
        } else {
            $sex = $_POST["sex"];
            $birth = $_POST["birth_year"];
            $death = $_POST["death_year"];
            $country_id = $_POST["country"];
            $year = $_POST["year"];
            $category = $_POST["category"];
            $contribution_sk = $_POST["contribution_sk"];
            $contribution_en = $_POST["contribution_en"];

            $person_sql = "INSERT INTO person (name, surname, sex, birth, death, country_id) VALUES ('$name', '$surname', '$sex', '$birth', '$death', '$country_id')";

            if (mysqli_query($conn, $person_sql)) {
                $person_id = mysqli_insert_id($conn);

                $prize_sql = "INSERT INTO prizes (year, contribution_sk, contribution_en, person_id, category_id) VALUES ('$year', '$contribution_sk', '$contribution_en', '$person_id', '$category')";

                if (mysqli_query($conn, $prize_sql)) {
                    echo "Records inserted successfully.";
                    echo "<script>window.location.href = '../../index.php?success=true';</script>";
                    exit();
                } else {
                    echo "ERROR: Could not able to execute $prize_sql. " . mysqli_error($conn);
                }
            } else {
                echo "ERROR: Could not able to execute $person_sql. " . mysqli_error($conn);
            }
        }

        $conn->close();
    }
}
?>


</div>


</body>
</html>





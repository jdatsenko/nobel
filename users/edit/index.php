<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laureate</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="max-w-md mx-auto my-10 bg-white p-5 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Edit Laureate</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?php
    $prizeId = $_GET['prize_id'];
$personId = $_GET['person_id'];
?>
        <input type = "hidden" name = "prizeId" value = "<?php echo $prizeId; ?>">
        <input type = "hidden" name = "personId" value = "<?php echo $personId; ?>">
        

        <div class="mb-4">
    <label for="editYear" class="block mb-1">Year:</label>
    <select id="editYear" name="editYear" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        <?php
        include "../../config.php";
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
        $sql = "SELECT DISTINCT year FROM prizes ORDER BY year DESC"; 
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['year'] . "'>" . $row['year'] . "</option>";
            }
        } else {
            echo "<option value=''>No years found</option>";
        }
        $conn->close();
        ?>
    </select>
</div>
        
        <div class="mb-4">
            <label for="editFirstName" class="block mb-1">First Name:</label>
            <input type="text" id="editFirstName" name="editFirstName" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="editLastName" class="block mb-1">Last Name:</label>
            <input type="text" id="editLastName" name="editLastName" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
        </div>
        
        <div class="mb-4">
            <label for="editCountry" class="block mb-1">Country:</label>
            <select id="editCountry" name="editCountry" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
                <?php
                include "../../config.php";
                $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
                $sql = "SELECT id, country_name FROM countries";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['country_name'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No countries found</option>";
                }
                $conn->close();
                ?>
            </select>
        </div>
        
        <div class="mb-4">
            <label for="editCategory" class="block mb-1">Category:</label>
            <select id="editCategory" name="editCategory" class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:border-blue-500">
                <?php
                include "../../config.php";
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
        
        <div class="flex items-center justify-center">
            <button type="submit" name="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">Submit</button>
        </div>
    </form>

    <?php 
    include "../../config.php";
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
        $prizeId = $_POST['prizeId'];
        $year = $_POST['editYear'];
        $name = $_POST['editFirstName'];
        $surname = $_POST['editLastName'];
        $category = $_POST['editCategory'];
        $country = $_POST['editCountry'];
        $personId = $_POST['personId']; 
        $required_fields = array("editFirstName", "editLastName");
    $errors = array();
    $errorShown = false;

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo '<p class="text-lg font-bold text-center mt-3 text-red-500">Fill the name & surname fields.</p>';

            $errorShown = true;
            break;
        }
    }

    if (!$errorShown) {
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $stmt1 = $conn->prepare("UPDATE prizes SET year = ?, category_id = ? WHERE id = ?");
        $stmt1->bind_param("sii", $year, $category, $prizeId); 
        $result1 = $stmt1->execute();
        
        $stmt2 = $conn->prepare("UPDATE person SET name = ?, surname = ?, country_id = ? WHERE id = ?");
        $stmt2->bind_param("ssii", $name, $surname, $country, $personId);
        $result2 = $stmt2->execute();
        
        $stmt1->close();
        $stmt2->close();
        $conn->close();
        
        if ($result1 && $result2) {
            echo "<script>window.location.href = '../../index.php?success=true';</script>";
            exit();
        } else {
            echo "Error updating data.";
            exit();
        }
    }
}
    ?>
</div>

</body>
</html>






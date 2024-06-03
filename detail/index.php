<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Person Details</title>
  <!-- Include Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

<?php
include '../config.php'; // Include your database configuration file
$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);

if(isset($_GET['person_id'])) {
    $person_id = mysqli_real_escape_string($conn, $_GET['person_id']);
    
    $sql = "SELECT 
            prizes.*, 
            person.*, 
            prize_details.*, 
            categories.*, 
            countries.*
        FROM prizes
        JOIN person ON prizes.person_id = person.id
        LEFT JOIN prize_details ON prizes.prize_details_id = prize_details.id
        JOIN categories ON prizes.category_id = categories.id
        JOIN countries ON person.country_id = countries.id
        WHERE person.id = $person_id";

    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Output data of the person
        $person_details = $result->fetch_assoc();
        ?>
        <button class="bg-blue-900 hover:bg-blue-700 text-white font-bold py-2 px-4 m-4 rounded" onclick="goToIndex()">Back</button>
        <div class="max-w-xl mx-auto bg-gray-300 shadow-md rounded-lg overflow-hidden my-8">
            <div class="p-6">
                <div class="text-xl font-bold mb-2">Person Details</div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-700 font-semibold">Year:</p>
                        <p class="text-gray-900"><?php echo $person_details["year"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Name:</p>
                        <p class="text-gray-900"><?php echo $person_details["name"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Surname:</p>
                        <p class="text-gray-900"><?php echo $person_details["surname"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Country:</p>
                        <p class="text-gray-900"><?php echo $person_details["country_name"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Category:</p>
                        <p class="text-gray-900"><?php echo $person_details["category"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Organization:</p>
                        <p class="text-gray-900"><?php echo $person_details["organization"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Sex:</p>
                        <p class="text-gray-900"><?php echo $person_details["sex"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Birthdate:</p>
                        <p class="text-gray-900"><?php echo $person_details["birth"]; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-700 font-semibold">Deathdate:</p>
                        <p class="text-gray-900"><?php echo $person_details["death"]; ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } else {
        echo "Person details not found.";
    }
}
    ?>



<script>
  function goToIndex() {
    window.location.href = "../index.php";
  }
</script>
</body>
</html>


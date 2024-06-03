<?php
session_start(); 

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["username"])) {

  include './config.php';

 $conn = new mysqli($dbhost, $dbuser, $dbpass, $db, $dbport);

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  error_reporting(E_ALL);
ini_set('display_errors', 1);
  $username = $_SESSION["username"];

  $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $userInfo = $result->fetch_assoc();

    echo "<div id='userInfoSection' class='hidden mt-8 bg-gray-100 p-6 rounded-md'>";
    echo "<h2 class='text-2xl font-bold mb-4'>User Information</h2>";
    echo "<p class='mb-2'><strong>Username:</strong> " . $userInfo['username'] . "</p>";
    echo "<p class='mb-2'><strong>Email:</strong> " . $userInfo['email'] . "</p>";
    echo "</div>";
}
 else {
      echo "<p>User not found.</p>";
  }

  $stmt->close();
  $conn->close();

} 

include './config.php';

$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);

$sql = "SELECT prizes.year, prizes.id AS prizes_id, person.id AS person_id, person.name, person.surname, countries.country_name, categories.category
        FROM prizes
        JOIN person ON prizes.person_id = person.id
        LEFT JOIN prize_details ON prizes.prize_details_id = prize_details.id
        JOIN categories ON prizes.category_id = categories.id
        JOIN countries ON person.country_id = countries.id";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nobel Prize</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
<style>
  .dataTables_length {
    margin-bottom: 20px; 
  }

  .dataTables_length select {
    font-size: 0.875rem; 
    line-height: 1.25rem; 
    color: gray;
  }
</style>

<div class="container mx-auto mt-8 flex items-center justify-center gap-4">
  <img src="./img/logo.png" alt="Nobel Prize" class="h-10 w-auto">

  <?php 
  if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["username"])): ?>
    <div class="flex gap-4">
      <button onclick="showUserInfo()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Show My Information</button>
      <button onclick="logout()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</button>
      <button onclick="addLaureate()" class="bg-blue-500 text-white px-4 py-2 font-bold rounded-md hover:bg-blue-600 transition duration-300 ease-in-out">Add Laureate</button>


    </div>
    <div id="loginNotification" class="bg-green-500 text-white rounded px-6 py-4 border-0 fixed top-4 right-4">
    Welcome, <?php echo $_SESSION["username"]; ?>! You are logged in.
    </div>

    
<?php else: ?>
    <button class="bg-blue-900 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onclick="goToLogin()">Login</button>
    <button class="bg-blue-900 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onclick="goToRegister()">Register</button>
  <?php endif; ?>
</div>

  <h1 class="text-3xl font-bold text-center mt-8">Nobel Prize Receivers</h1>

  <div class="select-filters py-2 mx-8 flex gap-6">
  </div>

  <div id="userInfoSection" class="hidden mt-8">
  </div>

  <div class="mt-8 mx-8">
  <table id="myTable" class="min-w-full bg-white shadow-md rounded-md overflow-hidden">
  <thead class="bg-gray-800 text-white">
    <tr>
      <th class="px-6 py-3 text-left" id="yearColumn">Year</th>
      <th class="px-6 py-3 text-left">Person</th>
      <th class="px-6 py-3 text-left">Surname</th>
      <th class="px-6 py-3 text-left">Country</th>
      <th class="px-6 py-3 text-left" id="categoryColumn">Category</th>
      <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["username"])): ?>
        <th class="px-6 py-3 text-left">Actions</th>
      <?php endif; ?>
    </tr>
  </thead>
  <tbody id="myTableBody" class="divide-y divide-gray-200">

    <?php
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        echo "<tr class='hover:bg-gray-50'>";
        echo "<td class='px-6 py-4 whitespace-nowrap year-column'>".$row["year"]."</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap name-column'><a href='detail/index.php?person_id=".$row["person_id"]."' class='underline'>".$row["name"]."</a></td>";
        echo "<td class='px-6 py-4 whitespace-nowrap surname-column'>".$row["surname"]."</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap country_column'>".$row["country_name"]."</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap category_column'>".$row["category"]."</td>";
        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["username"])) {
          echo "<td class='px-6 py-4 whitespace-nowrap'>";
          echo "<button onclick='deleteRow(" . $row["prizes_id"] . ")' class='bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded'>Delete</button>";
          echo "<button onclick='editRow(" . $row["prizes_id"] . ", " . $row["person_id"] . ")' class='bg-blue-500 m-2 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded'>Edit</button>";



          echo "</td>";
        }
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='5' class='px-6 py-4'>0 results</td></tr>";
    }
    ?>
    

  </tbody>
    </table>
  </div>

</div>
<div id="notificationContainer" class="fixed top-0 right-0 m-8 z-50"></div>
<?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["username"])): ?>
    <script>
      setTimeout(function() {
        let loginNotification = document.getElementById('loginNotification');
        loginNotification.style.display = 'none';
      }, 1000); 
    </script>
<?php endif; ?>

</body>
<?php
if(isset($_GET["success"]) && $_GET["success"] === "true") {
    echo "<script>displayNotification('Laureate added successfully.', 'success');</script>";
}
?>

        
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function() {    
      $('#myTable').DataTable({
      initComplete: function () {
        this.api()
            .columns()
            .every(function () {
                let column = this;

                if (column.header().textContent !== 'Year' && column.header().textContent !== 'Category') {
                  return;
                }
 
                let span = document.createElement('span');
                span.textContent = column.header().textContent + ': ';
                span.className = 'text-gray-700 mr-2';

                let select = document.createElement('select');
                select.add(new Option(''));
                select.className = 'shadow mt-6 appearance-none border rounded w-40 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline';

                let div = document.createElement('div');
                div.appendChild(span);
                div.appendChild(select);
                document.querySelector('.select-filters').appendChild(div);
 
                select.addEventListener('change', function () {
                  if (select.value != '') {
                    column.visible(false);
                  } else {
                    column.visible(true);
                  }
                    column
                        .search(select.value, {exact: true})
                        .draw();
                });
 
                column
                    .data()
                    .unique()
                    .sort()
                    .each(function (d, j) {
                        select.add(new Option(d));
                    });
            });
      }
    });
  });

  function goToRegister() {
    window.location.href = 'users/register/index.php';
  }

  function goToLogin() {
    window.location.href = 'users/login/index.php';
  }
  
  function showUserInfo() {
    let userInfoSection = document.getElementById('userInfoSection');
    if (userInfoSection.style.display === 'none' || userInfoSection.style.display === '') {
      userInfoSection.style.display = 'block';
    } else {
      userInfoSection.style.display = 'none';
    }
  }

  function displayNotification(message, type) {
    let notificationContainer = document.getElementById('notificationContainer');
    let notification = document.createElement('div');
    notification.className = `bg-${type === 'success' ? 'green' : 'green'}-500 text-white px-6 py-4 border-0 rounded relative mb-4`;
    notification.textContent = message;
    notificationContainer.appendChild(notification);
}



  function logout() {
    fetch('users/logout/index.php')
        .then(response => {
            if (response.ok) {
                displayNotification("Logged out successfully", "green", 10000); // Set timeout to 10 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 1500); 
            } else {
                displayNotification("Failed to logout", "red");
            }
        })
        .catch(error => {
            displayNotification("An error occurred", "red");
            console.error('Error:', error);
        });
}

function submitEditForm() {
    // Prevent the default form submission behavior
    event.preventDefault();

    // Get form data
    let form = document.getElementById('editForm');
    let formData = new FormData(form);

    // Send a POST request to the PHP file
    fetch('users/edit/index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if the request was successful
        if (response.ok) {
            // Display a success message
            displayNotification("Laureate updated successfully.", "success");
            // Reload the page after a delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Display an error message
            displayNotification("Failed to update laureate.", "error");
        }
    })
    .catch(error => {
        // Display an error message
        displayNotification("An error occurred", "error");
        console.error('Error:', error);
    });
}



function deleteRow(prizesId) {
    fetch('users/delete_row/index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'prizes_id=' + prizesId
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response text:', response.statusText);
        return response.text();
    })
    .then(data => {
            displayNotification("Row deleted successfully.", "success");
            setTimeout(() => {
                window.location.reload();
            }, 1500); 
          
    })
    .catch(error => {
        displayNotification("An error occurred", "error");
        console.error('Error:', error);
    });
}

function addLaureate() {
    window.location.href = 'users/add_laureate/index.php';
}

function editRow(prizeId, personId) {
    window.location.href = 'users/edit/index.php?prize_id=' + prizeId + '&person_id=' + personId;
}


</script>

</body>
</html>




 






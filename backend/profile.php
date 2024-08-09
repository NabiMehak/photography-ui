<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
  header("Location: login.php");
  exit();
}

$email = $_SESSION['email'];

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Update profile information
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = test_input($_POST["title"]);
  $firstname = test_input($_POST["firstname"]);
  $lastname = test_input($_POST["lastname"]);
  $gender = test_input($_POST["gender"]);
  $age = test_input($_POST["age"]);
  // $designation = test_input($_POST["designation"]);
  // $institution = test_input($_POST["institution"]);
  $address1 = test_input($_POST["address1"]);
  $city = test_input($_POST["city"]);
  $state = test_input($_POST["state"]);
  $postal = test_input($_POST["postal"]);
  $country = test_input($_POST["country"]);
  $mobile = test_input($_POST["mobile"]);
  $conferencecategory = test_input($_POST["conferencecategory"]);
  $iapmembershipno = test_input($_POST["iapmembershipno"]);
  $medicolegalchapter = test_input($_POST["medicolegalchapter"]) === 'Yes' ? 1 : 0;

  $sql = "UPDATE userauthentication 
            SET title=?, firstname=?, lastname=?, gender=?, age=?, address1=?, city=?, state=?, postal=?, country=?, mobile=?, conferencecategory=?, iapmembershipno=?, medicolegalchapter=? 
            WHERE email=?";

  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
  }

  $bind = $stmt->bind_param("ssssissssssssss", $title, $firstname, $lastname, $gender, $age, $address1, $city, $state, $postal, $country, $mobile, $conferencecategory, $iapmembershipno, $medicolegalchapter, $email);
  if ($bind === false) {
    die('Bind failed: ' . htmlspecialchars($stmt->error));
  }

  if ($stmt->execute()) {
    $message = "Profile updated successfully!";
  } else {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
  }

  $stmt->close();
}

// Fetch profile information
$sql = "SELECT title, firstname, lastname, gender, age, address1, city, state, postal, country, mobile, conferencecategory, iapmembershipno, medicolegalchapter FROM userauthentication WHERE email=?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
  die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($title, $firstname, $lastname, $gender, $age, $address1, $city, $state, $postal, $country, $mobile, $conferencecategory, $iapmembershipno, $medicolegalchapter);
$stmt->fetch();
$stmt->close();
$conn->close();

$medicolegalchapter = $medicolegalchapter == 1 ? 'Yes' : 'No';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Page</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
    }

    .container {
      background-color: #ffae4a;
      padding: 38px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      max-width: 640px;
      width: 100%;
      margin: 111px auto;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    h3 {
      color: #333;
      margin-bottom: 10px;
      margin-top: 20px;
      border-bottom: 2px solid #f4f4f4;
      padding-bottom: 5px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #333;
    }

    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="tel"],
    select {
      width: calc(100% - 20px);
      padding: 10px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 3px;
      box-sizing: border-box;
    }

    input[type="submit"] {
      color: #fff;
      background-color: #dc3545;
      border-color: #dc3545;
      padding: 10px;
      border-radius: 3px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #0056b3;
    }

    .h3-info {
      background-color: #150328;
      color: #fff !important;
      text-align: center !important;
      font-size: 25px;
      padding-top: calc(.375rem + 1px);
      padding-bottom: calc(.375rem + 1px);
      margin-bottom: 15px;
      margin-right: -38px;
      margin-left: -38px;
    }

    .text-center {
      text-align: center !important;
    }

    @media screen and (max-width: 768px) {
      .container {
        margin: 81px auto;
      }
    }
  </style>
</head>
<body>
  <?php include 'topbar.php'; ?>
  <div class="container">
    <h2>Registration Form</h2>
    <?php if (isset($message)) {
      echo '<p class="message">' . $message . '</p>';
    } ?>
    <form action="update_profile.php" method="post">
      <h3 class="h3-info">Personal Information</h3>
      <label for="title">Title:</label>
      <select id="title" name="title">
        <option value="Mr." <?php if ($title == 'Mr.') echo 'selected'; ?>>Mr.</option>
        <option value="Mrs." <?php if ($title == 'Mrs.') echo 'selected'; ?>>Mrs.</option>
        <option value="Ms." <?php if ($title == 'Ms.') echo 'selected'; ?>>Ms.</option>
      </select>

      <label for="firstname">First Name:</label>
      <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>

      <label for="lastname">Last Name:</label>
      <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>

      <label for="gender">Gender:</label>
      <select id="gender" name="gender">
        <option value="Male" <?php if ($gender === 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if ($gender === 'Female') echo 'selected'; ?>>Female</option>
        <option value="Other" <?php if ($gender === 'Other') echo 'selected'; ?>>Other</option>
      </select>

      <label for="age">Age:</label>
      <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" required>

      <!-- <label for="designation">Designation:</label>
      <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($designation); ?>" required>

      <label for="institution">Institution:</label>
      <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($institution); ?>" required> -->

      <label for="iapmembershipno">IAP Membership Number:</label>
      <input type="text" id="iapmembershipno" name="iapmembershipno" value="<?php echo htmlspecialchars($iapmembershipno); ?>" required>

      <label for="medicolegalchapter">Member of MedicoLegal Chapter?</label>
      <input type="radio" id="medicolegalchapter_yes" name="medicolegalchapter" value="Yes" <?php if ($medicolegalchapter === 'Yes') echo 'checked'; ?>>
      <label for="medicolegalchapter_yes">Yes</label>
      <input type="radio" id="medicolegalchapter_no" name="medicolegalchapter" value="No" <?php if ($medicolegalchapter === 'No') echo 'checked'; ?>>
      <label for="medicolegalchapter_no">No</label>

      <h3 class="h3-info">Contact Information</h3>
      <label for="address1">Address1:</label>
      <input type="text" id="address1" name="address1" value="<?php echo htmlspecialchars($address1); ?>" required>

      <label for="city">City:</label>
      <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required>

      <label for="state">State:</label>
      <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>" required>

      <label for="postal">Postal Code:</label>
      <input type="text" id="postal" name="postal" value="<?php echo htmlspecialchars($postal); ?>" required>

      <label for="country">Country:</label>
      <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>" required>

      <label for="mobile">Phone:</label>
      <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required>

      <h3 class="h3-info">Fees</h3>
      <label for="conferencecategory">Registering as:</label>
      <select id="conferencecategory" name="conferencecategory">
        <option value="Faculty" <?php if ($conferencecategory == 'Faculty') echo 'selected'; ?>>Faculty</option>
        <option value="Delegate" <?php if ($conferencecategory == 'Delegate') echo 'selected'; ?>>Delegate</option>
          <option value="Intern / MBBS Student / Nursing Student" <?php if ($conferencecategory == 'Intern / MBBS Student / Nursing Student
          ') echo 'selected'; ?>>Intern / MBBS Student / Nursing Student
        </option>
        <option value="Accompanying Person" <?php if ($conferencecategory == 'Accompanying Person') echo 'selected'; ?>>Accompanying Person</option>
      </select>

      <div class="text-center">
        <input type="submit" value="Update Profile">
      </div>
    </form>
  </div>
</body>
</html>

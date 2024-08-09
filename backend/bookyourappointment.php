<?php
session_start();
include 'db.php';
require 'authentication.php';


if (!isset($_SESSION['email'])) {
  header("Location: login.php");
  exit();
}

$email = $_SESSION['email'];

function test_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


// Update profile information
if ($_SERVER["REQUEST_METHOD"] == "POST") {


  $fees = [
    "IAP Member" => 5000,
    "Non IAP Member" => 6000,
    "PG Student" => 2000,
    "Accompanying Person" => 4000
  ];


  $title = test_input($_POST["title"]);
  $firstname = test_input($_POST["firstname"]);
  $lastname = test_input($_POST["lastname"]);
  $gender = test_input($_POST["gender"]);
  $age = test_input($_POST["age"]);
  $designation = test_input($_POST["designation"]);
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
            SET title=?, firstname=?, lastname=?, gender=?, age=?, address1=?, city=?, state=?, postal=?, country=?, mobile=?, designation=?, conferencecategory=?, iapmembershipno=?, medicolegalchapter=? 
            WHERE email=?";

  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
  }

  $bind = $stmt->bind_param("ssssisssssssssss", $title, $firstname, $lastname, $gender, $age, $address1, $city, $state, $postal, $country, $mobile, $designation, $conferencecategory, $iapmembershipno, $medicolegalchapter, $email);
  if ($bind === false) {
    die('Bind failed: ' . htmlspecialchars($stmt->error));
  }

  if ($stmt->execute()) {
    $message = "Profile updated successfully!";
  } else {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
  }

  $stmt->close();

  if (isset($_POST['action']) && $_POST['action'] == 'Proceed To Pay') {
    $clientCode = 'IAPM70';
    $username = 'aiman_17008';
    $password = 'IAPM70_SP17008';
    $authKey = 'FiWE14vcZy3EFIBM';
    $authIV = '36rMczfUBWtzE0DM';
    $payerName = $firstname . ' ' . $lastname;
    $payerEmail = $email;
    $payerMobile = $mobile;
    $payerAddress = $address1 . ', ' . $city . ', ' . $state . ', ' . $country . ', ' . $postal;
    $clientTxnId = uniqid('TXN');
    $amount = isset($fees[$conferencecategory]) ? $fees[$conferencecategory] : 0;
    $amountType = 'INR';
    $mcc = 5137;
    $channelId = 'W';
    $callbackUrl = 'https://mlcon2024kashmir.com/backend/payment_response.php';

    $gender = $gender;
    $age = $age;
    $memberOfMedicoLegalChapter= $medicolegalchapter;
    $registeringAs =  $conferencecategory;
    $iapmembershipNumber =  $iapmembershipno;
    $designation =  $designation;


    $encData = "?clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password . "&payerName=" . $payerName .
      "&payerMobile=" . $payerMobile . "&payerEmail=" . $payerEmail . "&payerAddress=" . $payerAddress . "&clientTxnId=" . $clientTxnId .
      "&amount=" . $amount . "&amountType=" . $amountType . "&mcc=" . $mcc . "&channelId=" . $channelId . "&callbackUrl=" . $callbackUrl.
      "&udf1=".$age."&udf2=".$gender."&udf3=".$memberOfMedicoLegalChapter."&udf4=".$registeringAs."&udf5=".$iapmembershipNumber."&udf6=".$designation;

    $data = AesCipher::encrypt($authKey, $authIV, $encData);

    // Redirect to payment gateway
    echo '
          <form id="paymentForm" action="https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1" method="post">
              <input type="hidden" name="encData" value="' . $data . '" id="frm1">
              <input type="hidden" name="clientCode" value="' . $clientCode . '" id="frm2">
          </form>
         <script type="text/javascript">
                document.getElementById("paymentForm").submit();
            </script>
      ';
    exit();
  }
}

// Fetch profile information
$sql = "SELECT title, firstname, lastname, gender, age, address1, city, state, postal, country, mobile, designation, conferencecategory, iapmembershipno, medicolegalchapter,transtatus,tranamount,transactionid FROM userauthentication WHERE email=?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
  die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($title, $firstname, $lastname, $gender, $age, $address1, $city, $state, $postal, $country, $mobile, $designation, $conferencecategory, $iapmembershipno, $medicolegalchapter, $transaction_status,$transactionamount,$transactionid);
$stmt->fetch();

$profileComplete = !empty($title) && !empty($firstname) && !empty($lastname) && 
                   !empty($gender) && !empty($age) && !empty($address1) && 
                   !empty($city) && !empty($state) && !empty($postal) && 
                   !empty($country) && !empty($mobile) && !empty($conferencecategory);

$stmt->close();
$conn->close();

$medicolegalchapter = $medicolegalchapter == 1 ? 'Yes' : 'No';


if (!isset($transaction_status)) {
  $transaction_status = 'PENDING';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="900;url=logout.php" />
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
      background-color: #fff;
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

    .radio-group label {
      display: inline-block;
      margin-right: 20px;
      /* Adjust margin as needed */
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: 4px;
    }

    .alert-success {
      color: #155724;
      background-color: #d4edda;
      border-color: #c3e6cb;
    }

    @media screen and (max-width: 768px) {
      .container {
        margin: 81px auto;
      }
    }
  </style>
  <script>
    // JavaScript to toggle visibility and required status of IAP Membership Number field
    function toggleIAPMembership() {
      var conferenceCategory = document.getElementById('conferencecategory').value;
      var iapMembershipNoField = document.getElementById('iapmembershipno');
      var iapMembershipNoLabel = document.getElementById('iapmembershipno_label');

      if (conferenceCategory === 'IAP Member') {
        iapMembershipNoField.style.display = 'block';
        iapMembershipNoLabel.style.display = 'block';
        iapMembershipNoField.setAttribute('required', 'required');
      } else {
        iapMembershipNoField.style.display = 'none';
        iapMembershipNoLabel.style.display = 'none';
        iapMembershipNoField.removeAttribute('required');
      }
    }

    // Ensure the initial state matches on page load
    window.onload = function() {
      toggleIAPMembership();
    };

    function printTransactionDetails() {
       window.open('receipt.php', '_blank');
    }
  </script>
</head>

<body>
  <?php include 'topbar.php'; ?>
  <div class="container">
    <h2>Registration Form</h2>
    <?php if (isset($message)) {
      echo '<p class="alert alert-success">' . $message . '</p>';
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

      <label for="medicolegalchapter">Member of MedicoLegal Chapter?</label>
      <div class="radio-group">
        <input type="radio" id="medicolegalchapter_yes" name="medicolegalchapter" value="Yes" <?php if ($medicolegalchapter === 'Yes') echo 'checked'; ?>>
        <label for="medicolegalchapter_yes">Yes</label>
        <input type="radio" id="medicolegalchapter_no" name="medicolegalchapter" value="No" <?php if ($medicolegalchapter === 'No') echo 'checked'; ?>>
        <label for="medicolegalchapter_no">No</label>
      </div>


      <h3 class="h3-info">Contact Information</h3>
      <label for="address1">Address:</label>
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
      <select id="conferencecategory" name="conferencecategory" onchange="toggleIAPMembership()" required>
        <option value="" disabled selected>Select</option>
        <option value="IAP Member" <?php if ($conferencecategory == 'IAP Member') echo 'selected'; ?>>IAP Member</option>
        <option value="Non IAP Member" <?php if ($conferencecategory == 'Non IAP Member') echo 'selected'; ?>>Non IAP Member</option>
        <option value="PG Student" <?php if ($conferencecategory == 'PG Student') echo 'selected'; ?>>PG Student</option>
        <option value="Accompanying Person" <?php if ($conferencecategory == 'Accompanying Person') echo 'selected'; ?>>Accompanying Person</option>
      </select>

      <label for="iapmembershipno" id="iapmembershipno_label" style="display: none;">IAP Membership Number:</label>
      <input type="text" id="iapmembershipno" name="iapmembershipno" value="<?php echo htmlspecialchars($iapmembershipno); ?>" style="display: none;">

      <label for="designation">Designation:</label>
      <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($designation); ?>">

      <?php if ($transaction_status == 'SUCCESS') { ?>
        <label for="transaction_status">Transaction Status:</label>
        <input type="text" id="transaction_status" name="transaction_status" value="<?php echo htmlspecialchars($transaction_status); ?>" disabled>
        <label for="transaction_status">Transaction Amount:</label>
        <input type="text" id="transactionid" name="transactionid" value="<?php echo htmlspecialchars($transactionamount); ?>" disabled>
        <label for="transaction_status">Transaction Id:</label>
        <input type="text" id="transactionid" name="transactionid" value="<?php echo htmlspecialchars($transactionid); ?>" disabled>
      <?php } ?>

      <div class="text-center">
      <?php if ($transaction_status != 'SUCCESS') { ?>
        <input type="submit" name="action" value="Save">
      <?php if ($profileComplete) { ?>
          <input type="submit" name="action" value="Proceed To Pay">
        <?php } ?>
      <?php } ?>

      </div>
    </form>
  </div>
</body>

</html>
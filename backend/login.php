<?php
session_start(); // Start session to manage user authentication

// Check if user is already logged in, redirect to profile page if true
if (isset($_SESSION['email'])) {
    header("Location: update_profile.php");
    exit();
}

include 'db.php'; // Include your database connection file

// Function to sanitize input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Please fill out all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("SELECT userpassword FROM userauthentication WHERE email = ?");
        $stmt->bind_param("s", $email);

        // Execute the statement
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Bind result variables
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Login successful, set session variables
                $_SESSION['email'] = $email;

                // Redirect to profile page
                header("Location: update_profile.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }

        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
    <style>
              body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            margin-top: 70px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input[type="email"],
        input[type="password"],
        input[type="submit"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        input[type="submit"] {
            background-color: #FBC02D;
            color: black;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #ffb600;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'topbar.php'; ?>
    <div class="container">
        <h2>Login Form</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? $email : ''; ?>">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
            <!-- <a href="transactions_enquery.php" style="display: block; text-align: center; margin-top: 10px;">Check Transaction Status</a> -->
        </form>
        <div class="register-link">
            <p>Don't have an account? <a href="registration.php">Register here</a></p>
        </div>
    </div>
</body>
</html>

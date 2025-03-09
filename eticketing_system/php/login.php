<?php
// Include database connection
include 'db_connect.php';

// Initialize variables
$email = $password = "";
$email_err = $password_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, proceed with login
    if (empty($email_err) && empty($password_err)) {
        // Prepare SQL query to fetch user credentials
        $query = "SELECT id, name, email, password, role FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        // Check if email exists in the database
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a session
                session_start();
                // $_SESSION['user_id'] = $user['id'];
                $_SESSION['userID'] = $user['id'];  

                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect to respective dashboard based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin/admin_dashboard.php"); // Admin Dashboard
                } else {
                    header("Location: user/user_dashboard.php");  // User Dashboard
                }
                exit();
            } else {
                $password_err = "The password you entered is incorrect.";
            }
        } else {
            $email_err = "No account found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Ticketing System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 0.9rem;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Login to Your Account</h2>
    <form method="POST" action="login.php">
        <div class="form-group">
            <input type="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>">
            <span class="error"><?php echo $email_err; ?></span>
        </div>

        <div class="form-group">
            <input type="password" name="password" placeholder="Enter your password">
            <span class="error"><?php echo $password_err; ?></span>
        </div>

        <div class="form-group">
            <input type="submit" value="Login">
        </div>
    </form>
    <div class="back-link">
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
</div>

</body>
</html>

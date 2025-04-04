<?php
// Include database connection
include 'db_connect.php';

// Initialize variables
$name = $email = $password = "";
$name_err = $email_err = $password_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);

        // Check if email format is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invalid email format.";
        } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@(gmail\.com|email\.com)$/', $email)) {
            $email_err = "Only Gmail and Email domain addresses are allowed.";
        } else {
            // Check if email already exists in the database
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $email_err = "This email is already taken.";
            }
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must be at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, insert data into database
    if (empty($name_err) && empty($email_err) && empty($password_err)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = "user"; // Default role set to 'user'

        // Prepare the SQL query
        $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $pdo->prepare($query);

        // Bind parameters
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $password_hash, PDO::PARAM_STR);
        $stmt->bindParam(":role", $role, PDO::PARAM_STR);

        // Execute the query and redirect if successful
        if ($stmt->execute()) {
            header("Location: login.php");  // Redirect to login page after successful sign-up
            exit();
        } else {
            echo "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - E-Ticketing System</title>
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
    <h2>Create an Account</h2>
    <form method="POST" action="signup.php">
        <div class="form-group">
            <input type="text" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>">
            <span class="error"><?php echo $name_err; ?></span>
        </div>

        <div class="form-group">
            <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>">
            <span class="error"><?php echo $email_err; ?></span>
        </div>

        <div class="form-group">
            <input type="password" name="password" placeholder="Enter your password">
            <span class="error"><?php echo $password_err; ?></span>
        </div>

        <div class="form-group">
            <input type="submit" value="Sign Up">
        </div>
    </form>
    <div class="back-link">
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>

</body>
</html>

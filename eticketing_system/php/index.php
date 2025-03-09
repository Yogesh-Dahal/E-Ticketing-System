<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticketing System - Welcome</title>
    <style>
        /* Reset default margin and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://www.shutterstock.com/image-photo/white-passenger-bus-side-view-600nw-2488920331.jpg'); /* Replace with your image URL */
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        /* Overlay to darken background */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        /* Container for content */
        .content {
            z-index: 1;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #fff;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        .btn {
            background-color: #FF5733;
            border: none;
            padding: 15px 25px;
            margin: 15px;
            font-size: 1.2rem;
            color: white;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #C1351D;
        }

        .btn-container {
            display: flex;
            justify-content: center;
        }

        .btn-container a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="content">
        <h1>Welcome to the E-Ticketing System</h1>
        <div class="btn-container">
            <a href="login.php">
                <button class="btn">Login</button>
            </a>
            <a href="signup.php">
                <button class="btn">Sign Up</button>
            </a>
        </div>
    </div>
</body>
</html>

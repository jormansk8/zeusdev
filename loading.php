
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZEUSDEV</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #29ABE2;
            margin: 0;
        }

        img.logo {
            width: 100px;
            height: 100px;
            position: absolute;
            top: calc(50% - 50px);
            left: calc(50% - 50px);
        }

        .progress-container {
            position: absolute;
            top: calc(50% + 60px);
            left: calc(50% - 50px);
            width: 100px;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.4);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: #ffffff;
            width: 0%;
            animation: loading 3s ease-in-out infinite;
        }

        @keyframes loading {
            0% {
                width: 0%;
            }
            50% {
                width: 100%;
            }
            100% {
                width: 0%;
            }
        }
    </style>
</head>
<body>
    <img src="zeusdev.png" alt="ZeusDev Logo" class="logo">
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    <script>
        window.history.pushState({}, document.title, "/zeusdev");

        setTimeout(function() {
            window.location.href = "dashboard.php";
        }, 3000);
    </script>
</body>
</html>

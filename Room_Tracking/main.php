<?php
    //0: untrigger, locked
    //1: triggered, login
    //2: tempkey inputted, mayoral
    //3: tempkey inputted, maintenance
    //4: tempkey inputted, temporary
    //9: Transaction Completed, timer for 5 minutes to allow parties to leave
    //10: restricted, alerted admin
    $mode = isset($_GET['mode']) ? (int)$_GET['mode'] : 0;

    //Key Types
    //1: Mayoral
    //2: Maintenance
    //3: Temporary

    require_once('db_connect.php');
    $sql = "SELECT * FROM professors";
    $result = $conn->query($sql);
    $professors = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $professors[] = $row;
        }
    } else {
        echo "No records found.";
    }

    $sql2 = "SELECT * FROM tempkeys";
    $result2 = $conn->query($sql2);
    $tempkeys = [];
    if ($result2->num_rows > 0) {
        while ($row = $result2->fetch_assoc()) {
            $tempkeys[] = $row;
        }
    } else {
        echo "No records found.";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative;
        }
        body.mode-10 {
            background-color: red;
            color: white;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            font-family: Arial, sans-serif;
            background-color: #fff;
            appearance: none;
        }
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }
        .login-container {
            background: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #a02832;
        }
        .text-message {
            font-size: 24px;
            color: #333;
        }
        .text-message-locked {
            font-size: 24px;
        }
        .simulate-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .simulate-btn:hover {
            background-color: #a02832;
        }
        #timer {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 50px;
            font-family: Arial, sans-serif;
            color: #333;
        }
    </style>
</head>
<body class="<?php echo $mode === 10 ? 'mode-10' : ''; ?>">
    <?php if ($mode === 1): ?>
        <!-- TRIPWIRE SHOULD BE DOWN -->
        <div id="timer"></div>
        <div class = "container">
            <div class="login-container">
                <h2>Login</h2>
                <form>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <select id="section" name="section" required>
                            <option value="" disabled selected>Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="trylogin()">Login</button>
                    </div>
                </form>
            </div>
            <div class="login-container">
                <h2>Temporary Key</h2>
                <form>
                    <div class="form-group">
                        <label for="key">Key</label>
                        <input type="text" id="key" name="key" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="trykey()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($mode ===  0): ?>
        <!-- TRIPWIRE SHOULD BE UP -->
        <button class="simulate-btn" onclick="simulateDetection()">Simulate Detection</button>
        <div class="text-message">
            <p>Locked. The alarm is currently active.</p>
        </div>
    <?php elseif ($mode === 2): ?>
        <!-- TRIPWIRE SHOULD BE DOWN -->
        <div id="timer"></div>
        <div class="container">
            <div class="text-message" style="font-size: 16px;">
                <p>Mayoral Temporary Key Entered.</p>
            </div>
            <div class="login-container">
                <h2>Login</h2>
                <form>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <select id="section" name="section" required>
                            <option value="" disabled selected>Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="trylogin()">Login</button>
                    </div>
                </form>
            </div>
            <div class="form-group">
                <button type="button" onclick="extendtimer()">Extend for 5 minutes</button>
            </div>
            <div class="form-group">
                <button type="button" onclick="abandonkey()">End Class</button>
            </div>
        </div>
    <?php elseif ($mode === 3): ?>
        <!-- TRIPWIRE SHOULD BE DOWN -->
        <div class="container">
            <div class="text-message">
                <p>Maintenance key inputted. Alarms are down until the button is pushed.</p>
            </div>
            <div class="form-group">
                <button type="button" onclick="abandonkey()">Finished maintenance</button>
            </div>
        </div>
    <?php elseif ($mode === 4): ?>
        <!-- TRIPWIRE SHOULD BE DOWN -->
        <div class="container">
            <div id="timer"></div>
            <div class="text-message">
                <p>Temporary key inputted. Reentering of key is required to extend timer.</p>
            </div>
            <div class="login-container">
                <h2>Temporary Key</h2>
                <form>
                    <div class="form-group">
                        <label for="key">Key</label>
                        <input type="text" id="key" name="key" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="trykey()">Extend Timer</button>
                    </div>
                </form>
            </div>
            <div class="form-group">
                <button type="button" onclick="abandonkey()">Finish Task</button>
            </div>
        </div>
    <?php elseif ($mode === 9): ?>
        <!-- TRIPWIRE SHOULD BE DOWN -->
        <div id="timer"></div>
        <div class="text-message">
            <p>You may leave the room. After the timer stops, the alarms will be turned on.</p>
        </div>
    <?php elseif ($mode === 10): ?>
        <!-- TRIPWIRE SHOULD BE DOWN -->
        <div class="text-message-locked">
            <p>Restricted. Contact Administrator.</p>
        </div>
    <?php endif; ?>

    <script>
        let totalSeconds = 300;
        const alarm = new Audio('alertsound.mp3');
        const morealarm = new Audio('alarmingsound.mp3');
        const erroralarm = new Audio('errorsound.mp3');
        const professors = <?php echo json_encode($professors); ?>;
        const tempkeys = <?php echo json_encode($tempkeys); ?>;

        function updateTimer() {
            const timerElement = document.getElementById('timer');
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;

            timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            
            if (seconds === 0 && minutes === 1) {
                morealarm.play()
            } else if (seconds === 0 && minutes !== 0) {
                alarm.play()
            }

            if (totalSeconds > 0) {
                totalSeconds--;
            } else {
                if (<?php echo $mode; ?> === 1) {
                    erroralarm.play().then(() => {
                        setTimeout(() => {
                            window.location.href = '?mode=10';
                        }, 1000);
                    });
                } else if (<?php echo $mode; ?> === 2 || <?php echo $mode; ?> ===  4) {
                    //TODO: DELETE ENTERED KEY
                    erroralarm.play().then(() => {
                        setTimeout(() => {
                            window.location.href = '?mode=9';
                        }, 1000);
                    });
                } else if (<?php echo $mode; ?> === 9) {
                    erroralarm.play().then(() => {
                        setTimeout(() => {
                            window.location.href = '?mode=0';
                        }, 1000);
                    });
                }
            }
        }

        const timerInterval = setInterval(updateTimer, 1000);

        function simulateDetection() {
            alarm.play().then(() => {
            setTimeout(() => {
                window.location.href = '?mode=1';
            }, 1000);
            });
        }

        function trylogin() {
            //TODO: ENCRYPT PASSWORD
            //TODO: PROCEED TO STUDENT LOGIN
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const professor = professors.find(prof => prof.username === username && prof.password === password);

            if (professor) {
                alert('Login successful!');
            } else {
                alert('Invalid username or password.');
            }
        }

        function trykey() {
            const keyInput = document.getElementById('key');
            const key = keyInput.value;
            const tempkey = tempkeys.find(temp => temp.tempkey == key);

            if (tempkey) {
                if (<?php echo $mode; ?> === 1) {
                    if (tempkey.type == 1) {
                    window.location.href = '?mode=2';
                    } else if (tempkey.type == 2) {
                        window.location.href = '?mode=3';
                    } else if (tempkey.type == 3) {
                        window.location.href = '?mode=4';
                    } else {
                        alert('Invalid key type.');
                    }
                }
                else if (<?php echo $mode; ?> === 4) {
                    if (tempkey.type == 3) {
                        extendtimer();
                        keyInput.value = "";
                    } else {
                        alert('Invalid key.');
                    }
                }
            } else {
                alert('Invalid key.');
            }
        }

        function extendtimer() {
            if (totalSeconds < 60) {
                totalSeconds += 300;
            } else {
                totalSeconds = 300;
            }
        }

        function abandonkey() {
            //TODO: DELETE ENTERED KEY
            window.location.href = '?mode=9';
        }

    </script>
</body>
</html>
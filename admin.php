<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session for login management
session_start();
include('database_config.php');

// Check if database connection is working
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please check the server error log for details.");
}

// Debug information for troubleshooting
if (isset($_POST['login'])) {
    error_log("Login attempt: Username: " . ($_POST['username'] ?? 'not provided') . ", Password provided: " . (!empty($_POST['password']) ? 'Yes' : 'No'));
}

// Login credentials
$admin_username = "admin";
$admin_password = "aakash@5574";

$login_error = "";
$success_message = "";

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        
        // Debug session information
        error_log("Login successful. Session data: " . print_r($_SESSION, true));
        
        // Redirect with absolute URL to avoid potential issues
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("Location: {$protocol}{$host}{$uri}/admin.php");
        exit;
    } else {
        $login_error = "Invalid username or password";
        error_log("Login failed: Username provided was '$username'");
    }
}

// Handle message deletion
if (isset($_GET['delete_message']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $id = $_GET['delete_message'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Message deleted successfully!";
    } else {
        $login_error = "Error deleting message: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle donation deletion
if (isset($_GET['delete_donation']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $id = $_GET['delete_donation'];
    $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Donation record deleted successfully!";
    } else {
        $login_error = "Error deleting donation: " . $conn->error;
    }
    
    $stmt->close();
}

// Function to get all messages
function getMessages($conn) {
    $messages = [];
    try {
        $result = $conn->query("SELECT * FROM messages ORDER BY id DESC");
        
        if ($result === false) {
            error_log("Error in getMessages query: " . $conn->error);
            return [];
        }
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Exception in getMessages: " . $e->getMessage());
    }
    
    return $messages;
}

// Function to get all donations
function getDonations($conn) {
    $donations = [];
    try {
        $result = $conn->query("SELECT * FROM donations ORDER BY id DESC");
        
        if ($result === false) {
            error_log("Error in getDonations query: " . $conn->error);
            return [];
        }
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $donations[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Exception in getDonations: " . $e->getMessage());
    }
    
    return $donations;
}

// Get data if logged in
$messages = [];
$donations = [];

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $messages = getMessages($conn);
    $donations = getDonations($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Watchespati Foundation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true): ?>
        <!-- Login Form -->
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
                <h1 class="text-2xl font-bold text-center text-blue-800 mb-6">Admin Login</h1>
                
                <?php if (!empty($login_error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $login_error; ?></p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                        <input type="text" id="username" name="username" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button type="submit" name="login"
                        class="w-full bg-blue-800 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                        Login
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="min-h-screen flex flex-col">
            <!-- Header -->
            <header class="bg-blue-900 text-white shadow-lg">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Watchespati Foundation - Admin Panel</h1>
                    <a href="admin.php?logout=1" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                        Logout
                    </a>
                </div>
            </header>
            
            <!-- Main Content -->
            <div class="container mx-auto px-6 py-8 flex-grow">
                <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p><?php echo $success_message; ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-300">
                        <ul class="flex flex-wrap -mb-px">
                            <li class="mr-2">
                                <button class="tab-btn inline-block py-4 px-6 text-blue-800 font-medium border-b-2 border-blue-800" data-tab="donations">
                                    Donations
                                </button>
                            </li>
                            <li class="mr-2">
                                <button class="tab-btn inline-block py-4 px-6 text-gray-500 hover:text-blue-800 font-medium border-b-2 border-transparent hover:border-blue-800" data-tab="messages">
                                    Contact Messages
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Donations Tab -->
                <div id="donations" class="tab-content active">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Donation Records</h2>
                    
                    <?php if (empty($donations)): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p>No donations found.</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden shadow-md">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="py-3 px-4 text-left">ID</th>
                                    <th class="py-3 px-4 text-left">Name</th>
                                    <th class="py-3 px-4 text-left">Phone Number</th>
                                    <th class="py-3 px-4 text-left">Amount</th>
                                    <th class="py-3 px-4 text-left">Transaction ID</th>
                                    <th class="py-3 px-4 text-left">Date</th>
                                    <th class="py-3 px-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($donations as $donation): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4"><?php echo $donation['id']; ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($donation['name']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($donation['phone_number']); ?></td>
                                    <td class="py-3 px-4">₹<?php echo number_format($donation['amount']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($donation['transaction_id']); ?></td>
                                    <td class="py-3 px-4"><?php echo date('d M Y h:i A', strtotime($donation['donation_date'])); ?></td>
                                    <td class="py-3 px-4">
                                        <a href="admin.php?delete_donation=<?php echo $donation['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this donation record?')"
                                           class="text-red-600 hover:text-red-800">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Messages Tab -->
                <div id="messages" class="tab-content">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Contact Messages</h2>
                    
                    <?php if (empty($messages)): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p>No messages found.</p>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($messages as $msg): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">
                                        <?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-2">
                                        From: <?php echo htmlspecialchars($msg['name']); ?> 
                                        (<?php echo htmlspecialchars($msg['email']); ?>)
                                    </p>
                                </div>
                                <a href="admin.php?delete_message=<?php echo $msg['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this message?')"
                                   class="text-red-600 hover:text-red-800">
                                    Delete
                                </a>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($msg['message']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => {
                        btn.classList.remove('text-blue-800', 'border-blue-800');
                        btn.classList.add('text-gray-500', 'border-transparent');
                    });
                    
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Add active class to clicked button and corresponding content
                    button.classList.add('text-blue-800', 'border-blue-800');
                    button.classList.remove('text-gray-500', 'border-transparent');
                    
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        </script>
    <?php endif; ?>
</body>
</html> 
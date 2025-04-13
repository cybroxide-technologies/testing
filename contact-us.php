<?php 
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('database_config.php'); 

$message = '';
$donation_message = '';

// Process contact form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'contact') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message_text = $_POST['message'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($message_text)) {
        // Create messages table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL
        )";
        
        if ($conn->query($create_table_sql) === TRUE) {
            // Insert message data
            $sql = "INSERT INTO messages (name, email, subject, message) 
                    VALUES (?, ?, ?, ?)";   
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $subject, $message_text);
            
            if ($stmt->execute()) {
                $message = "Thank you for contacting us. We'll get back to you soon.";
            } else {
                $message = "Error: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $message = "Error setting up message system: " . $conn->error;
        }
    } else {
        $message = "Please fill all required fields.";
    }
}

// Process donation form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'donation') {
    $donor_name = $_POST['donor_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    
    if (!empty($donor_name) && !empty($phone_number) && !empty($amount) && !empty($transaction_id)) {
        // Create donations table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS donations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone_number VARCHAR(15) NOT NULL,
            amount INT NOT NULL,
            transaction_id VARCHAR(100) NOT NULL,
            donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table_sql) === TRUE) {
            // Insert donation data
            $sql = "INSERT INTO donations (name, phone_number, amount, transaction_id) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssis", $donor_name, $phone_number, $amount, $transaction_id);
            
            if ($stmt->execute()) {
                $donation_message = "Thank you for your generous donation of ₹" . $amount . ". Our team will verify your transaction ID and update you shortly.";
            } else {
                $donation_message = "Error processing donation: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $donation_message = "Error setting up donation system: " . $conn->error;
        }
    } else {
        $donation_message = "Please fill all required donation fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Watchespati Foundation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 50;
            overflow-y: auto;
            padding: 20px;
        }
        .modal-content {
            animation: modalFadeIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card-info {
            transition: all 0.3s ease;
            background-size: 200% 100%;
            background-position: right bottom;
        }
        .card-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            background-position: left bottom;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Info Bar -->
    <div class="bg-gray-100 py-2">
        <div class="container mx-auto px-6 flex justify-between items-center text-sm">
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">info@watchespatifoundation.org</span>
                <span class="text-gray-600">+91 9876543210</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="#" class="text-gray-600 hover:text-blue-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                </a>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-1.308c0-1.769.931-2.692 3.029-2.692h1.971v2z"/></svg>
                </a>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Header/Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="home.php" class="flex items-center">
                    <img src="https://res.cloudinary.com/anurupmillan/image/upload/v1744543309/watchespati/ChatGPT_Image_Apr_13_2025_04_51_39_PM_d4usja.png" alt="Logo" class="h-20 w-20 mr-3 rounded-full">
                    <span class="text-xl font-bold text-gray-800">Watchespati Foundation</span>
                </a>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="home.php" class="font-medium text-gray-600 hover:text-blue-600">Home</a>
                <a href="about_us.php" class="font-medium text-gray-600 hover:text-blue-600">About us</a>
                <!-- <a href="#events" class="font-medium text-gray-600 hover:text-blue-600">Events</a> -->
                <!-- <a href="#" class="font-medium text-gray-600 hover:text-blue-600">Pages</a> -->
                <!-- <a href="#news" class="font-medium text-gray-600 hover:text-blue-600">News</a> -->
                <a href="contact-us.php" class="font-medium text-gray-800 hover:text-blue-600">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <button id="donateBtn" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    Donate now
                </button>
            </div>
        </nav>
    </header>

    <!-- Contact Header -->
    <section class="py-20 bg-blue-900 text-white relative overflow-hidden">
        <div class="container mx-auto px-6 relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
            <p class="text-xl">Reach out to discuss how we can support Indian Army officials in legal matters</p>
        </div>
        <!-- Background circle decorations -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-orange-500 rounded-full opacity-20"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-800 rounded-full opacity-30"></div>
    </section>

    <!-- Contact Form & Info -->
    <section class="container mx-auto px-6 py-16 -mt-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-md p-8 card-info bg-gradient-to-r from-blue-50 to-blue-100">
                <h2 class="text-3xl font-bold text-blue-800 mb-6">Get in Touch</h2>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mr-4 text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 text-lg">Address</h3>
                            <p class="text-gray-600">123 Army Colony, Sector 4<br>New Delhi, 110001</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mr-4 text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 text-lg">Phone</h3>
                            <p class="text-gray-600">+91 9876543210</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mr-4 text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 text-lg">Email</h3>
                            <p class="text-gray-600">info@watchespatifoundation.org</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="font-semibold text-lg text-gray-800 mb-3">Legal Consultation Hours</h3>
                    <p class="text-gray-600">Monday - Friday: 9 AM - 5 PM</p>
                    <p class="text-gray-600">Saturday: 10 AM - 2 PM</p>
                    <p class="text-gray-600">Sunday: Closed</p>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-gray-700 font-medium">For urgent legal matters related to serving Army officials, please call our 24/7 helpline.</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-3xl font-bold text-blue-800 mb-6">Send Us a Message</h2>
                
                <?php if (!empty($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
                <?php endif; ?>
                
                <form action="contact-us.php" method="POST">
                    <input type="hidden" name="form_type" value="contact">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-semibold mb-2">Name *</label>
                        <input type="text" id="name" name="name" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-semibold mb-2">Email *</label>
                        <input type="email" id="email" name="email" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject</label>
                        <input type="text" id="subject" name="subject" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="message" class="block text-gray-700 font-semibold mb-2">Message *</label>
                        <textarea id="message" name="message" rows="5" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" 
                        class="bg-orange-500 text-white font-bold py-2 px-6 rounded-lg hover:bg-orange-600 transition duration-300">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Our Location</h2>
                <div class="w-20 h-1 bg-orange-500 mx-auto"></div>
                <p class="mt-4 text-gray-600">Visit our office in Delhi for in-person legal consultation</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d56069.38027775632!2d77.19062414014363!3d28.5571664546666!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce3ed421cb24d%3A0x5c530658f1def2bf!2sArmy%20Colony%2C%20New%20Delhi%2C%20Delhi!5e0!3m2!1sen!2sin!4v1744576814790!5m2!1sen!2sin" 
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-8 mt-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-2xl font-bold">Watchespati Foundation</h2>
                    <p class="mt-2">Supporting Indian Army Officials Since 2010</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-2">Quick Links</h3>
                    <ul>
                        <li><a href="home.php" class="hover:text-blue-200">Home</a></li>
                        <li><a href="about_us.php" class="hover:text-blue-200">About Us</a></li>
                        <li><a href="contact-us.php" class="hover:text-blue-200">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-6 text-center">
                <p>&copy; <?php echo date("Y"); ?> Watchespati Foundation. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Donate Modal -->
    <div id="donationModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 overflow-y-auto" style="max-height: 90vh">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold text-gray-900">Make a Donation</h3>
                <button id="closeModal" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal content -->
            <div class="p-6">
                <?php if (!empty($donation_message)): ?>
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    <span class="font-medium">
                        <svg class="inline-block w-5 h-5 mr-1 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Thank you!
                    </span> 
                    <?php echo $donation_message; ?>
                </div>
                <?php endif; ?>
                
                <form id="donationForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="form_type" value="donation">
                    <input type="hidden" id="donationAmount" name="amount" value="">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label for="donor_name" class="block text-sm font-medium text-gray-900 mb-2">Full Name *</label>
                                <input type="text" id="donor_name" name="donor_name" required 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                            
                            <div class="mb-4">
                                <label for="phone_number" class="block text-sm font-medium text-gray-900 mb-2">Phone Number *</label>
                                <input type="tel" id="phone_number" name="phone_number" required 
                                    pattern="[0-9]{10}" maxlength="10"
                                    placeholder="10-digit number (numbers only)"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <p class="mt-1 text-xs text-gray-500">Enter 10 digits without spaces, dashes or country code</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Select Amount</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-700 font-medium" data-amount="500">₹500</button>
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-700 font-medium" data-amount="1000">₹1000</button>
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-700 font-medium" data-amount="2000">₹2000</button>
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-700 font-medium" data-amount="5000">₹5000</button>
                                </div>
                            </div>
                            
                            <div class="mb-4 relative">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Custom Amount</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                                    <input type="text" id="customAmount" placeholder="Other Amount" 
                                        class="w-full py-2 px-4 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                                </div>
                            </div>
                        </div>
                        
                        <div id="upiDetails" class="space-y-4">
                            <div class="text-center">
                                <div id="qrcode" class="mx-auto h-48 w-48 border rounded flex items-center justify-center bg-white">
                                    <div class="text-gray-400">QR code will appear here</div>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Scan this QR code to pay via UPI</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    UPI ID: <span class="font-mono font-medium">8902249102@ybl</span>
                                </p>
                            </div>
                            
                            <div>
                                <label for="transaction_id" class="block text-sm font-medium text-gray-900 mb-2">Transaction ID *</label>
                                <input type="text" id="transaction_id" name="transaction_id" placeholder="Enter your UPI transaction ID" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <p class="mt-1 text-sm text-gray-500">Please enter your transaction ID for verification</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" id="proceedBtn" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                            Submit Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const donateBtn = document.getElementById('donateBtn');
        const donationModal = document.getElementById('donationModal');
        const closeModalBtn = document.getElementById('closeModal');
        const qrElement = document.getElementById('qrcode');
        let qrCode = null;
        
        // Check if there's a donation message and show modal
        <?php if (!empty($donation_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            donationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
        <?php endif; ?>
        
        donateBtn.addEventListener('click', function() {
            donationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
            
            // Generate QR code if amount is selected
            const amount = document.getElementById('customAmount').value;
            if (amount && parseFloat(amount) > 0) {
                generateQRCode(amount);
            }
        });
        
        closeModalBtn.addEventListener('click', function() {
            donationModal.classList.add('hidden');
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        });
        
        // Close modal when clicking outside the content
        window.addEventListener('click', function(event) {
            if (event.target === donationModal) {
                donationModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });

        // Amount buttons
        const amountBtns = document.querySelectorAll('.amount-btn');
        const customAmount = document.getElementById('customAmount');
        const donationAmount = document.getElementById('donationAmount');
        
        // Generate QR code based on amount
        function generateQRCode(amount) {
            // Clear previous QR code
            if (qrCode) {
                qrCode.clear();
            }
            qrElement.innerHTML = '';
            
            // UPI Payment URL
            const upiUrl = `upi://pay?pa=8902249102@ybl&pn=Watchespati%20Foundation&am=${amount}&cu=INR`;
            
            // Generate new QR code
            qrCode = new QRCode(qrElement, {
                text: upiUrl,
                width: 192,
                height: 192,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
        
        amountBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent button from submitting form
                
                // Remove active class from all buttons
                amountBtns.forEach(b => {
                    b.classList.remove('bg-blue-500', 'text-white');
                    b.classList.add('bg-blue-50', 'text-blue-700');
                });
                
                // Add active class to clicked button
                this.classList.remove('bg-blue-50', 'text-blue-700');
                this.classList.add('bg-blue-500', 'text-white');
                
                // Set the amount value
                const amount = this.getAttribute('data-amount');
                donationAmount.value = amount;
                customAmount.value = amount;
                
                // Generate QR code for this amount
                generateQRCode(amount);
            });
        });
        
        // Custom amount input
        customAmount.addEventListener('input', function() {
            // Clear selected amount buttons
            amountBtns.forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-blue-50', 'text-blue-700');
            });
            
            // Update hidden amount field
            donationAmount.value = this.value;
            
            // Generate QR code if amount is valid
            if (this.value && parseFloat(this.value) > 0) {
                generateQRCode(this.value);
            }
        });
        
        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            const amount = donationAmount.value;
            const phoneNumber = document.getElementById('phone_number').value;
            
            if (!amount || parseFloat(amount) <= 0) {
                e.preventDefault();
                alert('Please select or enter a valid donation amount');
                return;
            }
            
            // Check if phone number contains only digits
            if (!/^\d+$/.test(phoneNumber)) {
                e.preventDefault();
                alert('Please enter a valid phone number with numbers only (no spaces or special characters)');
                return;
            }
            
            // Check if phone number is within the acceptable range
            if (phoneNumber.length > 10) {
                e.preventDefault();
                alert('Phone number is too long. Please enter a valid 10-digit phone number without country code');
                return;
            }
        });
    </script>
</body>
</html> 
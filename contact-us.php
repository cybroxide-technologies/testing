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
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header/Navbar -->
    <header class="bg-blue-900 text-white shadow-lg">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="text-xl font-bold">Watchespati Foundation</span>
            </div>
            <div class="space-x-4">
                <a href="home.php" class="font-semibold hover:text-blue-200">Home</a>
                <a href="about_us.php" class="font-semibold hover:text-blue-200">About Us</a>
                <a href="contact-us.php" class="font-semibold hover:text-blue-200">Contact Us</a>
                <button id="donateBtn" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                    Donate Now
                </button>
            </div>
        </nav>
    </header>

    <!-- Contact Header -->
    <section class="bg-blue-800 text-white py-16">
        <div class="container mx-auto px-6">
            <h1 class="text-4xl font-bold mb-4">Contact Us</h1>
            <p class="text-xl">Reach out to us for assistance or inquiries</p>
        </div>
    </section>

    <!-- Contact Form & Info -->
    <section class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-3xl font-bold text-blue-800 mb-6">Get in Touch</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="text-blue-800 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Address</h3>
                            <p class="text-gray-600">123 Army Colony, Sector 4<br>New Delhi, 110001</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-blue-800 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Phone</h3>
                            <p class="text-gray-600">+91 9876543210</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="text-blue-800 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Email</h3>
                            <p class="text-gray-600">info@watchespatifoundation.org</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="font-semibold text-lg text-gray-800 mb-3">Office Hours</h3>
                    <p class="text-gray-600">Monday - Friday: 9 AM - 5 PM</p>
                    <p class="text-gray-600">Saturday: 10 AM - 2 PM</p>
                    <p class="text-gray-600">Sunday: Closed</p>
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
                        class="bg-blue-800 text-white font-bold py-2 px-6 rounded-md hover:bg-blue-700 transition duration-300">
                        Send Message
                    </button>
                </form>
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
    <div id="donateModal" class="modal">
        <div class="flex items-center justify-center min-h-screen">
            <div class="modal-content bg-white rounded-lg shadow-xl max-w-2xl w-full p-8 relative">
                <button id="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <h2 class="text-2xl font-bold text-blue-800 mb-4 text-center">Support Our Cause</h2>
                <p class="text-gray-600 mb-6 text-center">Your contribution helps us support Indian Army Officials and their families.</p>
                
                <?php if (!empty($donation_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6 shadow-md" role="alert">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="font-medium"><?php echo $donation_message; ?></p>
                    </div>
                    <p class="mt-2 text-sm">We appreciate your support to our cause.</p>
                </div>
                <?php endif; ?>
                
                <form id="donationForm" action="contact-us.php" method="POST">
                    <input type="hidden" name="form_type" value="donation">
                    <input type="hidden" id="donationAmount" name="amount" value="">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label for="donor_name" class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                <input type="text" id="donor_name" name="donor_name" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                                <input type="tel" id="phone_number" name="phone_number" required 
                                    pattern="[0-9]{10}" maxlength="10"
                                    placeholder="10-digit number (numbers only)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Enter 10 digits without spaces, dashes or country code</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Select Amount</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-800 font-medium" data-amount="500">₹500</button>
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-800 font-medium" data-amount="1000">₹1000</button>
                                    <button type="button" class="amount-btn bg-blue-50 hover:bg-blue-100 py-2 px-4 rounded border border-blue-200 text-blue-800 font-medium" data-amount="2000">₹2000</button>
                                </div>
                            </div>
                            
                            <div class="mb-4 relative">
                                <label class="block text-gray-700 font-medium mb-2">Custom Amount</label>
                                <input type="text" id="customAmount" placeholder="Other Amount" 
                                    class="w-full py-2 px-4 pl-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <span class="absolute left-3 top-10 text-gray-500">₹</span>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Payment Method</label>
                                <div>
                                    <button type="button" id="upiBtn" class="payment-method bg-white hover:bg-gray-50 py-2 px-3 rounded border border-gray-300 flex items-center justify-center w-full">
                                        <img src="https://cdn-icons-png.flaticon.com/512/888/888870.png" alt="UPI" class="h-6 w-6 mr-2">
                                        UPI
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div id="upiDetails" class="hidden space-y-4">
                            <div class="text-center">
                                <div id="upiQrCode" class="mx-auto h-56 w-56 border rounded flex items-center justify-center bg-white">
                                    <div class="text-gray-400">QR code will appear here</div>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">Scan this QR code to pay via UPI</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Or send directly to: <span class="font-mono font-medium">8902249102@ybl</span>
                                </p>
                            </div>
                            
                            <div>
                                <label for="transaction_id" class="block text-gray-700 font-medium mb-2">Transaction ID *</label>
                                <input type="text" id="transaction_id" name="transaction_id" placeholder="Enter your UPI transaction ID" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Please enter your transaction ID for verification</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" id="proceedBtn" class="w-full bg-blue-800 text-white font-bold py-3 px-6 rounded-md hover:bg-blue-700 transition duration-300">
                            Proceed to Donate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const donateBtn = document.getElementById('donateBtn');
        const donateModal = document.getElementById('donateModal');
        const closeModal = document.getElementById('closeModal');
        const upiDetails = document.getElementById('upiDetails');
        let qrCode = null;
        
        // Check if there's a donation message and show modal
        <?php if (!empty($donation_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            donateModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            upiDetails.classList.remove('hidden');
        });
        <?php endif; ?>
        
        donateBtn.addEventListener('click', function() {
            donateModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
            // Always show UPI details when modal opens for better layout
            upiDetails.classList.remove('hidden');
        });
        
        closeModal.addEventListener('click', function() {
            donateModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        });
        
        // Close modal when clicking outside the content
        window.addEventListener('click', function(event) {
            if (event.target === donateModal) {
                donateModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Amount buttons
        const amountBtns = document.querySelectorAll('.amount-btn');
        const customAmount = document.getElementById('customAmount');
        const donationAmount = document.getElementById('donationAmount');
        const qrElement = document.getElementById('upiQrCode');
        
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
            btn.addEventListener('click', function() {
                amountBtns.forEach(b => b.classList.remove('bg-blue-200'));
                this.classList.add('bg-blue-200');
                
                // Set the amount value
                const amount = this.getAttribute('data-amount');
                donationAmount.value = amount;
                customAmount.value = '';
                
                // Generate QR code for this amount
                generateQRCode(amount);
            });
        });
        
        // Custom amount input
        customAmount.addEventListener('input', function() {
            // Clear selected amount buttons
            amountBtns.forEach(btn => btn.classList.remove('bg-blue-200'));
            
            // Update hidden amount field
            donationAmount.value = this.value;
            
            // Generate QR code if amount is valid
            if (this.value && parseFloat(this.value) > 0) {
                generateQRCode(this.value);
            }
        });
        
        // Payment method buttons
        const paymentBtns = document.querySelectorAll('.payment-method');
        paymentBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                paymentBtns.forEach(b => b.classList.remove('border-blue-500', 'border-2'));
                this.classList.add('border-blue-500', 'border-2');
            });
        });
        
        // Show UPI details when UPI option is selected
        const upiBtn = document.getElementById('upiBtn');
        const proceedBtn = document.getElementById('proceedBtn');
        
        upiBtn.addEventListener('click', function() {
            upiDetails.classList.remove('hidden');
            proceedBtn.textContent = 'Submit Payment';
            
            // Generate QR code if amount is already selected
            const amount = donationAmount.value;
            if (amount && parseFloat(amount) > 0) {
                generateQRCode(amount);
            }
        });
        
        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            const amount = donationAmount.value;
            const phoneNumber = document.getElementById('phone_number').value;
            
            if (!amount || amount <= 0) {
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
            
            // Check if phone number is within the acceptable range for an integer field
            if (phoneNumber.length > 10) {
                e.preventDefault();
                alert('Phone number is too long. Please enter a valid 10-digit phone number without country code');
                return;
            }
        });
    </script>
</body>
</html> 
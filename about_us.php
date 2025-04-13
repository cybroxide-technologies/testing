<?php 
include('database_config.php');

$donation_message = '';

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
    <title>About Us - Watchespati Foundation</title>
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

    <!-- About Us Header -->
    <section class="bg-blue-800 text-white py-16">
        <div class="container mx-auto px-6">
            <h1 class="text-4xl font-bold mb-4">About Watchespati Foundation</h1>
            <p class="text-xl">Dedicated to supporting Indian Army officials in their legal battles</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-lg shadow-md p-8 mb-10">
            <h2 class="text-3xl font-bold text-blue-800 mb-6">Our Mission</h2>
            <p class="text-gray-700 mb-4">
                The Watchespati Foundation was established to provide legal assistance and support to Indian Army officials 
                who face legal challenges in the line of duty. We believe that those who protect our nation deserve protection 
                themselves when facing legal battles.
            </p>
            <p class="text-gray-700 mb-4">
                Our mission is to ensure that every Indian Army official has access to quality legal representation, 
                resources, and support throughout their legal proceedings.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8 mb-10">
            <h2 class="text-3xl font-bold text-blue-800 mb-6">Our History</h2>
            <p class="text-gray-700 mb-4">
                Founded in 2010, the Watchespati Foundation began as a small initiative by a group of lawyers with 
                military backgrounds who recognized the need for specialized legal support for army personnel.
            </p>
            <p class="text-gray-700 mb-4">
                Over the years, we have grown into a recognized organization that has successfully assisted in numerous 
                legal cases, providing both representation and resources to those who serve our country.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-3xl font-bold text-blue-800 mb-6">Our Team</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
                    <h3 class="text-xl font-bold text-blue-800">Maj. Rajiv Kumar (Retd.)</h3>
                    <p class="text-gray-600">Founder & President</p>
                </div>
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
                    <h3 class="text-xl font-bold text-blue-800">Adv. Priya Sharma</h3>
                    <p class="text-gray-600">Chief Legal Advisor</p>
                </div>
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
                    <h3 class="text-xl font-bold text-blue-800">Col. Vikram Singh (Retd.)</h3>
                    <p class="text-gray-600">Director of Operations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-8">
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
                
                <form id="donationForm" action="about_us.php" method="POST">
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
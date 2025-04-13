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
        
        .card-donation {
            transition: all 0.3s ease;
            background-size: 200% 100%;
            background-position: right bottom;
        }
        .card-donation:hover {
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
                <span class="text-gray-600">+91 123 456 7890</span>
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
                <a href="about_us.php" class="font-medium text-gray-800 hover:text-blue-600">About us</a>
                <!-- <a href="#causes" class="font-medium text-gray-600 hover:text-blue-600">Causes</a> -->
                <!-- <a href="#events" class="font-medium text-gray-600 hover:text-blue-600">Events</a> -->
                <!-- <a href="#" class="font-medium text-gray-600 hover:text-blue-600">Pages</a> -->
                <!-- <a href="#news" class="font-medium text-gray-600 hover:text-blue-600">News</a> -->
                <a href="contact-us.php" class="font-medium text-gray-600 hover:text-blue-600">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <button id="donateBtn" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    Donate now
                </button>
            </div>
        </nav>
    </header>

    <!-- About Us Header -->
    <section class="py-20 bg-blue-900 text-white relative overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="max-w-2xl relative z-10">
                <h1 class="text-5xl font-bold mb-4">About Watchespati Foundation</h1>
                <p class="text-xl text-blue-100">Dedicated to supporting Indian Army officials in their legal battles</p>
            </div>
        </div>
        <!-- Background circle decorations -->
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-800 rounded-full opacity-50"></div>
        <div class="absolute -top-20 -left-20 w-64 h-64 bg-orange-500 rounded-full opacity-20"></div>
    </section>

    <!-- Main Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 lg:pr-12 mb-10 lg:mb-0">
                    <div class="mb-8">
                        <span class="text-orange-500 font-medium">Our Mission</span>
                        <h2 class="text-4xl font-bold text-gray-800 mt-2 mb-4">
                            Supporting those who <span class="text-orange-500">protect</span><br>
                            our nation
                        </h2>
                    </div>
                    
                    <div class="space-y-4 mb-8">
                        <p class="text-gray-600">
                            The Watchespati Foundation was established to provide legal assistance and support to Indian Army officials 
                            who face legal challenges in the line of duty. We believe that those who protect our nation deserve protection 
                            themselves when facing legal battles.
                        </p>
                        <p class="text-gray-600">
                            Our mission is to ensure that every Indian Army official has access to quality legal representation, 
                            resources, and support throughout their legal proceedings.
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="#history" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                            Learn more
                        </a>
                        <a href="#contact" class="flex items-center text-gray-700 hover:text-orange-500 font-medium">
                            <span class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Contact us
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <img src="https://placehold.co/800x400/blue/white?text=Foundation" alt="Foundation headquarters" class="rounded-lg w-full h-60 object-cover">
                        </div>
                        <div>
                            <img src="https://placehold.co/400x300/orange/white?text=Support" alt="Legal support" class="rounded-lg w-full h-40 object-cover">
                        </div>
                        <div>
                            <img src="https://placehold.co/400x300/gray/white?text=Army" alt="Indian Army" class="rounded-lg w-full h-40 object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our History -->
    <section id="history" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Our History</h2>
                <div class="w-20 h-1 bg-orange-500 mx-auto"></div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-8 max-w-4xl mx-auto">
                <p class="text-gray-700 mb-6">
                    Founded in 2010, the Watchespati Foundation began as a small initiative by a group of lawyers with 
                    military backgrounds who recognized the need for specialized legal support for army personnel.
                </p>
                <p class="text-gray-700 mb-6">
                    Over the years, we have grown into a recognized organization that has successfully assisted in numerous 
                    legal cases, providing both representation and resources to those who serve our country.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                    <div class="text-center">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Founded</h3>
                        <p class="text-gray-600">2010</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Cases Won</h3>
                        <p class="text-gray-600">500+</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Officials Helped</h3>
                        <p class="text-gray-600">1,200+</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-3">Our Team</h2>
                <div class="w-20 h-1 bg-orange-500 mx-auto"></div>
                <p class="text-gray-600 mt-4 max-w-2xl mx-auto">Meet the dedicated professionals who work tirelessly to support our mission</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Team Member 1 -->
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="https://placehold.co/400x400/blue/white?text=Maj.+Rajiv+Kumar" alt="Maj. Rajiv Kumar" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Maj. Rajiv Kumar (Retd.)</h3>
                        <p class="text-orange-500 mb-4">Founder & President</p>
                        <p class="text-gray-600 mb-4">With over 20 years of military experience, Maj. Kumar founded the organization with a vision to provide legal support to army officials.</p>
                        <div class="flex space-x-3">
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-1.308c0-1.769.931-2.692 3.029-2.692h1.971v2z"/></svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2 16h-2v-6h2v6zm-1-6.891c-.607 0-1.1-.496-1.1-1.109 0-.612.492-1.109 1.1-1.109s1.1.497 1.1 1.109c0 .613-.493 1.109-1.1 1.109zm8 6.891h-1.998v-2.861c0-1.881-2.002-1.722-2.002 0v2.861h-2v-6h2v1.093c.872-1.616 4-1.736 4 1.548v3.359z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 2 -->
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="https://placehold.co/400x400/orange/white?text=Adv.+Priya+Sharma" alt="Adv. Priya Sharma" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Adv. Priya Sharma</h3>
                        <p class="text-orange-500 mb-4">Chief Legal Advisor</p>
                        <p class="text-gray-600 mb-4">A seasoned lawyer with expertise in military law, Adv. Sharma leads our legal team with dedication and exceptional legal acumen.</p>
                        <div class="flex space-x-3">
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-1.308c0-1.769.931-2.692 3.029-2.692h1.971v2z"/></svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2 16h-2v-6h2v6zm-1-6.891c-.607 0-1.1-.496-1.1-1.109 0-.612.492-1.109 1.1-1.109s1.1.497 1.1 1.109c0 .613-.493 1.109-1.1 1.109zm8 6.891h-1.998v-2.861c0-1.881-2.002-1.722-2.002 0v2.861h-2v-6h2v1.093c.872-1.616 4-1.736 4 1.548v3.359z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 3 -->
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="https://placehold.co/400x400/green/white?text=Col.+Vikram+Singh" alt="Col. Vikram Singh" class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Col. Vikram Singh (Retd.)</h3>
                        <p class="text-orange-500 mb-4">Director of Operations</p>
                        <p class="text-gray-600 mb-4">Col. Singh brings strategic planning and operational excellence to our organization with his extensive military leadership experience.</p>
                        <div class="flex space-x-3">
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-1.308c0-1.769.931-2.692 3.029-2.692h1.971v2z"/></svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-blue-500">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2 16h-2v-6h2v6zm-1-6.891c-.607 0-1.1-.496-1.1-1.109 0-.612.492-1.109 1.1-1.109s1.1.497 1.1 1.109c0 .613-.493 1.109-1.1 1.109zm8 6.891h-1.998v-2.861c0-1.881-2.002-1.722-2.002 0v2.861h-2v-6h2v1.093c.872-1.616 4-1.736 4 1.548v3.359z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section id="contact" class="py-16 bg-blue-900 text-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="lg:w-1/2 mb-8 lg:mb-0">
                    <h2 class="text-3xl font-bold mb-4">Join us in our mission</h2>
                    <p class="mb-6">Help us support the brave men and women of the Indian Army who need legal assistance. Your contribution can make a significant difference.</p>
                    <button id="heroDonateBtm" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                        Donate now
                    </button>
                </div>
                <div class="lg:w-1/3">
                    <img src="https://placehold.co/600x400/orange/white?text=Support+Our+Cause" alt="Support Our Cause" class="rounded-lg">
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'footer.php'; ?>

    <!-- Donation Modal -->
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
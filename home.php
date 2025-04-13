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
    <title>Watchespati Foundation - Supporting Those in Need</title>
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
                <a href="home.php" class="font-medium text-gray-800 hover:text-blue-600">Home</a>
                <a href="about_us.php" class="font-medium text-gray-600 hover:text-blue-600">About us</a>
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

    <!-- Hero Section -->
    <section class="py-20 bg-white relative overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 lg:pr-12 mb-10 lg:mb-0 z-10">
                    <h1 class="text-5xl font-bold text-gray-800 mb-4">
                        <span class="block">Supporting Indian Army</span>
                        <span class="text-3xl font-normal block mt-2">in their legal battles and justice</span>
                    </h1>
                    <p class="text-gray-600 mb-8">Watchespati Foundation provides legal assistance to Indian Army officials facing legal challenges. Based in Delhi, we offer support, resources, and expert legal counsel to those who have served our nation.</p>
                    <button id="heroDonateBtm" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                        Donate now
                    </button>
                </div>
                <div class="lg:w-1/2 relative z-10">
                    <div class="relative">
                        <img src="https://res.cloudinary.com/anurupmillan/image/upload/v1744542351/watchespati/ChatGPT_Image_Apr_13_2025_04_32_56_PM_lrw72l.png" alt="Hero" class="rounded-lg w-full">
                        
                        <!-- Donation Counter -->
                        <div class="absolute -top-5 -right-5 bg-white rounded-lg shadow-lg p-4 w-64">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-gray-700 font-medium">Total donations</h4>
                                <span class="text-orange-500">🔥</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-orange-500 h-2.5 rounded-full" style="width: 70%"></div>
                            </div>
                            <div class="mt-2 text-right">
                                <span class="text-xl font-bold text-gray-800">₹77,500</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Background circle decorations -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-orange-100 rounded-full opacity-50"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-100 rounded-full opacity-50"></div>
    </section>

    <!-- Donation Categories -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Category 1 -->
                <div class="card-donation bg-gradient-to-r from-blue-50 to-blue-100 p-8 rounded-xl shadow-sm hover:shadow-lg">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Donate for</h3>
                    <h2 class="text-2xl font-bold text-blue-800 mb-4">Legal Representation</h2>
                    <p class="text-gray-600 mb-4">Support Indian Army officials who need quality legal representation in courts and tribunals.</p>
                    <a href="#" class="text-blue-600 font-medium hover:underline">More details...</a>
                </div>
                
                <!-- Category 2 -->
                <div class="card-donation bg-gradient-to-r from-teal-50 to-teal-100 p-8 rounded-xl shadow-sm hover:shadow-lg">
                    <div class="bg-teal-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Donate for</h3>
                    <h2 class="text-2xl font-bold text-teal-800 mb-4">Legal Consultation</h2>
                    <p class="text-gray-600 mb-4">Fund legal advice and consultation services for Army officials facing complex legal challenges.</p>
                    <a href="#" class="text-teal-600 font-medium hover:underline">More details...</a>
                </div>
                
                <!-- Category 3 -->
                <div class="card-donation bg-gradient-to-r from-purple-50 to-purple-100 p-8 rounded-xl shadow-sm hover:shadow-lg">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Donate for</h3>
                    <h2 class="text-2xl font-bold text-purple-800 mb-4">Family Legal Support</h2>
                    <p class="text-gray-600 mb-4">Help Army families navigate legal matters related to pensions, benefits, and other rights.</p>
                    <a href="#" class="text-purple-600 font-medium hover:underline">More details...</a>
                </div>
                
                <!-- Category 4 -->
                <div class="card-donation bg-gradient-to-r from-green-50 to-green-100 p-8 rounded-xl shadow-sm hover:shadow-lg">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Donate for</h3>
                    <h2 class="text-2xl font-bold text-green-800 mb-4">Legal Research</h2>
                    <p class="text-gray-600 mb-4">Support our team in researching precedents and legal frameworks that can help Army officials.</p>
                    <a href="#" class="text-green-600 font-medium hover:underline">More details...</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us / Mission -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 lg:pr-12 mb-10 lg:mb-0">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <img src="https://images.pexels.com/photos/32976/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Legal support for army officials" class="rounded-lg w-full h-60 object-cover">
                        </div>
                        <div>
                            <img src="https://images.pexels.com/photos/8731037/pexels-photo-8731037.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Justice for army" class="rounded-lg w-full h-40 object-cover">
                        </div>
                        <div>
                            <img src="https://images.pexels.com/photos/5668473/pexels-photo-5668473.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Legal advocacy" class="rounded-lg w-full h-40 object-cover">
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 lg:pl-12">
                    <div class="mb-8">
                        <span class="text-orange-500 font-medium">Welcome to Watchespati Foundation</span>
                        <h2 class="text-4xl font-bold text-gray-800 mt-2 mb-4">
                            A world where <span class="text-orange-500">injustice</span><br>
                            will not prevail
                        </h2>
                        <p class="text-gray-600 mb-6">We are Delhi's leading legal support organization for Indian Army officials</p>
                    </div>
                    
                    <div class="space-y-4 mb-8">
                        <p class="text-gray-600">
                            Founded to support Indian Army officials navigate complex legal challenges, Watchespati Foundation provides expert legal assistance to those who have served our nation.
                        </p>
                        <p class="text-gray-600">
                            Our team of dedicated legal professionals based in Delhi works tirelessly to ensure that Army officials receive fair representation in courts, tribunals, and other legal forums.
                        </p>
                        <p class="text-gray-600">
                            From pension disputes to service-related matters, we stand with our Army officials in their pursuit of justice and legal rights.
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="#" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                            Learn more
                        </a>
                        <a href="#" class="flex items-center text-gray-700 hover:text-orange-500 font-medium">
                            <span class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            How we work
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Stats -->
    <section class="py-16 bg-blue-900 text-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-4">We are fundraising for 20+ years and<br>are helping humanity rise</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="p-4">
                    <div class="bg-white/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">2348</h3>
                    <p class="text-blue-200">Volunteers</p>
                </div>
                
                <div class="p-4">
                    <div class="bg-white/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">1748</h3>
                    <p class="text-blue-200">Organizations</p>
                </div>
                
                <div class="p-4">
                    <div class="bg-white/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">4287</h3>
                    <p class="text-blue-200">Projects</p>
                </div>
                
                <div class="p-4">
                    <div class="bg-white/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-4xl font-bold mb-2">1294</h3>
                    <p class="text-blue-200">Donations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-3">What people are saying</h2>
                <div class="w-20 h-1 bg-orange-500 mx-auto"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/12.jpg" alt="Testimonial Avatar" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Major Rajesh Singh (Retd.)</h4>
                            <p class="text-gray-600 text-sm">Indian Army</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">
                        "Watchespati Foundation provided exceptional legal support during my pension dispute case. Their dedicated team ensured I received my rightful benefits after retirement from the Army."
                    </p>
                    <div class="mt-4 text-orange-500">
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Testimonial Avatar" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Mrs. Kavita Sharma</h4>
                            <p class="text-gray-600 text-sm">Army Officer's Wife</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">
                        "After my husband's passing, I was struggling with property rights issues. The legal team at Watchespati Foundation helped me navigate the complex process and secure our family's future."
                    </p>
                    <div class="mt-4 text-orange-500">
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Testimonial Avatar" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Colonel Vikram Patel (Retd.)</h4>
                            <p class="text-gray-600 text-sm">Indian Army</p>
                        </div>
                    </div>
                    <p class="text-gray-700 italic">
                        "When facing service-related legal challenges, Watchespati Foundation stood by me with expert counsel and representation. Their knowledge of military law is unparalleled in Delhi."
                    </p>
                    <div class="mt-4 text-orange-500">
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-16 bg-orange-500 text-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="lg:w-1/2 mb-8 lg:mb-0">
                    <h2 class="text-3xl font-bold mb-4">Stay informed about legal rights</h2>
                    <p class="mb-6">Subscribe to receive legal updates, success stories, and news about our advocacy for Indian Army officials.</p>
                    <form class="flex w-full max-w-md">
                        <input type="email" placeholder="Your email address" class="flex-grow px-4 py-3 rounded-l-lg focus:outline-none text-gray-800">
                        <button type="submit" class="bg-blue-900 hover:bg-blue-800 px-6 py-3 rounded-r-lg font-medium transition duration-300">
                            Subscribe
                        </button>
                    </form>
                </div>
                <!-- <div class="lg:w-1/3">
                    <img src="https://placehold.co/600x400/orange/white?text=Legal+Updates" alt="Legal Updates" class="rounded-lg">
                </div> -->
            </div>
        </div>
    </section>
    
    <?php include 'footer.php'; ?>

    <!-- Donation Modal -->
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
                <?php if (isset($donation_message)) { ?>
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        <span class="font-medium">
                            <svg class="inline-block w-5 h-5 mr-1 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Thank you!
                        </span> 
                        <?php echo $donation_message; ?>
                    </div>
                <?php } ?>
                
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
                                    Or send directly to: <span class="font-mono font-medium">8902249102@ybl</span>
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
                        <button type="submit" id="proceedBtn" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded transition duration-300">
                            Submit Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        // Modal handling
        const donationModal = document.getElementById('donationModal');
        const donateBtn = document.getElementById('donateBtn');
        const heroDonateBtm = document.getElementById('heroDonateBtm');
        const closeModalBtn = document.getElementById('closeModal');
        const upiDetails = document.getElementById('qrcode').parentElement;
        let qrCode = null;

        // Check if there's a donation message and show modal
        <?php if (isset($donation_message) && !empty($donation_message)) { ?>
        document.addEventListener('DOMContentLoaded', function() {
            donationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
        <?php } ?>
        
        // Add event listeners to both donate buttons
        donateBtn.addEventListener('click', () => {
            donationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Generate QR code if amount is selected
            const amount = document.getElementById('customAmount').value;
            if (amount && parseFloat(amount) > 0) {
                generateQRCode(amount);
            }
        });
        
        heroDonateBtm.addEventListener('click', () => {
            donationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Generate QR code if amount is selected
            const amount = document.getElementById('customAmount').value;
            if (amount && parseFloat(amount) > 0) {
                generateQRCode(amount);
            }
        });
        
        closeModalBtn.addEventListener('click', () => {
            donationModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === donationModal) {
                donationModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Amount buttons
        document.querySelectorAll('.amount-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent button from submitting form
                
                const amount = button.getAttribute('data-amount');
                document.getElementById('customAmount').value = amount;
                
                // Update hidden amount field if it exists
                const donationAmount = document.getElementById('donationAmount');
                if (donationAmount) {
                    donationAmount.value = amount;
                }
                
                generateQRCode(amount);
                
                // Remove active class from all buttons
                document.querySelectorAll('.amount-btn').forEach(btn => {
                    btn.classList.remove('bg-blue-500', 'text-white', 'bg-blue-200');
                    btn.classList.add('bg-blue-50', 'text-blue-700');
                });
                
                // Add active class to the clicked button
                button.classList.remove('bg-blue-50', 'text-blue-700');
                button.classList.add('bg-blue-500', 'text-white');
            });
        });
        
        // Custom amount input
        document.getElementById('customAmount').addEventListener('input', (e) => {
            const amount = e.target.value;
            
            // Update hidden amount field if it exists
            const donationAmount = document.getElementById('donationAmount');
            if (donationAmount) {
                donationAmount.value = amount;
            }
            
            if (amount && parseFloat(amount) > 0) {
                generateQRCode(amount);
            }
            
            // Remove active class from all buttons
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'bg-blue-200');
                btn.classList.add('bg-blue-50', 'text-blue-700');
            });
        });
        
        // Generate QR code
        function generateQRCode(amount) {
            const qrcodeContainer = document.getElementById('qrcode');
            qrcodeContainer.innerHTML = '';
            
            if (amount) {
                // Use same UPI ID as contact-us.php
                const upiLink = `upi://pay?pa=8902249102@ybl&pn=Watchespati%20Foundation&am=${amount}&cu=INR`;
                
                // Clear previous QR code if exists
                if (qrCode) {
                    qrCode.clear();
                }
                
                qrCode = new QRCode(qrcodeContainer, {
                    text: upiLink,
                    width: 128,
                    height: 128,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                // Update UPI ID text if exists
                const upiIdText = qrcodeContainer.parentElement.querySelector('p:last-child');
                if (upiIdText) {
                    upiIdText.innerHTML = 'UPI ID: <span class="font-mono font-medium">8902249102@ybl</span>';
                }
            }
        }
        
        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            const amount = document.getElementById('customAmount').value;
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
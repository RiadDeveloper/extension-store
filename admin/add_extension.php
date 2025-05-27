<?php
// Include necessary files and functions
include "login_check.php";
include "db_conn_B.php";

// Fetch user profile
$userProfile = getUserProfile(); // Assuming getUserProfile is defined in login_check.php
$isAdmin = $userProfile['trust_leve'] === 'admin';

// Check if the user is logged in and is an admin
if (!$isAdmin) {
    header("Location: login.php");
    exit();
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    die("User ID is not set. Please log in again.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_extension'])) {
    $latest_version = $_POST['latest_version'];
    
    // Handle multiple platform selections
    $platforms = $_POST['platform']; // This will be an array
    $platforms_str = implode(", ", $platforms); // Convert array to comma-separated string
    
    $downloads = 0; // Default value for downloads
    $price = $_POST['price'] ?? 'FREE';
    $download_link = $_POST['download_link'];
    $extension_name = $_POST['extension_name'];
    $extension_package = $_POST['extension_package'];
    $description = $_POST['description'];
    $extension_price = $_POST['extension_price'] ?? 0;
    $html_content = $_POST['html_content'];
    $github_file_name = $_POST['github_file_name'];
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Format the released_on and last_update dates
    $released_on = date("d F Y"); // Format: "11 October 2023"
    $last_update = $released_on; // Initially the same as released_on
    $extension_id = uniqid(); // Generate a unique extension ID

    // Insert data into the database
    $query = "INSERT INTO extension (Released_On, Latest_Version, Platform, Downloads, price, Download_link, extension_name, extension_id, extension_package, description, last_update, user_id, extension_price, html_content, github_file_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssssssss", $released_on, $latest_version, $platforms_str, $downloads, $price, $download_link, $extension_name, $extension_id, $extension_package, $description, $last_update, $user_id, $extension_price, $html_content, $github_file_name);

    if ($stmt->execute()) {
        // Redirect to a new page (e.g., a list of extensions or a success page)
        header("Location: extension_list.php"); // Adjust this URL as needed
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Extension</title>
    <link rel="stylesheet" href="../assets/css/markdown.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #212529;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .create-extension-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .create-extension-btn:hover {
            background-color: #0056b3;
        }

        .form-container {
            flex: 1 1 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .preview-container {
            flex: 1 1 100%;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            overflow-y: auto;
            max-height: 600px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }

        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            color: #495057;
            background-color: #f8f9fa;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus, .form-group input[type="number"]:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: #007bff;
        }

        .form-group textarea {
            resize: vertical;
            height: 150px; /* Increased height for better visibility */
        }

        .card-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .card {
            flex: 1 1 30%;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .card.selected {
            background-color: #007bff;
            color: white;
            border-color: #0056b3;
        }

        .card.disabled {
            background-color: #f8f9fa;
            color: #aaa;
            cursor: not-allowed;
        }

        .preview-container .documentation-section {
            width: 100%;
            height: 100%;
        }

        .documentation-section h1 {
            font-size: 25px;
            margin-bottom: 15px;
        }

        .documentation-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .documentation-section img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        /* Responsive Design */
        @media (min-width: 1024px) {
            .container {
                flex-direction: row;
            }

            .form-container {
                flex: 2;
                max-width: 60%;
            }

            .preview-container {
                flex: 1;
                max-width: 35%;
            }
        }

        @media (max-width: 1023px) {
            .form-container, .preview-container {
                flex: 1 1 100%;
            }

            .header-title {
                font-size: 20px;
            }
        }

        /* Additional styles for the preview */
        #html_content {
            font-family: monospace;
            min-height: 300px;
        }
        
        .preview-container {
            color: inherit;
        }
        
        .preview-tabs {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .preview-tab {
            padding: 8px 16px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            margin-right: 5px;
        }
        
        .preview-tab.active {
            background-color: #fff;
            border-color: #ddd;
            border-bottom-color: #fff;
            margin-bottom: -1px;
            border-radius: 4px 4px 0 0;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title">Add New Extension</div>
            <button class="create-extension-btn" onclick="document.getElementById('extension_form').submit();">Create Extension</button>
        </div>
        <div class="form-container">
            <form id="extension_form" action="add_extension.php" method="post">
                <input type="hidden" name="create_extension" value="1">
                
                <div class="form-group">
                    <label for="extension_name">Extension Name:</label>
                    <input type="text" id="extension_name" name="extension_name" required>
                </div>

                <div class="form-group">
                    <label for="latest_version">Latest Version:</label>
                    <input type="text" id="latest_version" name="latest_version" required>
                </div>

                <div class="form-group">
                    <label>Platform:</label>
                    <div class="card-container">
                        <div class="card" data-value="Kodular">Kodular</div>
                        <div class="card" data-value="App Inventor">App Inventor</div>
                        <div class="card" data-value="Niotron">Niotron</div>
                    </div>
                    <input type="hidden" id="platform_hidden" name="platform[]">
                </div>

                <div class="form-group">
                    <label for="paid_ar_free">PAID or FREE:</label>
                    <select id="paid_ar_free" name="paid_ar_free">
                        <option value="FREE">FREE</option>
                        <option value="PAID">PAID</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="extension_package">Extension Package:</label>
                    <input type="text" id="extension_package" name="extension_package" required>
                </div>

                <div class="form-group">
                    <label for="extension_price">Extension Price:</label>
                    <input type="number" id="extension_price" name="extension_price" min="0">
                </div>

                <div class="form-group">
                    <label for="download_link">Download Link:</label>
                    <input type="text" id="download_link" name="download_link" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="github_file_name">GitHub File Name:</label>
                    <input type="text" id="github_file_name" name="github_file_name">
                </div>

                <div class="form-group">
                    <label for="html_content">Content (Supports both Markdown and HTML):</label>
                    <textarea id="html_content" name="html_content" onkeyup="updatePreview()" required></textarea>
                    <small class="form-text text-muted">You can use Markdown or HTML. For Markdown, use # for headers, ** for bold, * for italic, etc.</small>
                </div>
            </form>
        </div>
        <div class="preview-container">
            <div class="preview-tabs">
                <div class="preview-tab active" onclick="switchTab('preview')">Preview</div>
                <div class="preview-tab" onclick="switchTab('source')">Source</div>
            </div>
            <div id="preview_tab" class="tab-content active">
                <div id="live_preview" class="documentation-content"></div>
            </div>
            <div id="source_tab" class="tab-content">
                <pre><code id="source_preview"></code></pre>
            </div>
        </div>
    </div>

    <script>
        // Initialize marked with options
        marked.setOptions({
            breaks: true,           // Enable line breaks
            gfm: true,             // Enable GitHub Flavored Markdown
            headerIds: false,       // Disable header IDs
            mangle: false,         // Disable name mangling
            headerPrefix: '',      // No prefix for headers
            pedantic: false,       // Be more forgiving with Markdown syntax
            sanitize: false,       // Allow HTML in Markdown
            smartLists: true,      // Use smarter list behavior
            smartypants: true,     // Use smart punctuation
            xhtml: false           // Don't close single tags
        });

        // JavaScript for handling platform selection
        const cards = document.querySelectorAll('.card');
        const maxSelection = 3;
        const hiddenInput = document.getElementById('platform_hidden');

        cards.forEach(card => {
            card.addEventListener('click', () => {
                const selectedCards = document.querySelectorAll('.card.selected');

                if (card.classList.contains('selected')) {
                    card.classList.remove('selected');
                } else if (selectedCards.length < maxSelection) {
                    card.classList.add('selected');
                }

                const selectedValues = Array.from(document.querySelectorAll('.card.selected'))
                    .map(selectedCard => selectedCard.getAttribute('data-value'));

                hiddenInput.value = selectedValues.join(", ");
                
                // Disable cards if max selection reached
                if (selectedValues.length >= maxSelection) {
                    cards.forEach(c => {
                        if (!c.classList.contains('selected')) {
                            c.classList.add('disabled');
                        }
                    });
                } else {
                    cards.forEach(c => c.classList.remove('disabled'));
                }
            });
        });

        // Function to switch between preview tabs
        function switchTab(tab) {
            document.querySelectorAll('.preview-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            
            if (tab === 'preview') {
                document.querySelector('.preview-tab:first-child').classList.add('active');
                document.getElementById('preview_tab').classList.add('active');
            } else {
                document.querySelector('.preview-tab:last-child').classList.add('active');
                document.getElementById('source_tab').classList.add('active');
            }
        }

        // Function to update preview
        function updatePreview() {
            const content = document.getElementById('html_content').value;
            const previewDiv = document.getElementById('live_preview');
            const sourceDiv = document.getElementById('source_preview');

            // Always show the source
            sourceDiv.textContent = content;
            
            try {
                // Try to parse as Markdown first
                const htmlContent = marked.parse(content);
                previewDiv.innerHTML = htmlContent;
            } catch (e) {
                // If Markdown parsing fails, treat as HTML
                previewDiv.innerHTML = content;
            }
        }

        // Initial preview update
        updatePreview();

        // Add input event listener for real-time updates
        document.getElementById('html_content').addEventListener('input', updatePreview);
    </script>
</body>
</html>

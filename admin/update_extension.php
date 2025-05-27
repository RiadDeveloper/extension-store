<?php
include "login_check.php";
include "db_conn_B.php";

if (!isUserLogedin() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("User ID is not set. Please log in again.");
}

if (!isset($_GET['id'])) {
    die("No extension ID provided.");
}

$extension_id = $_GET['id'];
$extension_data = null;
$message = '';

// Fetch existing data
$query = "SELECT * FROM extension WHERE extension_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $extension_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $extension_data = $result->fetch_assoc();
} else {
    die("Extension not found.");
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $latest_version = $_POST['latest_version'];
    $platforms = $_POST['platform'];
    $platforms_str = implode(", ", $platforms);
    
    $downloads = 0;
    $price = $_POST['price'] ?? 'FREE';
    $download_link = $_POST['download_link'];
    $extension_name = $_POST['extension_name'];
    $extension_package = $_POST['extension_package'];
    $description = $_POST['description'];
    $extension_price = $_POST['extension_price'] ?? 0;
    $html_content = $_POST['html_content'];
    $github_file_name = $_POST['github_file_name'];
    $user_id = $_SESSION['user_id'];

    $last_update = date("d F Y");

    $query = "UPDATE extension SET Latest_Version = ?, Platform = ?, Downloads = ?, price = ?, Download_link = ?, extension_name = ?, extension_package = ?, description = ?, last_update = ?, user_id = ?, extension_price = ?, html_content = ?, github_file_name = ? WHERE extension_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssssssss", $latest_version, $platforms_str, $downloads, $price, $download_link, $extension_name, $extension_package, $description, $last_update, $user_id, $extension_price, $html_content, $github_file_name, $extension_id);

    if ($stmt->execute()) {
        $message = "<p class='success-message'>Extension updated successfully!</p>";
    } else {
        $message = "<p class='error-message'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Extension</title>
    <link rel="stylesheet" href="../assets/css/markdown.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #212529;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .message {
            width: 100%;
            max-width: 1200px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
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

        .update-extension-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .update-extension-btn:hover {
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

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            color: #495057;
            background-color: #f8f9fa;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #007bff;
        }

        .form-group textarea {
            resize: vertical;
            height: 150px;
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

        .documentation-section .documentation-content {
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
            .form-container,
            .preview-container {
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
    <?php if ($message): ?>
        <div class="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="container">
        <div class="header">
            <div class="header-title">Update Extension</div>
            <button class="update-extension-btn" onclick="document.getElementById('update_extension_form').submit();">Update Extension</button>
        </div>
        
        <div class="form-container">
            <form id="update_extension_form" action="update_extension.php?id=<?php echo htmlspecialchars($extension_id); ?>" method="post">
                <div class="form-group">
                    <label for="extension_name">Extension Name:</label>
                    <input type="text" id="extension_name" name="extension_name"
                        value="<?php echo htmlspecialchars($extension_data['extension_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="latest_version">Latest Version:</label>
                    <input type="text" id="latest_version" name="latest_version"
                        value="<?php echo htmlspecialchars($extension_data['Latest_Version']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="platform">Platform:</label>
                    <div id="platform" class="card-container">
                        <?php
                        $options = ['Kodular', 'App Inventor', 'Niotron'];
                        $selectedPlatforms = explode(", ", $extension_data['Platform']);
                        foreach ($options as $option) {
                            $selected = in_array($option, $selectedPlatforms) ? 'selected' : 'deselected';
                            echo "<div class='card $selected' data-value='$option'>$option</div>";
                        }
                        ?>
                    </div>
                    <input type="hidden" id="platform_hidden" name="platform[]"
                        value="<?php echo htmlspecialchars($extension_data['Platform']); ?>">
                </div>

                <div class="form-group">
                    <label for="paid_ar_free">PAID or FREE:</label>
                    <select id="paid_ar_free" name="price">
                        <option value="FREE" <?php echo ($extension_data['price'] == 'FREE') ? 'selected' : ''; ?>>
                            FREE
                        </option>
                        <option value="PAID" <?php echo ($extension_data['price'] == 'PAID') ? 'selected' : ''; ?>>
                            PAID
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="download_link">Download Link:</label>
                    <input type="text" id="download_link" name="download_link"
                        value="<?php echo htmlspecialchars($extension_data['Download_link']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="extension_package">Extension Package:</label>
                    <input type="text" id="extension_package" name="extension_package"
                        value="<?php echo htmlspecialchars($extension_data['extension_package']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"
                        required><?php echo htmlspecialchars($extension_data['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="extension_price">Extension Price:</label>
                    <input type="number" id="extension_price" name="extension_price"
                        value="<?php echo htmlspecialchars($extension_data['extension_price']); ?>">
                </div>

                <div class="form-group">
                    <label for="html_content">Content (Supports both Markdown and HTML):</label>
                    <textarea id="html_content" name="html_content" required><?php echo htmlspecialchars($extension_data['html_content']); ?></textarea>
                    <small class="form-text text-muted">You can use Markdown or HTML. For Markdown, use # for headers, ** for bold, * for italic, etc.</small>
                </div>

                <div class="form-group">
                    <label for="github_file_name">GitHub File Name:</label>
                    <input type="text" id="github_file_name" name="github_file_name"
                        value="<?php echo htmlspecialchars($extension_data['github_file_name']); ?>" required>
                </div>
            </form>
        </div>

        <div class="preview-container">
            <div class="preview-tabs">
                <div class="preview-tab active" onclick="switchTab('preview')">Preview</div>
                <div class="preview-tab" onclick="switchTab('source')">Source</div>
            </div>
            <div id="preview_tab" class="tab-content active">
                <div id="documentation" class="documentation-content"></div>
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

        const maxSelection = 3;
        const cards = document.querySelectorAll('.card');
        const hiddenInput = document.getElementById('platform_hidden');
        const htmlContentArea = document.getElementById('html_content');
        const documentationSection = document.getElementById('documentation');
        const sourcePreview = document.getElementById('source_preview');

        // Initialize selected cards
        const initialSelection = <?php echo json_encode($selectedPlatforms); ?>;
        initialSelection.forEach(value => {
            const card = document.querySelector(`.card[data-value="${value}"]`);
            if (card) card.classList.add('selected');
        });

        cards.forEach(card => {
            card.addEventListener('click', () => {
                if (card.classList.contains('disabled')) return;

                const selectedCards = document.querySelectorAll('.card.selected');

                if (card.classList.contains('selected')) {
                    card.classList.remove('selected');
                    card.classList.add('deselected');
                } else {
                    if (selectedCards.length >= maxSelection) {
                        return;
                    }
                    card.classList.remove('deselected');
                    card.classList.add('selected');
                }

                updateHiddenInput();
            });
        });

        function updateHiddenInput() {
            const selectedCards = Array.from(document.querySelectorAll('.card.selected'));
            const values = selectedCards.map(card => card.getAttribute('data-value'));
            hiddenInput.value = values.join(', ');
        }

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
            const content = htmlContentArea.value;
            
            // Always show the source
            sourcePreview.textContent = content;
            
            try {
                // Try to parse as Markdown first
                const htmlContent = marked.parse(content);
                documentationSection.innerHTML = htmlContent;
            } catch (e) {
                // If Markdown parsing fails, treat as HTML
                documentationSection.innerHTML = content;
            }
        }

        // Add input event listener
        htmlContentArea.addEventListener('input', updatePreview);

        // Initial preview
        updatePreview();
    </script>
</body>

</html>

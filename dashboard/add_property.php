<?php
session_start();
require_once '../inc/auth.php';
require_once '../inc/config.php';
require_once '../inc/functions.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in and has an ID in the session
    if (!isset($_SESSION['user_id'])) {
        $error_message = "Authentication error. Please log in again.";
    } else {
        $user_id = $_SESSION['user_id'];
        
        // --- Form Data Sanitization ---
        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $location = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING));
        $property_type = trim(filter_input(INPUT_POST, 'property_type', FILTER_SANITIZE_STRING));
        $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
        $bedrooms = filter_input(INPUT_POST, 'bedrooms', FILTER_VALIDATE_INT);
        $bathrooms = filter_input(INPUT_POST, 'bathrooms', FILTER_VALIDATE_INT);
        $area = filter_input(INPUT_POST, 'area', FILTER_VALIDATE_FLOAT);

        // --- Basic Validation ---
        if (empty($title) || empty($location) || $price === false || $bedrooms === false || $bathrooms === false) {
            $error_message = "Please fill in all required fields (Title, Location, Price, Bedrooms, Bathrooms).";
        } else {
            // --- Image Upload Handling ---
            $image_paths = [];
            $upload_dir = '../uploads/properties/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                        $error_message = "Error uploading file: " . $_FILES['images']['name'][$key];
                        continue; // Skip this file
                    }

                    $file_name = $_FILES['images']['name'][$key];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (in_array($file_ext, $allowed_types)) {
                        // Create a unique filename to prevent overwriting
                        $new_file_name = uniqid('', true) . '.' . $file_ext;
                        $target_path = $upload_dir . $new_file_name;

                        if (move_uploaded_file($tmp_name, $target_path)) {
                            // Store the relative path for the database
                            $image_paths[] = 'uploads/properties/' . $new_file_name;
                        } else {
                            $error_message = "Failed to move uploaded file: " . $file_name;
                        }
                    } else {
                        $error_message = "Invalid file type: " . $file_name . ". Only JPG, PNG, GIF, WEBP are allowed.";
                    }
                }
            }
            
            // Proceed only if there were no upload errors
            if (empty($error_message)) {
                $images_json = json_encode($image_paths);

                // --- Database Insertion ---
                try {
                    $sql = "INSERT INTO properties (user_id, title, description, price, location, property_type, status, bedrooms, bathrooms, area, images) 
                            VALUES (:user_id, :title, :description, :price, :location, :property_type, :status, :bedrooms, :bathrooms, :area, :images)";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':location', $location, PDO::PARAM_STR);
                    $stmt->bindParam(':property_type', $property_type, PDO::PARAM_STR);
                    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                    $stmt->bindParam(':bedrooms', $bedrooms, PDO::PARAM_INT);
                    $stmt->bindParam(':bathrooms', $bathrooms, PDO::PARAM_INT);
                    $stmt->bindParam(':area', $area);
                    $stmt->bindParam(':images', $images_json, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Property added successfully!";
                        header("Location: properties.php");
                        exit();
                    } else {
                        $error_message = "Failed to add property. Please try again.";
                    }
                } catch (PDOException $e) {
                    $error_message = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

// Include header
include '../inc/header.php';
?>

<div class="d-flex">
    <?php include '../inc/sidebar.php'; ?>

    <main class="main-content flex-grow-1 p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Add New Property</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Property Details</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Property Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location / Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (NGN) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="property_type" class="form-label">Property Type</label>
                                    <select class="form-select" id="property_type" name="property_type">
                                        <option value="House">House</option>
                                        <option value="Apartment">Apartment</option>
                                        <option value="Land">Land</option>
                                        <option value="Commercial">Commercial Property</option>
                                        <option value="Duplex">Duplex</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="For Sale">For Sale</option>
                                        <option value="For Rent">For Rent</option>
                                        <option value="Sold">Sold</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="bedrooms" class="form-label">Bedrooms <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="bathrooms" class="form-label">Bathrooms <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Area (sqm)</label>
                                    <input type="number" class="form-control" id="area" name="area" step="0.01">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <label for="images" class="form-label">Property Images</label>
                            <p class="small text-muted">You can upload multiple images. Allowed types: JPG, PNG, GIF, WEBP.</p>
                            <input type="file" class="form-control" id="images" name="images[]" multiple>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Property</button>
                        <a href="properties.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../inc/footer.php'; ?>

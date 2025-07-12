<?php
include __DIR__ . '/../includes/db.php';
session_start();

$upload_dir = __DIR__ . '/../customization/';
$reference_images = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name']);
    $email         = trim($_POST['email']);
    $flower_type   = trim($_POST['flower_type']);
    $color_theme   = trim($_POST['color_theme']);
    $message       = trim($_POST['message']);
    $delivery_date = trim($_POST['delivery_date']);

    // Validate required fields
    if (
        empty($name) ||
        empty($email) ||
        empty($flower_type) ||
        empty($color_theme) ||
        empty($message) ||
        empty($delivery_date) ||
        empty($_FILES['reference_image']['name'][0]) // <-- Require at least one image
    ) {
        $error = "Please fill in all fields and upload at least one reference image before submitting your custom order.";
    }

    // Only proceed if no error
    if (!isset($error)) {
        // Handle multiple image uploads
        if (!empty($_FILES['reference_image']['name'][0])) {
            foreach ($_FILES['reference_image']['name'] as $key => $image_name) {
                $image_ext   = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($image_ext, $allowed_ext)) {
                    $unique_name = uniqid('img_') . '.' . $image_ext;
                    $target_file = $upload_dir . $unique_name;

                    if (move_uploaded_file($_FILES['reference_image']['tmp_name'][$key], $target_file)) {
                        $reference_images[] = $unique_name;
                    }
                }
            }
            if (empty($reference_images)) {
                $error = "Failed to upload images.";
            }
        }

        $reference_images_json = json_encode($reference_images);

        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO custom_orders (name, email, flower_type, color_theme, message, delivery_date, reference_image)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $flower_type, $color_theme, $message, $delivery_date, $reference_images_json);

            if ($stmt->execute()) {
                $success = "🌸 Custom order submitted successfully!";
            } else {
                $error = "Database error. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customize Bouquet | HookcraftAvenue</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #fff0f5;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      border: none;
      border-radius: 20px;
      background: #ffffff;
      box-shadow: 0 4px 16px rgba(255, 192, 203, 0.3);
    }
    h2 {
      color: #d63384;
      font-weight: bold;
    }
    .btn-primary {
      background-color: #d63384;
      border-color: #d63384;
    }
    .btn-primary:hover {
      background-color: #c21872;
    }
    .form-label {
      color: #b4005a;
    }
    .alert-success, .alert-danger {
      border-radius: 10px;
    }
    .alert-success {
      background-color: #ffe4ec;
      color: #800040;
      border-color: #ffb3cc;
    }
    .alert-danger {
      background-color: #ffe4e1;
      color: #a80038;
      border-color: #ffcccc;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card p-4">
          <div class="card-body">
            <h2 class="text-center mb-4">Customize Your Bouquet</h2>

            <?php if (isset($success)): ?>
              <div class="alert alert-success text-center">
                <?= $success; ?>
              </div>

              <?php if (!empty($reference_images)): ?>
                <div class="text-center mb-4" id="uploaded-image">
                  <strong>Uploaded Reference Images:</strong><br>
                  <?php foreach ($reference_images as $img): ?>
                    <img src="<?= '/hookcraftavenue/customization/' . htmlspecialchars($img); ?>" class="img-thumbnail mt-2" style="max-width: 200px;" />
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="text-center">
                <button class="btn btn-outline-secondary" onclick="resetForm()">Make Another Custom Order</button>
              </div>
            <?php elseif (isset($error)): ?>
              <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <!-- 🌸 FORM -->
            <div id="custom-order-form" <?= isset($success) ? 'style="display:none;"' : '' ?>>
              <form method="POST" action="customization.php" enctype="multipart/form-data">
                <div class="mb-3">
                  <label class="form-label">Your Name</label>
                  <input type="text" name="name" class="form-control" required />
                </div>

                <div class="mb-3">
                  <label class="form-label">Your Email</label>
                  <input type="email" name="email" class="form-control" required />
                </div>

                <div class="mb-3">
                  <label class="form-label">Type of Flowers</label>
                  <input type="text" name="flower_type" class="form-control" required />
                </div>

                <div class="mb-3">
                  <label class="form-label">Color Theme</label>
                  <input type="text" name="color_theme" class="form-control" />
                </div>

                <div class="mb-3">
                  <label class="form-label">Personal Message</label>
                  <textarea name="message" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Preferred Delivery Date</label>
                  <input type="date" name="delivery_date" class="form-control" />
                </div>

                <div class="mb-3">
                  <label class="form-label">Upload Reference Images (optional)</label>
                  <input type="file" name="reference_image[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple id="referenceImageInput" />
                  <div id="imagePreviewContainer" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-primary btn-lg rounded-pill">Send Custom Order</button>
                </div>
              </form>
            </div>

          </div>
        </div>
        <p class="text-center mt-4"><a href="../index.php">← Back to Shop</a></p>
      </div>
    </div>
  </div>

  <script>
    function resetForm() {
      document.getElementById('custom-order-form').style.display = 'block';
      const successMessages = document.querySelectorAll('.alert-success, #uploaded-image, .btn-outline-secondary');
      successMessages.forEach(el => el.style.display = 'none');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('referenceImageInput').addEventListener('change', function(event) {
      const container = document.getElementById('imagePreviewContainer');
      container.innerHTML = '';
      const files = Array.from(event.target.files);

      files.forEach((file, idx) => {
        if (!file.type.match('image.*')) return;

        const reader = new FileReader();
        reader.onload = function(e) {
          const previewDiv = document.createElement('div');
          previewDiv.className = 'position-relative d-inline-block';

          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'img-thumbnail';
          img.style.maxWidth = '120px';
          img.style.maxHeight = '120px';

          const removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.innerHTML = '&times;';
          removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
          removeBtn.style.transform = 'translate(50%,-50%)';
          removeBtn.onclick = function() {
            files.splice(idx, 1);
            // Create a new DataTransfer to update the input files
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            event.target.files = dt.files;
            previewDiv.remove();
          };

          previewDiv.appendChild(img);
          previewDiv.appendChild(removeBtn);
          container.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
      });
    });
  </script>
</body>
</html>
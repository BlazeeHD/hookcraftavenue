<?php
include __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$categoryId = $input["category_id"] ?? null;
$categoryName = $input["category_name"] ?? null;

if (!$categoryId || !$categoryName) {
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit;
}

// Function to get table and id field from category name
function getCategoryTableInfo($categoryName) {
    $safeName = strtolower(preg_replace("/[^a-zA-Z0-9_]/", "_", $categoryName));
    $tableName = $safeName . "_products";
    $idField = $safeName . "_id";
    return [$tableName, $idField];
}

try {
    list($tableName, $idField) = getCategoryTableInfo($categoryName);
    
    // Check if the category table exists
    $checkTable = $conn->query("SHOW TABLES LIKE \"$tableName\"");
    if ($checkTable->num_rows === 0) {
        echo json_encode(["success" => true, "products" => [], "message" => "No category-specific table found"]);
        exit;
    }
    
    // Get products from category table
    $query = $conn->prepare("SELECT `$idField` as id, name, price FROM `$tableName` ORDER BY name");
    if (!$query) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $query->execute();
    $result = $query->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "price" => $row["price"]
        ];
    }
    
    echo json_encode(["success" => true, "products" => $products]);
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}

$conn->close();
?>
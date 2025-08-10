<?php
include '../includes/db.php';

$category = $_GET['category'] ?? 'all';
$search   = $_GET['search'] ?? '';

// Load all categories
$categories = [];
$catQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($cat = $catQuery->fetch_assoc()) {
    $categories[$cat['id']] = $cat['name'];
}

$products = [];

try {
    if ($category === 'all') {
        // Fetch from all category tables using the exact table structure from your database
        $unionQueries = [];
        
        // Satin products (category_id = 1)
        $searchCondition = "";
        if (!empty($search)) {
            $searchCondition = " AND name LIKE '%" . $conn->real_escape_string($search) . "%'";
        }
        
        $unionQueries[] = "
            SELECT '1' AS category_id, 'Satin' AS category_name, 
                   satin_id AS id, name, price, stock, image, description
            FROM satin_products 
            WHERE 1=1 $searchCondition
        ";
        
        // Fizzywire products (category_id = 2)
        $unionQueries[] = "
            SELECT '2' AS category_id, 'Fizzywire' AS category_name, 
                   fizzywire_id AS id, name, price, stock, image, description
            FROM fizzywire_products 
            WHERE 1=1 $searchCondition
        ";
        
        // Customize products (category_id = 3)
        $unionQueries[] = "
            SELECT '3' AS category_id, 'Customize' AS category_name, 
                   customize_id AS id, name, price, stock, image, description
            FROM customize_products 
            WHERE 1=1 $searchCondition
        ";
        
        // Add any additional category tables dynamically
        foreach ($categories as $catId => $catName) {
            if (!in_array($catId, [1, 2, 3])) { // Skip already handled categories
                $safeName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $catName));
                $tableName = $safeName . "_products";
                $idField = $safeName . "_id";
                
                // Check if table exists
                $tableCheck = $conn->query("SHOW TABLES LIKE '$tableName'");
                if ($tableCheck && $tableCheck->num_rows > 0) {
                    $unionQueries[] = "
                        SELECT '$catId' AS category_id, '" . $conn->real_escape_string($catName) . "' AS category_name, 
                               $idField AS id, name, price, stock, image, description
                        FROM `$tableName` 
                        WHERE 1=1 $searchCondition
                    ";
                }
            }
        }
        
        if ($unionQueries) {
            $sql = implode(" UNION ALL ", $unionQueries) . " ORDER BY category_id, id";
            $result = $conn->query($sql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $products[] = [
                        'id' => $row['id'],
                        'category_id' => $row['category_id'],
                        'category_name' => $row['category_name'],
                        'name' => $row['name'],
                        'price' => $row['price'],
                        'stock' => $row['stock'],
                        'image' => $row['image'],
                        'description' => $row['description']
                    ];
                }
            }
        }
    } else {
        // Fetch from specific category
        $category_id = (int)$category;
        
        if (isset($categories[$category_id])) {
            $categoryName = $categories[$category_id];
            
            // Handle the three main categories with exact table names
            if ($category_id == 1) { // Satin
                $sql = "SELECT satin_id AS id, name, price, stock, image, description FROM satin_products WHERE 1=1";
            } elseif ($category_id == 2) { // Fizzywire
                $sql = "SELECT fizzywire_id AS id, name, price, stock, image, description FROM fizzywire_products WHERE 1=1";
            } elseif ($category_id == 3) { // Customize
                $sql = "SELECT customize_id AS id, name, price, stock, image, description FROM customize_products WHERE 1=1";
            } else {
                // Dynamic category tables
                $safeName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $categoryName));
                $tableName = $safeName . "_products";
                $idField = $safeName . "_id";
                
                // Check if table exists
                $tableCheck = $conn->query("SHOW TABLES LIKE '$tableName'");
                if ($tableCheck && $tableCheck->num_rows > 0) {
                    $sql = "SELECT $idField AS id, name, price, stock, image, description FROM `$tableName` WHERE 1=1";
                } else {
                    $sql = null;
                }
            }
            
            if ($sql) {
                $params = [];
                $types = "";
                
                if (!empty($search)) {
                    $sql .= " AND name LIKE ?";
                    $params[] = "%$search%";
                    $types .= "s";
                }
                
                if (!empty($params)) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $conn->query($sql);
                }
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $products[] = [
                            'id' => $row['id'],
                            'category_id' => $category_id,
                            'category_name' => $categoryName,
                            'name' => $row['name'],
                            'price' => $row['price'],
                            'stock' => $row['stock'],
                            'image' => $row['image'],
                            'description' => $row['description']
                        ];
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error in fetch_products.php: " . $e->getMessage());
    $products = [];
}

header('Content-Type: application/json');
echo json_encode($products);
?>
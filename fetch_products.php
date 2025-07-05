<?php
include 'db.php';

$category = $_GET['category'] ?? 'all';
$search   = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE 1";
if ($category !== 'all') {
    $sql .= " AND category = ?";
}
if (!empty($search)) {
    $sql .= " AND name LIKE ?";
}

$stmt = $conn->prepare($sql);
if ($category !== 'all' && !empty($search)) {
    $like = "%$search%";
    $stmt->bind_param('ss', $category, $like);
} elseif ($category !== 'all') {
    $stmt->bind_param('s', $category);
} elseif (!empty($search)) {
    $like = "%$search%";
    $stmt->bind_param('s', $like);
}
$stmt->execute();
$res = $stmt->get_result();

$products = [];
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}
header('Content-Type: application/json');
echo json_encode($products);

<?php
include '../../../includes/config.php';

$search = $_GET['search'] ?? '';
$search = $conn->real_escape_string($search);

// Fetch products with category info
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.title LIKE '%$search%'
        ORDER BY p.created_at DESC";

$result = $conn->query($sql);

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        // Decode images JSON safely
        $images = json_decode($row['images'], true) ?: [];
        $img_html = '';
        if(!empty($images)){
            foreach($images as $img){
                // Use basename() to avoid double paths
                $filename = basename($img);
                // All uploads are in admin/uploads/
                $img_html .= '<img src="../uploads/'.$filename.'" class="thumbnail">';
            }
        }

        echo '<tr>
            <td>'.$row['id'].'</td>
            <td>'.htmlspecialchars($row['title']).'</td>
            <td>'.htmlspecialchars($row['description']).'</td>
            <td>NPR '.number_format($row['price'],2).'</td>
            <td>'.$img_html.'</td>
            <td>'.$row['rating_avg'].'</td>
            <td>'.$row['rating_count'].'</td>
            <td>'.$row['created_at'].'</td>
            <td>
                <button class="btn btn-primary btn-sm editBtn" data-id="'.$row['id'].'">Edit</button>
                <button class="btn btn-danger btn-sm deleteBtn" data-id="'.$row['id'].'">Delete</button>
            </td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="10" class="text-center">No products found.</td></tr>';
}
?>

<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

if(!isset($_GET['order_id'])) {
    die("Missing order ID.");
}

$order_id = $_GET['order_id'];

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id=? LIMIT 1");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$order) die("Order not found.");

// Fetch order items
$stmt_items = $conn->prepare("
    SELECT oi.*, p.title 
    FROM order_items oi 
    JOIN products p ON oi.product_id=p.id 
    WHERE oi.order_id=?
");
$stmt_items->bind_param("i", $order['id']);
$stmt_items->execute();
$items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Create unique invoice number
$invoice_no = 'INV-' . strtoupper(uniqid());
$invoice_dir = __DIR__ . '/../invoices/';
$invoice_file = $invoice_no . '.pdf';
$invoice_path = $invoice_dir . $invoice_file;

// Company info
$company_name = "IT Store";
$company_address = "Kathmandu, Nepal";
$company_phone = "+977 9800000000";
$company_email = "info@itstore.com";

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Logo
$logoPath = __DIR__ . '/../assets/img/logo.png';
if(file_exists($logoPath)){
    $pdf->Image($logoPath,10,10,50);
}

// Company info
$pdf->SetXY(65,10);
$pdf->Cell(0,6,$company_name,0,1);
$pdf->SetFont('Arial','',12);
$pdf->SetX(65); $pdf->Cell(0,6,$company_address,0,1);
$pdf->SetX(65); $pdf->Cell(0,6,"Phone: ".$company_phone,0,1);
$pdf->SetX(65); $pdf->Cell(0,6,"Email: ".$company_email,0,1);

$pdf->Ln(15);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Invoice',0,1,'C');
$pdf->Ln(5);

// Customer & Order info
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,"Invoice No: ".$invoice_no,0,1);
$pdf->Cell(0,6,"Order ID: ".$order['order_id'],0,1);
$pdf->Cell(0,6,"Order Date: ".date('Y-m-d H:i', strtotime($order['created_at'])),0,1);
$pdf->Cell(0,6,"Customer: ".$order['fullname'],0,1);
$pdf->Cell(0,6,"Email: ".$order['email'],0,1);
$pdf->Cell(0,6,"Phone: ".$order['phone'],0,1);
$pdf->Cell(0,6,"Payment Method: ".$order['payment_method'],0,1);
$pdf->Ln(10);

// Table header
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(200,200,200);
$pdf->Cell(80,8,'Product',1,0,'C',true);
$pdf->Cell(30,8,'Qty',1,0,'C',true);
$pdf->Cell(40,8,'Price',1,0,'C',true);
$pdf->Cell(40,8,'Total',1,1,'C',true);

// Table body
$pdf->SetFont('Arial','',12);
$fill = false;
foreach($items as $item){
    $pdf->SetFillColor($fill?240:255,$fill?240:255,$fill?240:255);
    $pdf->Cell(80,8,$item['title'],1,0,'L',$fill);
    $pdf->Cell(30,8,$item['quantity'],1,0,'C',$fill);
    $pdf->Cell(40,8,number_format($item['price'],2),1,0,'R',$fill);
    $pdf->Cell(40,8,number_format($item['price']*$item['quantity'],2),1,1,'R',$fill);
    $fill = !$fill;
}

// Total
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(150,8,'Total Amount',1,0,'R',true);
$pdf->Cell(40,8,number_format($order['total_amount'],2),1,1,'R',true);

// Footer
$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,6,"Thank you for your purchase! Visit us again at IT Store.",0,1,'C');
$pdf->Cell(0,6,"This is a computer-generated invoice and does not require a signature.",0,1,'C');

// Save PDF to invoices folder
$pdf->Output('F', $invoice_path);

// Insert or update invoice record
$stmt_check = $conn->prepare("SELECT id FROM invoices WHERE order_id=? LIMIT 1");
$stmt_check->bind_param("i", $order['id']);
$stmt_check->execute();
$existing_invoice = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if($existing_invoice){
    $stmt_update = $conn->prepare("UPDATE invoices SET invoice_file=? WHERE order_id=?");
    $stmt_update->bind_param("si", $invoice_file, $order['id']);
    $stmt_update->execute();
    $stmt_update->close();
} else {
    $stmt_insert = $conn->prepare("INSERT INTO invoices (id, order_id, user_id, invoice_file, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt_insert->bind_param("siis", $invoice_no, $order['id'], $order['user_id'], $invoice_file);
    $stmt_insert->execute();
    $stmt_insert->close();
}

// Output PDF to browser
$pdf->Output('I', 'Invoice_'.$invoice_no.'.pdf');
exit;

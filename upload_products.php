<?php
include 'header.php';
include 'translations.php';
$page_title = 'Import Products';


// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: dashboard.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    csrf_verify();
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
 
        $header = fgetcsv($handle, 1000, ';');

 
        $requiredFields = ['name', 'reference', 'description', 'price', 'pricevat', 'stock', 'category_id'];
        $missingFields = array_diff($requiredFields, $header);

        if (!empty($missingFields)) {
            $message = translate('requiredFields',$translations) . implode(';', $missingFields);
        } else {
          
            $fieldIndices = array_flip($header);

           
            $insertedCount = 0;
            $invalidRows = 0;

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
              
                if (count($data) < count($header)) {
                    $invalidRows++;
                    continue;
                }

                
                $name = $mysqli->real_escape_string($data[$fieldIndices['name']]);
                $reference = $mysqli->real_escape_string($data[$fieldIndices['reference']]);
                $description = $mysqli->real_escape_string($data[$fieldIndices['description']]);
                $price = isset($data[$fieldIndices['price']]) ? (float)$data[$fieldIndices['price']] : 0.0;
                $pricevat = isset($data[$fieldIndices['pricevat']]) ? (float)$data[$fieldIndices['pricevat']] : 0.0;
                $stock = isset($data[$fieldIndices['stock']]) ? (int)$data[$fieldIndices['stock']] : 0;
                $category_id = isset($data[$fieldIndices['category_id']]) ? (int)$data[$fieldIndices['category_id']] : null;

                
                if ($category_id !== null) {
                    $stmtCategory = $mysqli->prepare("SELECT id FROM categories WHERE id = ?");
                    $stmtCategory->bind_param('i', $category_id);
                    $stmtCategory->execute();
                    $stmtCategory->store_result();

                    if ($stmtCategory->num_rows === 0) {
                        $stmtCategory->close();
                        $invalidRows++;
                        continue;
                    }
                    $stmtCategory->close();
                } else {
                    $invalidRows++;
                    continue;
                }

    // Validar se a referência já existe
    $stmtCheckReference = $mysqli->prepare("SELECT id FROM products WHERE reference = ?");
    $stmtCheckReference->bind_param('s', $reference);
    $stmtCheckReference->execute();
    $stmtCheckReference->store_result();

    if ($stmtCheckReference->num_rows > 0) {
        // Se a referência já existir, ignore a linha
        $stmtCheckReference->close();
        $invalidRows++;
        continue;
    }
    $stmtCheckReference->close();                
                    
                    
                    
                $stmt = $mysqli->prepare("INSERT INTO products (name, reference, description, price, pricevat, stock, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('sssddii', $name, $reference, $description, $price, $pricevat, $stock, $category_id);

                if ($stmt->execute()) {
                    $insertedCount++;
                } else {
                    $invalidRows++;
                }
                $stmt->close();
            }

            fclose($handle);

            $message = $insertedCount .' '.translate('productsImported',$translations);
            if ($invalidRows > 0) {
                $message .= $invalidRows .' '.translate('ignoredLines',$translations);
            }
        }
    } else {
        $message = translate('csvError',$translations);
    }
}


$mysqli->close();
include 'template.php';
?>

<div class="row">
    <h1 class="mb-4" data-translate="uploadProducts">Import Products</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form action="upload_products.php" method="POST" enctype="multipart/form-data" class="mb-4">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="csv_file" class="form-label" data-translate="selectFile">Select CSV File:</label>
            <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" data-translate="uploadProducts">Import Products</button>
        </div>
    </form>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title" data-translate="csvformat">CSV Format</h5>
            <pre class="bg-light p-3">
name;reference;description;price;pricevat;stock;category_id
"Product 1";"REF001";"Description 1";100.00;123.00;50;1
"Product 2";"REF002";"Description 2";200.00;246.00;30;2
            </pre>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<?php
include 'header.php';
include 'translations.php';

$company_name = $_SESSION['company_name'];
$page_title = $company_name.' | '.translate('orderProducts',$translations);


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Verifica se o produto existe
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Adiciona ou atualiza o produto no carrinho
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        $_SESSION['success_message'] = $product['name']. ' '.translate('productAdded',$translations);
    } else {
        $_SESSION['error_message'] = 'Product not found.';
    }

   
    header('Location: order_products.php');
    exit();
}

// Obtém a lista de produtos
$result_products = $mysqli->query('
    SELECT products.*, categories.name AS category_name 
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
');
$products = $result_products->fetch_all(MYSQLI_ASSOC);
                  


include 'template.php';
?>

<div class="row">
     <div class="col-lg-6 col-md-6 mb-4">  
     <h1 data-translate="orderProducts">Order Products</h1>   
     </div>  
  
     <div class="col-lg-6 col-md-6 mb-4 text-right">  
      <a href="cart.php" class="btn btn-primary" data-translate="checkout"><i class="fas fa-shopping-cart"></i>Checkout</a>   
     </div>          
        
    

    <!-- Dropdown para filtrar por categoria -->
<div class="mb-4">
    <div id="categoryTags" class="d-flex flex-wrap gap-2">
        <i class="fa fa-filter fa-1x" aria-hidden="true"></i>    
        <?php
        // Gerar as tags das categorias
        $result_categories = $mysqli->query('SELECT id, name FROM categories ORDER BY name ASC');
        while ($category = $result_categories->fetch_assoc()) {
            echo '<h4><span class="p-3 badge badge-pill bg-primary text-white category-tag btn-sm" data-category="' . htmlspecialchars($category['name']) . '">' . htmlspecialchars($category['name']) . '</span></h4>';
        }
        ?>
    </div>
</div>
        
    <div class="table-responsive-md">
        <table id="Data_Table_0" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th style="" data-translate="category">Category</th>                         
					<th data-translate="reference">Reference</th>
                    <th data-translate="name">Name</th>
                    <th data-translate="pricewvat">Price w/VAT</th>
                    <th data-translate="stock">Stock</th>
                    <th data-translate="quantity">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
						<td style=""><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>€ <?php echo htmlspecialchars(number_format($product['pricevat'], 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td style="text-align:right">
                            <form method="POST" action="" class="d-inline-block">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="quantity" style="width:50px" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary" data-bs-dismiss="process" data-translate="add">Add</button>
                                </div>
                            </form>
                        </td>
               
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>
<script src="js/categoriefilter.js"></script>

<?php
include 'header.php';
include 'translations.php';

$company_name = $_SESSION['company_name'];
$page_title = $company_name.' | '.translate('clients',$translations);

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: dashboard.php');
    exit();
}

// Create the 'clients' table if it does not exist
$create_clients_table_sql = "CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    nif VARCHAR(10) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(50),
    zip VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$mysqli->query($create_clients_table_sql);

// Process actions to add, edit, and delete clients
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_client'])) {
        $name = trim($_POST['name']);
        $nif = trim($_POST['nif']); 
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $zip = trim($_POST['zip']);
        $stmt_add = $mysqli->prepare('INSERT INTO clients (name, nif, email, phone, address, city, state, zip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt_add->bind_param('ssssssss', $name, $nif, $email, $phone, $address, $city, $state, $zip);
        $stmt_add->execute();
        $stmt_add->close();
    } elseif (isset($_POST['edit_client'])) {
        $client_id = intval($_POST['client_id']);
        $name = trim($_POST['name']);
        $nif = trim($_POST['nif']);     
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $zip = trim($_POST['zip']);
        $stmt_edit = $mysqli->prepare('UPDATE clients SET name = ?, nif = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip = ? WHERE id = ?');
        $stmt_edit->bind_param('ssssssssi', $name, $nif, $email, $phone, $address, $city, $state, $zip, $client_id);
        $stmt_edit->execute();
        $stmt_edit->close();
    } elseif (isset($_POST['delete_client'])) {
        $client_id = intval($_POST['client_id']);
        $stmt_delete = $mysqli->prepare('DELETE FROM clients WHERE id = ?');
        $stmt_delete->bind_param('i', $client_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
}

// Fetch all clients for DataTables
$result = $mysqli->query('SELECT * FROM clients ORDER BY name ASC');
$clients = $result->fetch_all(MYSQLI_ASSOC);
$mysqli->close();
include 'template.php';
?>

<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">  
    <h1 data-translate="clients">Clients</h1>
     </div>         
     <div class="col-lg-6 col-md-6 mb-4 text-right">
        <?php if ($is_admin): ?>     
        <button class="btn btn-rounded btn-info" data-bs-toggle="modal" data-bs-toggle="modal"  data-bs-target="#addClientModal" aria-hidden="true">
            	<i class="fa-solid fa-plus fa-1x" ></i>
        </button>   
        <?php endif; ?>     
     </div>              

    <div class="table-responsive">
        <table id="Data_Table_7" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="nif">NIF</th>   
                    <th data-translate="email">Email</th>
                    <th data-translate="phone">Phone</th>
                    <th data-translate="city">City</th>
                    <th data-translate="state">State</th>
                    <?php if ($is_admin): ?>
                        <th data-translate="actions">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                        <td><?php echo htmlspecialchars($client['nif']); ?></td>    
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone']); ?></td>
                        <td><?php echo htmlspecialchars($client['city']); ?></td>
                        <td><?php echo htmlspecialchars($client['state']); ?></td>
                        <?php if ($is_admin): ?>
                            <td class="text-right">
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editClientModal" onclick='populateEditModalClient(<?php echo json_encode($client); ?>)' data-translate="edit">Edit</button>
                                <form method="POST" action="" class="d-inline-block">
                                    <input type="hidden" name="delete_client" value="1">
                                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                    <button type="submit" class="btn btn-danger" data-translate="delete">Delete</button>
                                </form>
                                 <a href="client_details.php?client_id=<?php echo $client['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>        
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="<?php echo $is_admin ? '6' : '5'; ?>" class="text-center" data-translate="noClientsFound">No clients found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Client Modal -->
<div id="addClientModal" class="modal fade" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addClientForm" method="POST" action="">
                <input type="hidden" name="add_client" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel" data-translate="addClient">Add Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="client_name" class="form-label" data-translate="name">Name</label>
                        <input type="text" class="form-control" id="client_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_nif" class="form-label" data-translate="nif">NIF</label>
                        <input type="text" class="form-control" id="client_nif" name="nif" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_email" class="form-label" data-translate="email">Email</label>
                        <input type="email" class="form-control" id="client_email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="client_phone" class="form-label" data-translate="phone">Phone</label>
                        <input type="text" class="form-control" id="client_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="client_address" class="form-label" data-translate="address">Address</label>
                        <input type="text" class="form-control" id="client_address" name="address">
                    </div>
                    <div class="mb-3">
                        <label for="client_city" class="form-label" data-translate="city">City</label>
                        <input type="text" class="form-control" id="client_city" name="city">
                    </div>
                    <div class="mb-3">
                        <label for="client_state" class="form-label" data-translate="state">State</label>
                        <input type="text" class="form-control" id="client_state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="client_zip" class="form-label" data-translate="zip">ZIP</label>
                        <input type="text" class="form-control" id="client_zip" name="zip">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                    <button type="button" id="addClientSubmit" class="btn btn-primary" data-translate="add">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Client Modal -->
<div id="editClientModal" class="modal fade" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="edit_client" value="1">
                <input type="hidden" id="edit_client_id" name="client_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel" data-translate="editClient">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label" data-translate="name">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nif" class="form-label" data-translate="nif">NIF</label>
                        <input type="text" class="form-control" id="edit_nif" name="nif" required>
                    </div>                        
                    <div class="mb-3">
                        <label for="edit_email" class="form-label" data-translate="email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label" data-translate="phone">Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label" data-translate="address">Address</label>
                        <input type="text" class="form-control" id="edit_address" name="address">
                    </div>
                    <div class="mb-3">
                        <label for="edit_city" class="form-label" data-translate="city">City</label>
                        <input type="text" class="form-control" id="edit_city" name="city">
                    </div>
                    <div class="mb-3">
                        <label for="edit_state" class="form-label" data-translate="state">State</label>
                        <input type="text" class="form-control" id="edit_state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="edit_zip" class="form-label" data-translate="zip">ZIP</label>
                        <input type="text" class="form-control" id="edit_zip" name="zip">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                    <button type="buton" id="updateClientSubmit" class="btn btn-primary" data-translate="saveChanges">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="js/client_validations.js"></script>
<?php include 'footer.php'; ?>
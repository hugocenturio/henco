function populateEditModalProduct(product) {
    document.getElementById('edit_product_id').value = product.id || '';
    document.getElementById('edit_name').value = product.name || '';
    document.getElementById('edit_reference').value = product.reference || '';
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_price').value = product.price || '';
    document.getElementById('edit_pricevat').value = product.pricevat || '';
    document.getElementById('edit_stock').value = product.stock || '';
    document.getElementById('edit_category_id').value = product.category_id || '';
}


function populateEditModalClient(client) {
    document.getElementById('edit_client_id').value = client.id;
    document.getElementById('edit_name').value = client.name || '';
    document.getElementById('edit_nif').value = client.nif || '';    
    document.getElementById('edit_email').value = client.email || '';
    document.getElementById('edit_phone').value = client.phone || '';
    document.getElementById('edit_address').value = client.address || '';
    document.getElementById('edit_city').value = client.city || '';
    document.getElementById('edit_state').value = client.state || '';
    document.getElementById('edit_zip').value = client.zip || '';
    }



function populateEditModalUser(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role_id').value = user.role_id;
    document.getElementById('edit_password').value = ''; // Password is left blank
    }

// Ensure modals handle focus properly
    document.addEventListener('shown.bs.modal', function (event) {
        event.target.removeAttribute('aria-hidden');
    });

    document.addEventListener('hidden.bs.modal', function (event) {
        event.target.setAttribute('aria-hidden', 'true');
    });

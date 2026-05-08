$('#clientSelect').val();
    $(document).ready(function() {
        // Handle client selection
        $('#clientSelect').change(function() {
            var clientId = $(this).val();
            if (clientId) {
                // Make an AJAX call to get the client details
                $.ajax({
                    url: 'get_client_details.php',
                    type: 'GET',
                    data: { client_id: clientId },
                    success: function(response) {
                        var client = JSON.parse(response);
                        $('#address').val(client.address);
                        $('#city').val(client.city);
                        $('#state').val(client.state);
                        $('#zip').val(client.zip);
                        $('#clientDetails').show();
                    },
                    error: function() {
                        alert('Unable to fetch client details.');
                    }
                });
            } else {
                $('#clientDetails').hide();
            }
        });
    });
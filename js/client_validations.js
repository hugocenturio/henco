document.addEventListener('DOMContentLoaded', function () {
    const addClientForm = document.getElementById('addClientForm');
    const addClientSubmit = document.getElementById('addClientSubmit');
    const editClientForm = document.getElementById('editClientForm');
    const updateClientSubmit = document.getElementById('updateClientSubmit');       
        
//Client        
    addClientSubmit.addEventListener('click', function (event) {
      
        if (!addClientForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

     
            addClientForm.classList.add('was-validated');
        } else {
            
            addClientForm.submit();
        }
    });
        
     updateClientSubmit.addEventListener('click', function (event) {
      
        if (!editClientForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

            
            editClientForm.classList.add('was-validated');
        } else {
            
            editClientForm.submit();
        }
    });       
              
});
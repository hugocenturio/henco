document.addEventListener('DOMContentLoaded', function () {

    const addProductForm = document.getElementById('addProductForm');
    const addProductSubmit = document.getElementById('addProductSubmit');        
    const updateProductForm = document.getElementById('updateProductForm');
    const updateProductSubmit = document.getElementById('updateProductSubmit');         
              
//Product
        
    addProductSubmit.addEventListener('click', function (event) {
      
        if (!addProductForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

     
            addProductForm.classList.add('was-validated');
        } else {
            
            addProductForm.submit();
        }
    });        
     updateProductSubmit.addEventListener('click', function (event) {
      
        if (!updateProductForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

     
            updateProductForm.classList.add('was-validated');
        } else {
            
            updateProductForm.submit();
        }
    });       
        
});
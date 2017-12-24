<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WePay API Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="css/custom.css" media="screen">
    
</head>
<body>
</div>
    <div class="container">
        <div class="row">
            <form method="post" id="upload_csv" enctype="multipart/form-data" class="formcontrol">
                <div class="col-lg-6 middle margin-top">
                    <div class="form-group">
                        <input type="file" id="file" name="upload_file" class="form-control">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg" id="save">Pay</button>
                        <a class="btn btn-success btn-lg" id="download" href="payment_status.csv" role="button" style="display: none;">Download File</a>
                        <a class="btn btn-danger btn-lg" href="cards.csv" role="button">Download CSV Format</a>
                    </div>
                    <div class="form-group min_height" id="process" style="display: none;">

                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>  
      $(document).ready(function(){  
           $('#upload_csv').on("submit", function(e){ 
           
           $('#process').show();
           $('#save').prop('disabled', true);
           $('#process').html('Processing Please Wait...'); 
                e.preventDefault(); //form will not submitted  
                $.ajax({  
                     url:"test_creditcard.php",  
                     method:"POST",  
                     data:new FormData(this),  
                     contentType:false,          // The content type used when sending data to the server.  
                     cache:false,                // To unable request pages to be cached  
                     processData:false,          // To send DOMDocument or non processed data file it is set to false  
                     success: function(data){
                         $('#process').html(data);
                         if(data != 'Please select valid file format.'){
                             $('#download').show();
                         }else{
                            $('#download').hide();
                         }
                            
                         $('#save').prop('disabled', false);
                     }  
                })  
           });  
      });  
    </script> 
</body>
</html>
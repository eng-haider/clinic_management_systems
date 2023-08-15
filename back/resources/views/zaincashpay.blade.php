<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ZainCash</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div id="test"></div>

</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const token = urlParams.get('token')


    var requestOptions = {
  method: 'GET',
  redirect: 'follow'
};

  //fetch("https://test.zaincash.iq/decodeZainCashToken/"+ token, requestOptions)
  fetch("https://tctate.com/api/api/decodeZainCashToken/"+ token, requestOptions)
  .then(response => response.text())

  .then(response => {
              response
     
           appendData(JSON.parse(response));


            })
  .catch(error => console.log('error', error));
 
    function appendData(data) {


        if (data.success == false) {

            Swal.fire({
                title: data.data,
                icon: 'warning',
               // showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            })
            .then((value) => {
              window.close();
});

        } else if (data.success == true) {
          
          Swal.fire({
                title: 'تمت عمليه الدفع بنجاح!',
                icon: 'success',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            })

        }
        mainContainer.appendChild(div);
    }

</script>
</body>

</html>

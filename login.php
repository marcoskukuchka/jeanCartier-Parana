<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jean Cartier - Toma Pedidos</title>
    <!-- <meta name="robots" content="noindex"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
        integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../assets/css/css.css?v=<?= time() ?>">
    <link rel="icon" type="image/x-icon" href="../favicon-32x32.png">
</head>

<body>
    <div class="container">
        <div class="img-container">
            <img src="../assets/img/JC-logo.svg" alt="Alav SRL" class="img-fluid" width="200px" height="200px">
        </div>
        <h2 class="login-title">Iniciar Sesión</h2>
        <form id="form_login" class="login-form" action="./ajax/ingresar">
            <div>
                <label for="email">Email </label>
                <input id="email" type="email" placeholder="email@ejemplo.com" name="email" required />
            </div>
            <div>
                <label for="password">Contraseña </label>
                <input id="password" type="password" placeholder="******" name="pass" required />
            </div>
            <button class="btn btn--form" type="submit">
                Ingresar
            </button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $("#form_login").submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var actionUrl = form.attr('action');
            $.ajax({
                type: "POST",
                url: actionUrl,
                data: form.serialize(),
                success: function(data) {
                    if (data.estado == 1) {
                        console.log(data);
                        toastr.success(data.mensaje)
                        setTimeout(() => {
                            location.href = "./productos";
                        }, "1000");
                    } else {
                        toastr.error(data.mensaje)
                    }
                }
            });
        });
    </script>
</body>

</html>
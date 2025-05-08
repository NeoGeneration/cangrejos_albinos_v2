<?php
session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set maximum tickets available and per person
$max_tickets_per_person = 4;
$total_tickets_available = 100; // Change this to your actual capacity

// Check if form was submitted
$form_submitted = false;
$form_errors = [];
$success_message = "";
$error_message = "";

// Handle success
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $form_submitted = true;
    $confirmation_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : '';
    $success_message = "¡Reserva registrada con éxito! ";
    
    if (isset($_GET['email_confirmation']) && $_GET['email_confirmation'] == 1) {
        // Caso de reserva que requiere confirmación por email
        if (isset($_GET['email_warning']) && $_GET['email_warning'] == 1) {
            $success_message .= "Sin embargo, hubo un problema al enviar el correo de confirmación. Por favor, contacta con nosotros para completar el proceso de confirmación. Tu código de reserva es: <strong>$confirmation_code</strong>";
        } else {
            $success_message .= "<strong>Hemos enviado un correo electrónico a tu dirección de email con un enlace de confirmación.</strong> Por favor, revisa tu bandeja de entrada (y la carpeta de spam) y haz clic en el enlace para confirmar tu reserva. Tu reserva no será válida hasta que confirmes tu dirección de email.";
        }
    } else {
        // Caso de reserva sin confirmación de email (este caso ya no debería ocurrir con el nuevo sistema)
        if (isset($_GET['email_warning']) && $_GET['email_warning'] == 1) {
            $success_message .= "No se pudo enviar el correo de confirmación. Por favor, guarda este código de confirmación: <strong>$confirmation_code</strong>";
        } else {
            $success_message .= "Hemos enviado un correo de confirmación a tu email.";
        }
    }
}

// Handle errors
if (isset($_GET['error'])) {
    $error_type = $_GET['error'];
    
    switch ($error_type) {
        case 'validation':
            if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) {
                $form_errors = $_SESSION['form_errors'];
                unset($_SESSION['form_errors']);
            } else {
                $error_message = "Por favor, verifica los datos del formulario.";
            }
            break;
            
        case 'tickets':
            $tickets_left = isset($_GET['tickets_left']) ? intval($_GET['tickets_left']) : 0;
            $error_message = "Lo sentimos, solo quedan $tickets_left entradas disponibles.";
            break;
            
        case 'duplicate':
            $error_message = isset($_GET['msg']) ? urldecode($_GET['msg']) : "Ya existe una reserva con este email o DNI.";
            break;
            
        case 'db':
            $error_message = isset($_GET['msg']) ? urldecode($_GET['msg']) : "Error al procesar la reserva. Por favor, inténtalo de nuevo más tarde.";
            break;
            
        default:
            $error_message = "Ha ocurrido un error inesperado. Por favor, inténtalo de nuevo.";
    }
}

// Pre-fill form with previous data if available
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>
<!doctype html>
<html class="no-js" lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Cangrejos Albinos | CACT Lanzarote</title>
    <meta name="description" content="Reserva tu entrada para el espectáculo Cangrejos Albinos en CACT Lanzarote">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/logo/favicon.png">

    <!-- CSS here -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/flaticon_mycollection.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/odometer.css">
    <link rel="stylesheet" href="assets/css/default.css">
    <link rel="stylesheet" href="assets/css/main.css">
    
    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .ticket-selector {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .ticket-selector label {
            margin-right: 15px;
            margin-bottom: 0;
        }
        .ticket-selector select {
            flex: 0 0 100px;
        }
    </style>
</head>

<body>

    <!-- Preloader Start -->
    <div class="preloader">
        <div class="loader"></div>
    </div>
    <!-- Preloader End -->

    <!-- Scroll-top -->
    <button class="scroll__top scroll-to-target" data-target="html">
        <i class="fa-sharp fa-regular fa-arrow-up"></i>
    </button>
    <!-- Scroll-top-end-->

    <!-- main-area -->
    <main>
        <!-- td-contact-form-area-start -->
        <div id="entradas" class="td-contact-form-area pb-60">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="td-contact-form-wrap">
                            <?php if ($form_submitted && !empty($success_message)): ?>
                            <div class="success-message">
                                <?php echo $success_message; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                            <?php endif; ?>
                            
                            <form id="reservation-form" action="process_reservation.php" method="POST" novalidate>
                                <div class="td-contact-form-box">
                                    <h3 class="td-postbox-form-title mb-15">Reserva tu sitio en esta noche inspiradora</h3>
                                    
                                    <!-- Hidden CSRF token for security -->
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    
                                    <div class="row">
                                        <!-- Fila 1: Nombre y Apellidos -->
                                        <div class="col-lg-6 col-md-6 mb-20">
                                            <input class="td-input" name="name" type="text" placeholder="Nombre" required value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
                                            <div class="error-message" id="name-error">
                                                <?php if (isset($form_errors['name'])): ?>
                                                    <?php echo $form_errors['name']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 mb-20">
                                            <input class="td-input" name="last_name" type="text" placeholder="Apellidos" required value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>">
                                            <div class="error-message" id="last-name-error">
                                                <?php if (isset($form_errors['last_name'])): ?>
                                                    <?php echo $form_errors['last_name']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Fila 2: Email, Teléfono y DNI -->
                                        <div class="col-lg-4 col-md-4 mb-20">
                                            <input class="td-input" name="email" type="email" placeholder="Email" required value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                                            <div class="error-message" id="email-error">
                                                <?php if (isset($form_errors['email'])): ?>
                                                    <?php echo $form_errors['email']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 mb-20">
                                            <input class="td-input" name="phone" type="tel" placeholder="Teléfono" required value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>">
                                            <div class="error-message" id="phone-error">
                                                <?php if (isset($form_errors['phone'])): ?>
                                                    <?php echo $form_errors['phone']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 mb-20">
                                            <input class="td-input" name="dni" type="text" placeholder="DNI/NIF" required value="<?php echo isset($form_data['dni']) ? htmlspecialchars($form_data['dni']) : ''; ?>">
                                            <div class="error-message" id="dni-error">
                                                <?php if (isset($form_errors['dni'])): ?>
                                                    <?php echo $form_errors['dni']; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Selector de número de entradas -->
                                        <div class="col-lg-6 col-md-6 mb-20">
                                            <div class="ticket-selector">
                                                <label for="num_tickets">Número de entradas:</label>
                                                <select class="form-select" id="num_tickets" name="num_tickets">
                                                    <?php 
                                                    $selected_tickets = isset($form_data['num_tickets']) ? intval($form_data['num_tickets']) : 1;
                                                    for ($i = 1; $i <= 4; $i++): 
                                                    ?>
                                                    <option value="<?php echo $i; ?>" <?php echo ($selected_tickets == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                                <div class="error-message" id="tickets-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Fila 3: Comentarios -->
                                        <div class="col-12 mb-15">
                                            <textarea class="td-input message" name="comments" cols="30" rows="5" placeholder="Comentarios"><?php echo isset($form_data['comments']) ? htmlspecialchars($form_data['comments']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 mb-15">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="privacy_policy" name="privacy_policy" required <?php echo isset($form_data['privacy_policy']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="privacy_policy">
                                                    He leído y acepto la <a href="https://cactlanzarote.com/politica-de-privacidad/" target="_blank" class="text-decoration-underline">política de privacidad</a> y el tratamiento de mis datos personales.
                                                </label>
                                                <div class="error-message" id="privacy-error">
                                                    <?php if (isset($form_errors['privacy_policy'])): ?>
                                                        <?php echo $form_errors['privacy_policy']; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="td-btn">Reservar</button> 
                                            <small class="d-block text-muted mt-1 fst-italic small">Máximo 4 entradas por persona</small>
                                        </div>
                                    </div>
                                    <div id="form-response" class="pt-20"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- td-contact-form-area-end -->
    </main>
    <!-- main-area-end -->

    <!-- JS here -->
    <script src="assets/js/vendor/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/isotope.pkgd.min.js"></script>
    <script src="assets/js/imagesloaded.pkgd.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.odometer.min.js"></script>
    <script src="assets/js/jquery-appear.js"></script>
    <script src="assets/js/swiper-bundle.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
    $(document).ready(function() {
        // Form validation
        $('#reservation-form').on('submit', function(e) {
            let isValid = true;
            
            // Reset error messages
            $('.error-message').text('');
            
            // Validate name
            const name = $('input[name="name"]').val().trim();
            if (name === '') {
                $('#name-error').text('Por favor, introduce tu nombre');
                isValid = false;
            } else if (name.length < 2) {
                $('#name-error').text('El nombre debe tener al menos 2 caracteres');
                isValid = false;
            }
            
            // Validate last name
            const lastName = $('input[name="last_name"]').val().trim();
            if (lastName === '') {
                $('#last-name-error').text('Por favor, introduce tus apellidos');
                isValid = false;
            } else if (lastName.length < 2) {
                $('#last-name-error').text('Los apellidos deben tener al menos 2 caracteres');
                isValid = false;
            }
            
            // Validate email
            const email = $('input[name="email"]').val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                $('#email-error').text('Por favor, introduce tu email');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                $('#email-error').text('Por favor, introduce un email válido');
                isValid = false;
            }
            
            // Validate phone
            const phone = $('input[name="phone"]').val().trim();
            const phoneRegex = /^[0-9]{9,}$/;
            if (phone === '') {
                $('#phone-error').text('Por favor, introduce tu teléfono');
                isValid = false;
            } else if (!phoneRegex.test(phone)) {
                $('#phone-error').text('Por favor, introduce un teléfono válido (mínimo 9 dígitos)');
                isValid = false;
            }
            
            // Validate DNI/NIF
            const dni = $('input[name="dni"]').val().trim();
            const dniRegex = /^[0-9]{8}[A-Z]$/;
            const nieRegex = /^[XYZ][0-9]{7}[A-Z]$/;
            if (dni === '') {
                $('#dni-error').text('Por favor, introduce tu DNI/NIF');
                isValid = false;
            } else if (!dniRegex.test(dni) && !nieRegex.test(dni)) {
                $('#dni-error').text('Por favor, introduce un DNI/NIE válido');
                isValid = false;
            }
            
            // Validate privacy policy
            if (!$('#privacy_policy').is(':checked')) {
                $('#privacy-error').text('Debes aceptar la política de privacidad');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault(); // Prevent form submission if validation fails
            } else {
                // Show loading indicator or disable button
                $('button[type="submit"]').prop('disabled', true).text('Procesando...');
            }
        });
    });
    </script>
</body>

</html>
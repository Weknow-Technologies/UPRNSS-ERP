<?php
function alert($message, $type = 'success') {
    echo '
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <script src="js/sweetalert2.all.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: "'.$type.'",
                text: "'.addslashes($message).'",
                confirmButtonText: "OK",
                confirmButtonColor: "#3085d6"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        });
    </script>
    ';
}
?>
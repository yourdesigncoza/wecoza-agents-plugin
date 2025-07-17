/**
 * Agent Form Validation
 * 
 * Standalone JavaScript file for form validation
 * Bootstrap 5 compatible validation with jQuery
 */

jQuery(document).ready(function($) {
    // ID Type Toggle
    $('input[name="id_type"]').on('change', function() {
        if ($(this).val() === 'sa_id') {
            $('#sa_id_field').removeClass('d-none');
            $('#passport_field').addClass('d-none');
            $('#sa_id_no').prop('required', true);
            $('#passport_number').prop('required', false).val('');
        } else {
            $('#sa_id_field').addClass('d-none');
            $('#passport_field').removeClass('d-none');
            $('#sa_id_no').prop('required', false).val('');
            $('#passport_number').prop('required', true);
        }
    });
    
    // Initialize Select2 for preferred working areas if available
    if ($.fn.select2) {
        $('[id^="preferred_working_area_"]').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select a location'
        });
    }
    
    // Bootstrap form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // Custom validation for ID fields
            var idType = $('input[name="id_type"]:checked').val();
            if (idType === 'sa_id') {
                var saId = $('#sa_id_no').val();
                if (!saId || saId.length !== 13) {
                    $('#sa_id_no')[0].setCustomValidity('Invalid');
                } else {
                    $('#sa_id_no')[0].setCustomValidity('');
                }
            } else if (idType === 'passport') {
                var passport = $('#passport_number').val();
                if (!passport || passport.length < 6) {
                    $('#passport_number')[0].setCustomValidity('Invalid');
                } else {
                    $('#passport_number')[0].setCustomValidity('');
                }
            }
            
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
(function($) {
    'use strict';
}) // (function($)
    /*------------------YDCOZA-----------------------*/
    /* Client-side form validation using Bootstrap 5  */
    /* with visual feedback for agents-form only.   */
    /* Prevents form submission if validation fails   */
    /* and shows custom Bootstrap feedback styles.    */
    /*-----------------------------------------------*/
jQuery(document).ready(function($) {

        setTimeout(function() {
          $('#wecoza-agents-loader-container').hide();
        }, 2000);



        const form = $('#agents-form'); // Target the specific agents form

        if (form.length) {
            form.on('submit', function(event) {
                // Check if form is valid
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // Add Bootstrap's 'was-validated' class to trigger validation styles
                $(this).addClass('was-validated');
            });
        }

    /*------------------YDCOZA-----------------------*/
    /* Toggle SA ID and Passport Fields Based on Radio*/
    /*-----------------------------------------------*/

        // IMPORTANT!  Reference Helper Functions in app.js


        const SA_ID_PATTERN = /^([0-9]{2})((?:[0][1-9])|(?:[1][0-2]))((?:[0-2][0-9])|(?:[3][0-1]))(?:[0-9]{7})$/;
        const PASSPORT_PATTERN = /^[A-Z0-9]{6,12}$/i;

        function validateSaId(idNumber) {
            if (!SA_ID_PATTERN.test(idNumber)) {
                return {
                    valid: false,
                    message: 'ID number must be 13 digits in format: YYMMDD + 7 digits'
                };
            }
            const year = parseInt(idNumber.substring(0, 2));
            const month = parseInt(idNumber.substring(2, 4));
            const day = parseInt(idNumber.substring(4, 6));
            const fullYear = year + (year < 50 ? 2000 : 1900);
            const date = new Date(fullYear, month - 1, day);
            if (date.getDate() !== day || date.getMonth() !== month - 1 || date.getFullYear() !== fullYear) {
                return {
                    valid: false,
                    message: 'Invalid date in ID number'
                };
            }
            let sum = 0;
            let isSecond = false;
            for (let i = idNumber.length - 1; i >= 0; i--) {
                let digit = parseInt(idNumber.charAt(i));
                if (isSecond) {
                    digit *= 2;
                    if (digit > 9) {
                        digit -= 9;
                    }
                }
                sum += digit;
                isSecond = !isSecond;
            }
            if (sum % 10 !== 0) {
                return {
                    valid: false,
                    message: 'Invalid ID number checksum'
                };
            }
            return { valid: true };
        }

        function validatePassport(passportNumber) {
            if (!PASSPORT_PATTERN.test(passportNumber)) {
                return {
                    valid: false,
                    message: 'Passport number must be 6-12 characters (letters and numbers only)'
                };
            }
            return { valid: true };
        }

        function showValidationFeedback(input, validationResult) {
            if (!validationResult.valid) {
                input.addClass('is-invalid').removeClass('is-valid');
                input.siblings('.invalid-feedback').text(validationResult.message);
            } else {
                input.addClass('is-valid').removeClass('is-invalid');
                input.siblings('.valid-feedback').text('Valid!');
            }
        }   


            const $form = $('#agents-form');
            const saIdOption = $form.find('#sa_id_option');
            const passportOption = $form.find('#passport_option');
            const saIdField = $form.find('#sa_id_field');
            const passportField = $form.find('#passport_field');
            const saIdInput = $form.find('#sa_id_no');
            const passportInput = $form.find('#passport_number');

            var initialSaId = saIdInput.val();
            var initialPassportNumber = passportInput.val();

            function toggleIdFields(selectedType) {
                if (selectedType === 'sa_id') {
                    saIdField.removeClass('d-none');
                    passportField.addClass('d-none');
                    saIdInput.prop('required', true);
                    passportInput.prop('required', false);
                    if (passportInput.val() !== initialPassportNumber) {
                        passportInput.val('').removeClass('is-valid is-invalid');
                    }
                } else if (selectedType === 'passport') {
                    passportField.removeClass('d-none');
                    saIdField.addClass('d-none');
                    passportInput.prop('required', true);
                    saIdInput.prop('required', false);
                    if (saIdInput.val() !== initialSaId) {
                        saIdInput.val('').removeClass('is-valid is-invalid');
                    }
                }
            }

            // Event listener for radio buttons
            $form.find('input[name="id_type"]').change(function() {
                toggleIdFields($(this).val());
            });

            // Real-time SA ID validation
            saIdInput.on('input', function() {
                const idNumber = $(this).val().trim();
                if (idNumber) {
                    const validationResult = validateSaId(idNumber);
                    showValidationFeedback($(this), validationResult);
                } else {
                    $(this).removeClass('is-valid is-invalid');
                }
            });

            // Real-time passport validation
            passportInput.on('input', function() {
                const passportNumber = $(this).val().trim();
                if (passportNumber) {
                    const validationResult = validatePassport(passportNumber);
                    showValidationFeedback($(this), validationResult);
                } else {
                    $(this).removeClass('is-valid is-invalid');
                }
            });

            // Form submit validation
            $form.on('submit', function(e) {
                const selectedType = $form.find('input[name="id_type"]:checked').val();
                let isValid = true;

                if (selectedType === 'sa_id') {
                    const idNumber = saIdInput.val().trim();
                    const validationResult = validateSaId(idNumber);
                    if (!validationResult.valid) {
                        isValid = false;
                        showValidationFeedback(saIdInput, validationResult);
                    }
                } else if (selectedType === 'passport') {
                    const passportNumber = passportInput.val().trim();
                    const validationResult = validatePassport(passportNumber);
                    if (!validationResult.valid) {
                        isValid = false;
                        showValidationFeedback(passportInput, validationResult);
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });



            function toggleSignedAgreementFields() {
                if ($('#signed_agreement').is(':checked')) {
                    // Show the fields by removing the 'd-none' class from their parent containers
                    $('#signed_agreement_date').closest('.col-md-3').removeClass('d-none');
                    $('#signed_agreement_file').closest('.col-md-3').removeClass('d-none');
                    // Optionally, you can set these fields as required:
                    $('#signed_agreement_date').prop('required', true);
                    $('#signed_agreement_file').prop('required', true);
                } else {
                    // Clear any entered values:
                    $('#signed_agreement_date').val('');
                    $('#signed_agreement_file').val('');
                    // Hide the fields by adding the 'd-none' class back to their parent containers
                    $('#signed_agreement_date').closest('.col-md-3').addClass('d-none');
                    $('#signed_agreement_file').closest('.col-md-3').addClass('d-none');
                    // Optionally, remove the required property:
                    $('#signed_agreement_date').prop('required', false);
                    $('#signed_agreement_file').prop('required', false);
                }
            }

            // Run the function when the checkbox state changes
            $('#signed_agreement').on('change', toggleSignedAgreementFields);

            // Optionally, initialize on page load (in case the checkbox is pre-checked)
            toggleSignedAgreementFields();

}) // jQuery(document).ready(function($) {
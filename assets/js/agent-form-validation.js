/**
 * Agent Form Validation
 * 
 * Standalone JavaScript file for form validation
 * Bootstrap 5 compatible validation with jQuery
 */

// SA ID Validation Function
function validateSAID(id) {
    // Check if ID is exactly 13 digits
    if (!/^\d{13}$/.test(id)) {
        return false;
    }
    
    // SA ID checksum validation
    var checksum = 0;
    for (var i = 0; i < 12; i++) {
        var digit = parseInt(id[i]);
        if (i % 2 === 0) {
            checksum += digit;
        } else {
            var doubled = digit * 2;
            // Sum the digits of the doubled value (e.g., 14 becomes 1 + 4 = 5)
            checksum += (doubled >= 10) ? (1 + (doubled % 10)) : doubled;
        }
    }
    
    var calculatedChecksum = (10 - (checksum % 10)) % 10;
    var actualChecksum = parseInt(id[12]);
    
    return calculatedChecksum === actualChecksum;
}

jQuery(document).ready(function($) {
    // Disable Select2 on plugin form elements
    if (typeof $.fn.select2 !== 'undefined') {
        // Destroy any existing Select2 instances on our form
        $('#agents-form select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        
        // Prevent Select2 from initializing on our form elements
        $('#agents-form select').addClass('no-select2');
    }
    
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
    
    // Bootstrap form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // Log form data before submission
            console.log('[WeCoza Agents DEBUG] Form submission started');
            
            // Collect all form data
            var formData = new FormData(form);
            var formDataObj = {};
            for (var [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            console.log('[WeCoza Agents DEBUG] Form data being submitted:', formDataObj);
            
            // Specifically log address-related fields for debugging
            console.log('[WeCoza Agents DEBUG] Address fields:', {
                'address_line_1': formDataObj.address_line_1,
                'residential_suburb': formDataObj.residential_suburb,
                'city_town': formDataObj.city_town,
                'province_region': formDataObj.province_region,
                'postal_code': formDataObj.postal_code,
                'residential_town_id': formDataObj.residential_town_id
            });
            // Custom validation for ID fields
            var idType = $('input[name="id_type"]:checked').val();
            if (idType === 'sa_id') {
                var saId = $('#sa_id_no').val();
                if (!validateSAID(saId)) {
                    $('#sa_id_no')[0].setCustomValidity('Please enter a valid SA ID number');
                } else {
                    $('#sa_id_no')[0].setCustomValidity('');
                }
            } else 
            if (idType === 'passport') {
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
    
    // Real-time SA ID validation
    $('#sa_id_no').on('input', function() {
        var saId = $(this).val();
        var feedback = $(this).siblings('.invalid-feedback');
        
        if (saId.length === 0) {
            $(this).removeClass('is-valid is-invalid');
            return;
        }
        
        if (saId.length < 13) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            feedback.text('SA ID number must be 13 digits');
        } else if (saId.length > 13) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            feedback.text('SA ID number must be exactly 13 digits');
        } else if (!/^\d{13}$/.test(saId)) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            feedback.text('SA ID number must contain only digits');
        } else if (!validateSAID(saId)) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            feedback.text('SA ID number checksum is invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $(this)[0].setCustomValidity('');
        }
    });
    
    // Restrict SA ID input to numbers only
    $('#sa_id_no').on('keypress', function(e) {
        // Allow backspace, delete, tab, escape, enter
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
            // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true)) {
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
        // Prevent input if already 13 characters
        if ($(this).val().length >= 13) {
            e.preventDefault();
        }
    });
    
    // Auto-generate initials from first name, second name, and surname
    function generateInitials() {
        var firstName = $('#first_name').val().trim();
        var secondName = $('#second_name').val().trim();
        var surname = $('#surname').val().trim();
        var initials = '';
        
        if (firstName) {
            initials += firstName.charAt(0).toUpperCase();
        }
        if (secondName) {
            initials += secondName.charAt(0).toUpperCase();
        }
        if (surname) {
            initials += surname.charAt(0).toUpperCase();
        }
        
        $('#initials').val(initials);
    }
    
    // Bind initials generation to name field changes
    $('#first_name, #second_name, #surname').on('input', generateInitials);
    
    // Generate initials on page load if names are pre-filled
    generateInitials();
    
    // Google Places Autocomplete initialization - handled by WordPress enqueue system
});

// Initialize Google Places Autocomplete (global function for callback)
async function initializeGooglePlaces() {
    try {
        // Import the places library
        const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
        
        // Get the container element
        var container = document.getElementById('google_address_container');
        var input = document.getElementById('google_address_search');
        
        if (!container || !input) return;
        
        // Hide the original input completely
        input.style.display = 'none';
        input.style.visibility = 'hidden';
        
        // Create PlaceAutocompleteElement
        const placeAutocomplete = new PlaceAutocompleteElement({
            includedRegionCodes: ['za'], // Restrict to South Africa
            requestedLanguage: 'en',
            requestedRegion: 'za'
        });
        
        // Add CSS classes for styling to match Bootstrap form-control
        placeAutocomplete.className = 'form-control form-control-sm';
        placeAutocomplete.setAttribute('placeholder', 'Start typing an address...');
        
        // Replace the original input with the new element
        container.replaceChild(placeAutocomplete, input);
        
        // Add event listener for place selection
        placeAutocomplete.addEventListener('gmp-select', async (event) => {
            const place = event.placePrediction.toPlace();
            
            // Fetch place details
            await place.fetchFields({
                fields: ['displayName', 'formattedAddress', 'addressComponents', 'location']
            });
            
            if (!place.addressComponents) {
                return;
            }
            
            // Parse address components
            var streetNumber = '';
            var route = '';
            var suburb = '';
            var city = '';
            var province = '';
            var postalCode = '';
            
            for (var i = 0; i < place.addressComponents.length; i++) {
                var component = place.addressComponents[i];
                var types = component.types;
                
                if (types.includes('street_number')) {
                    streetNumber = component.longText;
                }
                if (types.includes('route')) {
                    route = component.longText;
                }
                if (types.includes('sublocality_level_1') || types.includes('sublocality')) {
                    suburb = component.longText;
                }
                if (types.includes('locality')) {
                    city = component.longText;
                }
                if (types.includes('administrative_area_level_1')) {
                    province = component.longText;
                }
                if (types.includes('postal_code')) {
                    postalCode = component.longText;
                }
            }
            
            // Log parsed address components
            console.log('[WeCoza Agents DEBUG] Google Maps parsed address components:', {
                'streetNumber': streetNumber,
                'route': route,
                'suburb': suburb,
                'city': city,
                'province': province,
                'postalCode': postalCode
            });
            
            // Populate form fields
            var streetAddress = streetNumber ? streetNumber + ' ' + route : route;
            jQuery('#address_line_1').val(streetAddress).trigger('change');
            jQuery('#residential_suburb').val(suburb).trigger('change');
            jQuery('#city_town').val(city).trigger('change');
            jQuery('#postal_code').val(postalCode).trigger('change');
            
            // Map province names to form values
            var provinceMap = {
                'Gauteng': 'Gauteng',
                'Western Cape': 'Western Cape',
                'KwaZulu-Natal': 'KwaZulu-Natal',
                'Eastern Cape': 'Eastern Cape',
                'Free State': 'Free State',
                'Mpumalanga': 'Mpumalanga',
                'Limpopo': 'Limpopo',
                'North West': 'North West',
                'Northern Cape': 'Northern Cape'
            };
            
            if (provinceMap[province]) {
                jQuery('#province_region').val(provinceMap[province]).trigger('change');
            }
            
            // Log what was populated in the form
            console.log('[WeCoza Agents DEBUG] Form fields populated from Google Maps:', {
                'address_line_1': streetAddress,
                'residential_suburb': suburb,
                'city_town': city,
                'province_region': provinceMap[province] || province,
                'postal_code': postalCode
            });
        });
        
        // Handle errors
        placeAutocomplete.addEventListener('gmp-error', (event) => {
            console.error('Google Maps API error:', event);
        });
        
    } catch (error) {
        console.error('Failed to initialize Google Places:', error);
        
        // Fallback: show the original input if the new API fails
        var input = document.getElementById('google_address_search');
        if (input) {
            input.style.display = 'block';
            input.placeholder = 'Address search unavailable';
        }
    }
}
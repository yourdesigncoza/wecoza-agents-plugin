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
            // Collect all form data
            var formData = new FormData(form);
            var formDataObj = {};
            for (var [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
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
        // Check if form elements exist before proceeding
        var $firstName = $('#first_name');
        var $secondName = $('#second_name');
        var $surname = $('#surname');
        var $initials = $('#initials');
        
        // Exit if required form fields don't exist
        if (!$firstName.length || !$surname.length || !$initials.length) {
            return;
        }
        
        var firstName = $firstName.val() ? $firstName.val().trim() : '';
        var secondName = $secondName.val() ? $secondName.val().trim() : '';
        var surname = $surname.val() ? $surname.val().trim() : '';
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
        
        $initials.val(initials);
    }
    
    // Bind initials generation to name field changes (only if elements exist)
    if ($('#first_name').length || $('#second_name').length || $('#surname').length) {
        $('#first_name, #second_name, #surname').on('input', generateInitials);
        
        // Generate initials on page load if names are pre-filled
        generateInitials();
    }
    
    // Initialize Google Places Autocomplete if container exists
    if (document.getElementById('google_address_container')) {
        // Wait for Google Maps API and places library to load
        waitForGoogleMapsAndInitialize();
    }
});

// Wait for Google Maps API to fully load including places library
function waitForGoogleMapsAndInitialize() {
    var maxAttempts = 50; // 5 seconds max wait
    var attempts = 0;
    
    function checkGoogleMapsLoaded() {
        attempts++;
        
        // Check if Google Maps API is loaded with places library
        if (typeof google !== 'undefined' && 
            google.maps && 
            google.maps.places && 
            google.maps.places.Autocomplete) {
            
            console.log('Google Maps API loaded, initializing Places...');
            initializeGooglePlaces();
            return;
        }
        
        // Check if we've reached max attempts
        if (attempts >= maxAttempts) {
            console.error('Google Maps API failed to load after ' + (maxAttempts * 100) + 'ms');
            showFallbackInput();
            return;
        }
        
        // Try again in 100ms
        setTimeout(checkGoogleMapsLoaded, 100);
    }
    
    // Start checking
    checkGoogleMapsLoaded();
}

// Initialize Google Places Autocomplete (global function for callback)
async function initializeGooglePlaces() {
    try {
        // Check if the new importLibrary method is available
        if (google.maps.importLibrary) {
            // Use new API method
            const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
            initializeNewGooglePlaces(PlaceAutocompleteElement);
        } else {
            // Use old API method as fallback
            initializeOldGooglePlaces();
        }
    } catch (error) {
        console.error('Failed to initialize Google Places:', error);
        
        // Try fallback to old API
        try {
            initializeOldGooglePlaces();
        } catch (fallbackError) {
            console.error('Fallback Google Places initialization also failed:', fallbackError);
            showFallbackInput();
        }
    }
}

// New Google Places API implementation
function initializeNewGooglePlaces(PlaceAutocompleteElement) {
    try {
        
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
            
        });
        
        // Handle errors
        placeAutocomplete.addEventListener('gmp-error', (event) => {
            console.error('Google Maps API error:', event);
        });
        
    } catch (error) {
        console.error('Failed to initialize new Google Places:', error);
        showFallbackInput();
    }
}

// Old Google Places API implementation (fallback)
function initializeOldGooglePlaces() {
    var input = document.getElementById('google_address_search');
    if (!input) return;
    
    // Show the input for old API
    input.style.display = 'block';
    input.style.visibility = 'visible';
    
    // Create Autocomplete instance with old API
    var autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: 'za' }, // Restrict to South Africa
        fields: ['place_id', 'geometry', 'name', 'formatted_address', 'address_components']
    });
    
    // Add event listener for place selection
    autocomplete.addListener('place_changed', function() {
        var place = autocomplete.getPlace();
        
        if (!place.address_components) {
            return;
        }
        
        // Parse address components (same logic as new API)
        var streetNumber = '';
        var route = '';
        var suburb = '';
        var city = '';
        var province = '';
        var postalCode = '';
        var country = '';
        
        for (var i = 0; i < place.address_components.length; i++) {
            var component = place.address_components[i];
            var types = component.types;
            
            if (types.includes('street_number')) {
                streetNumber = component.long_name;
            } else if (types.includes('route')) {
                route = component.long_name;
            } else if (types.includes('sublocality_level_1') || types.includes('neighborhood')) {
                suburb = component.long_name;
            } else if (types.includes('locality')) {
                city = component.long_name;
            } else if (types.includes('administrative_area_level_1')) {
                province = component.long_name;
            } else if (types.includes('postal_code')) {
                postalCode = component.short_name;
            } else if (types.includes('country')) {
                country = component.long_name;
            }
        }
        
        // Populate form fields
        var streetAddress = (streetNumber + ' ' + route).trim();
        if (streetAddress) {
            jQuery('#street_address').val(streetAddress);
        }
        if (suburb) {
            jQuery('#suburb_area').val(suburb);
        }
        if (city) {
            jQuery('#city_town').val(city);
        }
        if (postalCode) {
            jQuery('#postal_code').val(postalCode);
        }
        
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
    });
}

// Show fallback input when both APIs fail
function showFallbackInput() {
    var input = document.getElementById('google_address_search');
    if (input) {
        input.style.display = 'block';
        input.style.visibility = 'visible';
        input.placeholder = 'Address search unavailable - enter manually';
        input.disabled = true;
    }
}
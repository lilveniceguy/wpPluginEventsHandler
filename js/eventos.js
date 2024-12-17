jQuery(document).ready(function($) {
    // Function to sanitize RUT input
    function sanitizeRutInput($inputField) {
        var inputValue = $inputField.val().trim();
        // Remove non-numeric characters and convert 'k' to uppercase
        var sanitizedValue = inputValue.replace(/[^\dkK]/gi, '').replace(/^0+/, '').toUpperCase();

        // Update the value of the input field
        $inputField.val(sanitizedValue);
    }

    var rutFieldIds = [];
    var $rutField = [];
    var rutPersonal = []
    $('label').each(function() {
        var $label = $(this);
        if ($label.text().toLowerCase().includes('rut')) {
            var fieldId = $label.attr('for');
            if (fieldId) {
                rutFieldIds.push('#' + fieldId);
            }
        }
    });

    $('label').each(function() {
        var $labelx3 = $(this);
        if ($labelx3.text().toLowerCase().includes('rut') && !$labelx3.text().toLowerCase().includes('empresa')) {
            var fieldIdx3 = $labelx3.attr('for');
            if (fieldIdx3) {
                rutPersonal.push('#' + fieldIdx3);
            }
        }
    });
    rutPersonal = $(rutPersonal[0])
    console.log(rutPersonal)

    $('label').each(function() {
        var $labelx2 = $(this);
        if ($labelx2.text().toLowerCase().includes('empresa') && $labelx2.text().toLowerCase().includes('rut')) {
            var fieldIdx2 = $labelx2.attr('for');
            if (fieldIdx2 && $rutField.length===0) {
                $rutField.push('#' + fieldIdx2);
            }
        }
    })

    $rutFieldEmpresa = $($rutField[0])

    // Join the collected field IDs into a single jQuery selector
    var $rutFields = ''
    for (let i = 0; i < rutFieldIds.length; i++) {
      $rutFields += rutFieldIds[i]+',';
    }
    $rutFields = $($rutFields.slice(0, -1)) 

    // Check if there are any RUT fields
    if ($rutFields.length !== 0){

        // Attach input event handler to RUT fields
        $rutFields.on('input', function(event) {
            sanitizeRutInput($(this));
        });

        var $submitButton = $('.wpforms-form').find('button[type="submit"]');
        $submitButton.prop('disabled', true);
        $(".wpforms-form").after("<em id='results'><ul style='text-align: center;font-size: 12px;color: red;font-style: normal;''></ul></em>")
        var $globalMessage = $("#results ul")
        var $rutPersonalError, $rutEmpresaError
        var fieldIsValid = false;
        var rutIsValid = false;

        $rutFieldEmpresa.on('keyup', function() {
            event.preventDefault(); // Prevent default behavior
            validateAndDisplayError($(this));
        });

        rutPersonal.on('keyup', function() {
            console.log($(this))
            if(!validateRut($(this))){
                $(this).addClass('error');
                $(this).next('.wpforms-has-error').remove(); 
                $(this).after('<em class="wpforms-has-error wpforms-error">Debe ingresar un Rut sin puntos ni guion Ej: 192885279.</em>');
                rutIsValid = false;
                $rutPersonalError = "Ingresa un Rut personal valido"
            } else {
                $(this).removeClass('error');
                $(this).next('.wpforms-has-error').remove(); 
                rutIsValid = true;
                $rutPersonalError = ''
            }
            globalMessage()
            toggleSubmitButton();
        });

        $('input[type="text"]').not($rutFieldEmpresa).on('blur', function() {
            validateWithoutDisplaying($rutFieldEmpresa);
        });

        $('.wpforms-form').on('submit', function(event) {
            var isValid = validateAndDisplayError($rutFieldEmpresa);
            if (!isValid || !rutIsValid) {
                event.preventDefault();
            }
        });

    }


    function validateWithoutDisplaying($field){
        var fieldValue = $($field).val();
        var isValid = validateField(fieldValue);
        
        if (!isValid) {
            $submitButton.prop('disabled', true); 
            fieldIsValid = false;
        } else {
            compareWithAssociatedPosts(fieldValue, function(isInList) {
                if (!isInList) {
                    $submitButton.prop('disabled', true);
                    fieldIsValid = false;
                } else {
                    compareWithAssociatedPosts(fieldValue);
                    $rutEmpresaError = ''
                    fieldIsValid = true;
                }
                toggleSubmitButton();
            });
        }
    }
    
    function validateAndDisplayError($field) {
        var fieldValue = $field.val();
        var isValid = validateField(fieldValue);
        
        if (!isValid) {
            $field.addClass('error');
            $field.next('.wpforms-has-error').remove(); 
            $field.after('<em class="wpforms-has-error wpforms-error">Debe ingresar un Rut sin puntos ni guion Ej: 192885279.</em>');
            $submitButton.prop('disabled', true);
            fieldIsValid = false;
            $rutEmpresaError = "Debe ingresar un RUT Empresa valido"
        } else {
            compareWithAssociatedPosts(fieldValue, function(isInList) {
                if (!isInList) {
                    $field.addClass('error');
                    $field.next('.wpforms-has-error').remove();
                    var message = 'Su Rut es incorrecto, por favor contactar a servicio al cliente.';
                    $field.after('<em class="wpforms-has-error wpforms-error">' + message + '</em>');
                    $submitButton.prop('disabled', true);
                    fieldIsValid = false;
                    $rutEmpresaError = message
                } else {
                    $field.removeClass('error');
                    $field.next('.wpforms-has-error').remove();
                    $submitButton.prop('disabled', false);
                    fieldIsValid = true;
                    $rutEmpresaError = ''
                }
                toggleSubmitButton();
            });
        }
        globalMessage()
        return isValid;
    }
    
    function validateField(value) {
        var numericValue = value.replace(/\D/g,'');
        var cleanValue = value.split('\n').map(function(line) {
                    return line.replace(/[.-]/g, '').toUpperCase();
                });
        
        if (numericValue.length < 7 || numericValue.length > 9) {
            return false;
        }
        
        var lastChar = numericValue.slice(-1).toUpperCase();
        if ((cleanValue.length === 9 || cleanValue.length === 8) && !(/[0-9K]/.test(lastChar))) {
            return false;
        }

        return true;
    }

    function compareWithTXT(inputValue, callback) {
        var txtFile = '/ruts.txt';
        var sanitizedInput = inputValue.replace(/[.-]/g, '').replace(/^0+/, '').toUpperCase();
        $.ajax({
            url: txtFile,
            dataType: 'text',
            success: function(contents) {
                var txtNumbers = contents.split('\n').map(function(line) {
                    return line.replace(/[.-]/g, '').replace(/^0+/, '').toUpperCase();
                });

                txtNumbers = txtNumbers.filter(function(number) {
                    return number.trim() !== '';
                });

                console.log(txtNumbers.includes(sanitizedInput))
                var matchFound = txtNumbers.includes(sanitizedInput) || sanitizedInput === "1234567K";

                if (typeof callback === 'function') {
                    callback(matchFound);
                }

                // Set the rutEmpresaError here
                $rutEmpresaError = matchFound ? '' : 'Su Rut Empresa es incorrecto, por favor contactar a servicio al cliente.';
                globalMessage(); // Update the global message
                toggleSubmitButton(); // Toggle the submit button
            },
            error: function(xhr, status, error) {
                console.error('Error fetching TXT file:', error);
                if (typeof callback === 'function') {
                    callback(false);
                }
                toggleSubmitButton(false);
            }
        });
    }

    function compareWithAssociatedPosts(inputValue, callback) {

        var sanitizedInput = inputValue.replace(/[.-]/g, '').replace(/^0+/, '').toUpperCase();
        
        var associatedPostIds = myScriptData.associatedPosts; // Get associated post IDs from localized script data

        // Fetch post content for each associated post
        var matchFound = false;
        var remainingPosts = associatedPostIds.length;

        if (remainingPosts === 0) {
            if (typeof callback === 'function') {
                callback(false);
            }
            return;
        }

        associatedPostIds.forEach(function(postId) {
            $.ajax({
                url: '/wp-json/wp/v2/events-users-db/' + postId,
                dataType: 'json',
                success: function(postData) {
                    var postContent = postData.content.rendered;
                    
                    // Remove <br />, <p>, and </p> tags
                    postContent = postContent.replace(/<br\s*\/?>|<\/?p>/gi, '');

                    // Sanitize post content in the same way as input
                    var sanitizedContent = postContent.replace(/[.,\-]/g, '').replace(/^0+/, '').toUpperCase();

                    // Create a regular expression to match the whole word
                    var regex = new RegExp('\\b' + sanitizedInput + '\\b', 'i');

                    // Check if sanitizedInput exists as a whole word in post content
                    // console.log("regex",regex)
                    // console.log("sanitized",sanitizedContent)
                    if (regex.test(sanitizedContent)) {
                        matchFound = true;
                    }

                    remainingPosts--;
                    if (remainingPosts === 0) {
                        // All posts checked, callback with match result
                        if (typeof callback === 'function') {
                            callback(matchFound);
                        }

                        // Set the rutEmpresaError here
                        $rutEmpresaError = matchFound ? '' : 'Su Rut Empresa es incorrecto, por favor contactar a servicio al cliente.';
                        globalMessage(); // Update the global message
                        toggleSubmitButton(); // Toggle the submit button
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching post data:', error);
                    remainingPosts--;
                    if (remainingPosts === 0) {
                        // All posts checked, callback with match result
                        if (typeof callback === 'function') {
                            callback(matchFound);
                        }

                        toggleSubmitButton(false);
                    }
                }
            });
        });
    }


    // Example function to get associated post IDs from meta box
    function getAssociatedPostIds() {
        // Your implementation to fetch associated post IDs from meta box
        // This could involve querying the DOM or making an AJAX request
        return [1, 2, 3]; // Dummy data, replace with actual IDs
    }

    function toggleSubmitButton() {
        $submitButton.prop('disabled', !(fieldIsValid && rutIsValid));
    }

    function validateRut(input) {
        console.log('rutValidation',input.val())
        // Remove any non-numeric characters
        var rut = input.val().replace(/[^\dK]/gi, '').replace(/^0+/, '').toUpperCase();

        // Check if the input is empty
        if (rut.length === 0) {
            return false;
        }

        // Split the rut into number and check digit
        var rutNumber = rut.slice(0, -1);
        var rutCheckDigit = rut.slice(-1).toUpperCase();

        // Check if rutNumber contains letters
        if (/[^\d]/.test(input.val().replace(/ +?/g, '').slice(0,-1))) {
            return false; // Contains letters
        }

        // Validate the last character to be a number or 'K'
        if (!/[0-9K]/.test(rutCheckDigit)) {
            return false;
        }

        // Validate the check digit
        var sum = 0;
        var multiplier = 2;
        for (var i = rutNumber.length - 1; i >= 0; i--) {
            sum += parseInt(rutNumber.charAt(i)) * multiplier;
            multiplier = multiplier === 7 ? 2 : multiplier + 1;
        }

        var expectedCheckDigit = 11 - (sum % 11);
        expectedCheckDigit = expectedCheckDigit === 11 ? '0' : expectedCheckDigit === 10 ? 'K' : expectedCheckDigit.toString();

        return rutCheckDigit === expectedCheckDigit;
    }

    function globalMessage() {
        $globalMessage.empty();
        if ($rutPersonalError !== undefined && $rutPersonalError !== '') {
            $globalMessage.append("<li style='margin-bottom:0'>" + $rutPersonalError + "</li>");
            $rutFields.addClass('wpforms-error')
        }else{
            $rutFields.removeClass('wpforms-error')
        }
        if ($rutEmpresaError !== undefined && $rutEmpresaError !== '') {
            $globalMessage.append("<li style='margin-bottom:0'>" + $rutEmpresaError + "</li>");
            $rutFields.addClass('wpforms-error')
        }else{
            $rutFields.removeClass('wpforms-error')
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {

    const phoneInput = document.querySelector('#career_contact');

    if (!phoneInput || !window.intlTelInput) {
        return;
    }

    const iti = window.intlTelInput(phoneInput, {

        initialCountry: 'gb',

        separateDialCode: true,

        countrySearch: false,

        loadUtils: () =>
            import(
                'https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.2/build/js/utils.js'
            )

    });

    const countrySuggestions = document.createElement('div');

    countrySuggestions.id = 'career_country_suggestions';

    phoneInput.parentNode.appendChild(countrySuggestions);


    const countries = [
        {
            name: 'India',
            iso2: 'in',
            dialCode: '91'
        },
        {
            name: 'Indonesia',
            iso2: 'id',
            dialCode: '62'
        },
        {
            name: 'United Kingdom',
            iso2: 'gb',
            dialCode: '44'
        },
        {
            name: 'United States',
            iso2: 'us',
            dialCode: '1'
        },
        {
            name: 'United Arab Emirates',
            iso2: 'ae',
            dialCode: '971'
        },
        {
            name: 'Australia',
            iso2: 'au',
            dialCode: '61'
        },
        {
            name: 'Canada',
            iso2: 'ca',
            dialCode: '1'
        },
        {
            name: 'Singapore',
            iso2: 'sg',
            dialCode: '65'
        },
        {
            name: 'Malaysia',
            iso2: 'my',
            dialCode: '60'
        }
    ];


    phoneInput.addEventListener('input', function () {

        const value = this.value
            .trim()
            .toLowerCase();

        countrySuggestions.innerHTML = '';

        if (!value) {

            countrySuggestions.style.display = 'none';

            return;
        }


        /*
         * If letters are typed,
         * search country names.
         */
        if (/[a-z]/i.test(value)) {

            const matches = countries.filter(function (country) {

                return country.name
                    .toLowerCase()
                    .includes(value);

            });

            showCountrySuggestions(matches);

            return;
        }


        /*
         * If numbers are typed,
         * search country dial codes.
         */
        const matches = countries.filter(function (country) {

            return country.dialCode.includes(value);

        });

        showCountrySuggestions(matches);


        /*
         * Keep phone number numeric only.
         */
        this.value = this.value
            .replace(/\D/g, '')
            .slice(0, 10);

    });


    function showCountrySuggestions(matches) {

        countrySuggestions.innerHTML = '';

        if (!matches.length) {

            countrySuggestions.style.display = 'none';

            return;
        }


        matches.forEach(function (country) {

            const item = document.createElement('div');

            item.className = 'career_country_option';

            item.innerHTML =
                '<span>' +
                country.name +
                '</span>' +
                '<span>+' +
                country.dialCode +
                '</span>';


            item.addEventListener('click', function () {

                iti.setCountry(country.iso2);

                phoneInput.value = '';

                countrySuggestions.innerHTML = '';

                countrySuggestions.style.display = 'none';

                phoneInput.focus();

            });


            countrySuggestions.appendChild(item);

        });


        countrySuggestions.style.display = 'block';

    }


    document.addEventListener('click', function (event) {

        if (
            !phoneInput.contains(event.target) &&
            !countrySuggestions.contains(event.target)
        ) {

            countrySuggestions.innerHTML = '';

            countrySuggestions.style.display = 'none';

        }

    });

});

document.addEventListener('DOMContentLoaded', function () {

    const uploadBox = document.getElementById('career_upload_box');
    const resumeInput = document.getElementById('career_resume');
    const fileCount = document.getElementById('career_file_count');
    const selectedFile = document.getElementById('career_selected_file');

    if (!uploadBox || !resumeInput) {
        return;
    }


    /*
     * Browse Files
     */

    resumeInput.addEventListener('change', function () {

        handleResumeFile(this.files);

    });


    /*
     * Drag Over
     */

    uploadBox.addEventListener('dragover', function (event) {

        event.preventDefault();

        uploadBox.classList.add('career_dragover');

    });


    /*
     * Drag Leave
     */

    uploadBox.addEventListener('dragleave', function () {

        uploadBox.classList.remove('career_dragover');

    });


    /*
     * Drop
     */

    uploadBox.addEventListener('drop', function (event) {

        event.preventDefault();

        uploadBox.classList.remove('career_dragover');

        const files = event.dataTransfer.files;

        handleResumeFile(files);

    });


    /*
     * Handle File
     */

    function handleResumeFile(files) {

        if (!files || files.length === 0) {
            return;
        }


        /*
         * Only one file
         */

        if (files.length > 1) {

            alert('Please upload only one resume.');

            resumeInput.value = '';

            fileCount.textContent = '0 of 1';

            selectedFile.innerHTML = '';

            return;
        }


        const file = files[0];


        /*
         * Allowed extensions
         */

        const allowedExtensions = [
            'pdf',
            'doc',
            'docx'
        ];


        const fileName = file.name;

        const extension = fileName
            .split('.')
            .pop()
            .toLowerCase();


        /*
         * Validate extension
         */

        if (!allowedExtensions.includes(extension)) {

            alert(
                'Only PDF, DOC and DOCX files are allowed.'
            );

            resumeInput.value = '';

            fileCount.textContent = '0 of 1';

            selectedFile.innerHTML = '';

            return;
        }


        /*
         * Show selected file
         */

        fileCount.textContent = '1 of 1';


        selectedFile.innerHTML =
            '<strong>Selected:</strong> ' +
            escapeHtml(fileName);


        /*
         * Keep dropped file in input
         */

        if (
            files instanceof FileList
        ) {

            try {

                const dataTransfer =
                    new DataTransfer();

                dataTransfer.items.add(file);

                resumeInput.files =
                    dataTransfer.files;

            } catch (error) {

                console.log(
                    'Unable to assign dropped file.',
                    error
                );

            }

        }

    }


    /*
     * Escape HTML
     */

    function escapeHtml(value) {

        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }

});

document.addEventListener('DOMContentLoaded', function () {

    const successMsg = document.getElementById('career_success_message');

    if (!successMsg) {
        return;
    }

    successMsg.style.display = 'block';

    requestAnimationFrame(function () {
        successMsg.classList.add('show');
    });

    setTimeout(function () {
        successMsg.classList.remove('show');
        successMsg.classList.add('hide');

        setTimeout(function () {
            successMsg.style.display = 'none';
        }, 400);

    }, 4000);

    if (window.history.replaceState) {
        const url = new URL(window.location.href);
        url.searchParams.delete('career_submitted');
        window.history.replaceState({}, document.title, url.toString());
    }
});

document.addEventListener('DOMContentLoaded', function () {

const careerForm = document.getElementById('career_form');

if (!careerForm) {
    return;
}

careerForm.addEventListener('submit', function (event) {

    const requiredFields = Array.from(
        careerForm.querySelectorAll('[required]')
    );

    /*
     * ---------------------------------------------------------
     * CHECK REQUIRED FIELDS
     * ---------------------------------------------------------
     */

    function isFieldEmpty(field) {

        /*
         * File
         */
        if (field.type === 'file') {

            return (
                !field.files ||
                field.files.length === 0
            );
        }

        /*
         * Checkbox
         */
        if (field.type === 'checkbox') {

            return !field.checked;
        }

        /*
         * Radio
         */
        if (field.type === 'radio') {

            const radios =
                careerForm.querySelectorAll(
                    'input[name="' +
                    CSS.escape(field.name) +
                    '"]'
                );

            return !Array.from(radios).some(
                function (radio) {
                    return radio.checked;
                }
            );
        }

        /*
         * Text / Select / Textarea / etc.
         */
        return !String(field.value || '').trim();
    }


    /*
     * ---------------------------------------------------------
     * FIND EMPTY REQUIRED FIELDS
     * ---------------------------------------------------------
     */

    const emptyRequiredFields =
        requiredFields.filter(function (field) {

            return isFieldEmpty(field);

        });


    /*
     * ---------------------------------------------------------
     * CASE 1:
     * ALL REQUIRED FIELDS ARE EMPTY
     * ---------------------------------------------------------
     */

    if (
        requiredFields.length > 0 &&
        emptyRequiredFields.length === requiredFields.length
    ) {

        event.preventDefault();

        /*
         * Remove previous individual errors.
         */
        careerForm
            .querySelectorAll(
                '.career_field_required_error'
            )
            .forEach(function (error) {

                error.remove();

            });

        careerForm
            .querySelectorAll(
                '.career_required_error'
            )
            .forEach(function (field) {

                field.classList.remove(
                    'career_required_error'
                );

            });


        /*
         * Existing general warning.
         */
        alert(
            'All fields are required.'
        );

        return;
    }


    /*
     * ---------------------------------------------------------
     * CASE 2:
     * ONLY PARTICULAR REQUIRED FIELD(S) ARE EMPTY
     * ---------------------------------------------------------
     */

    if (emptyRequiredFields.length > 0) {

        event.preventDefault();


        /*
         * Remove old individual errors.
         */
        careerForm
            .querySelectorAll(
                '.career_field_required_error'
            )
            .forEach(function (error) {

                error.remove();

            });

        careerForm
            .querySelectorAll(
                '.career_required_error'
            )
            .forEach(function (field) {

                field.classList.remove(
                    'career_required_error'
                );

            });


        /*
         * Show individual error for every
         * missing required field.
         */
        emptyRequiredFields.forEach(
            function (field) {

                const group =
                    field.closest(
                        '.career_form_group'
                    );

                if (!group) {
                    return;
                }


                /*
                 * Get field label.
                 */
                const label =
                    group.querySelector(
                        ':scope > label'
                    );


                let fieldName =
                    'This field';


                if (label) {

                    fieldName =
                        label.textContent
                            .replace(/\*/g, '')
                            .replace(/:/g, '')
                            .trim();
                }


                /*
                 * Remove existing message
                 * from this field.
                 */
                const oldMessage =
                    group.querySelector(
                        '.career_field_required_error'
                    );

                if (oldMessage) {
                    oldMessage.remove();
                }


                /*
                 * Create field-specific message.
                 */
                const error =
                    document.createElement('div');

                error.className =
                    'career_field_required_error';

                error.textContent =
                    fieldName +
                    ' is required.';


                /*
                 * Insert immediately BEFORE
                 * the actual input/select.
                 */
                field.parentNode.insertBefore(
                    error,
                    field
                );


                /*
                 * Mark invalid field.
                 */
                field.classList.add(
                    'career_required_error'
                );

            }
        );


        /*
         * Focus first missing field.
         */
        const firstInvalid =
            emptyRequiredFields[0];

        if (firstInvalid) {

            firstInvalid.focus();

            firstInvalid.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }


        return;
    }


    /*
     * ---------------------------------------------------------
     * EMAIL VALIDATION
     * ---------------------------------------------------------
     */

    const emailInput =
        document.getElementById(
            'career_email'
        );

    if (emailInput) {

        const email =
            emailInput.value.trim();

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (
            email &&
            !emailPattern.test(email)
        ) {

            event.preventDefault();

            emailInput.classList.add(
                'career_required_error'
            );

            const group =
                emailInput.closest(
                    '.career_form_group'
                );

            if (group) {

                const oldMessage =
                    group.querySelector(
                        '.career_field_required_error'
                    );

                if (oldMessage) {
                    oldMessage.remove();
                }

                const error =
                    document.createElement('div');

                error.className =
                    'career_field_required_error';

                error.textContent =
                    'Please enter a valid email address.';

                emailInput.parentNode.insertBefore(
                    error,
                    emailInput
                );
            }

            emailInput.focus();

            return;
        }
    }

});

});
/**
 * Career Form — admin settings page scripts.
 *
 * Handles:
 * - Bulk select/delete applications
 * - Add field modal
 * - Edit field modal
 * - Drag & drop field reordering
 *
 * Relies on:
 * careerFormAdmin.ajaxUrl
 * careerFormAdmin.bulkDeleteNonce
 * careerFormAdmin.fieldOrderNonce   <-- NEW: add this to your wp_localize_script() call
 */

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       BULK SELECT / DELETE APPLICATIONS
       ========================================================= */

    const selectAllButton = document.getElementById('career_select_all');
    const deleteAllButton = document.getElementById('career_delete_all');

    const checkboxes = document.querySelectorAll(
        '.career_application_checkbox'
    );

    if (
        selectAllButton &&
        deleteAllButton &&
        checkboxes.length
    ) {

        selectAllButton.addEventListener('click', function () {

            checkboxes.forEach(function (checkbox) {

                checkbox.checked = true;

            });

            updateDeleteButton();

        });


        checkboxes.forEach(function (checkbox) {

            checkbox.addEventListener('change', function () {

                updateDeleteButton();

            });

        });


        function updateDeleteButton() {

            const selected = document.querySelectorAll(
                '.career_application_checkbox:checked'
            );

            if (selected.length > 0) {

                deleteAllButton.style.display = 'inline-block';

            } else {

                deleteAllButton.style.display = 'none';

            }

        }


        deleteAllButton.addEventListener('click', function () {

            const selected = document.querySelectorAll(
                '.career_application_checkbox:checked'
            );

            if (!selected.length) {

                alert(
                    'Please select at least one application.'
                );

                return;

            }


            const confirmed = confirm(
                'Are you sure you want to delete the selected applications?'
            );

            if (!confirmed) {

                return;

            }


            if (
                typeof careerFormAdmin === 'undefined' ||
                !careerFormAdmin.ajaxUrl ||
                !careerFormAdmin.bulkDeleteNonce
            ) {

                alert(
                    'Career Form admin configuration is missing.'
                );

                console.error(
                    'careerFormAdmin object is not available.'
                );

                return;

            }


            const form = document.createElement('form');

            form.method = 'POST';
            form.action = careerFormAdmin.ajaxUrl;


            const actionInput = document.createElement('input');

            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value =
                'delete_all_career_applications';

            form.appendChild(actionInput);


            const nonceInput = document.createElement('input');

            nonceInput.type = 'hidden';
            nonceInput.name =
                'career_bulk_delete_nonce';

            nonceInput.value =
                careerFormAdmin.bulkDeleteNonce;

            form.appendChild(nonceInput);


            selected.forEach(function (checkbox) {

                const indexInput =
                    document.createElement('input');

                indexInput.type = 'hidden';

                indexInput.name =
                    'application_indices[]';

                indexInput.value =
                    checkbox.value;

                form.appendChild(indexInput);

            });


            document.body.appendChild(form);

            form.submit();

        });

    }


    /* =========================================================
       FIELD MANAGER MODAL
       ========================================================= */

    const overlay =
        document.getElementById(
            'career_field_modal_overlay'
        );

    const modalTitle =
        document.getElementById(
            'career_field_modal_title'
        );

    const addButton =
        document.getElementById(
            'career_add_field_btn'
        );

    const closeButton =
        document.getElementById(
            'career_field_modal_close'
        );

    const cancelButton =
        document.getElementById(
            'career_field_modal_cancel'
        );

    const fieldIdInput =
        document.getElementById(
            'career_field_id'
        );

    const fieldLabelInput =
        document.getElementById(
            'career_field_label'
        );

    const fieldTypeSelect =
        document.getElementById(
            'career_field_type'
        );

    const fieldOptionsGroup =
        document.getElementById(
            'career_field_options_group'
        );

    const fieldOptionsInput =
        document.getElementById(
            'career_field_options'
        );

    const presetOptionsGroup =
        document.getElementById(
            'career_preset_options_group'
        );

    const presetOptionsBody =
        document.getElementById(
            'career_preset_options_body'
        );

    const fieldRequiredBox =
        document.getElementById(
            'career_field_required'
        );

    const fieldEnabledBox =
        document.getElementById(
            'career_field_enabled'
        );


    if (!overlay) {

        /*
         * Field modal not on this page — but field REORDER
         * table might still exist, so don't return yet.
         * We only skip the modal-specific listeners below.
         */

    } else {

        /* =====================================================
           OPTIONS VISIBILITY
           ===================================================== */

        function toggleOptionsVisibility() {

            if (!fieldTypeSelect || !fieldOptionsGroup) {

                return;

            }


            const needsOptions =
                fieldTypeSelect.value === 'select' ||
                fieldTypeSelect.value === 'dropdown' ||
                fieldTypeSelect.value === 'radio' ||
                fieldTypeSelect.value === 'checkbox';


            fieldOptionsGroup.style.display =
                needsOptions ? 'block' : 'none';

        }


        if (fieldTypeSelect) {

            fieldTypeSelect.addEventListener(
                'change',
                toggleOptionsVisibility
            );

            fieldTypeSelect.addEventListener(
                'change',
                updatePresetOptionsUI
            );

        }

        if (fieldLabelInput) {

            fieldLabelInput.addEventListener(
                'input',
                updatePresetOptionsUI
            );

        }


        /* =====================================================
           PREDEFINED OPTIONS PICKER
           -----------------------------------------------------
           When the field type is Dropdown/Radio and the typed
           label matches a known preset (Designation,
           Qualification, Country, State, City, ...), show a
           grouped checkbox list under the Options textarea so
           the admin can tick values instead of typing them.
           Ticking/unticking keeps the textarea (and therefore
           the normal save logic) in sync.
           ===================================================== */

        function escapeHtmlText(value) {

            const holder = document.createElement('div');

            holder.textContent = value;

            return holder.innerHTML;

        }

        function matchPresetCatalogKey(label) {

            if (
                typeof careerFormAdmin === 'undefined' ||
                !careerFormAdmin.presetCatalogs
            ) {

                return '';

            }

            const normalized = (label || '').toLowerCase().trim();

            if (!normalized) {

                return '';

            }

            const keys = Object.keys(careerFormAdmin.presetCatalogs);

            for (let i = 0; i < keys.length; i++) {

                if (normalized.indexOf(keys[i]) !== -1) {

                    return keys[i];

                }

            }

            return '';

        }

        function getCatalogFlatValues(catalogKey) {

            const catalog =
                careerFormAdmin.presetCatalogs &&
                careerFormAdmin.presetCatalogs[catalogKey];

            if (!catalog || !catalog.groups) {

                return [];

            }

            let values = [];

            Object.keys(catalog.groups).forEach(function (group) {

                values = values.concat(catalog.groups[group]);

            });

            return values;

        }

        function getCurrentOptionValues() {

            if (!fieldOptionsInput) {

                return [];

            }

            return fieldOptionsInput.value
                .split(',')
                .map(function (value) {

                    return value.trim();

                })
                .filter(function (value) {

                    return value !== '';

                });

        }

        function syncOptionsFromPreset(catalogKey) {

            if (!fieldOptionsInput || !presetOptionsBody) {

                return;

            }

            const flatCatalogValues = getCatalogFlatValues(catalogKey);

            /* Keep any values already typed that are NOT part of
               this catalog, so manual extra entries survive. */
            const customValues = getCurrentOptionValues().filter(
                function (value) {

                    return flatCatalogValues.indexOf(value) === -1;

                }
            );

            const checkedValues = Array.prototype.map.call(
                presetOptionsBody.querySelectorAll(
                    '.career_preset_checkbox:checked'
                ),
                function (checkbox) {

                    return checkbox.value;

                }
            );

            fieldOptionsInput.value =
                customValues.concat(checkedValues).join(', ');

        }

        function renderPresetOptions(catalogKey) {

            const catalog =
                careerFormAdmin.presetCatalogs &&
                careerFormAdmin.presetCatalogs[catalogKey];

            if (!presetOptionsBody || !catalog || !catalog.groups) {

                return;

            }

            const currentValues = getCurrentOptionValues();

            let html = '';

            Object.keys(catalog.groups).forEach(function (group) {

                html += '<div class="career_preset_group">';
                html += '<h4 class="career_preset_group_title">' +
                    escapeHtmlText(group) + '</h4>';
                html += '<div class="career_preset_group_options">';

                const sortedOptions =
                    Array.isArray(catalog.groups[group])
                        ? catalog.groups[group].slice().sort(
                            function (a, b) {

                                return a.localeCompare(
                                    b,
                                    undefined,
                                    {
                                        numeric: true,
                                        sensitivity: 'base'
                                    }
                                );

                            }
                        )
                        : [];

                sortedOptions.forEach(function (option) {

                    const checked =
                        currentValues.indexOf(option) !== -1
                            ? 'checked'
                            : '';

                    html +=
                        '<label class="career_preset_checkbox_option">' +
                        '<input type="checkbox" class="career_preset_checkbox" value="' +
                        escapeHtmlText(option) + '" ' + checked + '> ' +
                        escapeHtmlText(option) +
                        '</label>';

                });

                html += '</div></div>';

            });

            presetOptionsBody.innerHTML = html;

            presetOptionsBody
                .querySelectorAll('.career_preset_checkbox')
                .forEach(function (checkbox) {

                    checkbox.addEventListener('change', function () {

                        syncOptionsFromPreset(catalogKey);

                    });

                });

        }

        function updatePresetOptionsUI() {

            if (
                !fieldLabelInput ||
                !fieldTypeSelect ||
                !presetOptionsGroup
            ) {

                return;

            }

            const type = fieldTypeSelect.value;

            if (type !== 'dropdown' && type !== 'radio') {

                presetOptionsGroup.style.display = 'none';

                return;

            }

            const catalogKey = matchPresetCatalogKey(
                fieldLabelInput.value
            );

            if (!catalogKey) {

                presetOptionsGroup.style.display = 'none';

                return;

            }

            presetOptionsGroup.style.display = 'block';

            renderPresetOptions(catalogKey);

        }


        /* =====================================================
           OPEN / CLOSE MODAL
           ===================================================== */

        function openModal() {

            overlay.classList.add(
                'career_field_modal_open'
            );

            document.body.classList.add(
                'career_modal_open'
            );

        }


        function closeModal() {

            overlay.classList.remove(
                'career_field_modal_open'
            );

            document.body.classList.remove(
                'career_modal_open'
            );

        }


        /* =====================================================
           RESET FORM
           ===================================================== */

        function resetForm() {

            if (fieldIdInput) {

                fieldIdInput.value = '';

            }

            if (fieldLabelInput) {

                fieldLabelInput.value = '';

            }

            if (fieldTypeSelect) {

                fieldTypeSelect.value = 'text';

            }

            if (fieldOptionsInput) {

                fieldOptionsInput.value = '';

            }

            if (fieldRequiredBox) {

                fieldRequiredBox.checked = false;

            }

            if (fieldEnabledBox) {

                fieldEnabledBox.checked = true;

            }

            if (modalTitle) {

                modalTitle.textContent =
                    'Add New Field';

            }

            toggleOptionsVisibility();

            updatePresetOptionsUI();

        }


        /* =====================================================
           ADD NEW FIELD
           ===================================================== */

        if (addButton) {

            addButton.addEventListener(
                'click',
                function () {

                    /*
                     * Server also enforces this limit.
                     * This prevents opening the modal from JS
                     * when the button is somehow triggered manually.
                     */
                    if (addButton.disabled) {

                        return;
                    }

                    const customRows =
                        document.querySelectorAll(
                            '#career_fields_table_body tr[data-field-id^="field_"]'
                        );

                    if (customRows.length >= 6) {

                        alert(
                            'You can add a maximum of 6 custom fields.'
                        );

                        addButton.disabled = true;

                        return;
                    }

                    resetForm();

                    openModal();

                }
            );

        }


        /* =====================================================
           CLOSE BUTTON
           ===================================================== */

        if (closeButton) {

            closeButton.addEventListener(
                'click',
                closeModal
            );

        }


        /* =====================================================
           CANCEL BUTTON
           ===================================================== */

        if (cancelButton) {

            cancelButton.addEventListener(
                'click',
                closeModal
            );

        }


        /* =====================================================
           CLICK OUTSIDE MODAL
           ===================================================== */

        overlay.addEventListener(
            'click',
            function (event) {

                if (event.target === overlay) {

                    closeModal();

                }

            }
        );


        /* =====================================================
           EDIT FIELD
           ===================================================== */

        document
            .querySelectorAll('.career_edit_field_btn')
            .forEach(function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        if (fieldIdInput) {

                            fieldIdInput.value =
                                button.dataset.id || '';

                        }


                        if (fieldLabelInput) {

                            fieldLabelInput.value =
                                button.dataset.label || '';

                        }


                        if (fieldTypeSelect) {

                            const savedType =
                                button.dataset.type || 'text';

                            fieldTypeSelect.value = savedType;

                        }


                        if (fieldOptionsInput) {

                            fieldOptionsInput.value =
                                button.dataset.options || '';

                        }

                        if (fieldRequiredBox) {

                            fieldRequiredBox.checked =
                                button.dataset.required === '1';

                        }


                        if (fieldEnabledBox) {

                            fieldEnabledBox.checked =
                                button.dataset.enabled === '1';

                        }


                        if (modalTitle) {

                            modalTitle.textContent =
                                'Edit Field';

                        }


                        toggleOptionsVisibility();

                        updatePresetOptionsUI();

                        openModal();

                    }
                );

            });


        /*
         * Initial state
         */

        toggleOptionsVisibility();

        updatePresetOptionsUI();

    }

    /* =========================================================
       FIELD REORDER (DRAG & DROP)
       =========================================================
       Requires the field table's <tbody> to have:
           id="career_fields_table_body"
       and each <tr> in it to have:
           draggable="true"
           data-field-id="<the field id>"
       ========================================================= */

    const fieldsTableBody = document.getElementById(
        'career_fields_table_body'
    );

    if (fieldsTableBody) {

        let draggedRow = null;


        fieldsTableBody.addEventListener('dragstart', function (event) {

            const row = event.target.closest('tr');

            if (!row) {

                return;

            }

            draggedRow = row;

            event.dataTransfer.effectAllowed = 'move';

            /* Some browsers need data set to allow dragging */
            event.dataTransfer.setData('text/plain', row.dataset.fieldId || '');

            row.classList.add('career_row_dragging');

        });


        fieldsTableBody.addEventListener('dragend', function () {

            if (draggedRow) {

                draggedRow.classList.remove('career_row_dragging');

            }

            draggedRow = null;

            saveFieldOrder();

        });


        fieldsTableBody.addEventListener('dragover', function (event) {

            /* Required to allow a drop */
            event.preventDefault();

            const targetRow = event.target.closest('tr');

            if (
                !targetRow ||
                !draggedRow ||
                targetRow === draggedRow
            ) {

                return;

            }


            const rect = targetRow.getBoundingClientRect();

            const nextIsAfter =
                (event.clientY - rect.top) / rect.height > 0.5;

            if (nextIsAfter) {

                targetRow.after(draggedRow);

            } else {

                targetRow.before(draggedRow);

            }

        });


        /*
         * Fires when the drop actually happens.
         * Needed mainly to prevent the browser's default
         * (opening the row content as if it were a link/file).
         */

        fieldsTableBody.addEventListener('drop', function (event) {

            event.preventDefault();

        });


        function saveFieldOrder() {

            if (
                typeof careerFormAdmin === 'undefined' ||
                !careerFormAdmin.ajaxUrl ||
                !careerFormAdmin.fieldOrderNonce
            ) {

                console.error(
                    'careerFormAdmin.fieldOrderNonce is missing — cannot save order.'
                );

                return;

            }


            const rows = fieldsTableBody.querySelectorAll('tr');

            const orderedIds = [];

            rows.forEach(function (row) {

                if (row.dataset.fieldId) {

                    orderedIds.push(row.dataset.fieldId);

                }

            });


            const body = new URLSearchParams();

            body.append('action', 'save_career_field_order');
            body.append('career_field_order_nonce', careerFormAdmin.fieldOrderNonce);

            orderedIds.forEach(function (id) {

                body.append('field_order[]', id);

            });


            fetch(careerFormAdmin.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            })
                .then(function (response) {

                    return response.json();

                })
                .then(function (data) {

                    if (!data || !data.success) {

                        console.error(
                            'Failed to save field order.',
                            data
                        );

                    }

                })
                .catch(function (error) {

                    console.error(
                        'Error saving field order:',
                        error
                    );

                });

        }

    }

});
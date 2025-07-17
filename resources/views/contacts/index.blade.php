@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Contacts</h4>

    <!-- Alert messages -->
    <div id="alertMsg"></div>

        <!-- Add Custom Field Button -->
    <button class="btn btn-outline-secondary mb-3" data-bs-toggle="modal" data-bs-target="#customFieldModal">
        + Add Custom Field
    </button>

    <!-- Add Contact Button -->
    <button class="btn btn-primary mb-3" id="openAddContactModal" >
        + Add Contact
    </button>

    <!-- Contacts Table -->
    <table class="table table-bordered" id="contactsTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

@include('contacts.add_contact_modal')
@include('contacts.add_custom_field_modal')
@endsection

@section('scripts')
<script>
    let customFields = [];
$(document).ready(function () {
    loadContacts();


    // Load custom fields dynamically
    customFields = <?php echo json_encode($custom_fields); ?>;


    function loadContacts() {
        $.get("{{ route('contacts.fetch') }}", function (res) {
            let rows = '';
            res.contacts.forEach(contact => {
                rows += `
                <tr>
                    <td>${contact.name}</td>
                    <td>${contact.email}</td>
                    <td>${contact.phone ?? ''}</td>
                    <td>${contact.gender ?? ''}</td>
                    <td>
                        <button class="btn btn-sm btn-warning editBtn" onclick="editContact(${contact.id})">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${contact.id}">Delete</button>
                    </td>
                </tr>`;
            });
            $('#contactsTable tbody').html(rows);
        });
    }

    $('#openAddContactModal').on('click', function () {
        $('#contactForm')[0].reset(); // Clear form
        $('#contact_id').val('');      // Clear hidden ID
        $('#contactModalTitle').text('Add Contact');
        $('#saveContactBtn').text('Save Contact');
        $('#contactModal').modal('show');
        $('.custom-field-container').empty(); // Clear custom fields
        if (customFields.length > 0) {
            customFields.forEach(field => {
                $('.custom-field-container').append(generateCustomFieldHTML(field));
            });
        }
    });

    $('#contactForm').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        let contactId = $('#contact_id').val(); // Get the hidden contact ID

        let url = contactId ? `/contacts/${contactId}` : "{{ route('contacts.store') }}";

        let method = contactId ? 'POST' : 'POST'; // Always POST; Laravel will interpret PUT using _method

        if (contactId) {
            formData.append('_method', 'PUT'); // Laravel requires this to handle update
        }

        $.ajax({
            url: url,
            type: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                $('#contactModal').modal('hide');
                // Show the alert message
                $('#alertMsg').html(`<div class="alert alert-success">${res.message}</div>`);

                // Hide it after 1 second (1000ms)
                setTimeout(() => {
                    $('#alertMsg').html('');
                }, 1000);
                $('#contactForm')[0].reset();
                $('#contact_id').val(''); // Clear the ID to revert to add mode
                $('#modalErrorMsg').html('');
                loadContacts();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errList = '<ul>';
                    $.each(errors, function (key, messages) {
                        messages.forEach(msg => {
                            errList += `<li>${msg}</li>`;
                        });
                    });
                    errList += '</ul>';

                    $('#modalErrorMsg').html(`<div class="alert alert-danger">${errList}</div>`);
                    setTimeout(() => {
                        $('#modalErrorMsg').fadeOut(200, function () {
                            $(this).html('').show(); // reset for next time
                        });
                    }, 2000);
                } else {
                    $('#modalErrorMsg').html(`<div class="alert alert-danger">Something went wrong</div>`);
                    setTimeout(() => {
                        $('#modalErrorMsg').fadeOut(200, function () {
                            $(this).html('').show();
                        });
                    }, 2000);
                }
            }
        });
    });

    // Delete contact
    $(document).on('click', '.deleteBtn', function () {
        if (!confirm('Are you sure?')) return;
        let id = $(this).data('id');
        $.ajax({
            url: `/contacts/${id}`,
            type: 'DELETE',
            data: {_token: "{{ csrf_token() }}"},
            success: function (res) {
                $('#alertMsg').html(`<div class="alert alert-success">${res.message}</div>`);

                // Hide it after 1 second (1000ms)
                setTimeout(() => {
                    $('#alertMsg').html('');
                }, 1000);
                loadContacts();
            }
        });
    });
    



    $('#customFieldForm').on('submit', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: "{{ route('custom_fields.store') }}",
            type: "POST",
            data: formData,
            success: function (res) {
                $('#customFieldModal').modal('hide');
                $('#customFieldForm')[0].reset();
                // Show the alert message
                $('#alertMsg').html(`<div class="alert alert-success">${res.message}</div>`);

                // Hide it after 1 second (1000ms)
                setTimeout(() => {
                    $('#alertMsg').html('');
                }, 1000);
                // Optionally reload fields or contact form
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errList = '<ul>';
                    $.each(errors, function (key, messages) {
                        messages.forEach(msg => {
                            errList += `<li>${msg}</li>`;
                        });
                    });
                    errList += '</ul>';
                    
                    $('#customfieldmodalErrorMsg').html(`<div class="alert alert-danger">${errList}</div>`);

                    // Wait 1 second, then instantly remove the message
                    setTimeout(() => {
                        $('#customfieldmodalErrorMsg').html(''); // Clears the content instantly
                    }, 1000);
                } else {
                    
                    $('#customfieldmodalErrorMsg').html(`<div class="alert alert-danger">Something went wrong</div>`);

                    // Wait 1 second, then instantly remove the message
                    setTimeout(() => {
                        $('#customfieldmodalErrorMsg').html(''); // Clears the content instantly
                    }, 1000);
                }
            }
            });
        });

    });

    function editContact(id) {
        console.log('Editing contact with ID:', id);
        // Fetch contact data and populate the form for editing
        $.ajax({
            url: `/contacts/${id}/edit`,
            type: 'GET',
            success: function (res) {
                    populateEditForm(res, id);
                },
                error: function () {
                    $('#alertMsg').html('<div class="alert alert-danger">Failed to fetch contact data.</div>');
                }
            });
    }

    function populateEditForm(data, id) {
        $('#contactModal').modal('show');
        $('#contactForm')[0].reset();
        $('#contactModalTitle').text('Edit Contact');
        $('#saveContactBtn').text('Update Contact');  // safer for user input

        $('#contact_id').val(id);  // Set hidden input with contact ID
        console.log('Populating form with contact data:', data.contact.custom_field_values);
        $('input[name="name"]').val(data.contact.name);
        $('input[name="email"]').val(data.contact.email);
        $('input[name="phone"]').val(data.contact.phone);
        $(`input[name="gender"][value="${data.contact.gender}"]`).prop('checked', true); // fixes gender
        // Handle profile image
        if (data.contact.profile_image) {
            $('input[name="profile_image"]').after(`<img src="${data.contact.profile_image}" alt="Profile Image" class="img-thumbnail mt-2" style="max-width: 100px;">`);
        } 
        // Handle additional file
        if (data.contact.additional_file) {
            $('input[name="additional_file"]').after(`<a href="${data.contact.additional_file}" target="_blank">View Additional File</a>`);
        }
        // Handle custom fields
        $('.custom-field-container').empty(); // Clear existing custom fields

        let addedFieldIds = [];

        data.contact.custom_field_values.forEach(cf => {
            let field = cf.field;
                // Parse the stored JSON value
            let parsedValue = '';
            try {
                let parsed = JSON.parse(cf.value); // Assuming cf.value is a JSON string
                parsedValue = parsed.value ?? '';
            } catch (e) {
                parsedValue = ''; // fallback if parsing fails
            }

            // console.log('Adding custom field:', cf);
            $('.custom-field-container').append(generateCustomFieldHTML(field, parsedValue));
            addedFieldIds.push(field.id);
        });

        // Add remaining (empty) fields
        customFields.forEach(field => {
            if (!addedFieldIds.includes(field.id)) {
                $('.custom-field-container').append(generateCustomFieldHTML(field));
            }
        });
    }

    function generateCustomFieldHTML(field, value = '') {
        let inputType = 'text';
        if (field.type === 'date') inputType = 'date';
        if (field.type === 'number') inputType = 'number';

        return `
            <div class="mb-3">
                <label>${field.label}</label>
                <input type="hidden" name="custom_fields[${field.id}][label]" value="${field.label}">
                <input type="${inputType}" name="custom_fields[${field.id}][value]" class="form-control" value="${value ?? ''}">
            </div>
        `;
    }


</script>
@endsection

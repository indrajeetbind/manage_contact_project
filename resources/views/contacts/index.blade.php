@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Contacts</h4>

    <!-- Alert messages -->
    <div id="alertMsg"></div>

        <!-- Add Custom Field Button -->
    <button class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#customFieldModal">
        + Add Custom Field
    </button>

    <!-- Add Contact Button -->
    <button class="btn btn-primary mb-3" id="openAddContactModal" >
        + Add Contact
    </button>
    <!-- Filter Section -->
    <div class="filter-section">
        <input type="text" id="name" placeholder="Search by Name">
        <input type="text" id="email" placeholder="Search by Email">
        <input type="number" id="phone" placeholder="Search by Phone">
        <select id="gender">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <button id="search-btn" class="btn btn-primary mb-2">Search</button>
        <button id="reset-btn" class="btn btn-secondary mb-2">Reset</button>
    </div>

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
@include('contacts.merge_contact_modal')
@include('contacts.view_merged_contact_modal')
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
                rows+=createContactListTable(res.contacts);
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

        $('.custom-field-container').html('');
        loadCustomFields();
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

        $('#search-btn').on('click', function () {
            const name = $('#name').val();
            const email = $('#email').val();
            const gender = $('#gender').val();
            const phone = $('#phone').val();

                $.ajax({
                    url: "{{ route('contacts.filter') }}",
                    type: "post",
                    data: {
                        name: name,
                        email: email,
                        gender: gender,
                        phone: phone,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        let rows = createContactListTable(response.contacts);
                        $('#contactsTable tbody').html(rows);
                    },
                    error: function () {
                        alert("Something went wrong.");
                    }
                });
        });

        function createContactListTable(contacts) {
            let rows = '';
            contacts.forEach(contact => {
                rows += `
                <tr>
                    <td>${contact.name}</td>
                    <td>${contact.email}</td>
                    <td>${contact.phone ?? ''}</td>
                    <td>${contact.gender ?? ''}</td>
                    <td>
                        <button class="btn btn-sm btn-warning editBtn" onclick="editContact(${contact.id})">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${contact.id}">Delete</button>`;
                        // if (contact.status != 'merged') {   
                                rows += `<button class="btn btn-sm btn-secondary mergebtn" style="margin-left:7px;" data-contact-id="${contact.id}" data-phone="${contact.phone}"  data-contact-name="${contact.name}">Merge</button>`;
                        // }
                        if(contact.status != null){
                        rows += `<button class="btn btn-sm btn-info ml-2 viewBtn" style="margin-left:7px;" data-id="${contact.id}">View Merged Contact</button>`;
                        }
                    rows += `</td>
                </tr>`;
            });
            return rows;
        }

        $('#reset-btn').on('click', function () {
            $('#name').val(''); 
            $('#email').val('');
            $('#gender').val('');
            $('#phone').val('');
            loadContacts(); // Reload all contacts
        });

        $(document).on('click', '.viewBtn', function () {
            let id=$(this).data('id');
            $.ajax({
                url: `/merged_contacts/${id}`,
                type: 'GET',
                success: function (response) {
                    const mergedContacts = response.merged_contacts;
                    let html = '';

                    if (mergedContacts.length === 0) {
                        html = '<p>No merged contacts found for this contact.</p>';
                    } else {
                        html += '<table class="table table-bordered">';
                        html += '<thead><tr><th>#</th><th>Contact</th><th>Merged Into</th><th>Master Contact</th><th>Merged At</th></tr></thead><tbody>';

                        mergedContacts.forEach((item, index) => {
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${item.merged_contact?.name + (item.merged_contact?.phone ? ' (' + item.merged_contact?.phone + ')' : '')}</td>
                                <td>${item.destination_contact?.name + (item.destination_contact?.phone ? ' (' + item.destination_contact?.phone + ')' : '') || '-'}</td>
                                <td>${item.master_contact?.name + (item.master_contact?.phone ? ' (' + item.master_contact?.phone + ')' : '') || '-'}</td>
                                <td>${item.merged_at}</td>
                            </tr>`;
                        });

                        html += '</tbody></table>';
                    }

                    $('#mergedContactsList').html(html);
                    $('#viewMergedContactsModal').modal('show');
                },
                error: function () {
                    alert('Failed to fetch merged contact data.');
                }
            });
        });

        let selectedContactId = null;

        $(document).on('click', '.mergebtn', function () {
            selectedContactId = $(this).data('contact-id');
            const name = $(this).data('contact-name');
            const phone = $(this).data('phone');

            $('#to_contact_id').val('');
            $('#radio1').val('');
            $('#radio2').val('');

            $('#selectedContactName').text(name + ' (' + phone + ')');
            $('#to_contact_id').val(selectedContactId);
            $('#masterChoice').hide();

            $.get('/contacts/fetch', function (response) {
                let options = '<option value="">Select</option>';
                response.contacts.forEach(contact => {
                    if (contact.id != selectedContactId) {
                        options += `<option value="${contact.id}">${contact.name} (${contact.phone})</option>`;
                    }
                });
                $('#contact_select').html(options);
                $('#mergeMasterModal').modal('show');
            });
        });

        $('#contact_select').on('change', function () {
            const contact2Id = $(this).val();
            const contact2Name = $(this).find('option:selected').text();

            // Uncheck both radio buttons every time dropdown changes
            $('input[name="master_contact_id"]').prop('checked', false);

            if (!contact2Id) {
                $('#masterChoice').hide();
                return;
            }

            // $('#contact_id').val(contact2Id);

            // Set radio values and labels
           
            $('#radio1').val(selectedContactId);
            $('#radio1_label').text($('#selectedContactName').text());

            $('#radio2').val('');
            $('#radio2').val(contact2Id);
            $('#radio2_label').text(contact2Name);

            $('#masterChoice').show();
        });

        $('#mergeContactForm').on('submit', function (e) {
            e.preventDefault();

            if (!$('input[name="master_contact_id"]:checked').val()) {
                alert('Please select master contact');
                return;
            }

            $.ajax({
                url: "{{ route('contacts.merge') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function () {
                    alert('Contacts merged successfully');
                    $('#mergeMasterModal').modal('hide');
                    loadContacts();
                },
                error: function () {
                    alert('Error while merging contacts');
                }
            });
        });

    function loadCustomFields() {
            $.ajax({
                url: '/custom-fields', // Make sure this URL returns JSON data
                method: 'GET',
                success: function (response) {
                    let html = '';

                    response.custom_fields.forEach(function(field) {
                        html += `<div class="mb-3">
                                    <label>${field.label}</label>
                                    <input type="hidden" name="custom_fields[${field.id}][label]" value="${field.label}">`;

                        if (field.type === 'date') {
                            html += `<input type="date" name="custom_fields[${field.id}][value]" class="form-control">`;
                        } else if (field.type === 'number') {
                            html += `<input type="number" name="custom_fields[${field.id}][value]" class="form-control">`;
                        } else {
                            html += `<input type="text" name="custom_fields[${field.id}][value]" class="form-control">`;
                        }

                        html += `</div>`;
                    });

                    $('.custom-field-container').html(html);
                },
                error: function () {
                    $('.custom-field-container').html('<p class="text-danger">Failed to load custom fields.</p>');
                }
            });
        }

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

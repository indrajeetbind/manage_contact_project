<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="contactForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalTitle">Add Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div id="modalErrorMsg"></div>
                    <input type="hidden" name="contact_id" id="contact_id">
                    <div class="col-6">
                        <label>Name:</label><span class="text-danger">*</span>
                        <input type="text" name="name" class="form-control" placeholder="Name" required>
                    </div>
                    <div class="col-6">
                        <label>Email:</label><span class="text-danger">*</span>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-6">
                        <label>Phone:</label>
                        <input type="text" name="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-6">
                        <label>Gender:</label><br>
                        <label><input type="radio" name="gender" value="male"> Male</label>
                        <label><input type="radio" name="gender" value="female"> Female</label>
                    </div>
                    <div class="col-12">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" class="form-control">
                    </div>
                    <div class="col-12">
                        <label>Additional File</label>
                        <input type="file" name="additional_file" class="form-control">
                    </div>
                    <div class="col-12 custom-field-container">
                        <label>Custom Fields</label>
                        <!-- Custom fields will be dynamically added here -->
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="saveContactBtn" class="btn btn-primary">Save Contact</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="mergeMasterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="mergeMasterModalLabel">Merge Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body with Form -->
            <form id="mergeContactForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="to_contact_id" id="to_contact_id">

                    <!-- Selected Contact Display -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Selected Contact to Merge:</strong></label>
                        <div id="selectedContactName" class="form-control-plaintext"></div>
                    </div>

                    <!-- Dropdown to Select Second Contact -->
                    <div class="mb-3">
                        <label for="contact_select" class="form-label">Select Contact to Merge With:</label>
                        <select id="contact_select" class="form-select" name="contact_id" required>
                            <option value="">Select Contact</option>
                            <!-- JS will populate this -->
                        </select>
                    </div>

                    <!-- Master Contact Selection -->
                    <div id="masterChoice" class="mb-3" style="display: none;">
                        <label class="form-label"><strong>Select Master Contact:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="master_contact_id" id="radio1" value="">
                            <label class="form-check-label" for="radio1" id="radio1_label"></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="master_contact_id" id="radio2" value="">
                            <label class="form-check-label" for="radio2" id="radio2_label"></label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer with Submit Button -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>

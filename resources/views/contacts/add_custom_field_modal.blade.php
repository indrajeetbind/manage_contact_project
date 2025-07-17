<!-- Add Custom Field Modal -->
<div class="modal fade" id="customFieldModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <form id="customFieldForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Custom Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="customfieldmodalErrorMsg"></div>
                    <div class="mb-3">
                        <label>Field Name</label>
                        <input type="text" name="label" class="form-control" >
                    </div>
                    <div class="mb-3">
                        <label>Field Type</label>
                        <select name="type" class="form-control" required>
                            <option value="text">Text</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Field</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

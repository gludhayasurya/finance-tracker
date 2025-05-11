<x-layouts.main :title="'Reminders'" :contentHeader="'Manage Reminders'">


<!-- Add Reminder Button -->
<button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addReminderModal">
    Add Reminder
</button>

@if($reminders->isEmpty())
        <div class="alert alert-info text-center">
            No reminders found.
        </div>
    @else
<!-- Reminders Table -->
<table id="mydataTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Reminder Name</th>
            <th>Purpose</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

        @foreach($reminders as $reminder)
        <tr>
            <td>{{ $reminder->date }}</td>
            <td>{{ $reminder->reminder_name }}</td>
            <td>{{ $reminder->purpose }}</td>
            <td>
                <!-- Edit -->
                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editReminderModal{{ $reminder->id }}">
                    Edit
                </button>
                <!-- Delete -->
                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteReminderModal{{ $reminder->id }}">
                    Delete
                </button>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editReminderModal{{ $reminder->id }}">
            <div class="modal-dialog">
                <form action="{{ route('reminders.update', $reminder->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Edit Reminder</h5>
                            <button type="button" class="btn-close" data-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" value="{{ $reminder->date }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Reminder Name</label>
                                <input type="text" name="reminder_name" class="form-control" value="{{ $reminder->reminder_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label>Purpose</label>
                                <textarea name="purpose" class="form-control">{{ $reminder->purpose }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteReminderModal{{ $reminder->id }}">
            <div class="modal-dialog">
                <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Delete Reminder</h5>
                            <button type="button" class="btn-close" data-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete <strong>{{ $reminder->reminder_name }}</strong>?
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
        @endif
    </tbody>
</table>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('reminders.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add New Reminder</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="datetime-local" name="date" class="form-control"  required>

                    </div>
                    <div class="mb-3">
                        <label>Reminder Name</label>
                        <input type="text" name="reminder_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Purpose</label>
                        <textarea name="purpose" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Add</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

</x-layouts.main>


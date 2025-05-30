<x-layouts.main :title="'Categories'" :contentHeader="'Category Management'">

    {{-- Add Button --}}
    <div class="d-flex justify-content-start mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">Add Category</button>
    </div>

    {{-- Categories Table or Message --}}
    @if($categories->isEmpty())
        <div class="alert alert-info text-center">
            No categories found.
        </div>
    @else
        <table id="mydataTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Icon</th>
                    <th>Color</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>
                            <span class="badge {{ $category->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($category->type) }}
                            </span>
                        </td>
                        <td>
                            @if($category->icon)
                                <i class="{{ $category->icon }}" style="color: {{ $category->color ?? '#000' }}"></i>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($category->color)
                                <span class="badge" style="background-color: {{ $category->color }}">{{ $category->color }}</span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $category->description ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $category->id }}">Edit</button>
                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $category->id }}">Delete</button>
                        </td>
                    </tr>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('categories.update', $category->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Category</h5>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <select name="type" class="form-control" required>
                                                <option value="income" {{ $category->type === 'income' ? 'selected' : '' }}>Income</option>
                                                <option value="expense" {{ $category->type === 'expense' ? 'selected' : '' }}>Expense</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Icon</label>
                                            <input type="text" name="icon" class="form-control" value="{{ $category->icon }}" placeholder="fas fa-tag">
                                        </div>
                                        <div class="form-group">
                                            <label>Color</label>
                                            <input type="color" name="color" class="form-control" value="{{ $category->color ?? '#007bff' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Update</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Delete Modal --}}
                    <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Category</h5>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete category <strong>{{ $category->name }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <input type="text" name="icon" class="form-control" placeholder="fas fa-tag">
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <input type="color" name="color" class="form-control" value="#007bff">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-layouts.main>

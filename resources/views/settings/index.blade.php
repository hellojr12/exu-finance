@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="page-header">
    <h4><i class="bi bi-gear me-2 text-secondary"></i>Settings</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Settings</li>
    </ol></nav>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="settingsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-general"><i class="bi bi-sliders me-1"></i>General</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-event-cats"><i class="bi bi-tag me-1"></i>Event Categories</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-expense-cats"><i class="bi bi-tags me-1"></i>Expense Categories</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-users"><i class="bi bi-people me-1"></i>Users & Roles</a></li>
</ul>

<div class="tab-content">

    {{-- ── General ─────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="tab-general">
        <div class="row justify-content-center">
        <div class="col-lg-6">
        <div class="table-card">
            <h6 class="fw-bold mb-3">General Settings</h6>
            <form method="POST" action="{{ route('settings.general.update') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Application Name</label>
                        <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name']?->value ?? 'EXU Finance' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Currency Code</label>
                        <input type="text" name="currency" class="form-control" value="{{ $settings['currency']?->value ?? 'PHP' }}" maxlength="3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fiscal Year</label>
                        <select name="fiscal_year" class="form-select">
                            <option value="jan-dec" {{ ($settings['fiscal_year']?->value ?? 'jan-dec') === 'jan-dec' ? 'selected':'' }}>January – December</option>
                            <option value="jul-jun" {{ ($settings['fiscal_year']?->value ?? '') === 'jul-jun' ? 'selected':'' }}>July – June</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
            </form>
        </div>
        </div>
        </div>
    </div>

    {{-- ── Event Categories ─────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-event-cats">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="table-card">
                    <h6 class="fw-bold mb-3">Add Event Category</h6>
                    <form method="POST" action="{{ route('settings.event-categories.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="color" name="color" class="form-control form-control-color" value="#3B82F6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-3 w-100">
                            <i class="bi bi-plus me-1"></i>Add Category
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="table-card">
                    <h6 class="fw-bold mb-3">Event Categories ({{ $eventCategories->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Name</th><th>Color</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            @foreach($eventCategories as $cat)
                            <tr class="{{ $cat->deleted_at ? 'table-secondary' : '' }}">
                                <td>
                                    <div class="fw-500">{{ $cat->name }}</div>
                                    @if($cat->description)<div class="text-muted" style="font-size:.75rem;">{{ $cat->description }}</div>@endif
                                </td>
                                <td>
                                    <span class="badge" style="background:{{ $cat->color }};color:#fff;padding:.4rem .7rem;">{{ $cat->color }}</span>
                                </td>
                                <td>
                                    @if($cat->deleted_at)
                                    <span class="badge bg-secondary">Deleted</span>
                                    @elseif($cat->is_active)
                                    <span class="badge badge-active">Active</span>
                                    @else
                                    <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$cat->deleted_at)
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary py-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editEventCat{{ $cat->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('settings.event-categories.destroy', $cat) }}" id="del-ec-{{ $cat->id }}">@csrf @method('DELETE')</form>
                                        <button class="btn btn-sm btn-outline-danger py-0"
                                                data-confirm="Delete {{ $cat->name }}?"
                                                data-form="#del-ec-{{ $cat->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            {{-- Edit Modal --}}
                            @if(!$cat->deleted_at)
                            <div class="modal fade" id="editEventCat{{ $cat->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="border-radius:12px;">
                                        <div class="modal-header"><h5 class="modal-title fw-bold">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <form method="POST" action="{{ route('settings.event-categories.update', $cat) }}">@csrf @method('PUT')
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-12"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $cat->name }}" required></div>
                                                    <div class="col-md-8"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{ $cat->description }}"></div>
                                                    <div class="col-md-4"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-color" value="{{ $cat->color }}"></div>
                                                    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ec-active-{{ $cat->id }}" {{ $cat->is_active?'checked':'' }}><label class="form-check-label" for="ec-active-{{ $cat->id }}">Active</label></div></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Expense Categories ───────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-expense-cats">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="table-card">
                    <h6 class="fw-bold mb-3">Add Expense Category</h6>
                    <form method="POST" action="{{ route('settings.expense-categories.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                            <div class="col-md-8"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-color" value="#EF4444"></div>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm mt-3 w-100"><i class="bi bi-plus me-1"></i>Add Category</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="table-card">
                    <h6 class="fw-bold mb-3">Expense Categories ({{ $expenseCategories->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Name</th><th>Color</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            @foreach($expenseCategories as $cat)
                            <tr class="{{ $cat->deleted_at ? 'table-secondary' : '' }}">
                                <td><div class="fw-500">{{ $cat->name }}</div></td>
                                <td><span class="badge" style="background:{{ $cat->color }};color:#fff;">{{ $cat->color }}</span></td>
                                <td>
                                    @if($cat->deleted_at)<span class="badge bg-secondary">Deleted</span>
                                    @elseif($cat->is_active)<span class="badge badge-active">Active</span>
                                    @else<span class="badge bg-secondary">Inactive</span>@endif
                                </td>
                                <td>
                                    @if(!$cat->deleted_at)
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#editExpCat{{ $cat->id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('settings.expense-categories.destroy', $cat) }}" id="del-expcat-{{ $cat->id }}">@csrf @method('DELETE')</form>
                                        <button class="btn btn-sm btn-outline-danger py-0" data-confirm="Delete {{ $cat->name }}?" data-form="#del-expcat-{{ $cat->id }}"><i class="bi bi-trash"></i></button>
                                    </div>
                                    <div class="modal fade" id="editExpCat{{ $cat->id }}" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content" style="border-radius:12px;"><div class="modal-header"><h5 class="modal-title fw-bold">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <form method="POST" action="{{ route('settings.expense-categories.update', $cat) }}">@csrf @method('PUT')
                                            <div class="modal-body"><div class="row g-3">
                                                <div class="col-12"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $cat->name }}" required></div>
                                                <div class="col-md-8"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{ $cat->description }}"></div>
                                                <div class="col-md-4"><label class="form-label">Color</label><input type="color" name="color" class="form-control form-control-color" value="{{ $cat->color }}"></div>
                                                <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="expcat-active-{{ $cat->id }}" {{ $cat->is_active?'checked':'' }}><label class="form-check-label" for="expcat-active-{{ $cat->id }}">Active</label></div></div>
                                            </div></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Save</button></div>
                                        </form></div></div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Users & Roles ────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-users">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="table-card">
                    <h6 class="fw-bold mb-3">Add User</h6>
                    <form method="POST" action="{{ route('settings.users.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                            <div class="col-12"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                            <div class="col-12"><label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucwords(str_replace('_',' ',$role->name)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="8"></div>
                            <div class="col-12"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-3 w-100"><i class="bi bi-person-plus me-1"></i>Create User</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="table-card">
                    <h6 class="fw-bold mb-3">Users ({{ $users->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                            <tbody>
                            @foreach($users as $u)
                            <tr>
                                <td class="fw-500">{{ $u->name }}
                                    @if($u->id === auth()->id())
                                    <span class="badge bg-primary ms-1" style="font-size:.65rem;">You</span>
                                    @endif
                                </td>
                                <td>{{ $u->email }}</td>
                                <td><span class="badge bg-primary bg-opacity-15 text-primary" style="font-size:.72rem;">{{ ucwords(str_replace('_',' ',$u->getRoleNames()->first() ?? 'none')) }}</span></td>
                                <td>
                                    @if($u->id !== auth()->id())
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#editUser{{ $u->id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('settings.users.destroy', $u) }}" id="del-u-{{ $u->id }}">@csrf @method('DELETE')</form>
                                        <button class="btn btn-sm btn-outline-danger py-0" data-confirm="Delete user {{ $u->name }}?" data-form="#del-u-{{ $u->id }}"><i class="bi bi-trash"></i></button>
                                    </div>
                                    <div class="modal fade" id="editUser{{ $u->id }}" tabindex="-1">
                                        <div class="modal-dialog"><div class="modal-content" style="border-radius:12px;"><div class="modal-header"><h5 class="modal-title fw-bold">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <form method="POST" action="{{ route('settings.users.update', $u) }}">@csrf @method('PUT')
                                            <div class="modal-body"><div class="row g-3">
                                                <div class="col-12"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $u->name }}" required></div>
                                                <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $u->email }}" required></div>
                                                <div class="col-12"><label class="form-label">Role</label>
                                                    <select name="role" class="form-select" required>
                                                        @foreach($roles as $role)
                                                        <option value="{{ $role->name }}" {{ $u->hasRole($role->name)?'selected':'' }}>{{ ucwords(str_replace('_',' ',$role->name)) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12"><label class="form-label">New Password <span class="text-muted">(leave blank to keep)</span></label><input type="password" name="password" class="form-control" minlength="8"></div>
                                                <div class="col-12"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control"></div>
                                            </div></div>
                                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
                                        </form></div></div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

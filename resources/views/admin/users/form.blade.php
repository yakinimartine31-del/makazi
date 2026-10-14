@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="card-body">
            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name ?? '') }}" required maxlength="255" autocomplete="name">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" class="form-control @error('username') is-invalid @enderror"
                    value="{{ old('username', $user->username ?? '') }}" required maxlength="255" autocomplete="username">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email ?? '') }}" required maxlength="255" autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="roles">Roles</label>
                <select id="roles" name="roles[]" class="form-control @error('roles') is-invalid @enderror" multiple required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected(in_array($role->id, old('roles', isset($user) ? $user->roles->modelKeys() : [])))>
                            {{ \Illuminate\Support\Str::headline($role->name) }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Use Ctrl (Windows/Linux) or Command (Mac) to select multiple roles.</small>
                @error('roles')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('roles.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password">{{ isset($user) ? 'New password (optional)' : 'Password' }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror"
                    {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-0">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control"
                    {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Save Changes' : 'Create User' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

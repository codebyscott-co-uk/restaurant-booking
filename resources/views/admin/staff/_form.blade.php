<div class="form-grid">
    <div class="field full">
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="field full">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="field">
        <label for="role">Role</label>
        <select id="role" name="role" required>
            @foreach (['owner' => 'Owner', 'manager' => 'Manager', 'host' => 'Host', 'staff' => 'Staff'] as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="job_title">Job title</label>
        <input id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}">
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
    </div>
    <label style="display: flex; gap: 10px; align-items: center; font-weight: 700; align-self: end;">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true)) style="width: 18px; min-height: 18px;">
        Active account
    </label>
    <div class="field">
        <label for="password">Password {{ $user->exists ? '(leave blank to keep current)' : '' }}</label>
        <input id="password" name="password" type="password" autocomplete="new-password" @required(! $user->exists)>
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $user->exists)>
    </div>
</div>

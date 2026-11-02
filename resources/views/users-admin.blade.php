@extends('layouts.app')

@section('title', 'Staff')
@section('page-title', 'Staff')
@section('page-subtitle', $totalUsers . ' ' . \Illuminate\Support\Str::plural('account', $totalUsers) . ' · ' . $activeUsers . ' active')

@php
    $roles = ['cashier' => 'Cashier', 'manager' => 'Manager', 'admin' => 'Admin'];
    $roleHelp = 'Cashiers take orders and payments. Managers also run services and inventory. Admins also see reports, staff and settings.';
@endphp

@section('page-actions')
    <button type="button" class="btn btn-primary" data-form-dialog="staffDialog" data-title="Add staff member" data-submit="Save staff member"
            data-action="{{ route('staff.store') }}" data-method="POST"><i class="fas fa-user-plus" aria-hidden="true"></i> Add staff</button>
@endsection

@section('content')
<form class="toolbar" method="GET" action="{{ route('staff.index') }}" role="search">
    <div class="search">
        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
        <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Name, username or role" aria-label="Search staff" autocomplete="off" id="staffSearch">
    </div>
    @if(request('search'))<a href="{{ route('staff.index') }}" class="btn btn-ghost">Clear</a>@endif
</form>

<div class="card card-flush" id="listCard" data-refresh>
    @if($users->isEmpty())
        <div class="empty">
            <span class="empty-icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
            <p class="empty-title">No staff match "{{ request('search') }}"</p>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">Clear search</a>
        </div>
    @else
    <div class="table-wrap">
        <table class="table table-stack">
            <thead>
                <tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Added</th><th class="actions"><span class="sr-only">Actions</span></th></tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                @php $isMe = $user->id === (int) session('staff.id'); @endphp
                <tr>
                    <td class="cell-primary">
                        <div class="cluster" style="flex-wrap:nowrap">
                            <span class="avatar" aria-hidden="true">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <div>
                                <div class="cell-title">{{ $user->name }} @if($isMe)<span class="badge badge-info">You</span>@endif</div>
                                @if($user->phone)<div class="cell-sub">{{ $user->phone }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td data-label="Username">{{ $user->username }}</td>
                    <td data-label="Role"><span class="badge {{ $user->role === 'admin' ? 'badge-ready' : ($user->role === 'manager' ? 'badge-info' : 'badge-neutral') }}">{{ $roles[$user->role] ?? ucfirst($user->role) }}</span></td>
                    <td data-label="Status"><span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td data-label="Added">{{ optional($user->created_at)->format('M j, Y') }}</td>
                    <td class="actions">
                        <div class="cell-actions">
                            <button type="button" class="icon-btn" aria-label="Edit {{ $user->name }}" title="Edit"
                                    data-form-dialog="staffDialog" data-title="Edit staff member" data-submit="Save changes"
                                    data-action="{{ route('staff.update', $user) }}" data-method="PUT"
                                    data-values="{{ json_encode($user->only('name', 'phone', 'username', 'role') + ['is_active' => $user->is_active ? 1 : 0]) }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                            @if($isMe)
                                <span class="icon-btn slot" aria-hidden="true"></span>
                            @else
                                <form method="POST" action="{{ route('staff.destroy', $user) }}" data-ajax
                                      data-confirm="Staff with orders on record can only be deactivated." data-confirm-title="Delete {{ $user->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn danger" aria-label="Delete {{ $user->name }}" title="Delete"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
    @endif
</div>

<x-form-dialog id="staffDialog" title="Add staff member" :action="route('staff.store')" submit="Save staff member" success="Staff saved.">
    <div class="form-row">
        <x-field name="name" label="Full name" required maxlength="255" autocomplete="off" />
        <x-field name="phone" label="Phone" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="09123456789" />
    </div>
    <div class="form-row">
        <x-field name="username" label="Username" required maxlength="50" autocomplete="off" autocapitalize="none" />
        <x-field name="password" label="Password" type="password" minlength="6" autocomplete="new-password" help="At least 6 characters. When editing, leave empty to keep it." />
    </div>
    <x-field name="role" label="Role" type="segmented" :options="$roles" data-default="cashier" :help="$roleHelp" />
    <x-field name="is_active" label="Account" type="segmented" :options="['1' => 'Active', '0' => 'Inactive']" data-default="1" help="Inactive accounts cannot sign in." />
</x-form-dialog>
@endsection

@push('scripts')
<script>
(function () {
    const search = document.getElementById('staffSearch');
    let timer;
    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => search.form.submit(), 500); });
})();
</script>
@endpush

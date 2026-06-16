@php
    $user = filament()->auth()->user();
    $profileUrl = filament()->getProfileUrl();
    $logoutUrl = filament()->getLogoutUrl();
@endphp

@if ($user)
    <div class="admin-user-menu-card">
        <x-filament-panels::avatar.user :user="$user" class="admin-user-menu-card-avatar" />

        <div class="admin-user-menu-card-body">
            <strong>{{ filament()->getUserName($user) }}</strong>
            <span>{{ $user->email }}</span>
        </div>

        <div class="admin-user-menu-actions">
            @if ($profileUrl)
                <a class="admin-user-menu-action admin-user-menu-action-profile" href="{{ $profileUrl }}">
                    My Account
                </a>
            @endif

            <form
                action="{{ $logoutUrl }}"
                method="post"
                onsubmit="return confirm('Yakin ingin keluar dari panel admin?');"
            >
                @csrf
                <button class="admin-user-menu-action admin-user-menu-action-logout" type="submit">
                    Keluar
                </button>
            </form>
        </div>
    </div>
@endif

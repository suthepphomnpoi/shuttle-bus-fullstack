<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/"> <img src="{{ asset('images/bus.png') }}" alt="Logo" width="100%"
                height="30" class="d-inline-block align-text-top object" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
            aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <i class='ti ti-menu-2'></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            @php
                $isEmployee = Auth::guard('employee')->check();
                $user = $isEmployee ? Auth::guard('employee')->user() : Auth::user();
                $logoutUrl = $isEmployee ? url('/auth/employees/logout') : url('/auth/users/logout');
                $displayName =
                    trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email ?? 'บัญชีของฉัน';
                $initial = strtoupper(mb_substr($user->first_name ?? ($user->email ?? 'U'), 0, 1));
            @endphp
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                @if (Auth::guard('employee')->check())
                <li class="nav-item mt-1">
                    <a class="nav-link" href="/backoffice">เข้าสู่ Back Office</a>
                </li>
                @endif

                <li class="nav-item dropdown dropdown-hover">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-initial">{{ $initial }}</span>
                        <span class="d-none d-sm-inline">{{ $displayName }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end user-menu">
                        <li class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-initial avatar-lg">{{ $initial }}</span>
                                <div class="lh-sm">
                                    <div class="fw-semibold">{{ $displayName }}</div>
                                    <div class="text-muted small">{{ $user->email ?? '' }}</div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="{{ $logoutUrl }}">ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title d-flex align-items-center" id="offcanvasNavbarLabel">
            <img src="{{ asset('images/bus.png') }}" width="32" height="32" alt="logo" class="me-2">

        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        @php
            $displayName =
                trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email ?? 'บัญชีของฉัน';
            $initial = strtoupper(mb_substr($user->first_name ?? ($user->email ?? 'U'), 0, 1));
        @endphp

        <div class="offcanvas-user px-2 py-2 mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar-initial avatar-lg">{{ $initial }}</span>
                <div class="user-text lh-sm flex-grow-1 min-w-0">
                    <div class="name fw-semibold text-truncate">{{ $displayName }}</div>
                    <div class="email text-muted small text-truncate">{{ $user->email ?? '' }}</div>
                </div>
            </div>
        </div>
        <hr class="dropdown-divider">

        <ul class="navbar-nav justify-content-end flex-grow-1">

            @if (auth()->guard('employee')->check())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('backoffice') ? 'active' : '' }}"
                        href="/backoffice">เข้าสู่
                        Back Office</a>
                </li>
            @endif

            <li>
                <hr class="dropdown-divider">
            </li>

            <li class="nav-item">
                <a class="nav-link text-danger" href="{{ $logoutUrl }}">ออกจากระบบ</a>
            </li>
        </ul>
    </div>
</div>

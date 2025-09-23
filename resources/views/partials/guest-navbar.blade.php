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
              <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                      <a class="nav-link" href="{{ url('/auth/users/login') }}">เข้าสู่ระบบ</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="{{ url('/auth/employees/login') }}">เข้าสู่ระบบพนักงาน</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link " href="{{ url('/auth/users/register') }}">สมัครสมาชิก</a>
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
          <ul class="navbar-nav justify-content-end flex-grow-1">
              <li class="nav-item">
                  <a class="nav-link {{ request()->is('auth/users/login') ? 'active' : '' }}" href="{{ url('/auth/users/login') }}">เข้าสู่ระบบ</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link {{ request()->is('auth/employees/login') ? 'active' : '' }}" href="{{ url('/auth/employees/login') }}">เข้าสู่ระบบพนักงาน</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link {{ request()->is('auth/users/register') ? 'active' : '' }}" href="{{ url('/auth/users/register') }}">สมัครสมาชิก</a>
              </li>
          </ul>
      </div>
  </div>

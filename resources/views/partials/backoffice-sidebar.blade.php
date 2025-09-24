 <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
     <div class="sidebar-brand">
         <a href="/backoffice" class="brand-link">
             <span class="brand-text fw-light">Backoffice</span>
         </a>
     </div>

     <div class="sidebar-wrapper">
         <nav class="mt-2">
             <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                 aria-label="Main navigation" data-accordion="false" id="navigation">



                 @if (function_exists('canMenu') ? canMenu('dashboard') : true)
                 <li class="nav-item">
                     <a href="/backoffice" class="nav-link {{ request()->is('backoffice') ? 'active' : '' }}">
                         <i class="nav-icon ti ti-dashboard"></i>
                         <p>แดชบอร์ด</p>
                     </a>
                 </li>
                 @endif

                 @if (function_exists('canMenu') ? canMenu('user_manage') : true)
                 <li class="nav-item">
                     <a href="/backoffice/users"
                         class="nav-link {{ request()->is('backoffice/users*') ? 'active' : '' }}">
                         <i class="nav-icon ti ti-users"></i>
                         <p>จัดการผู้ใช้</p>
                     </a>
                 </li>
                 @endif

                @if (function_exists('canMenu') ? canMenu('menu_manage') : true)
                <li class="nav-item">
                    <a href="/backoffice/menus" class="nav-link {{ request()->is('backoffice/menus*') ? 'active' : '' }}">
                        <i class="nav-icon ti ti-menu-2"></i>
                        <p>เมนู</p>
                    </a>
                </li>
                @endif

                @if (function_exists('canMenu') ? canMenu('department_position_manage') : true)
                <li class="nav-item">
                    <a href="/backoffice/org" class="nav-link {{ (request()->is('backoffice/org') || request()->is('backoffice/departments*') || request()->is('backoffice/positions*')) ? 'active' : '' }}">
                        <i class="nav-icon ti ti-hierarchy-3"></i>
                        <p>จัดการแผนก & ตำแหน่ง</p>
                    </a>
                </li>
                @endif

                @if (function_exists('canMenu') ? canMenu('employee_manage') : true)
                <li class="nav-item">
                    <a href="/backoffice/employees" class="nav-link {{ request()->is('backoffice/employees*') ? 'active' : '' }}">
                        <i class="nav-icon ti ti-user-cog"></i>
                        <p>พนักงาน</p>
                    </a>
                </li>
                @endif

                @if (function_exists('canMenu') ? canMenu('vehicle_vehicle_type_manage') : true)
                <li class="nav-item">
                    <a href="/backoffice/vehicles" class="nav-link {{ request()->is('backoffice/vehicles') || request()->is('backoffice/vehicle-types*') || request()->is('backoffice/vehicles*') ? 'active' : '' }}">
                        <i class="nav-icon ti ti-car"></i>
                        <p>รถ & ประเภทรถ</p>
                    </a>
                </li>
                @endif

                @if (function_exists('canMenu') ? canMenu('routes_places_manage') : true)
                <li class="nav-item">
                    <a href="/backoffice/routes-places" class="nav-link {{ request()->is('backoffice/routes-places') ? 'active' : '' }}">
                        <i class="nav-icon ti ti-road"></i>
                        <p>เส้นทาง & จุดรับ–ส่ง</p>
                    </a>
                </li>
                @endif

                 {{-- <li class="nav-item">
                     <a href="#" class="nav-link">
                         <i class="nav-icon bi bi-ui-checks-grid"></i>
                         <p>
                             Components
                             <i class="nav-arrow bi bi-chevron-right"></i>
                         </p>
                     </a>
                     <ul class="nav nav-treeview">
                         <li class="nav-item">
                             <a href="./docs/components/main-header.html" class="nav-link">
                                 <i class="nav-icon bi bi-circle"></i>
                                 <p>Main Header</p>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a href="./docs/components/main-sidebar.html" class="nav-link">
                                 <i class="nav-icon bi bi-circle"></i>
                                 <p>Main Sidebar</p>
                             </a>
                         </li>
                     </ul>
                 </li> --}}

             </ul>

         </nav>
     </div>

 </aside>

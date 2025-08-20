<!-- begin #sidebar -->
<div id="sidebar" class="sidebar">
    <!-- begin sidebar scrollbar -->
    <div data-scrollbar="true" data-height="100%">
        <!-- begin sidebar nav -->
        <div class="my-3"></div>
        @if (Route::currentRouteName() != 'report.generate')
            @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                <div class="text-center">
                    <img src="{{ route('file', ['url' => isset(Auth::user()->company->logo) ? Auth::user()->company->logo : null]) }}" alt="" style="max-width: 50px;">
                </div>
            @else
            <ul class="nav">
                <li class="{{ Route::currentRouteName() == 'home' ? 'active' : '' }}">
                    <a href="{{ url('/') }}"><i class="fa fa-laptop"></i> <span>Home</span></a>
                </li>
                <li class="{{ Route::currentRouteName() == 'company.index' ? 'active' : '' }}">
                    <a href="{{ route('company.index') }}"><i class="fa fa-home"></i> <span>Company Management</span></a>
                </li>
                <li class="{{ Route::currentRouteName() == 'project.index' ? 'active' : '' }}">
                    <a href="{{ route('project.index') }}"><i class="fa fa-building"></i> <span>Project Management</span></a>
                </li>
                <li class="{{ Route::currentRouteName() == 'user.index' ? 'active' : '' }}">
                    <a href="{{ route('user.index') }}"><i class="fa fa-users"></i> <span>User Management</span></a>
                </li>
                <li><a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
            </ul>
            @endif
        @endif

        <div class="container">
            @yield('sidebar')
        </div>
    </div>
    <!-- end sidebar scrollbar -->
</div>
<!-- end #sidebar -->
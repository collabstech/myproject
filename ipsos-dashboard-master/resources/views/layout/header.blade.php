<!-- begin #header -->
<div id="app-header" class="header navbar navbar-default navbar-fixed-top">
    <!-- begin container-fluid -->
    <div class="container-fluid">
      <!-- begin mobile sidebar expand / collapse button -->
      <div class="navbar-header">
        <a href="{{ url('/') }}" class="navbar-brand">
          <img src="{{ asset('images/logo.png') }}" alt="" class="header-logo">
          <span class="logo">IPSOS Online Dashboard</span>
        </a>
        <button type="button" class="navbar-toggle" data-click="top-menu-toggled">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <!-- end mobile sidebar expand / collapse button -->

      <div id="top-menu" class="top-menu">
        <!-- <ul class="nav">
          <li class="active"><a href="{{ url('/') }}">Home</a></li>
        </ul> -->
      </div>

      <!-- begin header navigation right -->
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown navbar-user">
          <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
            <img src="{{ route('file', ['url' => Auth::user()->avatar]) }}" alt="" />
            <span class="hidden-xs">{{ Auth::user()->name }}</span> <b class="caret"></b>
          </a>
          <ul class="dropdown-menu animated fadeInLeft">
            <li><a href="{{ route('logout') }}"><i class="fa fa-sign-out"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
      <!-- end header navigation right -->
    </div>
    <!-- end container-fluid -->
</div>
<!-- end #header -->
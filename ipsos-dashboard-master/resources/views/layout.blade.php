<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>IPSOS Online Dashboard</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" type="text/css">

        <!-- ================== BEGIN BASE CSS STYLE ================== -->
        <link href="{{ asset('plugins/jquery-ui/themes/base/minified/jquery-ui.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/bootstrap4/css/bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/animate.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/style-bs4.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/style.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/style-responsive.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/theme/default.css" rel="stylesheet') }}" id="theme" />
        <link href="{{ asset('plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/summernote/summernote.css') }}" rel="stylesheet" />
        <!-- ================== END BASE CSS STYLE ================== -->

        <!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
        <link href="{{ asset('plugins/DataTables/media/css/dataTables.bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/bootstrap-datepicker/css/bootstrap-datepicker.css') }}" rel="stylesheet"/>
        <link href="{{ asset('plugins/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/DataTables/extensions/Responsive/css/responsive.bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/DataTables/extensions/KeyTable/css/keyTable.bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('plugins/DataTables/extensions/Select/css/select.bootstrap.min.css') }}" rel="stylesheet" />
        
        <script src="{{ asset('plugins/pace/pace.min.js') }}"></script>

        <link href="{{ asset('plugins/bootstrap-sweetalert/sweetalert.css') }}" rel="stylesheet" />

        <link href="{{ asset('plugins/pivottable/pivot.min.css') }}" rel="stylesheet" />

        <link href="{{ asset('plugins/switchery/switchery.min.css') }}" rel="stylesheet" />
        <!-- ================== END PAGE LEVEL STYLE ================== -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet" />

        @yield('css')
    </head>
    <body>
        <!-- begin #page-loader -->
        <div id="page-loader" class="fade in"><span class="spinner"></span></div>
        <!-- end #page-loader -->

        @yield('body')

        <!-- ================== BEGIN BASE JS ================== -->
        <script src="{{ asset('plugins/jquery/jquery-1.9.1.min.js') }}"></script>
        <script src="{{ asset('plugins/jquery/jquery-migrate-1.1.0.min.js') }}"></script>
        <script src="{{ asset('plugins/jquery-ui/ui/minified/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('plugins/tether/js/tether.min.js') }}"></script>
        <script src="{{ asset('plugins/bootstrap4/js/bootstrap.min.js') }}"></script>
        <!--[if lt IE 9]>
        <script src="{{ asset('crossbrowserjs/html5shiv.js') }}"></script>
        <script src="{{ asset('crossbrowserjs/respond.min.js') }}"></script>
        <script src="{{ asset('crossbrowserjs/excanvas.min.js') }}"></script>
        <![endif]-->
        <script src="{{ asset('plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
        <script src="{{ asset('plugins/jquery-cookie/jquery.cookie.js') }}"></script>
        <!-- ================== END BASE JS ================== -->

        <!-- ================== BEGIN PAGE LEVEL JS ================== -->
        <script src="{{ asset('plugins/gritter/js/jquery.gritter.js') }}"></script>
        <script src="{{ asset('plugins/sparkline/jquery.sparkline.js') }}"></script>
        <script src="{{ asset('plugins/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
        <script src="{{ asset('plugins/parsley/dist/parsley.js') }}"></script>
        <script src="{{ asset('plugins/select2/dist/js/select2.min.js') }}"></script>
        <script src="{{ asset('plugins/summernote/summernote.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/media/js/jquery.dataTables.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/media/js/dataTables.bootstrap.min.js') }}"></script>

        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/buttons.flash.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/jszip.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/pdfmake.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/vfs_fonts.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Buttons/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/KeyTable/js/dataTables.keyTable.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/extensions/Select/js/dataTables.select.min.js') }}"></script>

        <script src="{{ asset('plugins/bootstrap-sweetalert/sweetalert.min.js') }}"></script>


        <script src="{{ asset('plugins/plotly/plotly-latest.min.js') }}"></script>
        <script src="{{ asset('plugins/pivottable/pivot.min.js') }}"></script>
        <script src="{{ asset('plugins/pivottable/plotly_renderers.js') }}"></script>

        <script type="text/javascript" src="//unpkg.com/blob.js@1.0.1/Blob.js"></script>
        <script type="text/javascript" src="//unpkg.com/file-saver@1.3.3/FileSaver.js"></script>

        <script type="text/javascript" src="http://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>

        <script src="{{ asset('plugins/switchery/switchery.min.js') }}"></script>

        <script src="{{ asset('js/apps.min.js') }}"></script>

        <script lang="javascript" src="{{ asset('plugins/pptxgenjs/libs/jszip.min.js') }}"></script>
        <script lang="javascript" src="{{ asset('plugins/pptxgenjs/dist/pptxgen.min.js') }}"></script>

        <!-- ================== END PAGE LEVEL JS ================== -->
        <script>
        $(document).ready(function() {
            App.init();
        });
        </script>
        <script src="{{ asset('js/app.js') }}"></script>
        <script>
        @if (Route::currentRouteName() != 'report.generate' && Auth::user())
            @if (Auth::user()->role != \App\User::ROLE_ADMIN)
                $('#page-container').addClass('page-sidebar-minified');
            @endif
        @endif
        </script>
        @yield('js')
    </body>
</html>

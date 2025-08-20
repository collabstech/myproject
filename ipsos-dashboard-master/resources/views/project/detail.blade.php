@extends('layout.master')
@section('page-title', 'Project Detail')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li>Project</li>
    <li class="active">Project Detail</li>
@endsection
@section('content')
    <ul class="nav nav-tabs" id="projectTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'project-detail' || request()->query('tab') == '' ? 'active' : '' }}" id="nav-project-detail" data-toggle="tab" href="#project-detail" role="tab">Project Detail</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'question-list' ? 'active' : '' }}" id="nav-question-list" data-toggle="tab" href="#question-list" role="tab">Question List</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'attachment-list' ? 'active' : '' }}" id="nav-attachment-list" data-toggle="tab" href="#attachment-list" role="tab">Attachment</a>
        </li>
        @if (\Auth::user()->role == \App\User::ROLE_ADMIN)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'history-list' ? 'active' : '' }}" id="nav-history-list" data-toggle="tab" href="#history-list" role="tab">History Upload Result</a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'map' ? 'active' : '' }}" id="nav-map" data-toggle="tab" href="#map" role="tab">Map</a>
        </li>
        @if ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE || $project->type == \App\Project::WHEEL_TYPE || $project->type == \App\Project::CQI_TYPE)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'file-manager' ? 'active' : '' }}" id="nav-file-manager" data-toggle="tab" href="#file-manager" role="tab">File Manager</a>
        </li>
        @endif
        @if ($project->type == \App\Project::CSI_TYPE)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'progress-total-csi' ? 'active' : '' }}" id="nav-progress-total" data-toggle="tab" href="#progress-total-csi" role="tab">Progress Total</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'progress-csi' ? 'active' : '' }}" id="nav-progress" data-toggle="tab" href="#progress-csi" role="tab">Progress Detail</a>
        </li>
        @endif
        @if ($project->type == \App\Project::CSL_TYPE)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'progress-total' ? 'active' : '' }}" id="nav-progress-total" data-toggle="tab" href="#progress-total" role="tab">Progress Total</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'progress' ? 'active' : '' }}" id="nav-progress" data-toggle="tab" href="#progress" role="tab">Progress Detail</a>
        </li>
        @endif
        @if ($project->type == \App\Project::CQI_TYPE)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'progress-total-cqi' ? 'active' : '' }}" id="nav-progress-total" data-toggle="tab" href="#progress-total-cqi" role="tab">Progress Total</a>
        </li>
        @endif
        @if ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE || $project->type == \App\Project::CQI_TYPE)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'interviewer' ? 'active' : '' }}" id="nav-interviewer" data-toggle="tab" href="#interviewer" role="tab">Interviewer</a>
        </li>
        @endif
        @if (Auth::user()->role != \App\User::ROLE_MAIN_DEALER && ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE))
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'week' ? 'active' : '' }}" id="nav-week" data-toggle="tab" href="#week" role="tab">Progress per Week</a>
        </li>
        @endif
        @if ($project->type == \App\Project::WHEEL_TYPE || $project->type == \App\Project::BLACKHOLE_TYPE)
        <li class="nav-item">
            <a class="nav-link {{ request()->query('tab') == 'progress-retail' ? 'active' : '' }}" id="nav-progress-retail" data-toggle="tab" href="#progress-retail" role="tab">Dashboard View</a>
        </li>
        @endif
    </ul>

    <div id="projectTabContent" class="tab-content">
        <div class="tab-pane {{ request()->query('tab') == 'project-detail' || request()->query('tab') == '' ? 'active' : '' }}" id="project-detail" role="tabpanel">
            @include('project.tab.info')
        </div>
        <div class="tab-pane {{ request()->query('tab') == 'question-list' ? 'active' : '' }}" id="question-list" role="tabpanel">
            @include('project.tab.question')
        </div>
        <div class="tab-pane {{ request()->query('tab') == 'attachment-list' ? 'active' : '' }}" id="attachment-list" role="tabpanel">
            @include('project.tab.attachment')
        </div>
        <div class="tab-pane {{ request()->query('tab') == 'history-list' ? 'active' : '' }}" id="history-list" role="tabpanel">
            @include('project.tab.history')
        </div>
        <div class="tab-pane {{ request()->query('tab') == 'map' ? 'active' : '' }}" id="map" role="tabpanel">
            @include('project.tab.map')
        </div>
        @if ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE || $project->type == \App\Project::WHEEL_TYPE || $project->type == \App\Project::CQI_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'file-manager' ? 'active' : '' }}" id="file-manager" role="tabpanel">
            @include('project.tab.filemanager')
        </div>
        @endif
        @if ($project->type == \App\Project::CSI_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'progress-total-csi' ? 'active' : '' }}" id="progress-total-csi" role="tabpanel">
            @include('project.tab.progresstotalcsi')
        </div>
        <div class="tab-pane {{ request()->query('tab') == 'progress-csi' ? 'active' : '' }}" id="progress-csi" role="tabpanel">
            @include('project.tab.progresscsi')
        </div>
        @endif
        @if ($project->type == \App\Project::CSL_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'progress-total' ? 'active' : '' }}" id="progress-total" role="tabpanel">
            @include('project.tab.progresstotal')
        </div>
        <div class="tab-pane {{ request()->query('tab') == 'progress' ? 'active' : '' }}" id="progress" role="tabpanel">
            @include('project.tab.progress')
        </div>
        @endif
        @if ($project->type == \App\Project::CQI_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'progress-total-cqi' ? 'active' : '' }}" id="progress-total-cqi" role="tabpanel">
            @include('project.tab.progresstotalcqi')
        </div>
        @endif
        @if ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE || $project->type == \App\Project::CQI_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'interviewer' ? 'active' : '' }}" id="interviewer" role="tabpanel">
            @include('project.tab.interviewer.index')
        </div>
        @endif
        @if ($project->type == \App\Project::CSI_TYPE || $project->type == \App\Project::CSL_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'week' ? 'active' : '' }}" id="week" role="tabpanel">
            @include('project.tab.week')
        </div>
        @endif
        @if ($project->type == \App\Project::WHEEL_TYPE || $project->type == \App\Project::BLACKHOLE_TYPE)
        <div class="tab-pane {{ request()->query('tab') == 'progress-retail' ? 'active' : '' }}" id="progress-retail" role="tabpanel">
            @include('project.tab.retail')
        </div>
        @endif
    </div>
@endsection
@section('js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css" type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.js"></script>
    <script src="{!! url('/') !!}/js/jquery.fileupload.js"></script>
    <script>
        $(document).ready(function () {
            var selectColumn = $('.select2').select2();
            $('#column').on('select2:select', function (e) {
                var data = e.params.data;
                window.location.href = '{{ route('project.detail', ['uuid' => $project->uuid]) }}?tab=attachment-list&page=1&question_id='+data.id;
            });
            @if (session('code'))
                swal({
                    title: "{{ session('code') == 200 ? 'Success' : 'Oops' }}",
                    text: "{{ session('message') }}",
                    type: "{{ session('code') == 200 ? 'success' : 'error' }}",
                    confirmButtonClass: "btn-{{ session('code') == 200 ? 'success' : 'danger' }}",
                    confirmButtonText: "{{ session('code') == 200 ? 'Success' : 'Retry' }}!"
                });
            @endif
            initProjectList();
            deleteReport();
            @if ($selectedQuestion)
                initAttachmentList();
            @endif
            initHistorylist();

            if ($('[data-render=switchery]').length !== 0) {
                $('[data-render=switchery]').each(function() {
                    var switchery = new Switchery(this);
                });
            }
        });
        function initProjectList() {
            var table = $('#report-list').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('report.data', ['uuid' => $project->uuid]) !!}',
                order: [
                    [7, 'asc'],
                    [2, 'asc'],
                ],
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type', searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'created_by_name', name: 'created_by' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'updated_by_name', name: 'updated_by' },
                    { data: 'action', name: 'action', searchable: false, orderable: false, className: 'text-center' },
                    { data: 'user.role', name: 'user.role', visible: false, searchable: false, defaultContent: '' },
                ]
            });
        }

        @if ($selectedQuestion)
        function initAttachmentList() {
            var table = $('#attachment-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('project.attachment', ['uuid' => $project->uuid]) !!}',
                    data: function (d) {
                        d.question_id = '{{ request()->question_id }}';
                    }
                },
                columns: [
                    { data: '{{ $identityQuestion->alias }}', name: '{{ $identityQuestion->alias }}' },
                    { data: '{{ $descriptionQuestion->alias }}', name: '{{ $descriptionQuestion->alias }}', defaultContent: '' },
                    { data: 'action', name: 'action' },
                ]
            });
            table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();
        }
        @endif

        function initHistorylist() {
            var table = $('#history-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('project.history', ['uuid' => $project->uuid]) !!}',
                },
                order: [
                    [2, 'desc']
                ],
                columns: [
                    { data: 'rownum', name: 'rownum', searchable: false, orderable: false },
                    { data: 'action', name: 'result_code' },
                    { data: 'updated_at', name: 'updated_at' },
                ]
            });
            table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();
        }

        function deleteReport() {
            $(document).on('click', '.btn-delete', function () {
                var projectid = $(this).attr('projectid');
                var reportid = $(this).attr('reportid');
                swal({
                    title: "Delete Report",
                    text: "Are you sure to delete this generated report",
                    type: "warning",
                    confirmButtonClass: "btn-warning",
                    confirmButtonText: "OK!",
                    showCancelButton: true,
                }, function () {
                    window.location.href = "{{ url('report') }}/"+projectid+"/delete/"+reportid;
                });
            });
        }

        function changeMultiSelectIcon() {
            $('.multiselect-item .input-group-addon').html('<i class="fa fa-search"></i>');
            $('.multiselect-clear-filter').html('<i class="fa fa-remove"></i>');
        }
    </script>
    @yield('fm-js')
    @yield('total-js')
    @yield('progress-js')
    @yield('interviewer-js')
    @yield('week-js')
    @yield('map-js')
@endsection

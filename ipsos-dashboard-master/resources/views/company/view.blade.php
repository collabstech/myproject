@extends('layout.master')
@section('page-title', 'Company Management View')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li><a href="{{ route('company.index') }}">Company Management</a></li>
    <li class="active">Company Management View</li>
@endsection
@section('content')
<div class="row">
    <div class="mx-auto col-8">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title text-center">Detail Company</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-5">
                        <img src="{{ isset($company) && $company->logo ? route('file', ['url' => $company->logo]) : asset('images/image.png') }}" alt="" class="default-thumbnail logo">
                    </div>
                    <div class="col-7">
                        Company Name:
                        <br>
                        <h3>{{ $company->name }}</h3>
                    </div>
                </div>
                <div class="py-3"></div>
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="projectTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="nav-project-list" data-toggle="tab" href="#project-list" role="tab">Project List</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="nav-user-list" data-toggle="tab" href="#user-list" role="tab">User List</a>
                            </li>
                        </ul>
                        <div id="projectTabContent" class="tab-content">
                            <div class="tab-pane active" id="project-list" role="tabpanel">
                                <table id="table-project-list" class="w-100 table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th nowrap>No</th>
                                            <th nowrap>Name</th>
                                            <th nowrap>Description</th>
                                            <th nowrap>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="100%">No data available.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="user-list" role="tabpanel">
                                <table id="table-user-list" class="w-100 table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th nowrap>No</th>
                                            <th nowrap>Name</th>
                                            <th nowrap>Email</th>
                                            <th nowrap>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="100%">No data available.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    $(document).ready(function() {
        @if (session('code'))
            swal({
                title: "{{ session('code') == 200 ? 'Success' : 'Oops' }}",
                text: "{{ session('message') }}",
                type: "{{ session('code') == 200 ? 'success' : 'error' }}",
                confirmButtonClass: "btn-{{ session('code') == 200 ? 'success' : 'danger' }}",
                confirmButtonText: "{{ session('code') == 200 ? 'Success' : 'Retry' }}!"
            });
        @endif
        initListProject();        
        initListUser();        
    });

    function initListProject() {
        var table = $('#table-project-list').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('company.project.data', ['uuid' => $company->uuid]) !!}',
            order: [
                [1, 'asc']
            ],
            columns: [
                { data: 'rownum', name: 'rownum', searchable: false },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'created_at', name: 'created_at', searchable: false },
            ]
        });
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    }

    function initListUser() {
        var table = $('#table-user-list').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('company.user.data', ['uuid' => $company->uuid]) !!}',
            order: [
                [1, 'asc']
            ],
            columns: [
                { data: 'rownum', name: 'rownum', searchable: false },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'created_at', name: 'created_at', searchable: false },
            ]
        });
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    }
    
</script>
@endsection
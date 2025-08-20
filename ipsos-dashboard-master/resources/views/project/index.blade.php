@extends('layout.master')
@section('page-title', 'Project Management')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li class="active">Project Management</li>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title">Project List</h4>
            </div>
            <div class="panel-body">
                <div class="pull-right">
                    <a href="{{ route('project.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Create New Project</a>
                </div>
                <div class="clearfix py-3"></div>
                <table id="table-list" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th nowrap>No</th>
                            <th nowrap>Company Name</th>
                            <th nowrap>Project Name</th>
                            <th nowrap>Start</th>
                            <th nowrap>Finish</th>
                            <th nowrap>Action</th>
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
        initListData();
        deleteProject();
    });
    
    function initListData() {
        var table = $('#table-list').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('project.data') !!}',
            order: [
                [2, 'asc']
            ],
            columns: [
                { data: 'rownum', name: 'rownum', searchable: false, orderable: false },
                { data: 'company.name', name: 'company.name', defaultContent: '' },
                { data: 'name', name: 'name' },
                { data: 'start_date', name: 'start_date', searchable: false },
                { data: 'finish_date', name: 'finish_date', searchable: false },
                // { data: 'action', name: 'action', searchable: false, orderable: false },
                { 
                    name: 'action', searchable: false, orderable: false,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return '<a href="{{ url('project') }}/'+row.uuid+'" class="btn btn-primary"><i class="fa fa-info"></i> View</a>' +
                        '&nbsp;<a href="{{ url('project') }}/'+row.uuid+'/edit" class="btn btn-success"><i class="fa fa-pencil"></i> Edit</a>' +
                        '&nbsp;<a href="javascript:;" class="btn btn-danger btn-delete" projectid="'+row.uuid+'" name="'+row.name+'"><i class="fa fa-trash"></i> Delete</a>';
                    } 
                }
            ]
        });
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    }

    function deleteProject() {
        $(document).on('click', '.btn-delete', function () {
            var projectid = $(this).attr('projectid');
            var name = $(this).attr('name');
            swal({
                title: "Delete Project",
                text: "Are you sure to delete project ["+name+"]?",
                type: "warning",
                confirmButtonClass: "btn-warning",
                confirmButtonText: "OK!",
                showCancelButton: true,
            }, function () {
                window.location.href = "{{ route('project.delete') }}/"+projectid;
            });
        });
    }
</script>
@endsection
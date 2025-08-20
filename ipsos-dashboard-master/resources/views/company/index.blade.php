@extends('layout.master')
@section('page-title', 'Company Management')
@section('breadcrumb')
    <li><a href="{{ url('/') }}">Home</a></li>
    <li class="active">Company Management</li>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="panel panel-iris">
            <div class="panel-heading">
                <h4 class="panel-title">Company List</h4>
            </div>
            <div class="panel-body">
                <div class="pull-right">
                    <a href="{{ route('company.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> Create New Company</a>
                </div>
                <div class="clearfix py-3"></div>
                <table id="table-list" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th nowrap>No</th>
                            <th nowrap>Name</th>
                            <th nowrap>Created At</th>
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
        deleteCompany();
    });
    
    function initListData() {
        var table = $('#table-list').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('company.data') !!}',
            order: [
                [1, 'asc']
            ],
            columns: [
                { data: 'rownum', name: 'rownum', searchable: false, orderable: false },
                { data: 'name', name: 'name' },
                { data: 'created_at', name: 'created_at', searchable: false },
                { data: 'action', name: 'action', searchable: false, orderable: false, className: 'text-center' },
            ]
        });
        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    }

    function deleteCompany() {
        $(document).on('click', '.btn-delete', function () {
            var companyid = $(this).attr('companyid');
            var name = $(this).attr('name');
            swal({
                title: "Delete Company",
                text: "Are you sure to delete company ["+name+"]?",
                type: "warning",
                confirmButtonClass: "btn-warning",
                confirmButtonText: "OK!",
                showCancelButton: true,
            }, function () {
                window.location.href = "{{ route('company.delete') }}/"+companyid;
            });
        });
    }
</script>
@endsection
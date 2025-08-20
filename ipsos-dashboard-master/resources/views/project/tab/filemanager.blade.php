<div class="container">
    <div class="row">
        <div class="col-2" style="border-right: 1px solid #cccccc; overflow-x: scroll;">
            <div id="folder-tree"></div>
        </div>
        <div class="col-10" id="upload-container">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default" id="fm-back"><i class="fa fa-chevron-left" aria-hidden="true"></i>&nbsp;Back</button>
            </div>
            <div class="btn-group" role="group">
                @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-upload"><i class="fa fa-upload" aria-hidden="true"></i>&nbsp;Upload</button>
                @endif
                <button type="button" class="btn btn-default" id="mass-download"><i class="fa fa-download" aria-hidden="true"></i>&nbsp;Download</button>
                @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-new-folder"><i class="fa fa-folder" aria-hidden="true"></i>&nbsp;New Folder</button>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-mass-delete-confirm"><i class="fa fa-trash" aria-hidden="true"></i>&nbsp;Delete</button>
                @endif
            </div>
            <br/><br/>
            <div class="alert-message alert" id="info-fm" role="alert" style="display: none;"></div>
            <div class="row">
                <div class="col-9">
                    <div class="well well-sm">Current Path: <strong><span id="current-path"></span></strong></div>
                </div>
                <div class="right col-3">
                    <select id="fm-sort" class="select2" style="width: 100%;">
                        <option></option>
                        <option value="1">Sort by name (asc)</option>
                        <option value="2">Sort by name (desc)</option>
                    </select>
                </div>
            </div>
            <table id="file-list" class="w-100 table table-striped">
                <thead>
                    <tr>
                        <th nowrap width="5%"><input type="checkbox" name="all-file-check" /></th>
                        <th nowrap>Item</th>
                        <th nowrap width="15%">Size</th>
                        <th nowrap width="15%">Type</th>
                        <th nowrap width="20%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="100%">No file / folder available.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-upload" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Upload File / Folder</h4>
            </div>
            <div class="modal-body">
                {{ csrf_field() }}
                <p id="path-info"></p>
                <input id="fileupload" type="file" name="files[]" data-url="{{ route('project.fm.upload', ['uuid' => $project->uuid]) }}" multiple>
                <div id="upload-info" style="padding-top: 5px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-delete-confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete File</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure to delete <strong><span id="will-be-deleted-file"></span></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="yes-delete" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-mass-delete-confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete Files</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure to delete?</p>
                <strong><ul id="will-be-deleted-files"></ul></strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="yes-mass-delete" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-delete-folder-confirm" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete Folder</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure to delete <strong><span id="will-be-deleted-folder"></span></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="yes-folder-delete" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-new-folder" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">New Folder</h4>
            </div>
            <div class="modal-body">
                <input type="text" name="foldername" id="foldername" class="form-control" placeholder="Folder Name" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="new-folder" data-dismiss="modal">Create</button>
            </div>
        </div>
    </div>
</div>

@if (Auth::user()->role == \App\User::ROLE_ADMIN)
<div id="modal-rename" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Rename File / Folder</h4>
            </div>
            <div class="modal-body">
                <p>You will change the name of this file: <strong><span id="old-name"></span></strong></p>
                <input type="text" name="new-name" id="new-name" class="form-control" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="rename" data-dismiss="modal">Rename</button>
            </div>
        </div>
    </div>
</div>
@endif

<div id="modal-fileinfo" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">File Info</h4>
            </div>
            <div class="modal-body">
                <table class="w-100 table table-striped">
                    <tbody>
                        <tr>
                            <td colspan="50%"><strong>Name</strong></td>
                            <td id="info-name" colspan="50%"></td>
                        </tr>
                        <tr>
                            <td colspan="50%"><strong>Size</strong></td>
                            <td id="info-size" colspan="50%"></td>
                        </tr>
                        <tr>
                            <td colspan="50%"><strong>Type</strong></td>
                            <td id="info-type" colspan="50%"></td>
                        </tr>
                        @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                        <tr>
                            <td colspan="50%"><strong>Uploaded By</strong></td>
                            <td id="info-uploaded-by" colspan="50%"></td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="50%"><strong>Last Modified</strong></td>
                            <td id="info-last-modified" colspan="50%"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="rename" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section('fm-js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    window.currentPath = '';
    window.prevPath = [];

    $('#folder-tree').jstree({ 
        'core' : {
            'data' : [
                { "id" : "_root_", "parent" : "#", "text" : "root" },
            ]
        }
    });

    $('#folder-tree').on('changed.jstree', function (e, data) {
        if(data.selected.length > 0) {
            window.prevPath.push(window.currentPath);
            path = '';
            if(data.instance.get_node(data.selected[0]).id != '_root_') {
                path = data.instance.get_node(data.selected[0]).id;
            }
            getAllFiles(path);
            $('#current-path').html(('root/' + window.currentPath).replace('//', '/'));
        }
    }).jstree();

    getAllFolders();
    getAllFiles(window.currentPath);

    $('#fileupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<span/>').text(file + ' has been uploaded').appendTo('#upload-info');
                $('<br/>').text('').appendTo('#upload-info');
            });
            // getAllFolders();
            getAllFiles(window.currentPath);
        }
    });

    $('#fileupload').bind('fileuploadsubmit', function (e, data) {
        data.formData = {
            path: data.files[0].relativePath,
            currentPath: window.currentPath,
            _token: $('[name="_token"]').val()
        };
    });

    $('#modal-upload').on('shown.bs.modal', function (e) {
        $('#upload-info').html('');
        path = ('root/' + window.currentPath).replace('//', '/');
        $('#path-info').html('Your file / folder will be uploaded to <strong>' + path + '</strong>');
    })

    $('#fm-back').click(function () {
        if(window.prevPath.length > 0) {
            getAllFiles(window.prevPath[window.prevPath.length - 1]);
            window.prevPath.splice(-1,1);
        }

        return false;
    })

    $('#yes-delete').click(function() {
        deleteFile($('#will-be-deleted-file').html());
        return false;
    });

    $('#yes-folder-delete').click(function() {
        deleteFolder($('#will-be-deleted-folder').html());
        return false;
    });

    $('#modal-mass-delete-confirm').on('shown.bs.modal', function (e) {
        deleteInfo = '';

        $('input[name="file-id[]"]').each(function() {
            if(this.checked) {
                deleteInfo += '<li>' + this.value + '</li>';
            }
        });
    })

    $('#yes-mass-delete').click(function() {
        filenames = [];

        index = 0;
        $('input[name="file-id[]"]').each(function() {
            if(this.checked) {
                filenames[index] = this.value;
                ++index;
            }
        });

        massDelete(filenames);
        
        return false;
    });

    $('#mass-download').click(function() {
        filenames = [];

        index = 0;
        $('input[name="file-id[]"]').each(function() {
            if(this.checked) {
                filenames[index] = this.value;
                ++index;
            }
        });

        if (filenames.length == 0) {
            alert('Please select at least 1 file');
            return;
        }

        massDownload(filenames);
        
        return false;
    });

    $('#modal-new-folder').on('shown.bs.modal', function (e) {
        $('#foldername').val('');
    })

    $('#new-folder').click(function() {
        newFolder($('#foldername').val());
    });

    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    $('#rename').click(function() {
        renameFile($('#old-name').html(), $('#new-name').val());
    });
    @endif

    $('input[name="all-file-check"]').click(function() {
        $('input[name="file-id[]"]').prop('checked', this.checked);
    });

    $('#fm-sort').select2({
        placeholder: "Sort by"
    });

    $('#fm-sort').change(function () {
        fmSort($('#fm-sort').val());
    });
});

function fmSort(sortType) {
    files = window.files;
    for (i = 0; i < files.length - 1; ++i) {
        for (j = 0; j < files.length - i - 1; ++j) {
            isSwap = false;
            if (sortType == 1 && files[j].name > files[j + 1].name) {
                isSwap = true;
            }
            else if (sortType == 2 && files[j].name < files[j + 1].name) {
                isSwap = true;
            }

            if (isSwap) {
                temp = files[j];
                files[j] = files[j + 1];
                files[j + 1] = temp;
            }
        }
    }

    generateTableView(files);
}

function getAllFolders() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.fm.folders', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            if(response.length == 0) {
                return;
            }

            $('#folder-tree').jstree(true).settings.core.data = response;
            $('#folder-tree').jstree(true).refresh();
        }
    });
}

function getAllFiles(path) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.fm.files', ['uuid' => $project->uuid]) }}?path=' + path,
        error: function (response) {
            
        },
        success: function (response) {
            $('input[name="all-file-check"]').prop('checked', false);

            window.currentPath = response.path;
            $('#current-path').html(('root/' + window.currentPath).replace('//', '/'));
            window.files = response.files;
            generateTableView(response.files);
        }
    });
}

function getFileInfo(filename) {
    filepath = window.currentPath + '/' + filename;

    $.ajax({
        method: 'get',
        url: '{{ route('project.fm.file', ['uuid' => $project->uuid]) }}?filepath=' + filepath,
        error: function (response) {
            
        },
        success: function (response) {
            $('#info-name').html(response.name);
            $('#info-size').html(response.size);
            $('#info-type').html(response.type);
            @if (Auth::user()->role == \App\User::ROLE_ADMIN)
            $('#info-uploaded-by').html(response.uploaded_by);
            @endif
            $('#info-last-modified').html(response.last_modified);

            $('#modal-fileinfo').modal('show');
        }
    });
}

function generateTableView(files) {
    if(files.length == 0) {
        $('#file-list>tbody').html('<tr><td colspan="100%">No file / folder available.</td></tr>');
        return;
    }
    htmlContent = '';
    for(i = 0; i < files.length; ++i) {
        size = '';
        icon = '<i class="fa fa-lg fa-folder-o" aria-hidden="true"></i>';
        click = 'onclick="openFolder(\'' + files[i].name + '\')"';
        action = '<a href="#" onclick="downloadFolder(\'' + files[i].name + '\')" style="color: #1FB8B3;"><i class="fa fa-lg fa-download" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;'
                @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                + '<a href="#" onclick="openRenameDialog(\'' + files[i].name + '\')" style="color: #4990E2;"><i class="fa fa-lg fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;'
                @endif
                + '<a href="#" onclick="openFolderConfirm(\'' + files[i].name + '\')" style="color: #F57572;"><i class="fa fa-lg fa-trash-o" aria-hidden="true"></i></a>';
        if(files[i].type != 'directory') {
            size = files[i].size + ' KB';
            icon = '<i class="fa fa-lg fa-file-o" aria-hidden="true"></i>';
            click = 'onclick="downloadFile(\'' + files[i].name + '\')"';
            action = '<a href="#" ' + click + ' style="color: #1FB8B3;"><i class="fa fa-lg fa-download" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;' 
                    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
                    + '<a href="#" onclick="openRenameDialog(\'' + files[i].name + '\')" style="color: #4990E2;"><i class="fa fa-lg fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;'
                    @endif
                    + '<a href="#" onclick="openConfirm(\'' + files[i].name + '\')" style="color: #F57572;"><i class="fa fa-lg fa-trash-o" aria-hidden="true"></i></a>'
                    + '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" onclick="getFileInfo(\'' + files[i].name + '\')" style="color: #724C9F;"><i class="fa fa-lg fa-info-circle" aria-hidden="true"></i></a>';
        }
        htmlContent += '<tr><td><input type="checkbox" name="file-id[]" value="' + files[i].name + '" /></td>' 
                + '<td>' + icon + '&nbsp;&nbsp;<strong><a href="#" ' + click + '>' 
                + files[i].name + '</a></strong></td><td>' + size + '</td><td>' 
                + files[i].type + '</td><td>' + action + '</td></tr>';
    }
    
    $('#file-list>tbody').html(htmlContent);
}

function newFolder(foldername) {
    var formData = new FormData();
    formData.append('_token', $('[name="_token"]').val());
    formData.append('path', window.currentPath);
    formData.append('foldername', foldername);

    $.ajax({
        method: 'post',
        url: '{{ route('project.fm.folder', ['uuid' => $project->uuid]) }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function (response) {
            $('#info-fm').hide().removeClass('alert-success alert-warning');
        },
        error: function (response) {
            $('#info-fm').show().removeClass('alert-success').addClass('alert-warning').text('Failed creating new folder');
        },
        success: function (response) {
            $('#info-fm').show().removeClass('alert-warning').addClass('alert-success').text(response);
            getAllFiles(window.currentPath);
        }
    });
}

@if (Auth::user()->role == \App\User::ROLE_ADMIN)
function renameFile(oldName, newName) {
    var formData = new FormData();
    formData.append('_token', $('[name="_token"]').val());
    formData.append('old_name', window.currentPath + '/' + oldName);
    formData.append('new_name', window.currentPath + '/' + newName);

    $.ajax({
        method: 'post',
        url: '{{ route('project.fm.file.rename', ['uuid' => $project->uuid]) }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function (response) {
            $('#info-fm').hide().removeClass('alert-success alert-warning');
        },
        error: function (response) {
            $('#info-fm').show().removeClass('alert-success').addClass('alert-warning').text('The new name is invalid');
        },
        success: function (response) {
            $('#info-fm').show().removeClass('alert-warning').addClass('alert-success').text(response);
            getAllFiles(window.currentPath);
        }
    });
}

function openRenameDialog(oldName) {
    $('#old-name').html(oldName);
    $('#new-name').val(oldName);
    $('#modal-rename').modal();
}
@endif

function downloadFile(filename) {
    window.open('{{ route('project.fm.file.download', ['uuid' => $project->uuid]) }}' 
            + '?filepath=' + window.currentPath + '/' + filename, '_blank');
}

function downloadFolder(foldername) {
    massDownload([foldername]);
}

function massDownload(filenames) {
    url = '{{ route('project.fm.files.download', ['uuid' => $project->uuid]) }}' 
            + '?currentpath=' + window.currentPath + '&files=' + JSON.stringify(filenames);

    window.open(url);
}

function openConfirm(filename) {
    $('#will-be-deleted-file').html(filename);
    $('#modal-delete-confirm').modal();
}

function openFolderConfirm(foldername) {
    $('#will-be-deleted-folder').html(foldername);
    $('#modal-delete-folder-confirm').modal();
}

function deleteFile(filename) {
    var formData = new FormData();
    formData.append('_token', $('[name="_token"]').val());
    formData.append('filepath', window.currentPath + '/' + filename);

    $.ajax({
        method: 'post',
        url: '{{ route('project.fm.file.delete', ['uuid' => $project->uuid]) }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function (response) {
            $('#info-fm').hide().removeClass('alert-success alert-warning');
        },
        error: function (response) {
            $('#info-fm').show().removeClass('alert-success').addClass('alert-warning').text('Failed');
            $('#modal-delete-confirm').modal('hide');
        },
        success: function (response) {
            $('#info-fm').show().removeClass('alert-warning').addClass('alert-success').text(response);
            getAllFiles(window.currentPath);
            $('#modal-delete-confirm').modal('hide');
        }
    });
}

function massDelete(filenames) {
    var formData = new FormData();
    formData.append('_token', $('[name="_token"]').val());

    for(i = 0; i < filenames.length; ++i) {
        formData.append('files[]', window.currentPath + '/' + filenames[i]);
    }

    $.ajax({
        method: 'post',
        url: '{{ route('project.fm.files.delete', ['uuid' => $project->uuid]) }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function (response) {
            $('#info-fm').hide().removeClass('alert-success alert-warning');
        },
        error: function (response) {
            $('#info-fm').show().removeClass('alert-success').addClass('alert-warning').text('Failed');
            $('#modal-mass-delete-confirm').modal('hide');
        },
        success: function (response) {
            $('#info-fm').show().removeClass('alert-warning').addClass('alert-success').text(response);
            getAllFiles(window.currentPath);
            $('#modal-mass-delete-confirm').modal('hide');
        }
    });
}

function deleteFolder(foldername) {
    var formData = new FormData();
    formData.append('_token', $('[name="_token"]').val());
    formData.append('folderpath', window.currentPath + '/' + foldername);

    $.ajax({
        method: 'post',
        url: '{{ route('project.fm.folder.delete', ['uuid' => $project->uuid]) }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function (response) {
            $('#info-fm').hide().removeClass('alert-success alert-warning');
        },
        error: function (response) {
            $('#info-fm').show().removeClass('alert-success').addClass('alert-warning').text('Failed');
        },
        success: function (response) {
            $('#info-fm').show().removeClass('alert-warning').addClass('alert-success').text(response);
            getAllFiles(window.currentPath);
            $('#modal-delete-folder-confirm').modal('hide');
        }
    });
}

function openFolder(foldername) {
    window.prevPath.push(window.currentPath);
    getAllFiles(window.currentPath + '/' + foldername);
}

</script>
@endsection
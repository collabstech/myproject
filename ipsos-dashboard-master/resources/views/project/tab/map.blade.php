<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="alert-message alert" id="info-upload-progress" role="alert" style="display: none;"></div>
        </div>
    </div>
    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    <form id="form-map" class="form-horizontal form-map" action="{{ route('project.map.import', [$project->uuid]) }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="file" id="file-import-map" name="import_map" class="file-hidden project-map-file" />
        <div class="row">
            <div class="col-2">
                <div class="thumbnail-dashed thumbnail-map-upload"><i class="fa fa-file"></i></div>
            </div>
            <button class="btn btn-success" id="btn-import-map">Choose File</button>&nbsp;&nbsp;
            <button type="submit" class="btn btn-primary" id="upload-map-data">Upload</button>
            <div class="col-2">
                <div class="progress-save"></div>
            </div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-upload-image"><i class="fa fa-image" aria-hidden="true"></i>&nbsp;Upload Image</button>&nbsp;&nbsp;
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-map-confirmation"><i class="fa fa-trash" aria-hidden="true"></i>&nbsp;Delete Map Data</button>
        </div>
    </form>
    <br/>
    @endif
    <div class="row">
        <div class="col-10">
            <div class="row">
                <div class="col-2">
                    <select name="map-province" id="map-province" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="map-kabupaten" id="map-kabupaten" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="map-kecamatan" id="map-kecamatan" class="select2" style="width: 100%;">
                        <option value="0">Select Kecamatan</option>
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="map-kelurahan" id="map-kelurahan" class="select2" style="width: 100%;">
                        <option value="0">Select Kelurahan</option>
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="map-segment" id="map-segment" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
                <div class="col-2"> 
                    <select name="map-brand" id="map-brand" multiple="multiple" style="width: 100%;">
                    </select>
                </div>
            </div>
        </div>
        <div class="col-2"> 
            <button class="btn btn-primary" id="map-filter" style="width: 100%;">Filter</button>
        </div>
    </div>
    <br/><br/>
    <div class="row">
        <div class="col-12" id="google-map" style="height: 450px;"></div>
    </div>
    <br/><br/>
</div>

<div id="modal-upload-image" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Upload Image</h4>
            </div>
            <div class="modal-body">
                {{ csrf_field() }}
                <input id="fileupload-image" type="file" name="files[]" data-url="{{ route('project.map.image-upload', ['uuid' => $project->uuid]) }}" multiple>
                <div id="upload-info-image" style="padding-top: 5px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-delete-map-confirmation" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete Map Data</h4>
            </div>
            <div class="modal-body">
                <p id="confirm-delete-text">Are you sure to remove all map data?</p>
                <div id="progress-delete" class="progress-save"></div>
            </div>
            <div class="modal-footer">
                <button id="delete-map-data" type="button" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="map-legend" id="map-legend"><h5>Legend</h5></div>

<style>
.progress-save {
    display: none;
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.map-legend {
    font-family: Arial, sans-serif;
    background: #fff;
    padding: 10px;
    margin: 10px;
    border: 1px solid #000;
}

.map-legend h5 {
    margin-top: 0;
}

.map-legend img {
    vertical-align: middle;
}
</style>

@section('map-js')
<script type="text/javascript">
$(document).ready(function() {
    var legendNames = [];
    var legendIcons = [];

    $('#map-province').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    $('#map-kabupaten').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    $('#map-segment').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });
    $('#map-brand').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        buttonWidth: '100%'
    });

    loadMapProvince();
    loadMapSegment();
    loadMapBrand();

    @if (Auth::user()->role == \App\User::ROLE_ADMIN)
    $('#btn-import-map').click(function() {
        $('#file-import-map').click();

        return false;
    });

    $('.thumbnail-map-upload').click(function() {
        $('#file-import-map').click();
    });

    $('#file-import-map').change(function() {
        readURL(this, '.thumbnail-map-upload');
    });

    $('#form-map').submit(function(e) {
        e.preventDefault();

        var progressFile = $('#file-import-map')[0].files;
        var formData = new FormData();
        var file = progressFile[0];
        formData.append('import_map', file, file.name);
        formData.append('project_id', {!! $project->id !!});
        formData.append('_token', $('[name="_token"]').val());

        var progress = 0;

        $.ajax({
            method: 'post',
            url: '{{ route('project.map.import', ['uuid' => $project->uuid]) }}',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function (response) {
                $('#btn-import-map').hide();
                $('#upload-map-data').hide();
                for (let index = 0; index < 100; index++) {
                    progress++;
                }
                $('.alert-message').hide().removeClass('alert-success alert-warning');
                $('.progress-save').show();
            },
            error: function (response) {
                response = JSON.parse(response.responseText);
                progress = 100;
                $('.alert-message').show().removeClass('alert-success').addClass('alert-warning').text(response.message);
                $('.progress-save').hide();
                $('#btn-import-map').show();
                $('#upload-map-data').show();
            },
            success: function (response) {
                progress = 100;
                $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                $('.progress-save').hide();
                $('#btn-import-map').show();
                $('#upload-map-data').show();
                loadMapProvince();
                loadMapSegment();
                loadMapBrand();
            }
        });

        return false;
    });

    $('#fileupload-image').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<span/>').text(file + ' has been uploaded').appendTo('#upload-info-image');
                $('<br/>').text('').appendTo('#upload-info-image');
            });
        },
        error: function(e, data) {
            $('<span/>').text(e.responseText).appendTo('#upload-info-image');
            $('<br/>').text('').appendTo('#upload-info-image');
        }
    });

    $('#fileupload-image').bind('fileuploadsubmit', function (e, data) {
        data.formData = {
            path: data.files[0].relativePath,
            currentPath: window.currentPath,
            _token: $('[name="_token"]').val()
        };
    });

    $('#modal-upload-image').on('shown.bs.modal', function (e) {
        $('#upload-info-image').html('');
    })
    @endif

    $('#map-province').change(function () {
        $('#map-kabupaten').html('');
        $('#map-kabupaten').multiselect('rebuild');
        $('#map-kecamatan').html('<option value="0">Select Kecamatan</option>');
        $('#map-kelurahan').html('<option value="0">Select Kelurahan</option>');

        provinces = [];
        $('#map-province > option:selected').each(function() {
            provinces.push($(this).val());
        });

        loadMapKabupaten(JSON.stringify(provinces));
    });

    $('#map-kabupaten').change(function () {
        $('#map-kecamatan').html('<option value="0">Select Kecamatan</option>');
        $('#map-kelurahan').html('<option value="0">Select Kelurahan</option>');

        provinces = [];
        $('#map-province > option:selected').each(function() {
            provinces.push($(this).val());
        });

        kabupatens = [];
        $('#map-kabupaten > option:selected').each(function() {
            kabupatens.push($(this).val());
        });

        loadMapKecamatan(JSON.stringify(provinces), JSON.stringify(kabupatens));
    });

    $('#map-kecamatan').change(function () {
        $('#map-kelurahan').html('<option value="0">Select Kelurahan</option>');

        provinces = [];
        $('#map-province > option:selected').each(function() {
            provinces.push($(this).val());
        });

        kabupatens = [];
        $('#map-kabupaten > option:selected').each(function() {
            kabupatens.push($(this).val());
        });

        loadMapKelurahan(JSON.stringify(provinces), JSON.stringify(kabupatens), $('#map-kecamatan').val());
    });

    $('#map-filter').click(function () {
        provs = [];
        $('#map-province > option:selected').each(function() {
            provs.push($(this).val());
        });

        kabs = [];
        $('#map-kabupaten > option:selected').each(function() {
            kabs.push($(this).val());
        });

        sgmts = [];
        $('#map-segment > option:selected').each(function() {
            sgmts.push($(this).val());
        });

        brnds = [];
        $('#map-brand > option:selected').each(function() {
            brnds.push($(this).val());
        });

        provinces = JSON.stringify(provs);
        kabupatens = JSON.stringify(kabs);
        kecamatan = $('#map-kecamatan').val();
        kelurahan = $('#map-kelurahan').val();
        segments = JSON.stringify(sgmts);;
        brands = JSON.stringify(brnds);;

        removeAllMarker();

        $.ajax({
            method: 'get',
            url: '{{ route('project.map.filter', ['uuid' => $project->uuid]) }}?provinces=' + provinces
                + '&kabupatens=' + kabupatens + '&kecamatan=' + kecamatan + '&kelurahan=' + kelurahan
                + '&segments=' + segments + '&brands=' + brands,
            error: function (response) {

            },
            success: function (response) {
                addMultipleMarker(response);
            }
        });
    });

    $('#delete-map-data').click(function () {
        $("#delete-map-data").attr("disabled", true);
        $('#confirm-delete-text').hide();
        $('#progress-delete').show();
        
        var formData = new FormData();
        formData.append('_token', $('[name="_token"]').val());

        $.ajax({
            method: 'post',
            url: '{{ route('project.map.delete', ['uuid' => $project->uuid]) }}',
            data: formData,
            processData: false,
            contentType: false,
            error: function (response) {
                $('.alert-message').show().removeClass('alert-success').addClass('alert-warning').text('Error deleted map data');
                $('#confirm-delete-text').show();
                $('#progress-delete').hide();
                $("#delete-map-data").attr("disabled", false);
                $('#modal-delete-map-confirmation').modal('hide');
            },
            success: function (response) {
                $('.alert-message').show().removeClass('alert-warning').addClass('alert-success').text(response.message);
                $('#confirm-delete-text').show();
                $('#progress-delete').hide();
                $("#delete-map-data").attr("disabled", false);
                $('#modal-delete-map-confirmation').modal('hide');
            }
        });
    });
});

function loadMapProvince() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.province', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].province + '">' 
                        + response[i].province + '</option>';
            }
            $('#map-province').html(htmlContent);
            $('#map-province').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadMapKabupaten(provinces) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.kabupaten', ['uuid' => $project->uuid]) }}?provinces=' + provinces,
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].kabupaten + '">' 
                        + response[i].kabupaten + '</option>';
            }
            $('#map-kabupaten').html(htmlContent);
            $('#map-kabupaten').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadMapKecamatan(provinces, kabupatens) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.kecamatan', ['uuid' => $project->uuid]) }}?provinces=' + provinces 
                + '&kabupatens=' + kabupatens,
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '<option value="0">Select Kecamatan</option>';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].kecamatan + '">' 
                        + response[i].kecamatan + '</option>';
            }
            $('#map-kecamatan').html(htmlContent);
        }
    });
}

function loadMapKelurahan(provinces, kabupatens, kecamatan) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.kelurahan', ['uuid' => $project->uuid]) }}?provinces=' + provinces 
                + '&kabupatens=' + kabupatens + '&kecamatan=' + kecamatan,
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '<option value="0">Select Kelurahan</option>';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].kelurahan + '">' 
                        + response[i].kelurahan + '</option>';
            }
            $('#map-kelurahan').html(htmlContent);
        }
    });
}

function loadMapSegment() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.segment', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].segment + '">' 
                        + response[i].segment + '</option>';
            }
            $('#map-segment').html(htmlContent);
            $('#map-segment').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function loadMapBrand() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.brand', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            htmlContent = '';
            for(i = 0; i < response.length; ++i) {
                htmlContent += '<option value="' + response[i].brand + '">' 
                        + response[i].brand + '</option>';
            }
            $('#map-brand').html(htmlContent);
            $('#map-brand').multiselect('rebuild');
            changeMultiSelectIcon();
        }
    });
}

function getAreaInfo(area) {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.areainfo') }}?areaInfo=' + encodeURI(area),
        error: function (response) {
            alert('error get area info');
        },
        success: function (response) {
            northeast = response.candidates[0].geometry.viewport.northeast;
            southwest = response.candidates[0].geometry.viewport.southwest;
            moveCameraByArea(northeast.lat, northeast.lng, southwest.lat, southwest.lng);
        }
    });
}

function moveCameraByArea(lat1, lon1, lat2, lon2) {
    loc1 = new google.maps.LatLng(lat1, lon1);
    loc2 = new google.maps.LatLng(lat2, lon2);
    
    var bounds = new google.maps.LatLngBounds();
    bounds.extend(loc1);
    bounds.extend(loc2);
    
    map.fitBounds(bounds);
    map.panToBounds(bounds);
}

function addMultipleMarker(datas) {
    var boundary = new google.maps.LatLngBounds();
    var iconUrl = '';
    for (i = 0; i < datas.length; ++i) {
        latLng = new google.maps.LatLng(datas[i].lat, datas[i].lon);
        iconUrl = getIconUrl(datas[i].segment);
        marker = new google.maps.Marker({
            position: latLng,
            map: map,
            title: datas[i].name,
            icon: {
                url: iconUrl
            }
        });
        addMarkerInfo(marker, datas[i]);
        markers.push(marker);

        loc = new google.maps.LatLng(datas[i].lat, datas[i].lon);
        boundary.extend(loc);
    }

    map.fitBounds(boundary);
    map.panToBounds(boundary);
}

function arraySearch(arr, val) {
    for (var i = 0; i < arr.length; i++)
        if (arr[i] === val)                    
            return i;
    return false;
}

function getIconUrl(segment) {
    index = arraySearch(legendNames, segment.toUpperCase());

    if (index !== false) {
        return legendIcons[index];
    }

    return '{{ url("/img/marker/FEBC12.png") }}';
}

function addMarkerInfo(marker, data) {
    content = '<image src="/project/{{ $project->uuid }}/map/image/' + data.photo + '/thumbnail" alt="Image not found"/><br/><br/><span style="font-weight:bold; font-size:15px;">' + data.name + '</span><br/><i>' + data.segment + '</i><br/><br/>' + data.address;
    var infowindow = new google.maps.InfoWindow({
        content: content
    });
    google.maps.event.addListener(marker, 'click', function() {
        infowindow.open(map, marker);
    });
}

function removeAllMarker() {
    for (i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];
}

function loadLegend() {
    $.ajax({
        method: 'get',
        url: '{{ route('project.map.legend', ['uuid' => $project->uuid]) }}',
        error: function (response) {
            
        },
        success: function (response) {
            legendNames = response.names;
            legendIcons = response.icons;

            generateLegend();
        }
    });
}

function generateLegend() {
    var legend = document.getElementById('map-legend');
    legend.innerHTML = '<h5>Legend</h5>';
    for(i = 0; i < legendNames.length; ++i) {
        var div = document.createElement('div');
        div.innerHTML = '<img src="' + legendIcons[i] + '" width="16px"/> ' + legendNames[i];
        legend.appendChild(div);
    }

    map.controls[google.maps.ControlPosition.RIGHT_TOP].push(legend);
}

@if (Auth::user()->role == \App\User::ROLE_ADMIN)
function readURL(input, fileClass) {
    if (input.files && input.files[0]) {
        for(i=0; i < input.files.length; i++) {
            var fileReader = new FileReader();
            fileReader.onload = function(file) {
            }
            fileReader.fileName = input.files[i].name;
            fileReader.readAsBinaryString(input.files[i]);
        }
        $(fileClass).html(fileReader.fileName);
    }
}
@endif

var map;
var markers = [];
function initMap() {
    map = new google.maps.Map(document.getElementById('google-map'), {
        center: {lat: -1.8436497, lng: 119.0155941},
        zoom: 5
    });
    loadLegend();
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('MAP_API_KEY', 'AIzaSyBZge1khnpkL5C93DacoXaxmHP6AjyC1q0') }}&callback=initMap"
    async defer></script>
@endsection
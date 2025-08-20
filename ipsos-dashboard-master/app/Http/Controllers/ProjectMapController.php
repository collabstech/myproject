<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Reader\XLSX\Sheet;

use GuzzleHttp\Client;

class ProjectMapController extends ProjectController
{
    public function importMap($uuid, Request $request) 
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $project = \App\Project::where('uuid', $uuid)->first();

        $validator = Validator::make($request->all(), [
            'import_map' => 'required|file|mimes:xlsx'
        ]);

        // if ($validator->fails()) {
        //     return response()->json(['message' => 'File type must be xlsx'], 422);
        // }

        if ($request->import_map) {
            $path = \Storage::putFileAs('map', $request->file('import_map'), $request->import_map->getClientOriginalName());
            $reader = ReaderFactory::create(Type::XLSX);
            $reader->open(storage_path('app/' . $path));

            // validate
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($this->validateMap($sheet)) {
                    continue;
                }
                return response()->json(['message' => 'File is not valid'], 422);
            }
            
            // import
            foreach ($reader->getSheetIterator() as $sheet) {
                $this->importMap2($project->id, $sheet);
            }

            $reader->close();
            return response()->json(['message' => 'Success'], 200);
        }
    }

    private function validateMap(Sheet $sheet) 
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 1 && $row[0] == 'RESP.ID' 
                    && $row[1] == 'PROVINSI' && $row[2] == 'KOTA/KABUPATEN'
                    && $row[3] == 'KECAMATAN' && $row[4] == 'KELURAHAN/DESA'
                    && $row[5] == 'SEGMENT') {
                $isValid = true;
                $i = 6;
                for ($i = 6; $row[$i] == 'MERK'; $i += 2) {
                    if (!($row[$i] == 'MERK' && $row[$i + 1] == 'SALES')) { //jika tidak valid
                        $isValid = false;
                        break;
                    }
                }

                if (!($row[$i] == 'LATITUDE' && $row[$i + 1] == 'LONGITUDE' 
                        && $row[$i + 2] == 'NAMA TOKO' && $row[$i + 3] == 'ALAMAT TOKO'
                        && $row[$i + 4] == 'FOTO')) {
                    $isValid = false;
                }

                break;
            }
        }

        return $isValid;
    }

    private function importMap2($projectId, Sheet $sheet) 
    {        
        $titleRow = null;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow < 2) {
                $titleRow = $row;
                continue;
            }

            if ($row[1] == null || $row[1] == '') {
                continue;
            }

            $this->storeMap($projectId, $row, $titleRow);
        }

        return true;
    }

    private function storeMap($projectId, $row, $titleRow) 
    {
        $map = \App\Map::where('project_id', $projectId)
                ->where('respondent_id', $row[0])
                ->first();

        if (!$map) {
            $map = new \App\Map;
            $map->project_id = $projectId;
            $map->respondent_id = $row[0];
        }

        $map->province = $row[1];
        $map->kabupaten = $row[2];
        $map->kecamatan = $row[3];
        $map->kelurahan = $row[4];
        $map->segment = $row[5];
        $map->address = ''; //tidak ada nilai default nya maka disimpan dengan string kosong dulu

        $map->save();

        $i = 6;
        for ($i = 6; $titleRow[$i] == 'MERK'; $i += 2) {
            $brandMap = null;
            $brandMap = \App\BrandMap::where('map_id', $map->id)
                    ->where('brand', $row[$i])
                    ->first();

            if (!$brandMap) {
                $brandMap = new \App\BrandMap;
                $brandMap->map_id = $map->id;
                $brandMap->brand = $row[$i];
            }

            $brandMap->sales = $row[$i + 1];
            $brandMap->save();
        }

        $map->lat = $row[$i];
        $map->lon = $row[$i + 1];
        $map->name = $row[$i + 2];
        $map->address = $row[$i + 3];
        $map->photo = $row[$i + 4];

        $map->save();
    }

    public function getMapProvince($uuid)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $provinces = \App\Map::select('province')
                ->where('project_id', $project->id)
                ->distinct('province')
                ->get();
        
        return response()->json($provinces, 200);
    }

    public function getMapKabupaten($uuid, Request $request)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $provinces = $request->provinces;

        $kabupatens = \App\Map::select('kabupaten')
                ->where('project_id', $project->id)
                ->where(function($query) use ($provinces) {
                    if ($provinces != null && $provinces != '[]' && $provinces != '') {
                        $query->whereIn('province', json_decode($provinces));
                    }
                })
                ->distinct('kabupaten')
                ->get();
        
        return response()->json($kabupatens, 200);
    }

    public function getMapKecamatan($uuid, Request $request)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $provinces = $request->provinces;
        $kabupatens = $request->kabupatens;

        $kecamatans = \App\Map::select('kecamatan')
                ->where('project_id', $project->id)
                ->where(function($query) use ($provinces) {
                    if ($provinces != null && $provinces != '[]' && $provinces != '') {
                        $query->whereIn('province', json_decode($provinces));
                    }
                })
                ->where(function($query) use ($kabupatens) {
                    if ($kabupatens != null && $kabupatens != '[]' && $kabupatens != '') {
                        $query->whereIn('kabupaten', json_decode($kabupatens));
                    }
                })
                ->distinct('kecamatan')
                ->get();
        
        return response()->json($kecamatans, 200);
    }

    public function getMapKelurahan($uuid, Request $request)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $provinces = $request->provinces;
        $kabupatens = $request->kabupatens;
        $kecamatan = $request->kecamatan;

        $kelurahans = \App\Map::select('kelurahan')
                ->where('project_id', $project->id)
                ->where(function($query) use ($provinces) {
                    if ($provinces != null && $provinces != '[]' && $provinces != '') {
                        $query->whereIn('province', json_decode($provinces));
                    }
                })
                ->where(function($query) use ($kabupatens) {
                    if ($kabupatens != null && $kabupatens != '[]' && $kabupatens != '') {
                        $query->whereIn('kabupaten', json_decode($kabupatens));
                    }
                })
                ->where(function($query) use ($kecamatan) {
                    if ($kecamatan != '0') {
                        $query->where('kecamatan', $kecamatan);
                    }
                })
                ->distinct('kelurahan')
                ->get();
        
        return response()->json($kelurahans, 200);
    }

    public function getMapSegment($uuid)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $segments = \App\Map::selectRaw('UPPER(segment) as segment')
                ->where('project_id', $project->id)
                ->distinct('segment')
                ->get();
        
        return response()->json($segments, 200);
    }

    public function getMapBrand($uuid)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $brands = \App\Map::select('brand')
                ->leftJoin('brand_map', 'brand_map.map_id', '=', 'maps.id')
                ->where('project_id', $project->id)
                ->where('sales', '>', 0)
                ->distinct('brand')
                ->get();
        
        return response()->json($brands, 200);
    }

    public function getMapFilteredData($uuid, Request $request) 
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $provinces = $request->provinces;
        $kabupatens = $request->kabupatens;
        $kecamatan = $request->kecamatan;
        $kelurahan = $request->kelurahan;
        $segments = $request->segments;
        $brands = $request->brands;

        $query = \App\Map::query();
        $query->select('lat', 'lon', 'name', 'address', 'photo', 'segment');

        if ($brands != null && $brands != '[]' && $brands != '') {
            $query->leftJoin('brand_map', 'brand_map.map_id', '=', 'maps.id');
        }

        $query->where('project_id', $project->id)
        ->where(function($query) use ($provinces) {
            if ($provinces != null && $provinces != '[]' && $provinces != '') {
                $query->whereIn('province', json_decode($provinces));
            }
        })
        ->where(function($query) use ($kabupatens) {
            if ($kabupatens != null && $kabupatens != '[]' && $kabupatens != '') {
                $query->whereIn('kabupaten', json_decode($kabupatens));
            }
        })
        ->where(function($query) use ($kecamatan) {
            if ($kecamatan != '0') {
                $query->where('kecamatan', $kecamatan);
            }
        })
        ->where(function($query) use ($kelurahan) {
            if ($kelurahan != '0') {
                $query->where('kelurahan', $kelurahan);
            }
        })
        ->where(function($query) use ($segments) {
            if ($segments != null && $segments != '[]' && $segments != '') {
                $query->whereIn('segment', json_decode($segments));
            }
        })
        ->where(function($query) use ($brands) {
            if ($brands != null && $brands != '[]' && $brands != '') {
                $query->whereIn('brand', json_decode($brands));
                $query->where('sales', '>', 0);
            }
        });

        $datas = $query->get();

        return response()->json($datas, 200);
    }

    public function getGMapAreaInfo(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'areaInfo' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Failed'], 422);
        }

        $client = new Client();
        $url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=' . 
            $request->areaInfo . '&inputtype=textquery&fields=formatted_address,name,geometry&key=AIzaSyBGE0MTqlG4VdptfUHIyPgzg1eY8jHzSFU';
        
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() == 200) {
            $body = $response->getBody();
            return response()->json(json_decode($body), 200);
        }

        return response()->json(['message' => 'Failed'], $response->getStatusCode());
    }

    public function uploadImage($uuid, Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required',
            'files.*' => 'mimes:jpg,jpeg,gif,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json('File type is not allowed or file size is more than 2MB', 422);
        }

        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $result = (object) array(
            'files' => array()
        );

        $currentPath = $request->currentPath;

        if ($request->hasfile('files')) {

            $index = 0;
            foreach ($request->file('files') as $file) {
                $name = $file->getClientOriginalName();
                $result->files[$index] = $name;
                ++$index;

                $thumbnail = \Image::make($file->getRealPath());
                $thumbnail = $thumbnail->resize(75, 75, function ($constraint) {
                    $constraint->aspectRatio();
                });

                \Storage::putFileAs('map/image/' . $uuid . '/', $file, $file->getClientOriginalName());
                \Storage::put('map/image/' . $uuid . '/thumbnail//' . $file->getClientOriginalName(), $thumbnail->encode());
            }
        }

        return response()->json($result, 200);
    }

    public function readMapImage($uuid, $filename) 
    {
        $path = storage_path('app/map/image/' . $uuid . '/' . $filename);
        
        if (!\File::exists($path)) {
            abort(404);
        }
        $file = \File::get($path);
        $type = \File::mimeType($path);
        
        $response = \Response::make($file, 200);
        $response->header("Content-Type", $type);
        
        return $response;
    }

    public function readMapImageThumbnail($uuid, $filename) 
    {
        $path = storage_path('app/map/image/' . $uuid . '/thumbnail//' . $filename);
        
        if (!\File::exists($path)) {
            abort(404);
        }
        $file = \File::get($path);
        $type = \File::mimeType($path);
        
        $response = \Response::make($file, 200);
        $response->header("Content-Type", $type);
        
        return $response;
    }

    public function deleteMapData($uuid, Request $request) 
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();
        $maps = \App\Map::where('project_id', $project->id)->get();

        foreach ($maps as $map) {
            $q = 'DELETE FROM brand_map where map_id = ?';
            \DB::delete($q, [$map->id]);
            
            $map->delete();
        }

        return response()->json(['message' => 'Success deleted map data'], 200);
    }

    public function getLegend($uuid)
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $project = \App\Project::where('uuid', $uuid)->first();

        if (Auth::user()->role == \App\User::ROLE_CLIENT) {
            $userProject = DB::table('user_projects')
                    ->where('user_id', Auth::user()->id)
                    ->where('project_id', $project->id)
                    ->get();
            
            if ($userProject == null || sizeof($userProject) < 1) {
                return response()->json(['message' => 'Unauthorized'], 400);
            }
        }

        $segments = \App\Map::selectRaw('UPPER(segment) as segment')
                ->where('project_id', $project->id)
                ->distinct('segment')
                ->get();

        $names = array();
        $icons = [
            url("/img/marker/A2CD3A.png"),
            url("/img/marker/0195B6.png"),
            url("/img/marker/E14733.png"),
            url("/img/marker/E5E526.png"),
            url("/img/marker/444B53.png"),
            url("/img/marker/C6C2C3.png"),
            url("/img/marker/8DD161.png"),
            url("/img/marker/70CCD5.png"),
            url("/img/marker/E83895.png"),
            url("/img/marker/FEBC12.png"),
            url("/img/marker/71C495.png"),
            url("/img/marker/43B8EA.png"),
            url("/img/marker/F0546C.png"),
            url("/img/marker/885DA6.png"),
            url("/img/marker/CD7F2C.png"),
            url("/img/marker/CE60A4.png"),
            url("/img/marker/F29322.png"),
            url("/img/marker/D461A4.png")
        ];
        for ($i = 0; $i < sizeof($segments); ++$i) {
            $names[$i] = $segments[$i]->segment;
        }

        return response()->json((object) array('names' => $names, 'icons' => $icons), 200);
    }
}
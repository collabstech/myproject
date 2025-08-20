<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Box\Spout\Reader\XLSX\Sheet;

class ProjectRetailController extends ProjectController
{
    public function importRetail(Request $request) 
    {
        if (Auth::user()->role != \App\User::ROLE_ADMIN) return;

        $validator = Validator::make($request->all(), [
            'import_progress' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File type must be xlsx'], 422);
        }

        if ($request->import_progress) {
            $path = \Storage::putFileAs('progress-retail', $request->file('import_progress'), $request->import_progress->getClientOriginalName());
            $reader = ReaderFactory::create(Type::XLSX);
            $reader->open(storage_path('app/' . $path));

            // validate
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = $sheet->getName();
                if (strpos($sheetName, 'Progress') !== false && $this->validateRetailProgressSheet($sheet)) {
                    continue;
                } else if (strpos($sheetName, 'Achievement') !== false && $this->validateRetailAchievementSheet($sheet)) {
                    continue;
                } else if (strpos($sheetName, 'Information') !== false && $this->validateInformationSheet($sheet)) {
                    continue;
                }
                return response()->json(['message' => 'File is not valid'], 422);
            }
            
            // import
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = $sheet->getName();
                if (strpos($sheetName, 'Progress') !== false) {
                    $this->importRetailProgress($request->project_id, $sheet);
                } else if (strpos($sheetName, 'Achievement') !== false) {
                    $this->importAchievementProgress($request->project_id, $sheet);
                } else if (strpos($sheetName, 'Information') !== false) {
                    $this->importInformationProgress($request->project_id, $sheet);
                }
            }

            $reader->close();
            return response()->json(['message' => 'Success'], 200);
        }
    }

    private function validateRetailProgressSheet(Sheet $sheet) 
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 1 && $row[0] == 'ID SAMPLE' 
                    && $row[1] == 'NAMA PROVINSI' && $row[2] == 'NAMA KABUPATEN'
                    && $row[3] == 'NAMA KECAMATAN' && $row[4] == 'NAMA KELURAHAN'
                    && $row[5] == 'SHELL_START_DATE (Konversi ke Week)' && $row[6] == 'Weeks'
                    && $row[7] == 'JUMLAH TOKO YANG DIWAWANCARAI') {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }

    private function validateRetailAchievementSheet(Sheet $sheet) 
    {
        $isValid = false;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 1 && $row[0] == 'RESP.ID' 
                    && $row[1] == 'PROVINSI' && $row[2] == 'KOTA/KABUPATEN'
                    && $row[3] == 'KECAMATAN' && $row[4] == 'KELURAHAN/DESA'
                    && $row[5] == 'RETAIL SEGMENT CODE' && $row[6] == 'RETAIL SEGMENT') {

                $isValid = true;
                for ($i = 7; $row[$i] == 'Merk Code'; $i += 9) {
                    if (!($row[$i + 0] == 'Merk Code' && $row[$i + 1] == 'MERK SEMEN' && 
                            $row[$i + 2] == '50 KG (DO)' && $row[$i + 3] == '50 KG (BAG)' && 
                            $row[$i + 4] == '50 KG (TON)' && $row[$i + 5] == '40 KG (DO)' && 
                            $row[$i + 6] == '40 KG (BAG)' && $row[$i + 7] == '40 KG (TON)' && 
                            $row[$i + 8] == 'CEMENT SALES / MONTH (TON)')) { //jika tidak valid
                        $isValid = false;
                        break;
                    }
                }
                break;
            }
        }

        return $isValid;
    }

    private function validateInformationSheet(Sheet $sheet) 
    {
        $isValid = true;
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow == 1 && $row[0] == 'Chart Title 1') {
                continue;
            }
            if ($keyRow == 2 && $row[0] == 'Chart Title 2') {
                continue;
            }
            if ($keyRow == 3 && $row[0] == 'Chart Title 3') {
                continue;
            }
            if ($keyRow == 4 && $row[0] == 'Chart Title 4') {
                continue;
            }
            if ($keyRow == 5 && $row[0] == 'Chart Title 5') {
                continue;
            }
            if ($keyRow > 5) {
                break;
            }
            $isValid = false;
            break;
        }

        return $isValid;
    }

    private function importRetailProgress($projectId, Sheet $sheet) 
    {        
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow < 2) {
                continue;
            }

            if ($row[1] == null || $row[1] == '') {
                continue;
            }

            $this->storeRetailProgressData($projectId, $row);
        }

        return true;
    }

    private function importAchievementProgress($projectId, Sheet $sheet) 
    {        
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow < 2) {
                continue;
            }

            if ($row[1] == null || $row[1] == '') {
                continue;
            }

            $this->storeRetailAchievementData($projectId, $row);
        }

        return true;
    }

    private function importInformationProgress($projectId, Sheet $sheet) 
    {       
        $chartTitles = '';
        $storeStatTitles = '';
        $storeStatValues = ''; 
        foreach ($sheet->getRowIterator() as $keyRow => $row) {
            if ($keyRow >= 1 && $keyRow <= 5) {
                $chartTitles = $chartTitles . $row[1] . ';';
                continue;
            }
            if ($keyRow > 5 && ($row[0] != null && $row[0] != '')) {
                $storeStatTitles = $storeStatTitles . $row[0] . ';';
                $storeStatValues = $storeStatValues . $row[1] . ';';
                continue;
            }
            break;
        }

        $this->storeRetailInformationData($projectId, $chartTitles, 
                $storeStatTitles, $storeStatValues);

        return true;
    }

    private function storeRetailProgressData($projectId, $row) 
    {
        $retailProgress = \App\RetailProgress::where('project_id', $projectId)
                ->where('sample_id', $row[0])
                ->first();

        if (!$retailProgress) {
            $retailProgress = new \App\RetailProgress;
            $retailProgress->project_id = $projectId;
            $retailProgress->sample_id = $row[0];
        }

        $retailProgress->province = $row[1];
        $retailProgress->kabupaten = $row[2];
        $retailProgress->kecamatan = $row[3];
        $retailProgress->kelurahan = $row[4];
        $retailProgress->weeks = filter_var($row[6], FILTER_VALIDATE_INT) === false ? 0 : $row[6];
        $retailProgress->number_of_interview = filter_var($row[7], FILTER_VALIDATE_INT) === false ? 0 : $row[7];
        $retailProgress->status = filter_var($row[8], FILTER_VALIDATE_INT) === false ? 1 : $row[8];

        $retailProgress->save();
    }

    private function storeRetailAchievementData($projectId, $row) 
    {
        $retailAchievement = \App\RetailAchievement::where('project_id', $projectId)
                ->where('respondent_id', $row[0])
                ->first();

        if (!$retailAchievement) {
            $retailAchievement = new \App\RetailAchievement;
            $retailAchievement->project_id = $projectId;
            $retailAchievement->respondent_id = $row[0];
        }

        $retailAchievement->province = $row[1];
        $retailAchievement->kabupaten = $row[2];
        $retailAchievement->kecamatan = $row[3];
        $retailAchievement->kelurahan = $row[4];
        $retailAchievement->segment_id = $row[5];

        $retailAchievement->save();

        $retailAchievement->brands()->sync($this->getArrayIds($row));
    }

    private function storeRetailInformationData($projectId, $chartTitles, 
            $storeStatTitles, $storeStatValues)
    {
        $project = \App\Project::where('id', $projectId)->first();
        $project->chart_titles = $chartTitles;
        $project->store_stat_titles = $storeStatTitles;
        $project->store_stat_values = $storeStatValues;
        $project->save();
    }

    private function getArrayIds($row) 
    {
        $arrayIds = array();

        $offset = 6;
        $numOfColumn = 9;
        $brandIdColumn = 1;
        $salesMonthColumn = 9;
        $rowLength = 187;
        for ($i = 0; $i < $rowLength - $numOfColumn;) {
            // if ($row[$i + $offset + $salesMonthColumn] == 0.00) {
            //     $i += $numOfColumn;
            //     continue;
            // }

            $arrayIds[$row[$i + $offset + $brandIdColumn]] = array('month_sales' => $row[$i + $offset + $salesMonthColumn]);
            $i += $numOfColumn;
        }

        return $arrayIds;
    }

    public function getRetailProgressProvince($uuid)
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

        $provinces = \App\RetailProgress::select('province')
                ->where('project_id', $project->id)
                ->distinct('province')
                ->get();
        
        return response()->json($provinces, 200);
    }

    public function getRetailVisitedChartData($uuid, Request $request) 
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

        $datas = \App\RetailProgress::selectRaw('weeks as label, sum(number_of_interview) as visited')
                ->where('project_id', $project->id)
                ->where('weeks', '!=', 0)
                ->where(function($query) use ($provinces) {
                    if ($provinces != null && $provinces != '[]' && $provinces != '') {
                        $query->whereIn('province', json_decode($provinces));
                    }
                })
                ->groupBy('weeks')
                ->orderBy('weeks', 'asc')
                ->get();

        $visited = array();
        $label = array();
        
        $index = 0;
        $accumulativeVisited = 0;
        foreach ($datas as $data) {
            $accumulativeVisited += $data->visited;
            $visited[$index] = $accumulativeVisited;
            $label[$index] = 'Week ' . $data->label;
            ++$index;
        }

        $result = (object) array(
            'label' => $label,
            'visited' => $visited
        );

        return response()->json($result, 200);
    }

    public function getProgressKelurahanChartData($uuid, Request $request) 
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

        $datas = \App\RetailProgress::select('status')
                ->where('project_id', $project->id)
                ->where(function($query) use ($provinces) {
                    if ($provinces != null && $provinces != '[]' && $provinces != '') {
                        $query->whereIn('province', json_decode($provinces));
                    }
                })
                ->orderBy('status', 'asc')
                ->get();

        $progress = [0, 0, 0];
        $label = ['Not Yet', 'On Progress', 'Completed'];
        
        foreach ($datas as $data) {
            ++$progress[$data->status - 1];
        }

        $result = (object) array(
            'label' => $label,
            'progress' => $progress,
            'color' => ['#ED7D31', '#FFC000', '#92D050']
        );

        return response()->json($result, 200);
    }
    
    public function getRetailAchievementProvince($uuid)
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

        $provinces = \App\RetailAchievement::select('province')
                ->where('project_id', $project->id)
                ->distinct('province')
                ->get();
        
        return response()->json($provinces, 200);
    }

    public function getRetailAchievementKabupaten($uuid, Request $request)
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

        $kabupatens = \App\RetailAchievement::select('kabupaten')
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

    public function getRetailAchievementKecamatan($uuid, Request $request)
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

        $kecamatans = \App\RetailAchievement::select('kecamatan')
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

    public function getRetailAchievementKelurahan($uuid, Request $request)
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

        $kelurahans = \App\RetailAchievement::select('kelurahan')
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

    public function getSegment()
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $segments = \App\Segment::select('id', 'name')->get();
        
        return response()->json($segments, 200);
    }

    public function getBrand()
    {
        if (!(Auth::user()->role == \App\User::ROLE_ADMIN || Auth::user()->role == \App\User::ROLE_CLIENT)) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $brands = \App\Brand::select('id', 'name')->where('id', '<=', 17)->orWhere('id', '>=', 50)->get();
        
        return response()->json($brands, 200);
    }

    public function getRetailSegmentChartData($uuid, Request $request) {
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
        $retailSegments = $request->retailSegments;
        $brands = $request->brands;

        $query = \App\RetailAchievement::query();
        $query->selectRaw('segments.name as label, count(*) as achievement')
                ->leftJoin('segments', 'segments.id', '=', 'retail_achievements.segment_id');

        if ($brands != null && $brands != '[]' && $brands != '') {
            $query->leftJoin('brand_retail_achievement', 'brand_retail_achievement.retail_achievement_id', '=', 'retail_achievements.id');
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
                ->where(function($query) use ($retailSegments) {
                    if ($retailSegments != null && $retailSegments != '[]' && $retailSegments != '') {
                        $query->whereIn('segment_id', json_decode($retailSegments));
                    }
                })
                ->where(function($query) use ($brands) {
                    if ($brands != null && $brands != '[]' && $brands != '') {
                        $query->whereIn('brand_retail_achievement.brand_id', json_decode($brands));
                        $query->where('brand_retail_achievement.month_sales', '>', 0);
                    }
                })
                ->groupBy('segments.name');
        
        $datas = $query->get();
        
        $temp = array();
        $achievement = array();
        $label = array();
        
        foreach ($datas as $data) {
            $temp[$data->label] = $data->achievement;
        }

        arsort($temp);

        //memisahkan label dan achievement
        $i = 0;
        foreach ($temp as $key => $value) {
            $label[$i] = $key;
            $achievement[$i] = $value;
            ++$i;
        }

        $result = (object) array(
            'label' => $label,
            'achievement' => $achievement,
            'color' => [
                '#E52B50', '#FFBF00', '#9966CC', '#FBCEB1', '#7FFFD4', 
                '#007FFF', '#89CFF0', '#0095B6', '#8A2BE2', '#DE5D83'
            ],
            'sort' => $label
        );

        return response()->json($result, 200);
    }

    public function getBrandChartData($uuid, Request $request) {
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
        $retailSegments = $request->retailSegments;
        $brands = $request->brands;

        $datas = \App\RetailAchievement::selectRaw('retail_achievements.id as id, brand_retail_achievement.brand_id as brand_id, brand_retail_achievement.month_sales as month_sales')
                ->leftJoin('brand_retail_achievement', 'brand_retail_achievement.retail_achievement_id', '=', 'retail_achievements.id')
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
                ->where(function($query) use ($kelurahan) {
                    if ($kelurahan != '0') {
                        $query->where('kelurahan', $kelurahan);
                    }
                })
                ->where(function($query) use ($retailSegments) {
                    if ($retailSegments != null && $retailSegments != '[]' && $retailSegments != '') {
                        $query->whereIn('segment_id', json_decode($retailSegments));
                    }
                })
                ->where(function($query) use ($brands) {
                    if ($brands != null && $brands != '[]' && $brands != '') {
                        $query->whereIn('brand_retail_achievement.brand_id', json_decode($brands));
                        $query->where('brand_retail_achievement.month_sales', '!=', 0);
                    }
                })
                ->orderBy('brand_retail_achievement.brand_id', 'asc')
                ->get();
        
        if ($brands != null && $brands != '[]' && $brands != '') { //tambahkan data brand dari retail achievement id yang sama
            $ids = array();
            $i = 0;
            foreach ($datas as $data) {
                $ids[$i] = $data->id;
                ++$i;
            }

            $datas = DB::table('brand_retail_achievement')->selectRaw('retail_achievements.id as id, brand_retail_achievement.brand_id as brand_id, brand_retail_achievement.month_sales as month_sales')
                    ->leftJoin('retail_achievements', 'brand_retail_achievement.retail_achievement_id', '=', 'retail_achievements.id')
                    ->whereIn('retail_achievements.id', $ids)
                    ->get();
        }

        $brands = \App\Brand::select('id', 'name')->orderBy('id')->get();
        $temp = array();
        $totalData = array();
        $tempMS = array();
        $brandNames = array();
        $achievement = array();
        $monthSales = array();
        $label = array();
        $labelMs = array();

        foreach ($brands as $brand) {
            if ($brand->id > 17 && $brand->id < 50) continue; //brand id dalam rentang 18 - 49 tidak ditampilkan
            
            $brandNames[$brand->id] = $brand->name;
            $temp[$brand->name] = 0;
            $tempMS[$brand->name] = 0;
            $totalData[$brand->name] = 0;
        }
        
        $totalDataId = $brandNames[1];
        foreach ($datas as $data) {
            if (array_key_exists($data->brand_id, $brandNames)) {
				if ($data->month_sales != 0) {
					++$temp[$brandNames[$data->brand_id]];
					$tempMS[$brandNames[$data->brand_id]] += $data->month_sales;
                }
                $totalDataId = $brandNames[$data->brand_id];
				++$totalData[$brandNames[$data->brand_id]];
            }
        }

        $i = 0;
        arsort($temp);

        // memisahkan label dan achievement
        // meletakkan semen brand lainnya ke paling bawah (17 & 52)
        foreach ($temp as $key => $value) {
            if ($key == $brandNames[17] || $key == $brandNames[52]) {
                continue;
            }
            $label[$i] = $key;
            $achievement[$i] = $value;
            ++$i;
        }

        $label[$i] = $brandNames[17];
        $achievement[$i] = $temp[$brandNames[17]];
        ++$i;
        $label[$i] = $brandNames[52];
        $achievement[$i] = $temp[$brandNames[52]];

        // sorting disamakan dengen cement brand distribution
        $i = 0;
        foreach ($temp as $key => $value) {
            if ($key == $brandNames[17] || $key == $brandNames[52]) {
                continue;
            }
            $monthSales[$i] = $tempMS[$key];
            $labelMs[$i] = $key;
            ++$i;
        }

        $monthSales[$i] = $tempMS[$brandNames[17]];
        $labelMs[$i] = $brandNames[17];
        ++$i;
        $monthSales[$i] = $tempMS[$brandNames[52]];
        $labelMs[$i] = $brandNames[52];

        $result = (object) array(
            'label' => $label,
            'achievement' => $achievement,
            'label_ms' => $labelMs,
            'month_sales' => $monthSales,
            'total_data' => $totalData[$totalDataId], //ambil salah satu total data saja karena total data tiap brand sama
            'color' => [
                '#2D89EF', '#FFBF00', '#F966CC', '#FBCEB1', '#7FFFD4', 
                '#ff00aa', '#89CFF0', '#0095B6', '#8AFBE2', '#DE5D83',
                '#CD7F32', '#00ABA9', '#a0e0ff', '#f0f963', '#96fe18',
                '#DEA163', '#F07BA7', '#7FFF00', '#FFD700', '#808080',
                '#008000', '#B57EDC', '#808000', '#3F00FF', '#7F50FF'
            ],
            'sort' => $label
        );

        return response()->json($result, 200);
    }

    public function loadRetailInformation($uuid) {
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

        $chartTitles = $project->chart_titles;
        $chartTitle1 = 'Retail Segment Distribution';
        $chartTitle2 = 'Cement Brand Distribution';
        $chartTitle3 = 'Business Turn Over Per Month (Ton)';
        $chartTitle4 = 'Number of Retail Visited (Accumulative)';
        $chartTitle5 = 'Progress at Kelurahan Level';
        if ($chartTitles != null && $chartTitles != '') {
            $titles = explode(';', $chartTitles);

            if ($titles[0]) {
                $chartTitle1 = $titles[0];
            }
            if ($titles[1]) {
                $chartTitle2 = $titles[1];
            }
            if ($titles[2]) {
                $chartTitle3 = $titles[2];
            }
            if ($titles[3]) {
                $chartTitle4 = $titles[3];
            }
            if ($titles[4]) {
                $chartTitle5 = $titles[4];
            }
        }

        $storeStatTitles = explode(';', $project->store_stat_titles);
        $storeStatValues = explode(';', $project->store_stat_values);

        $result = (object) array(
            'chart_title1' => $chartTitle1,
            'chart_title2' => $chartTitle2,
            'chart_title3' => $chartTitle3,
            'chart_title4' => $chartTitle4,
            'chart_title5' => $chartTitle5,
            'store_stat_titles' => $storeStatTitles,
            'store_stat_values' => $storeStatValues,
        );

        return response()->json($result, 200);
    }
}
<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], function () {
    Route::get('file/{url}/{name?}', 'FileController@getFile')->name('file');
    Route::get('download/{path}', 'FileController@downloadFile')->name('download');
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/home/project/data', 'HomeController@listProject')->name('home.project.data');
    Route::get('/logout', 'Auth\LogoutController@postLogout')->name('logout');
    
    Route::group(['prefix' => 'profile'], function () {
        Route::post('', 'ProfileController@updateProfile')->name('profile.save');
    });

    Route::group(['prefix' => 'project'], function () {
        Route::get('', 'ProjectController@index')->name('project.index');
        Route::get('data', 'ProjectController@listData')->name('project.data');
        Route::get('create', 'ProjectController@create')->name('project.create');
        Route::get('{uuid}', 'ProjectController@detail')->name('project.detail')->middleware('user.project');
        Route::get('{uuid}/edit', 'ProjectController@edit')->name('project.edit');
        Route::get('delete/{uuid?}', 'ProjectController@delete')->name('project.delete');
        Route::post('store', 'ProjectController@store')->name('project.store');
        Route::get('{uuid}/history', 'ProjectController@listHistoryResult')->name('project.history');
        Route::get('{uuid}/attachment', 'ProjectController@attachmentData')->name('project.attachment');
        Route::post('progress-csi/import', 'ProjectController@importCSIProgress')->name('project.progresscsi.import');
        Route::post('progress/import', 'ProjectController@importProgress')->name('project.progress.import');
        Route::get('{uuid}/progress/regions', 'ProjectController@getRegions')->name('project.progress.regions');
        Route::get('{uuid}/progress/main-dealers', 'ProjectController@getMainDealers')->name('project.progress.maindealers');
        Route::get('{uuid}/progress/brands', 'ProjectController@getBrands')->name('project.progress.brands');
        Route::get('{uuid}/progress/main-dealers/assignment', 'ProjectController@getMainDealerAssignment')->name('project.progress.maindealer.assignments');
        Route::post('{uuid}/progress/main-dealer/{main_dealer_code}/assignment', 'ProjectController@storeMainDealerAssignment')->name('project.progress.maindealer.assignment');
        Route::get('{uuid}/progress/dealers', 'ProjectController@getDealers')->name('project.progress.dealers');
        Route::get('{uuid}/progress/chart', 'ProjectController@getProgressChartData')->name('project.progress.chart');
        Route::get('{uuid}/total/chart', 'ProjectController@getTotalChartData')->name('project.total.chart');
        Route::get('{uuid}/total-csi/chart', 'ProjectController@getCSITotalChartData')->name('project.totalcsi.chart');
        Route::post('{uuid}/total/upload', 'ProjectController@uploadProgressTotal')->name('project.total.upload');
        Route::post('{uuid}/total/delete', 'ProjectController@deleteProgressTotal')->name('project.total.delete');
        Route::get('{uuid}/total/download', 'ProjectController@downloadProgressTotal')->name('project.total.download');
        Route::post('{uuid}/fm/upload', 'ProjectController@fileManagerUpload')->name('project.fm.upload');
        Route::get('{uuid}/fm/files', 'ProjectController@getAllFiles')->name('project.fm.files');
        Route::get('{uuid}/fm/file', 'ProjectController@getFileInfo')->name('project.fm.file');
        Route::post('{uuid}/fm/folder', 'ProjectController@fileManagerNewFolder')->name('project.fm.folder');
        Route::get('{uuid}/fm/folders', 'ProjectController@getAllFolders')->name('project.fm.folders');
        Route::get('{uuid}/fm/file/download', 'ProjectController@fileManagerDownload')->name('project.fm.file.download');
        Route::get('{uuid}/fm/files/download', 'ProjectController@fileManagerMassDownload')->name('project.fm.files.download');
        Route::post('{uuid}/fm/file/delete', 'ProjectController@fileManagerDelete')->name('project.fm.file.delete');
        Route::post('{uuid}/fm/file/rename', 'ProjectController@fileManagerRename')->name('project.fm.file.rename');
        Route::post('{uuid}/fm/files/delete', 'ProjectController@fileManagerMassDelete')->name('project.fm.files.delete');
        Route::post('{uuid}/fm/folder/delete', 'ProjectController@fileManagerFolderDelete')->name('project.fm.folder.delete');
        Route::post('retail/import', 'ProjectRetailController@importRetail')->name('project.retail.import');
        Route::get('{uuid}/retail/progress/province', 'ProjectRetailController@getRetailProgressProvince')->name('project.retail.progress.province');
        Route::get('{uuid}/retail/retail-visited/chart', 'ProjectRetailController@getRetailVisitedChartData')->name('project.retail.retailvisited.chart');
        Route::get('{uuid}/retail/progress-kelurahan/chart', 'ProjectRetailController@getProgressKelurahanChartData')->name('project.retail.progresskelurahan.chart');
        Route::get('{uuid}/retail/achievement/provinces', 'ProjectRetailController@getRetailAchievementProvince')->name('project.retail.achievement.province');
        Route::get('{uuid}/retail/achievement/kabupatens', 'ProjectRetailController@getRetailAchievementKabupaten')->name('project.retail.achievement.kabupaten');
        Route::get('{uuid}/retail/achievement/kecamatans', 'ProjectRetailController@getRetailAchievementKecamatan')->name('project.retail.achievement.kecamatan');
        Route::get('{uuid}/retail/achievement/kelurahans', 'ProjectRetailController@getRetailAchievementKelurahan')->name('project.retail.achievement.kelurahan');
        Route::get('retail/segments', 'ProjectRetailController@getSegment')->name('project.retail.segment');
        Route::get('retail/brands', 'ProjectRetailController@getBrand')->name('project.retail.brand');
        Route::get('{uuid}/retail/retail-segment/chart', 'ProjectRetailController@getRetailSegmentChartData')->name('project.retail.retailsegment.chart');
        Route::get('{uuid}/retail/brand/chart', 'ProjectRetailController@getBrandChartData')->name('project.retail.brand.chart');
        Route::post('interviewer/import', 'ProjectCSIController@importInterviewer')->name('project.interviewer.import');
        Route::get('{uuid}/interviewer/main-dealers', 'ProjectCSIController@getMainDealersInterviewer')->name('project.interviewer.maindealers');
        Route::get('{uuid}/interviewer/ids', 'ProjectCSIController@getIdsInterviewer')->name('project.interviewer.ids');
        Route::get('{uuid}/interviewer/chart', 'ProjectCSIController@getInterviewerChartData')->name('project.interviewer.chart');
        Route::get('{uuid}/interviewer/chart/by-id', 'ProjectCSIController@getInterviewerChartDataByInterviewerId')->name('project.interviewer.chart.by-id');
        Route::post('week/import', 'ProjectCSIController@importWeek')->name('project.week.import');
        Route::get('{uuid}/week/main-dealers', 'ProjectCSIController@getMainDealersWeek')->name('project.week.maindealers');
        Route::get('{uuid}/week/main-week', 'ProjectCSIController@getMainWeek')->name('project.week.mainweek');
        Route::get('{uuid}/week/chart', 'ProjectCSIController@getWeekChartData')->name('project.week.chart');
        Route::get('{uuid}/week/delete', 'ProjectCSIController@getWeekDeleteData')->name('project.week.delete');
        Route::post('{uuid}/map/import', 'ProjectMapController@importMap')->name('project.map.import');
        Route::get('{uuid}/map/provinces', 'ProjectMapController@getMapProvince')->name('project.map.province');
        Route::get('{uuid}/map/kabupatens', 'ProjectMapController@getMapKabupaten')->name('project.map.kabupaten');
        Route::get('{uuid}/map/kecamatans', 'ProjectMapController@getMapKecamatan')->name('project.map.kecamatan');
        Route::get('{uuid}/map/kelurahans', 'ProjectMapController@getMapKelurahan')->name('project.map.kelurahan');
        Route::get('{uuid}/map/segments', 'ProjectMapController@getMapSegment')->name('project.map.segment');
        Route::get('{uuid}/map/brands', 'ProjectMapController@getMapBrand')->name('project.map.brand');
        Route::get('{uuid}/map/filter', 'ProjectMapController@getMapFilteredData')->name('project.map.filter');
        Route::get('map/areaInfo', 'ProjectMapController@getGMapAreaInfo')->name('project.map.areainfo');
        Route::post('{uuid}/map/upload-image', 'ProjectMapController@uploadImage')->name('project.map.image-upload');
        Route::get('{uuid}/map/image/{filename}/thumbnail', 'ProjectMapController@readMapImageThumbnail')->name('project.map.image.thumbnail');
        Route::get('{uuid}/map/image/{filename}', 'ProjectMapController@readMapImage')->name('project.map.image');
        Route::post('{uuid}/map/delete', 'ProjectMapController@deleteMapData')->name('project.map.delete');
        Route::get('{uuid}/map/legend', 'ProjectMapController@getLegend')->name('project.map.legend');
        Route::get('{uuid}/retail/information', 'ProjectRetailController@loadRetailInformation')->name('project.retail.information');
        Route::post('{uuid}/progress-cqi/import', 'ProjectCQIController@importCQIProgress')->name('project.progresscqi.import');
        Route::get('{uuid}/progress-cqi/motorcycle-type', 'ProjectCQIController@getCQIMotorcycleType')->name('project.progresscqi.mtype');
        Route::get('{uuid}/progress-cqi/motorcycle-model', 'ProjectCQIController@getCQIMotorcycleModel')->name('project.progresscqi.mmodel');
        Route::get('{uuid}/total-cqi/chart', 'ProjectCQIController@getCQITotalChartData')->name('project.totalcqi.chart');
    });

    Route::group(['prefix' => 'report'], function () {
        Route::get('{project_id}/data', 'ReportController@listData')->name('report.data');
        Route::get('{project_id}/generate/{report_id?}', 'ReportController@generate')->name('report.generate');
        Route::get('{project_id}/{report_id}/summary', 'ReportSummaryController@summaryTable')->name('report.summary');

        Route::post('{project_id}/generate', 'ReportController@save')->name('report.save');
        Route::post('{report_id}/{project_id}/filter', 'ReportController@saveFilter')->name('report.save.filter');
        Route::post('{report_id}/{project_id}/filter/summary', 'ReportSummaryController@saveFilterSummary')->name('report.save.filter.summary');
        Route::post('{report_id}/trendline', 'ReportController@saveTrendline')->name('report.save.trendline');
        Route::post('{report_id}/summary', 'ReportController@saveSummary')->name('report.save.summary');
        Route::post('{report_id}/showvalues', 'ReportController@saveShowValues')->name('report.save.showvalues');
        Route::post('{report_id}/{project_id}/summary_table', 'ReportController@saveSummaryTable')->name('report.save.summary_table');
        
        Route::get('{project_id?}/delete/{report_id?}', 'ReportController@delete')->name('report.delete');
        Route::get('{project_id?}/excel/{report_id?}', 'ReportController@exportToExcel')->name('report.excel');
        Route::get('{project_id?}/pdf/{report_id?}', 'ReportController@exportToPDF')->name('report.pdf');
        Route::get('{project_id?}/ppt/{report_id?}', 'ReportController@exportToPPT')->name('report.ppt');

        Route::get('{project_id}', 'ReportController@questionList')->name('report.question');
        Route::get('{project_id}/{question_id?}', 'ReportController@resultList')->name('report.question.answer');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('data', 'UserController@listData')->name('user.data');
        Route::get('delete/{user?}', 'UserController@delete')->name('user.delete');
        Route::get('block/{user?}', 'UserController@block')->name('user.block');
        Route::get('unblock/{user?}', 'UserController@unblock')->name('user.unblock');
    });
    Route::resource('user', 'UserController');


    Route::group(['prefix' => 'company'], function () {
        Route::get('data', 'CompanyController@listData')->name('company.data');
        Route::get('{uuid}/view', 'CompanyController@show')->name('company.view');
        Route::get('delete/{company?}', 'CompanyController@delete')->name('company.delete');
        Route::get('{uuid?}/project', 'CompanyController@getCompanyProject')->name('company.project');

        Route::get('{uuid}/project/data', 'CompanyController@listProjectCompany')->name('company.project.data');
        Route::get('{uuid}/user/data', 'CompanyController@listCompanyUser')->name('company.user.data');
    });
    
    Route::resource('company', 'CompanyController');
    
});

Auth::routes();
Route::get('login', 'Auth\LoginController@index')->name('login');
Route::post('login', 'Auth\LoginController@postLogin')->name('login.post');

// Route::get('/home', 'HomeController@index')->name('home');

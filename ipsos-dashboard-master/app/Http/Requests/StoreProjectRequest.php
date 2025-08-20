<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Factory as ValidationFactory;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class StoreProjectRequest extends FormRequest
{
    public function __construct(ValidationFactory $validationFactory)
    {
        $matchProjectField = [
            'project_code'          => 'code',
            'project_name'          => 'name',
            'start_date_timestamp'  => 'start_date',
            'end_date_timestamp'    => 'finish_date',
            'project_description'   => 'description',
            'methodology'           => 'methodology',
            'project_objective'     => 'objective',
            'target_respondent'     => 'respondent',
            'area_coverage'         => 'coverage',
            'project_timeline'      => 'timeline',
        ];
        $validationFactory->extend(
            'valid_project_data',
            function ($attribute, $value, $parameters) use ($matchProjectField) {
                if ($value->getClientOriginalExtension() != 'xls' && $value->getClientOriginalExtension() != 'xlsx') {
                    return $value->getClientOriginalExtension() == 'xls' || $value->getClientOriginalExtension() == 'xlsx';
                }

                $reader = ReaderFactory::create(Type::XLSX); // for XLSX files

                $reader->open($value);

                foreach ($reader->getSheetIterator() as $key => $sheet) {
                    if ($key == 1) {
                        foreach ($sheet->getRowIterator() as $row) {
                            if ($row[0]) {
                                return array_key_exists($row[0], $matchProjectField);
                            }
                        }
                    }
                }

                $reader->close();
            },
            'Import project file sheet [project data] doesn\'t match the field, please check again.'
        );

        $validationFactory->extend(
            'valid_project_question',
            function ($attribute, $value, $parameters) {
                if ($value->getClientOriginalExtension() != 'xls' && $value->getClientOriginalExtension() != 'xlsx') {
                    return $value->getClientOriginalExtension() == 'xls' || $value->getClientOriginalExtension() == 'xlsx';                    
                }

                $reader = ReaderFactory::create(Type::XLSX); // for XLSX files

                $reader->open($value);

                foreach ($reader->getSheetIterator() as $key => $sheet) {
                    if ($key == 2) {
                        foreach ($sheet->getRowIterator() as $row) {
                            if ($row[0]) {
                                return preg_match('/^Q[0-9]/', $row[0]) || preg_match('/^Q[0-9]_[0-9]/', $row[0]);
                            }
                        }
                    }
                }
                
                $reader->close();
            },
            'Import project file sheet [question answer data] doesn\'t match the field, please check again.'
        );

        $validationFactory->extend(
            'valid_result_question',
            function ($attribute, $value, $parameters) {
                
                if ($value->getClientOriginalExtension() != 'xls' && $value->getClientOriginalExtension() != 'xlsx') {
                    return $value->getClientOriginalExtension() == 'xls' || $value->getClientOriginalExtension() == 'xlsx';                    
                }

                $reader = ReaderFactory::create(Type::XLSX); // for XLSX files

                $reader->open($value);

                foreach ($reader->getSheetIterator() as $key => $sheet) {
                    if ($key == 1) {
                        foreach ($sheet->getRowIterator() as $row => $value) {
                            if ($row == 1) {
                                foreach ($value as $val) {
                                    if ($val) {
                                        return preg_match('/^Q[0-9]/', $val);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $reader->close();
            },
            'Import result file sheet doesn\'t match the field, please check again.'
        );
        $validationFactory->extend(
            'valid_extension',
            function ($attribute, $value, $parameters) {
                return $value->getClientOriginalExtension() == 'xls' || $value->getClientOriginalExtension() == 'xlsx';
            },
            'Valid extension for import is: xls, xlsx'
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'company_id' => 'required',
            'type' => 'required',
            // 'name' => 'required',
        ];

        $rules['import_project'] = 'required_with:import_result|file|valid_extension|valid_project_data|valid_project_question';
        $rules['import_result'] = 'required_with:import_project|file|valid_extension|valid_result_question';
        if ($this->uuid == 'add') {
            $rules['import_project'] = 'required|file|valid_extension|valid_project_data|valid_project_question';
            $rules['import_result'] = 'required|file|valid_extension|valid_result_question';
        }

        return $rules;
    }
}

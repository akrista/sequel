<?php

declare(strict_types=1);

namespace Akrista\Sequel\Http\Requests;

use Akrista\Sequel\Database\DatabaseTraverser;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SequelDatabaseRequest
 *
 * @property mixed $database
 * @property mixed $table
 * @property mixed $model
 * @property mixed $qualifiedName
 */
final class SequelDatabaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'database' => 'string',
            'table' => 'string',
            'qualifiedName' => 'string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'database.required' => 'Database name is required',
            'table.required' => 'Table name is required',
            'qualifiedName.required' => 'Could not construct sensible table name.',
        ];
    }

    /**
     * Get the validator instance for the request.
     *
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $request = $this->validationData();

        try {
            $request['database'] = $this->route('database');
            $request['table'] = $this->route('table');

            $request['qualifiedName'] =
                $request['database'] . '.' . $request['table'];

            $request['model'] = app(DatabaseTraverser::class)->getModel(
                $request['table']
            )['model'];
        } catch (Exception) {
            return parent::getValidatorInstance();
        }

        $this->getInputSource()->replace($request);

        return parent::getValidatorInstance();
    }
}

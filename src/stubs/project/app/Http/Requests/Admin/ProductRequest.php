<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ProductRequest extends FormRequest
{

    private $mergeReturn = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $chProduct = request()->route('chProduct');
        $rules = [
            'categories' => ['required', 'array', function($attribute, $value, $fail) {
                if(Category::where('site_id', app()->make("Site")->id)->whereIn('id', $value)->count() != count($value)) {
                    return $fail(trans('admin/products/validation.categories.not_found'));
                }
            }],
            'add.name' => 'required|max:255',
            'add.description' => 'nullable',
            'pictures' => ['nullable', 'array',  function($attribute, $value, $fail) use ($chProduct) {
                $type = 'pictures';
                $inputName = "product[{$type}]";
                $attachIds = array();
//                $value = [ $value ]; //to can just copy/paste
                foreach($value as $index => $attachTypeId) {
                    $attachIds[(int)str_replace(["{$inputName}_", "{$type}_"], '', $attachTypeId) ] = $index;
                }
                $return = \App\Models\Attachment::where([
                    'attachable_id' => null,
                    'attachable_type' => null,
                    'session_id' => session()->getId(),
                    'type' => $inputName
                ])->whereIn('id', array_keys($attachIds))->get()->keyBy('id');

                $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/gif'];
                foreach($return as $attach) {
                    //make some additional validation - may use new rules key pictures.*, too
                    if(!in_array(
                        \Illuminate\Support\Facades\Storage::disk( $attach->disk )->mimeType($attach->getFilePath()),
                        $allowedMimeTypes
                    )) {
                        return $fail( trans('admin/products/validation.products.pictures.*.mime') );
                    }
                }
                if($chProduct) {
                    $return = $return->union( \App\Models\Attachment::where([
                        'attachable_id' => $chProduct->id,
                        'attachable_type' => get_class($chProduct),
                        'session_id' => null,
                        'type' => $type
                    ])->whereIn('id', array_keys($attachIds))->get()->keyBy('id') );
                }
                //sorting
                $this->mergeReturn['pictures'] = collect();
                foreach($attachIds as $attachId => $index) {
                    if(!isset($return[$attachId])) continue;
                    $this->mergeReturn['pictures']->push( $return[$attachId] );
                }
            }],
            'active' => 'boolean',
        ];
        $rules = array_merge( $rules, \App\Models\Uri::validationRules('product', $chProduct));

        // @HOOK_REQUEST_RULES

        return $rules;
    }

    public function messages() {
        $return = array_merge(
            Arr::dot((array)trans('admin/uriable/uriable.validation')),
            Arr::dot((array)trans('admin/products/validation'))
        );

        // @HOOK_REQUEST_MESSAGES

        return $return;
    }

    public function validationData() {
        $inputBag = 'product';
        $this->errorBag = $inputBag;
        $inputs = $this->all();
        if(!isset($inputs[$inputBag])) {
            throw new ValidationException(trans('admin/products/validation.no_inputs') );
        }
        $inputs[$inputBag]['active'] = isset($inputs[$inputBag]['active']);

        // @HOOK_REQUEST_PREPARE

        $this->replace($inputs);
        request()->replace($inputs); //global request should be replaced, too
        return $inputs[$inputBag];
    }

    public function validated($key = null, $default = null) {
        $validatedData = parent::validated($key, $default);

        // @HOOK_REQUEST_VALIDATED

        if(is_null($key)) {
            \App\Models\Uri::validated($validatedData);

            // @HOOK_REQUEST_AFTER_VALIDATED

            return array_merge($validatedData, $this->mergeReturn);
        }

        // @HOOK_REQUEST_AFTER_VALIDATED_KEY

        return $validatedData;
    }
}

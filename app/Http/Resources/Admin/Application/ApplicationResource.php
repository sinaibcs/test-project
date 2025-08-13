<?php

namespace App\Http\Resources\Admin\Application;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [parent::toArray($request), ...$this->getAdditionalData()];
    }

    private function getAdditionalData(){
        if(request()->has('additionalFieldIds')){
            $values = $this->applicationAllowanceValues()->whereIn('allow_addi_fields_id', request()->additionalFieldIds)->with('additionalFieldValue','additionalField')->get()??[];
            $data = [];
            // Log::debug(json_encode($values));
            foreach($values as $value){
                $key = str_replace(' ', '_', strtolower($value->additionalField->name_en));
                if($value->additionalFieldValue){
                    $data[$key] = $value->additionalFieldValue;
                }else{
                    $data[$key] = $value->value;
                }
            }
            return $data;
        }

        return [];
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniquePaymentProcessor implements ValidationRule
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table('payroll_payment_processors')
            ->join('payroll_payment_processor_areas', 'payroll_payment_processors.id', '=', 'payroll_payment_processor_areas.payment_processor_id')
            ->where('payroll_payment_processors.processor_type', $this->request->processor_type)
            ->where('payroll_payment_processors.name_en', $this->request->name_en)
            ->where('payroll_payment_processors.name_bn', $this->request->name_bn)
            ->where('payroll_payment_processor_areas.division_id', $this->request->division)
            ->where('payroll_payment_processor_areas.district_id', $this->request->district)
            ->where('payroll_payment_processor_areas.location_type', $this->request->location_type);

        switch ($this->request->location_type) {
            case 1:
                $query->where('payroll_payment_processor_areas.district_pourashava_id', $this->request->district_pourashava);
                break;
            case 2:
                $query->where('payroll_payment_processor_areas.upazila_id', $this->request->upazila)
                      ->where('payroll_payment_processor_areas.union_id', $this->request->union);
                break;
            case 3:
                $query->where('payroll_payment_processor_areas.city_corp_id', $this->request->city_corporation)
                      ->where('payroll_payment_processor_areas.thana_id', $this->request->thana);
                break;
            default:
                $fail('Invalid location type.');
                return;
        }

        if ($query->exists()) {
            $fail('The combination of processor type, names, and location already exists.');
        }
    }
}

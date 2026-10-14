<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_type' => ['sometimes', Rule::in(['room', 'house', 'residential_plot', 'commercial_plot', 'farm', 'commercial_space', 'office'])],
            'transaction_type' => ['sometimes', Rule::in(['rent', 'sale'])],
            'room_type_id' => ['nullable', 'required_if:property_type,room', 'exists:room_types,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:5000'],
            'rental_purpose' => ['nullable', 'string', 'max:100'],
            'room_size' => ['nullable', 'string', 'max:50'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathroom_type' => ['nullable', 'string', 'max:100'],
            'bathroom_sharing' => ['nullable', 'boolean'],
            'kitchen_availability' => ['nullable', 'string', 'max:100'],
            'indoor_bathroom' => ['nullable', 'boolean'],
            'other_features' => ['nullable', 'string', 'max:5000'],
            'region_id' => ['sometimes', 'required', 'exists:regions,id'],
            'district_id' => ['sometimes', 'required', 'exists:districts,id'],
            'ward_id' => ['nullable', 'exists:wards,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'monthly_rent' => ['nullable', 'required_if:transaction_type,rent', 'prohibited_if:transaction_type,sale', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'required_if:transaction_type,sale', 'prohibited_if:transaction_type,rent', 'numeric', 'min:0'],
            'installment_allowed' => ['nullable', 'boolean'],
            'deposit' => ['nullable', 'exclude_if:transaction_type,sale', 'numeric', 'min:0'],
            'payment_frequency' => ['nullable', 'string', 'max:50'],
            'minimum_rental_period' => ['nullable', 'integer', 'min:1'],
            'negotiable' => ['nullable', 'boolean'],
            'additional_charges' => ['nullable', 'string', 'max:2000'],
            'contact_name' => ['sometimes', 'required', 'string', 'max:255'],
            'preferred_contact_method' => ['sometimes', 'required', 'in:phone,whatsapp'],
            'contact_phone' => ['sometimes', 'required', 'string', 'max:30'],
            'whatsapp_phone' => ['nullable', 'required_if:preferred_contact_method,whatsapp', 'string', 'max:30'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,gif,avif,mp4,webm,ogg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.max' => 'Each image or video must be no larger than 2 MB.',
        ];
    }
}

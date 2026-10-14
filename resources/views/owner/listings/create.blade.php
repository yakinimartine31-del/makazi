@extends('layouts.dashboard')

@section('title', 'Create Listing')

@section('content')
<link rel="stylesheet" href="{{ asset('css/owner-listing-detail.css') }}">
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Create New Listing</h1>
            <a href="{{ route('owner.listings.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left mr-1"></i>Back to Listings
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('owner.listings.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="property_type">Property category</label>
                                <select id="property_type" name="property_type" class="form-control" required>
                                    @foreach (['room' => 'Room', 'house' => 'House / Apartment', 'residential_plot' => 'Residential plot', 'commercial_plot' => 'Commercial plot', 'farm' => 'Farm', 'commercial_space' => 'Business space', 'office' => 'Office'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('property_type', 'room') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('property_type')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="transaction_type">Listing type</label>
                                <select id="transaction_type" name="transaction_type" class="form-control" required>
                                    <option value="rent" @selected(old('transaction_type', 'rent') === 'rent')>For rent</option>
                                    <option value="sale" @selected(old('transaction_type') === 'sale')>For sale</option>
                                </select>
                                @error('transaction_type')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3" id="room-type-field">
                            <x-input-label for="room_type_id" :value="__('Room Type')" />
                            <select id="room_type_id" name="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                <option value="">Select room type</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('room_type_id', $room->room_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="form-control"
                                :value="old('title', $room->title ?? '')" required />
                            @error('title')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $room->description ?? '') }}</textarea>
                            @error('description')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input-label for="rental_purpose" :value="__('Rental Purpose')" />
                                <input id="rental_purpose" name="rental_purpose" type="text"
                                    class="form-control @error('rental_purpose') is-invalid @enderror"
                                    value="{{ old('rental_purpose', $room->rental_purpose ?? '') }}"
                                    placeholder="e.g. Long-term, Short-term">
                                @error('rental_purpose')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <x-input-label for="room_size" :value="__('Room Size')" />
                                <input id="room_size" name="room_size" type="text"
                                    class="form-control @error('room_size') is-invalid @enderror"
                                    value="{{ old('room_size', $room->room_size ?? '') }}"
                                    placeholder="e.g. 12 sqm">
                                @error('room_size')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-4">
                                <x-input-label for="bedrooms" :value="__('Bedrooms')" />
                                <input id="bedrooms" name="bedrooms" type="number" min="0"
                                    class="form-control @error('bedrooms') is-invalid @enderror"
                                    value="{{ old('bedrooms', $room->bedrooms ?? '') }}">
                                @error('bedrooms')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <x-input-label for="bathroom_type" :value="__('Bathroom Type')" />
                                <input id="bathroom_type" name="bathroom_type" type="text"
                                    class="form-control @error('bathroom_type') is-invalid @enderror"
                                    value="{{ old('bathroom_type', $room->bathroom_type ?? '') }}"
                                    placeholder="e.g. Shared, Private">
                                @error('bathroom_type')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="bathroom_sharing"
                                        id="bathroom_sharing" value="1"
                                        {{ old('bathroom_sharing', $room->bathroom_sharing ?? false) ? 'checked' : '' }}>
                                    <x-input-label for="bathroom_sharing" :value="__('Bathroom Sharing')" class="mb-0" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-input-label for="kitchen_availability" :value="__('Kitchen Availability')" />
                            <input id="kitchen_availability" name="kitchen_availability" type="text"
                                class="form-control @error('kitchen_availability') is-invalid @enderror"
                                value="{{ old('kitchen_availability', $room->kitchen_availability ?? '') }}"
                                placeholder="e.g. Full, Partial, None">
                            @error('kitchen_availability')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="indoor_bathroom"
                                    id="indoor_bathroom" value="1"
                                    {{ old('indoor_bathroom', $room->indoor_bathroom ?? false) ? 'checked' : '' }}>
                                <x-input-label for="indoor_bathroom" :value="__('Indoor Bathroom')" class="mb-0" />
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-input-label for="other_features" :value="__('Other Features')" />
                            <textarea id="other_features" name="other_features" rows="3"
                                class="form-control @error('other_features') is-invalid @enderror"
                                placeholder="Additional amenities or features">{{ old('other_features', $room->other_features ?? '') }}</textarea>
                            @error('other_features')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <x-input-label for="region_id" :value="__('Region')" />
                            <select id="region_id" name="region_id"
                                class="form-select @error('region_id') is-invalid @enderror" required>
                                <option value="">Select region</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}"
                                        {{ old('region_id', $room->region_id ?? '') == $region->id ? 'selected' : '' }}
                                        data-regions>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('region_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="district_id" :value="__('District')" />
                            <select id="district_id" name="district_id"
                                class="form-select @error('district_id') is-invalid @enderror" required>
                                <option value="">Select district</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}"
                                        {{ old('district_id', $room->district_id ?? '') == $district->id ? 'selected' : '' }}
                                        data-region="{{ $district->region_id }}">
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('district_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="ward_id" :value="__('Ward')" />
                            <select id="ward_id" name="ward_id"
                                class="form-select @error('ward_id') is-invalid @enderror">
                                <option value="">Select ward</option>
                                @foreach($wards as $ward)
                                    <option value="{{ $ward->id }}"
                                        {{ old('ward_id', $room->ward_id ?? '') == $ward->id ? 'selected' : '' }}
                                        data-district="{{ $ward->district_id }}">
                                        {{ $ward->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ward_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="area_id" :value="__('Area')" />
                            <select id="area_id" name="area_id"
                                class="form-select @error('area_id') is-invalid @enderror">
                                <option value="">Select area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}"
                                        {{ old('area_id', $room->area_id ?? '') == $area->id ? 'selected' : '' }}
                                        data-ward="{{ $area->ward_id }}">
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('area_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="landmark" :value="__('Landmark')" />
                            <input id="landmark" name="landmark" type="text"
                                class="form-control @error('landmark') is-invalid @enderror"
                                value="{{ old('landmark', $room->landmark ?? '') }}"
                                placeholder="Nearby landmark or address">
                            @error('landmark')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="location-map-create" :value="__('Pin the room location on the map')" />
                            <p class="text-muted small">Click the map or drag the pin to set the approximate location.</p>
                            <div id="location-map-create" data-map-picker
                                data-latitude-input="latitude-create"
                                data-longitude-input="longitude-create"
                                data-status="location-map-create-status"
                                data-map-link="location-google-map-link-create"
                                class="border rounded"
                                style="height: 350px;"></div>
                            <p id="location-map-create-status" class="small text-muted mt-2 mb-0" aria-live="polite">Loading map…</p>
                            <div class="mt-3">
                                <a id="location-google-map-link-create" data-map-link class="btn btn-sm btn-outline-primary mt-2"
                                    href="https://www.google.com/maps/search/?api=1&amp;query=Tanzania"
                                    target="_blank" rel="noopener noreferrer">Open location in Google Maps</a>
                            </div>
                            <input id="latitude-create" name="latitude" type="hidden" value="{{ old('latitude', $room->latitude ?? '') }}">
                            <input id="longitude-create" name="longitude" type="hidden" value="{{ old('longitude', $room->longitude ?? '') }}">
                            @error('latitude')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                            @error('longitude')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Amenities</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($amenities as $amenity)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]"
                                            id="amenity_{{ $amenity->id }}"
                                            value="{{ $amenity->id }}"
                                            {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="amenity_{{ $amenity->id }}">
                                            {{ $amenity->name }}
                                        </label>
                                    </div>
                                </div>
                             @endforeach
                         </div>
                         @error('amenities')
                             <div class="mt-2">
                                 <x-input-error :messages="$message" />
                             </div>
                         @enderror
                         @error('amenities.*')
                             <div class="mt-2">
                                 <x-input-error :messages="$message" />
                             </div>
                         @enderror
                     </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Room Images</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Select multiple photos at once (use Ctrl or Command while selecting). Upload up to 10 photos or videos, each no larger than 2 MB. The first media item will be the cover photo.</p>
                        <label for="images" class="font-weight-bold">Add multiple room photos or videos</label>
                        <input type="file" name="images[]" id="images" class="form-control" multiple
                            accept="image/jpeg,image/png,image/gif,image/avif,video/mp4,video/webm,video/ogg"
                            data-max-file-size="2097152" data-max-files="10" data-multiple-media>
                        <p class="small text-muted mt-2 mb-0" data-media-selection-status aria-live="polite">No media selected.</p>
                        <div class="owner-media-preview-grid" data-media-preview aria-live="polite"></div>
                         @error('images.*')
                             <x-input-error :messages="$message" class="mt-2" />
                         @enderror
                         @error('images')
                             <x-input-error :messages="$message" class="mt-2" />
                         @enderror
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pricing</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3" data-rent-pricing>
                            <x-input-label for="monthly_rent" :value="__('Monthly Rent (TZS)')" />
                            <input id="monthly_rent" name="monthly_rent" type="number" step="0.01" min="0"
                                class="form-control @error('monthly_rent') is-invalid @enderror"
                                value="{{ old('monthly_rent', $room->monthly_rent ?? '') }}">
                            @error('monthly_rent')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <input type="hidden" name="installment_allowed" value="0">
                        <div class="mb-3" data-sale-pricing>
                            <x-input-label for="sale_price" :value="__('Sale Price (TZS)')" />
                            <input id="sale_price" name="sale_price" type="number" step="0.01" min="0"
                                class="form-control @error('sale_price') is-invalid @enderror"
                                value="{{ old('sale_price', $room->sale_price ?? '') }}">
                            @error('sale_price')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="installment_allowed"
                                    id="installment_allowed" value="1" @checked(old('installment_allowed'))>
                                <label class="form-check-label" for="installment_allowed">Accept installment payments</label>
                            </div>
                        </div>

                        <div class="mb-3" data-rent-pricing>
                            <x-input-label for="deposit" :value="__('Deposit (TZS)')" />
                            <input id="deposit" name="deposit" type="number" step="0.01" min="0"
                                class="form-control @error('deposit') is-invalid @enderror"
                                value="{{ old('deposit', $room->deposit ?? '') }}">
                            @error('deposit')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3" data-rent-pricing>
                            <x-input-label for="payment_frequency" :value="__('Payment Frequency')" />
                            <select id="payment_frequency" name="payment_frequency"
                                class="form-select @error('payment_frequency') is-invalid @enderror">
                                <option value="">Select frequency</option>
                                <option value="monthly" {{ old('payment_frequency', $room->payment_frequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('payment_frequency', $room->payment_frequency ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('payment_frequency', $room->payment_frequency ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('payment_frequency')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3" data-rent-pricing>
                            <x-input-label for="minimum_rental_period" :value="__('Minimum Rental Period (months)')" />
                            <input id="minimum_rental_period" name="minimum_rental_period" type="number" min="1"
                                class="form-control @error('minimum_rental_period') is-invalid @enderror"
                                value="{{ old('minimum_rental_period', $room->minimum_rental_period ?? '') }}">
                            @error('minimum_rental_period')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="negotiable"
                                    id="negotiable" value="1"
                                    {{ old('negotiable', $room->negotiable ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="negotiable">
                                    {{ __('Negotiable') }}
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <x-input-label for="additional_charges" :value="__('Additional Charges')" />
                            <textarea id="additional_charges" name="additional_charges" rows="2"
                                class="form-control @error('additional_charges') is-invalid @enderror"
                                placeholder="e.g. Service fee, Maintenance">{{ old('additional_charges', $room->additional_charges ?? '') }}</textarea>
                            @error('additional_charges')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contact Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <x-input-label for="contact_name" :value="__('Contact Name')" />
                            <input id="contact_name" name="contact_name" type="text"
                                class="form-control @error('contact_name') is-invalid @enderror"
                                value="{{ old('contact_name') }}" required>
                            @error('contact_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="contact_phone" :value="__('Phone Number')" />
                            <input id="contact_phone" name="contact_phone" type="tel"
                                class="form-control @error('contact_phone') is-invalid @enderror"
                                value="{{ old('contact_phone') }}" required>
                            @error('contact_phone')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-3">
                            <x-input-label for="whatsapp_phone" :value="__('WhatsApp Number (optional)')" />
                            <input id="whatsapp_phone" name="whatsapp_phone" type="tel"
                                class="form-control @error('whatsapp_phone') is-invalid @enderror"
                                value="{{ old('whatsapp_phone') }}">
                            @error('whatsapp_phone')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="mb-0">
                            <x-input-label for="preferred_contact_method" :value="__('Preferred Contact Method')" />
                            <select id="preferred_contact_method" name="preferred_contact_method"
                                class="form-control @error('preferred_contact_method') is-invalid @enderror" required>
                                <option value="">Select contact method</option>
                                <option value="phone" {{ old('preferred_contact_method') === 'phone' ? 'selected' : '' }}>Phone</option>
                                <option value="whatsapp" {{ old('preferred_contact_method') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            </select>
                            @error('preferred_contact_method')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save mr-2"></i>                        Save as Draft
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateListingFields() {
        var isSale = document.getElementById('transaction_type').value === 'sale';
        var isRoom = document.getElementById('property_type').value === 'room';

        document.querySelectorAll('[data-rent-pricing]').forEach(function(field) {
            field.hidden = isSale;
            field.querySelectorAll('input, select, textarea').forEach(function(input) {
                input.disabled = isSale;
                if (input.id === 'monthly_rent') input.required = !isSale;
            });
        });
        document.querySelectorAll('[data-sale-pricing]').forEach(function(field) {
            field.hidden = !isSale;
            field.querySelectorAll('input, select, textarea').forEach(function(input) {
                input.disabled = !isSale;
                if (input.id === 'sale_price') input.required = isSale;
            });
        });
        document.getElementById('room-type-field').hidden = !isRoom;
        document.getElementById('room_type_id').disabled = !isRoom;
        document.getElementById('room_type_id').required = isRoom;
    }

    document.getElementById('transaction_type').addEventListener('change', updateListingFields);
    document.getElementById('property_type').addEventListener('change', updateListingFields);
    updateListingFields();

    function filterByData(selectId, targetSelectId, dataAttr) {
        var selectedValue = document.getElementById(selectId).value;
        var options = document.querySelectorAll('#' + targetSelectId + ' option[data-' + dataAttr + ']');
        var allOption = document.querySelector('#' + targetSelectId + ' option[value=""]');

        if (allOption) allOption.style.display = '';

        options.forEach(function(opt) {
            if (selectedValue == '' || opt.getAttribute('data-' + dataAttr) == selectedValue) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    document.getElementById('region_id').addEventListener('change', function() {
        filterByData('region_id', 'district_id', 'region');
        document.getElementById('district_id').value = '';
        document.getElementById('district_id').dispatchEvent(new Event('change'));
    });

    document.getElementById('district_id').addEventListener('change', function() {
        filterByData('district_id', 'ward_id', 'district');
        document.getElementById('ward_id').value = '';
        document.getElementById('ward_id').dispatchEvent(new Event('change'));
    });

    document.getElementById('ward_id').addEventListener('change', function() {
        filterByData('ward_id', 'area_id', 'ward');
        document.getElementById('area_id').value = '';
    });

    // Initial filtering
    filterByData('region_id', 'district_id', 'region');
    filterByData('district_id', 'ward_id', 'district');
    filterByData('ward_id', 'area_id', 'ward');
});
</script>
@endsection

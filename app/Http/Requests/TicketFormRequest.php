<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketFormRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->ticketDetailsRules(),
            $this->tableDetailsRules(),
            $this->cookoutDetailRules(),
            $this->wellnessDetailRules()
        );
    }

    /**
     * Ticket Details Rules
     */
    public function ticketDetailsRules(): array
    {
        return [
            'id' => 'nullable|exists:tickets,id',
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'ticket_type' => 'required|string',
            'description' => 'nullable|string',
            'has_table' => 'nullable|in:yes,no',
            'is_free' => 'nullable|in:yes,no',
            'price' => 'nullable|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'tickets_per_attendee' => 'nullable|integer|min:0',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after_or_equal:sale_start',
            'sale_start_time' => 'nullable|string',
            'sale_end_time' => 'nullable|string',
            'sections' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
            'drink_addons' => 'nullable|array',
            'has_drink_addons' => 'nullable|in:yes,no',
            'commMode' => 'nullable|string|in:pct,flat,none',
            'commission' => 'nullable|numeric|min:0',
            'commFlat' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Table Details Rules
     */
    public function tableDetailsRules(): array
    {
        return [
            'table_price' => 'nullable|numeric|min:0',
            'table_capacity' => 'nullable|integer|min:0',
            'sections' => 'nullable|array',
            'main_bottles' => 'nullable|array',
            'main_bottles.*.name' => 'string|max:255',
            'main_bottles.*.qty' => 'string|max:255',
            'main_bottles.*.price' => 'string|max:255',
            'chasers_or_mixers' => 'nullable|array',
            'chasers_or_mixers.*.name' => 'string|max:255',
            'chasers_or_mixers.*.qty' => 'string|max:255',
            'chasers_or_mixers.*.price' => 'string|max:255',
            'water_options' => 'nullable|array',
            'water_options.*.name' => 'string|max:255',
            'water_options.*.qty' => 'string|max:255',
            'water_options.*.price' => 'string|max:255',
            'package_id' => 'nullable',
        ];
    }

    /**
     * cookout details rules
     */
    public function cookoutDetailRules(): array
    {
        return [
            'cookout' => 'nullable|array',
            'cookout.includeFood' => 'nullable|in:yes,no',
            'cookout.cuisinePreset' => 'nullable|string',
            'cookout.proteins' => 'nullable|array',
            'cookout.proteins.*.name' => 'nullable|string',
            'cookout.proteins.*.qty' => 'nullable|numeric|min:0',
            'cookout.proteins.*.mode' => 'nullable|in:included,addon',
            'cookout.proteins.*.price' => 'nullable|numeric|min:0',
            'cookout.sides' => 'nullable|array',
            'cookout.sides.*' => 'string',
            'cookout.drinks' => 'nullable|array',
            'cookout.drinks.*.name' => 'nullable|string',
            'cookout.drinks.*.qty' => 'nullable|numeric|min:0',
            'cookout.drinks.*.mode' => 'nullable|in:included,addon',
            'cookout.drinks.*.price' => 'nullable|numeric|min:0',
            'cookout.customSides' => 'nullable|array',
            'cookout.customSides.*.name' => 'nullable|string',
            'cookout.customSides.*.checked' => 'nullable|boolean',
            'cookout.customProteins' => 'nullable|array',
            'cookout.customProteins.*.name' => 'nullable|string',
            'cookout.customProteins.*.checked' => 'nullable|boolean',
            'cookout.customProteins.*.qty' => 'nullable|numeric|min:0',
            'cookout.customProteins.*.mode' => 'nullable|in:included,addon',
            'cookout.customProteins.*.price' => 'nullable|numeric|min:0',
            'cookout.manualAddons' => 'nullable|array',
            'cookout.manualAddons.*.name' => 'nullable|string',
            'cookout.manualAddons.*.price' => 'nullable|numeric|min:0',
            'cookout.manualAddons.*.qty' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Wellness Details Rules
     */
    public function wellnessDetailRules(): array
    {
        return [
            'wellness' => 'nullable|array',
            'wellness.includeService' => 'nullable|in:yes,no',
            'wellness.preset' => 'nullable|string',
            'wellness.services' => 'nullable|array',
            'wellness.services.*.name' => 'nullable|string',
            'wellness.services.*.mode' => 'nullable|in:included,addon',
            'wellness.services.*.price' => 'nullable|numeric|min:0',
            'wellness.services.*.duration' => 'nullable|numeric|min:0',
            'wellness.manualAddons' => 'nullable|array',
            'wellness.manualAddons.*.name' => 'nullable|string',
            'wellness.manualAddons.*.price' => 'nullable|numeric|min:0',
            'wellness.manualAddons.*.qty' => 'nullable|numeric|min:0',
            'wellness.customServices' => 'nullable|array',
            'wellness.customServices.*.name' => 'nullable|string',
            'wellness.customServices.*.checked' => 'nullable|boolean',
            'wellness.customServices.*.mode' => 'nullable|in:included,addon',
            'wellness.customServices.*.price' => 'nullable|numeric|min:0',
            'wellness.customServices.*.duration' => 'nullable|numeric|min:0',
            'wellness.booking' => 'nullable|array',
            'wellness.booking.duration' => 'nullable|numeric|min:0',
            'wellness.booking.buffer' => 'nullable|numeric|min:0',
            'wellness.booking.maxPerSlot' => 'nullable|numeric|min:1',
            'wellness.booking.mode' => 'nullable|in:in_home,studio,mobile',
            'wellness.booking.mobileFee' => 'nullable|numeric|min:0',
            'wellness.booking.policy' => 'nullable|string',
            'wellness.booking.slotDate' => 'nullable|date',
            'wellness.booking.slotStart' => 'nullable|string',
            'wellness.booking.slotEnd' => 'nullable|string',
            'wellness.booking.slots' => 'nullable|array',
            'wellness.booking.selectedSlotText' => 'nullable|string',
        ];
    }
}

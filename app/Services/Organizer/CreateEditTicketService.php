<?php

namespace App\Services\Organizer;

use App\Models\DrinkPackage;
use App\Models\EventTicketDrink;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use App\Models\TicketExtraSetting;
use Illuminate\Support\Facades\Auth;

class CreateEditTicketService
{
    /**
     * Get Tickets and events
     */
    public function getIndexData()
    {
        $userId = Auth::user()->id;
        $organizerId = Auth::user()->organizerProfile->id;
        $OrganizerCategories = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $categories = $OrganizerCategories->categories ?? [];

        $drinks = EventTicketDrink::where('created_by', $userId)
            ->select('id', 'name', 'type', 'amount')
            ->get()
            ->groupBy('type');

        $events = LinkUpEvent::with(['tickets' => fn($q) => $q->filterByOrganizerCategory($categories)->orderBy('created_at', 'desc')])
            ->where('organizer_id', $organizerId)
            ->select('id', 'title', 'image_object', 'featured_image')
            ->get();

        $packages = DrinkPackage::where('organizer_id', $organizerId)
            ->latest()
            ->get();

        return [
            'drinks' => [
                'mix_drinks'   => $drinks->get('mix_drinks', []),
                'wines'        => $drinks->get('wines', []),
                'waters'       => $drinks->get('waters', []),
                'beers'        => $drinks->get('beers', []),
                'soft_drinks'  => $drinks->get('soft_drinks', []),
                'bottles'      => $drinks->get('bottles', []),
            ],
            'events' => $events,
            'packages' => $packages,
            'organizer_categories' => $OrganizerCategories->categories ?? [],
        ];
    }

    public function createOrUpdateTicket($data)
    {
        $userId = Auth::user()->id;
        $organizerProfile = OrganizerProfile::where('user_id', $userId)->first();

        $ticket = isset($data['id']) ? Ticket::find($data['id']) : new Ticket();

        $ticket->fill($this->ticketDetails($data));

        // Ensure the ticket is associated with the correct event
        if (!$ticket->event_id && isset($data['event_id'])) {
            $ticket->event_id = $data['event_id'];
        }

        $ticket->save();

        if (($ticket->type !== 'Cookouts/Food' && $ticket->type !== 'Cookouts') && isset($data['cookout'])) {
            $this->clearWellnessCookoutData($ticket->id);
        } elseif (($data['type'] === 'Cookouts/Food' || $data['type'] === 'Cookouts') && isset($data['cookout'])) {
            $this->saveCookoutData($ticket->id, $data['cookout']);
        }

        if ($ticket->type != 'Wellness and Spa' && isset($data['wellness'])) {
            $this->clearWellnessCookoutData($ticket->id);
        } elseif ($data['type'] === 'Wellness and Spa' && isset($data['wellness'])) {
            $this->saveWellnessData($ticket->id, $data['wellness']);
        }

        if ($data['type'] != 'Wellness and Spa' && ($data['type'] != 'Cookouts/Food' && $data['type'] != 'Cookouts')) {
            $this->clearWellnessCookoutData($ticket->id);
        }

        return $ticket;
    }

    /**
     * Ticket Details
     */
    private function ticketDetails($data)
    {
        $tableData = ($data['has_table'] ?? 'no') === 'yes'
            ? $this->ticketTableOption($data)
            : [
                'table_price' => 0,
                'table_capacity' => 0,
                'sections' => [],
                'main_bottles' => [],
                'chasers_or_mixers' => [],
                'water_options' => [],
                'package_id' => null,
                'drink_addons' => [],
            ];

        if (($data['is_free'] ?? 'no') === 'yes') {
            $data['price'] =  0;
            $data['promo_price'] = 0;
        }

        if (($data['has_drink_addons'] ?? 'no') == 'yes') {
            $data['drink_addons'] = $data['drink_addons'] ?? [];
        } else {
            $data['drink_addons'] = [];
        }


        if (($data['type'] === 'Cookouts/Food' || $data['type'] === 'Cookouts') && isset($data['cookout'])) {
            $data['has_table'] = 'no';
            $data['table_price'] = 0;
            $data['table_capacity'] = 0;
            $data['sections'] = [];
            $data['main_bottles'] = [];
            $data['chasers_or_mixers'] = [];
            $data['water_options'] = [];
            $data['package_id'] = null;
            $data['drink_addons'] = [];
        }

        return [
            'event_id' => $data['event_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'ticket_type' => $data['ticket_type'],
            'description' => $data['description'] ?? null,
            'has_table' => $data['has_table'] ?? 'no',
            'is_free' => $data['is_free'] ?? 'no',
            'price' => $data['price'] ?? 0,
            'promo_price' => $data['promo_price'] ?? 0,
            'quantity' => $data['quantity'] ?? 0,
            'tickets_per_attendee' => $data['tickets_per_attendee'] ?? 0,
            'sale_start' => isset($data['sale_start']) && !empty($data['sale_start']) ? date('Y-m-d H:i:s', strtotime($data['sale_start'])) : null,
            'sale_end' => isset($data['sale_end']) && !empty($data['sale_end']) ? date('Y-m-d H:i:s', strtotime($data['sale_end'])) : null,
            'status' => $data['status'] ?? 'inactive',
            'table_price' => $tableData['table_price'],
            'table_capacity' => $tableData['table_capacity'],
            'sections' => $tableData['sections'],
            'main_bottles' => $tableData['main_bottles'],
            'chasers_or_mixers' => $tableData['chasers_or_mixers'],
            'water_options' => $tableData['water_options'],
            'package_id' => $tableData['package_id'],
            'drink_addons' => $data['drink_addons'],
            'commMode' => $data['commMode'] ?? 'none',
            'commission' => $data['commission'] ?? 0,
            'commFlat' => $data['commFlat'] ?? 0,
        ];
    }

    /**
     * Ticket Table Option
     */
    private function ticketTableOption(&$data)
    {
        if (isset($data['package_id'])) {

            $data['promo_price'] = 0;
            $data['package_id'] = $data['package_id'];
            $data['main_bottles'] = [];
            $data['chasers_or_mixers'] = [];
            $data['water_options'] = [];
            $data['tickets_per_attendee'] = 0;
        } else {

            $data['promo_price'] = 0;
            $data['package_id'] = null;
            $data['tickets_per_attendee'] = 0;

            $data['main_bottles'] = $data['main_bottles'] ?? [];
            $data['chasers_or_mixers'] = $data['chasers_or_mixers'] ?? [];
            $data['water_options'] = $data['water_options'] ?? [];
        }

        /**after change these will move to else and if condition */
        $data['quantity'] = $data['table_capacity'] ?? 0;
        $data['price'] =  $data['table_price'] ?? 0;
        /**end */

        $data['table_price'] = $data['table_price'] ?? 0;
        $data['table_capacity'] = $data['table_capacity'] ?? 0;
        $data['sections'] = $data['sections'] ?? [];
        $data['drink_addons'] = [];

        return $data;
    }

    /**
     * Save Cookout Ticket Data to TicketExtraSetting
     */
    private function saveCookoutData($ticketId, $cookoutData)
    {
        $extraSetting = TicketExtraSetting::firstOrNew(['ticket_id' => $ticketId]);
        $extraSetting->cookout = $cookoutData;
        $extraSetting->wellness = null;
        $extraSetting->save();
    }

    /**
     * Save Wellness Ticket Data to TicketExtraSetting
     */
    private function saveWellnessData($ticketId, $wellnessData)
    {
        $extraSetting = TicketExtraSetting::firstOrNew(['ticket_id' => $ticketId]);
        $extraSetting->cookout = null;
        $extraSetting->wellness = $wellnessData;
        $extraSetting->save();
    }

    /**
     * Clear Wellness Data from TicketExtraSetting
     */
    private function clearWellnessCookoutData($ticketId)
    {
        $extraSetting = TicketExtraSetting::where('ticket_id', $ticketId)->first();
        if ($extraSetting) {
            $extraSetting->delete();
        }
    }
}

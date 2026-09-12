<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;

class TicketDrinkAddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = Ticket::take(3)->get();

        if ($tickets->isEmpty()) {
            $this->command->warn('No tickets found. Please create tickets first.');
            return;
        }

        foreach ($tickets as $ticket) {
            $ticket->drink_addons = [
                [
                    'id' => 'addon_' . $ticket->id . '_1',
                    'name' => 'VIP Parking',
                    'description' => 'Reserved parking spot close to the venue entrance',
                    'price' => 25.00,
                    'quantity_available' => 50,
                    'is_required' => false,
                    'max_quantity_per_ticket' => 1
                ],
                [
                    'id' => 'addon_' . $ticket->id . '_2',
                    'name' => 'Welcome Drink',
                    'description' => 'Complimentary welcome drink upon arrival',
                    'price' => 15.00,
                    'quantity_available' => 200,
                    'is_required' => false,
                    'max_quantity_per_ticket' => 2
                ],
                [
                    'id' => 'addon_' . $ticket->id . '_3',
                    'name' => 'Event T-Shirt',
                    'description' => 'Commemorative event t-shirt in various sizes',
                    'price' => 30.00,
                    'quantity_available' => 100,
                    'is_required' => false,
                    'max_quantity_per_ticket' => 3
                ]
            ];
            $ticket->save();
        }

        $this->command->info('Sample drink addons added to ' . $tickets->count() . ' tickets');
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $attendees;

    public function __construct($attendees)
    {
        $this->attendees = $attendees;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->attendees;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Reference',
            'Event',
            'Ticket Type',
            'Number of Tickets',
            'Attendee Name',
            'Email',
            'Payment Method',
            'Status',
            'Price',
            'Order Date',
            'Checked In',
            'Check-in Time'
        ];
    }

    /**
     * @param mixed $attendee
     * @return array
     */
    public function map($attendee): array
    {
        return [
            $attendee->ticket_qrcode_id,
            $attendee->event->title ?? 'N/A',
            $attendee->ticket_type ?? 'General',
            $attendee->no_of_tickets,
            $attendee->user->name ?? 'N/A',
            $attendee->user->email ?? 'N/A',
            $attendee->payment_method ?? 'N/A',
            $attendee->ticket_status,
            $attendee->total,
            $attendee->created_at->format('Y-m-d H:i:s'),
            $attendee->ticket_status === 'checked_in' ? 'Yes' : 'No',
            $attendee->ticket_status === 'checked_in' ? $attendee->updated_at->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 15,
            'D' => 18,
            'E' => 25,
            'F' => 30,
            'G' => 18,
            'H' => 15,
            'I' => 12,
            'J' => 20,
            'K' => 15,
            'L' => 20,
        ];
    }
}

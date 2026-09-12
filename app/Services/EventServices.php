<?php

namespace App\Services;

use App\Models\EventDetails;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class EventServices
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Create a new event with its details.
     */
    public function createEvent(array $validated, $organizer = null, $admin = null): LinkUpEvent
    {
        if ($organizer !== null && $admin !== null) {
            $organizerProfile = OrganizerProfile::where('id', $organizer)->firstOrFail();
        } elseif ($organizer !== null) {
            $organizerProfile = OrganizerProfile::where('id', $organizer)->firstOrFail();
            $admin = null;
        } else {
            $organizerProfile = OrganizerProfile::where('user_id', Auth::id())->firstOrFail();
            $admin = null;
        }

        $mediaPaths   = $this->processMedia($validated['mediaFiles'] ?? []);
        $galleryPaths = $this->processGallery($validated['additionalOptions']['gallery'] ?? []);
        $artistImagePaths = $this->processArtistImages($validated['additionalOptions']['artist_image'] ?? []);

        $linkUpEvent = LinkUpEvent::create([
            'user_id'        => $admin,
            'organizer_id'   => $organizerProfile->id,
            'organizer_name' => $organizerProfile->name,
            'title'          => Arr::get($validated, 'eventDetails.name'),
            'description'    => Arr::get($validated, 'eventDetails.description'),
            'category_id'    => Arr::get($validated, 'eventDetails.category_id'),
            'location_type'  => Arr::get($validated, 'location_type', 'venue'),
            'venue'          => Arr::get($validated, 'venue'),
            'longtitude'          => Arr::get($validated, 'longtitude'),
            'latitude'          => Arr::get($validated, 'latitude'),
            'type'           => Arr::get($validated, 'additionalOptions.type', 'public'),
            'email'          => Arr::get($validated, 'additionalOptions.email'),
            'phone'          => Arr::get($validated, 'additionalOptions.phone'),
            'website'        => Arr::get($validated, 'additionalOptions.website'),
            'country'        => Arr::get($validated, 'additionalOptions.country'),
            'state'          => Arr::get($validated, 'additionalOptions.state'),
            'city'           => Arr::get($validated, 'additionalOptions.city'),
            'disclaimer'     => Arr::get($validated, 'additionalOptions.disclaimer'),
            'image_object'   => $mediaPaths[0] ?? null,
            'status'         => 'live',
        ]);

        EventDetails::create(
            $this->mapEventDetails($validated, $mediaPaths, $galleryPaths, $linkUpEvent->id, null, $artistImagePaths)
        );

        return $linkUpEvent;
    }


    /**
     * Update an existing event with its details.
     */
    public function updateEvent(array $validated, int $eventId): LinkUpEvent
    {
        $linkUpEvent = LinkUpEvent::findOrFail($eventId);

        $mediaPaths   = $this->processMedia($validated['mediaFiles'] ?? [], $linkUpEvent->eventDetails->media ?? []);
        $galleryPaths = $this->processGallery($validated['additionalOptions']['gallery'] ?? []);
        $artistImagePaths = $this->processArtistImages($validated['additionalOptions']['artist_image'] ?? [], $linkUpEvent->eventDetails->artist_image ?? []);

        // Handle organizer update if provided
        if (isset($validated['organizer_id'])) {
            $organizer_id = $validated['organizer_id'] ?? Auth::id();
            $organizer    = OrganizerProfile::findOrFail($organizer_id);

            $linkUpEvent->update([
                'organizer_id'   => $organizer->id,
                'organizer_name' => $organizer->name,
            ]);
        }

        // Update other event fields
        $linkUpEvent->update([
            'title'        => Arr::get($validated, 'eventDetails.name', $linkUpEvent->title),
            'description'  => Arr::get($validated, 'eventDetails.description', $linkUpEvent->description),
            'category_id'  => Arr::get($validated, 'eventDetails.category_id', $linkUpEvent->category_id),
            'location_type' => Arr::get($validated, 'location_type', $linkUpEvent->location_type),
            'venue'        => Arr::get($validated, 'venue', $linkUpEvent->venue),
            'longtitude'          => Arr::get($validated, 'longtitude', $linkUpEvent->longtitude),
            'latitude'          => Arr::get($validated, 'latitude', $linkUpEvent->latitude),
            'type'         => Arr::get($validated, 'additionalOptions.type', $linkUpEvent->type),
            'email'        => Arr::get($validated, 'additionalOptions.email', $linkUpEvent->email),
            'phone'        => Arr::get($validated, 'additionalOptions.phone', $linkUpEvent->phone),
            'website'      => Arr::get($validated, 'additionalOptions.website', $linkUpEvent->website),
            'country'      => Arr::get($validated, 'additionalOptions.country', $linkUpEvent->country),
            'state'        => Arr::get($validated, 'additionalOptions.state', $linkUpEvent->state),
            'city'         => Arr::get($validated, 'additionalOptions.city', $linkUpEvent->city),
            'disclaimer'   => Arr::get($validated, 'additionalOptions.disclaimer', $linkUpEvent->disclaimer),
            'image_object' => $mediaPaths[0] ?? $linkUpEvent->image_object,
            'status'         => 'live',
        ]);

        // Update event details
        $eventDetails = EventDetails::where('event_id', $linkUpEvent->id)->first();
        if ($eventDetails) {
            $eventDetails->update(
                $this->mapEventDetails($validated, $mediaPaths, $galleryPaths, $linkUpEvent->id, $eventDetails, $artistImagePaths)
            );
        }

        return $linkUpEvent;
    }


    /**
     * Map EventDetails fields from validated data.
     */
    private function mapEventDetails(array $validated, array $mediaPaths, array $galleryPaths, int $eventId, ?EventDetails $existing = null, array $artistImagePaths = []): array
    {
        return [
            'event_id'          => $eventId,
            'attendees'         => Arr::get($validated, 'eventDetails.attendees') === 'show',
            'enable_views'      => Arr::get($validated, 'eventDetails.reviews') === 'enable',
            'seating_plan'      => Arr::get($validated, 'eventDetails.seating_plan') === 'yes',
            'audiences'         => Arr::get($validated, 'eventDetails.audiences', $existing->audiences ?? []),
            'artists'           => Arr::get($validated, 'additionalOptions.artists'),
            'artist_image'           => $artistImagePaths,
            'event_type'        => Arr::get($validated, 'dateTime.eventType', $existing->event_type ?? 'single'),
            'single_event_date' => Arr::get($validated, 'dateTime.singleDate', $existing->single_event_date ?? null),
            'single_start_time' => isset($validated['dateTime']['singleStartTime'])
                ? Carbon::parse($validated['dateTime']['singleStartTime'])->format('H:i:s')
                : ($existing->single_start_time ?? null),
            'single_end_time'   => isset($validated['dateTime']['singleEndTime'])
                ? Carbon::parse($validated['dateTime']['singleEndTime'])->format('H:i:s')
                : ($existing->single_end_time ?? null),
            'recurr_pattern'    => Arr::get($validated, 'dateTime.recurrPattern', $existing->recurr_pattern ?? null),
            'recurr_start_date' => Arr::get($validated, 'dateTime.recurrStartDate', $existing->recurr_start_date ?? null),
            'recurr_end_date'   => Arr::get($validated, 'dateTime.recurrEndDate', $existing->recurr_end_date ?? null),
            'scanner_id'        => array_column($validated['scanners'] ?? [], 'id'),
            'media'             => $mediaPaths,
            'twitter'           => Arr::get($validated, 'socialMedia.twitter', $existing->twitter ?? null),
            'instagram'         => Arr::get($validated, 'socialMedia.instagram', $existing->instagram ?? null),
            'facebook'          => Arr::get($validated, 'socialMedia.facebook', $existing->facebook ?? null),
            'tiktok'            => Arr::get($validated, 'socialMedia.tiktok', $existing->tiktok ?? null),
            'linkedin'          => Arr::get($validated, 'socialMedia.linkedin', $existing->linkedin ?? null),
            'zip'               => Arr::get($validated, 'additionalOptions.zip', $existing->zip ?? null),
            'tax_rate'          => Arr::get($validated, 'additionalOptions.tax_rate', $existing->tax_rate ?? null),
            'tax_included'      => Arr::get($validated, 'additionalOptions.tax_included', $existing->tax_included ?? 'no'),
            'image_gallery'     => $galleryPaths,
        ];
    }

    /**
     * Handle media uploads with fallback to existing paths.
     */
    private function processMedia(array $files, array $existing = []): array
    {
        return !empty($files) ? $this->imageService->multi('events/media', $files) : $existing;
    }

    /**
     * Handle gallery uploads with fallback to existing paths (supports images and videos).
     */
    private function processGallery(array $files, array $existing = []): array
    {
        // Separate new uploads from existing paths
        $newFiles = array_filter($files, fn($f) => $f instanceof \Illuminate\Http\UploadedFile);
        $existingPaths = array_filter($files, fn($f) => is_string($f));

        // Upload new files (both images and videos)
        $uploadedPaths = !empty($newFiles)
            ? $this->imageService->multi('events/gallery', $newFiles)
            : [];

        // Return only the current selection: existing paths + newly uploaded files
        // This prevents duplication by not merging with the old $existing array
        return array_merge($existingPaths, $uploadedPaths);
    }
    /**
     * Handle artist image uploads with fallback to existing paths.
     */
    private function processArtistImages(array $files, array $existing = []): array
    {
        // Separate new uploads from existing paths
        $newFiles = array_filter($files, fn($f) => $f instanceof \Illuminate\Http\UploadedFile);
        $existingPaths = array_filter($files, fn($f) => is_string($f) || $f === null);

        // Upload new files (images only)
        $uploadedPaths = !empty($newFiles)
            ? $this->imageService->multi('events/artists', $newFiles)
            : [];

        // Convert null values to empty strings for consistency, keep existing string paths
        $processedExisting = array_map(function ($item) {
            return $item === null ? '' : $item;
        }, $existingPaths);

        // Return processed array: empty strings for nulls + newly uploaded files
        return array_merge($processedExisting, $uploadedPaths);
    }
}

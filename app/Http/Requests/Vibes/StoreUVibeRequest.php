<?php

namespace App\Http\Requests\Vibes;

use App\Support\UVibeOptions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * "Post to U Vibe" — the same post as a Vibe (media, tags, boosts) with the U Vibe rules on top:
 * always a U Vibe post, a title and a type are required, and it goes to one of the poster's own
 * universities (their main one when none is chosen — enforced when the post is created).
 *
 * Website and mobile API send the same fields. Everything but the text/title is optional:
 *  - `publisher_type`/`publisher_id` default to the member themselves (or `custom_publisher` +
 *    the id of an organization from `GET uvibe/organizations`);
 *  - `as_university` posts as the chosen university itself ("University (official)");
 *  - `posted_role` is the member's role in that organization (shown as "Posted by you · role");
 *  - `text_style` {pal, font, motion, sticker} makes it a text card — the words are the caption
 *    and photos are not allowed (choices come from `GET uvibe/options`);
 *  - `category` defaults to campus life, "Also post on Vibes" (`show_on_vibes`) to on.
 */
class StoreUVibeRequest extends StoreVibeRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $asUniversity = $this->boolean('as_university');

        $this->merge([
            'is_uvibe' => true,
            // Posting as the university is still posted by the member; an organization is picked otherwise.
            'publisher_type' => $asUniversity || ! $this->filled('publisher_type') ? 'user' : $this->input('publisher_type'),
            'publisher_id' => $asUniversity || ! $this->filled('publisher_id') ? $this->user()?->getKey() : $this->input('publisher_id'),
            'as_university' => $asUniversity,
            'category' => $this->filled('category') ? $this->input('category') : 'campus-life',
            'show_on_vibes' => $this->has('show_on_vibes') ? $this->boolean('show_on_vibes') : true,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(UVibeOptions::categoryIds())],
            'price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'as_university' => ['boolean'],
            'posted_role' => ['nullable', 'string', 'max:'.UVibeOptions::ROLE_MAX],
            'text_style' => ['nullable', 'array'],
            'text_style.pal' => ['required_with:text_style', 'integer', 'between:0,'.(count(UVibeOptions::PALETTES) - 1)],
            'text_style.font' => ['required_with:text_style', 'integer', 'between:0,'.(count(UVibeOptions::FONTS) - 1)],
            'text_style.motion' => ['required_with:text_style', Rule::in(UVibeOptions::motionIds())],
            'text_style.sticker' => ['nullable', Rule::in(UVibeOptions::STICKERS)],
        ]);
    }

    public function after(): array
    {
        return array_merge(parent::after(), [function (Validator $validator) {
            // A text card is its words — the design drops photos when you switch to it.
            // A Campus Gig is paid from the poster's wallet, and the wallet moves whole dollars.
            if ($this->input('category') === 'opportunity') {
                $pay = $this->input('price');
                if (! is_numeric($pay) || (float) $pay < 1 || (float) $pay != floor((float) $pay)) {
                    $validator->errors()->add('price', 'A Campus Gig needs a pay of at least $1, in whole dollars.');
                }
            }
            if (is_array($this->input('text_style')) && count($this->file('media', [])) > 0) {
                $validator->errors()->add('media', 'A text card can’t have photos.');
            }
            if (is_array($this->input('text_style')) && blank($this->input('caption'))) {
                // Replace the general "caption or media" message with the one for text cards.
                $validator->errors()->forget('caption');
                $validator->errors()->add('caption', 'Write something for your text card.');
            }
        }]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Add a title.',
            'category.in' => 'Choose a valid type for your post.',
            // PHP itself refused the file (upload_max_filesize / post_max_size) — say so, with the real limits.
            'media.*.uploaded' => 'A photo could not be uploaded — this server accepts files up to '
                .ini_get('upload_max_filesize').' each ('.ini_get('post_max_size').' per post). Try a smaller photo.',
        ];
    }
}

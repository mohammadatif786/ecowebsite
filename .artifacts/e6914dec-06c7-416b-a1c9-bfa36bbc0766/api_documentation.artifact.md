# LinkUp Vibe, Reel & Creator Mobile API Documentation

Base URL: `https://your-domain.com/api/regular-user/`

## Authentication & Headers
All requests require Sanctum bearer token authentication:
- `Authorization: Bearer <sanctum_token>`
- `Accept: application/json`
- `Content-Type: multipart/form-data` (for file uploads or form-data submissions)

---

## 1. VIBES MANAGEMENT APIS

### Create Vibe (`POST /vibes/store`)
* **Content-Type:** `multipart/form-data`
* **Form Data Fields:**
  * `publisher_type` (string, required): `'user'`, `'organization'`, or `'group'`
  * `publisher_id` (integer, required): User or Entity ID
  * `caption` (string, required if no media): Text caption or message
  * `text_bg` (string, optional): Gradient or color string for Text Vibe (e.g. `"linear-gradient(135deg, #a855f7, #ec4899)"`)
  * `text_font` (string, optional): Font family name (`"Aa Classic"`, `"Aa Modern"`, `"Aa Mono"`, `"Aa Script"`, `"Aa Heavy"`, `"Aa Italic"`, `"Aa Impact"`, `"Aa Typewriter"`, `"Aa Elegant"`)
  * `media[]` (file(s), optional): Array of images/videos
  * `media_sources[]` (string(s), optional): `'camera'`, `'video_recording'`, or `'gallery'`
  * `location_name` (string, optional): Location text
  * `allow_coin_gifts` (integer/boolean, optional): `1` or `0`
  * `product_ids[]` (integer(s), optional): Array of tagged marketplace product IDs
  * `event_ids[]` (integer(s), optional): Array of tagged event IDs
* **JSON Response (`201 Created`):**
```json
{
    "data": {
        "id": 108,
        "created_by": 2,
        "caption": "Enjoying carnival vibes!",
        "text_bg": "linear-gradient(135deg, #a855f7, #ec4899)",
        "text_font": "Aa Classic",
        "likes_count": 0,
        "comments_count": 0
    }
}
```

### Edit / Update Vibe Post (`PUT /vibes/{vibe_id}` or `POST /vibes/{vibe_id}/update`)
* **Description:** Updates an existing vibe post. **Only the post owner** can update their post.
* **Fields (JSON or Form Data):**
  * `caption` (string, optional): Updated text
  * `location_name` (string, optional): Updated location
  * `text_bg` (string, optional)
  * `text_font` (string, optional)
* **JSON Response (`200 OK`):**
```json
{
    "success": true,
    "message": "Post updated successfully",
    "vibe": {
        "id": 108,
        "caption": "Updated caption text",
        "location_name": "The Valley, Anguilla",
        "text_bg": "linear-gradient(135deg, #a855f7, #ec4899)",
        "text_font": "Aa Classic"
    }
}
```

### Delete Vibe Post (`DELETE /vibes/{vibe_id}`)
* **Description:** Deletes a vibe post. **Only the post owner** can delete their post.
* **JSON Response (`200 OK`):**
```json
{
    "success": true
}
```

### Copy Link Vibe (`GET /vibes/{vibe_id}/copy-link`)
* **Description:** Returns the shareable link for a vibe post.
* **JSON Response (`200 OK`):**
```json
{
    "success": true,
    "share_url": "https://your-domain.com/new_frontend/vibes?id=108"
}
```

### Repost Vibe (`POST /vibes/{vibe_id}/repost`)
* **Description:** Creates an exact duplicate post under the authenticated user's feed and increments the original post's `reposts_count`.
* **JSON Response (`200 OK`):**
```json
{
    "is_reposed": true,
    "reposts_count": 1,
    "new_vibe": {
        "id": 109,
        "created_by": 2,
        "handle": "CurrentUser",
        "caption": "Carnival flashback 📸",
        "text_bg": null,
        "text_font": null,
        "likes_count": 0,
        "reposts_count": 0
    }
}
```

### List Vibes Feed (`GET /vibes/posts`)
* **JSON Response (`200 OK`):**
```json
{
    "posts": [
        {
            "id": 108,
            "created_by": 5,
            "handle": "Nadia",
            "publisher_type": "user",
            "avatar": "https://...",
            "location": "The Valley",
            "media": [],
            "kind": "text",
            "caption": "Beautiful sunset",
            "text_bg": "linear-gradient(135deg, #a855f7, #ec4899)",
            "text_font": "Aa Classic",
            "likes_count": 14,
            "comments_count": 3,
            "shares_count": 2,
            "reposts_count": 1,
            "bigups_count": 5,
            "is_liked": false,
            "allow_coin_gifts": true
        }
    ]
}
```

---

## 2. SUGGESTED CREATORS & FOLLOW APIS

### Get Suggested Creators (`GET /vibes/suggested-creators`)
* **Description:** Retrieves creators. Only users who have created **at least 1 vibe or reel** are returned.
* **JSON Response (`200 OK`):**
```json
{
    "suggested_creators": [
        {
            "user_id": 8,
            "handle": "marcus",
            "name": "Marcus",
            "avatar": "https://your-domain.com/storage/avatars/marcus.jpg",
            "is_following": false
        }
    ]
}
```

### Toggle Follow / Unfollow Creator (`POST /creators/{user_id}/toggle-follow`)
* **Description:** Toggles following or unfollowing a creator.
* **JSON Response (`200 OK`):**
```json
{
    "is_following": true,
    "message": "Creator followed",
    "reels": [...]
}
```

---

## 3. VIBE INTERACTIONS APIS

### Toggle Like Vibe (`POST /vibes/{vibe_id}/like`)
* **JSON Response (`200 OK`):** `{"is_liked": true, "likes_count": 15}`

### Get Vibe Comments (`GET /vibes/{vibe_id}/comments`)
* **JSON Response (`200 OK`):** Paginated array of comments and nested replies.

### Post Vibe Comment / Reply (`POST /vibes/{vibe_id}/comments`)
* **Parameters:** `comment` (string), `parent_id` (optional for reply)
* **JSON Response (`201 Created`):** Created comment object.

### Like / Unlike Comment (`POST /vibe-comments/{comment_id}/like`)
* **JSON Response (`200 OK`):** `{"is_liked": true, "likes_count": 4}`

### Share Vibe (`POST /vibes/{vibe_id}/share`)
* **JSON Response (`200 OK`):** `{"shares_count": 3}`

### Send Big Up / Gift (`POST /vibes/{vibe_id}/bigup`)
* **Parameters:** `coins` (integer), `gift_name` (optional), `emoji` (optional)
* **JSON Response (`200 OK`):** `{"bigups_count": 6, "user_coins": 450}`

---

## 4. REELS APIS

### Create / Store Reel (`POST /reels/store`)
* **Content-Type:** `multipart/form-data`
* **Parameters:** `type` (`'video'`, `'image'`, `'gallery'`), `file` (file max 50MB), `caption`, `location`

### Get All Reels (`GET /reels`)
### Get Followed Users' Reels (`GET /reels/followed`)
### Get User's Reels (`GET /reels/user/{user_id}`)
### Show Single Reel (`GET /reels/{reel_id}`)
### Reel Interactions (`/reels/{reel}/like`, `/reels/{reel}/comments`, `/reels/{reel}/share`, `/reels/{reel}/save`, `/reels/{reel}/bigup`, `/reels/{reel}/gift`)

# LinkUp Vibe & Reel Mobile App API Documentation

Base URL: `https://your-domain.com/api/regular-user/`

## Authentication & Headers
All requests require Sanctum token authentication:
- `Authorization: Bearer <sanctum_token>`
- `Accept: application/json`
- `Content-Type: multipart/form-data` (for endpoints requiring file uploads or form inputs)

---

## 1. VIBES APIS

### Create Vibe
- **Endpoint:** `POST /vibes/store`
- **Content-Type:** `multipart/form-data`
- **Parameters:**
  - `caption` (string, optional): Text caption
  - `publisher_type` (string, optional): `'user'`, `'organization'`, or `'group'`
  - `publisher_id` (integer, optional)
  - `media[]` (file(s), optional): Images or videos
  - `allow_coin_gifts` (boolean, optional): Default `true`
- **JSON Response (`201 Created`):**
```json
{
    "data": {
        "id": 1,
        "caption": "Enjoying the vibe!",
        "likes_count": 0,
        "comments_count": 0,
        "shares_count": 0,
        "bigups_count": 0
    }
}
```

### List Vibes Feed
- **Endpoint:** `GET /vibes/posts`
- **JSON Response (`200 OK`):**
```json
{
    "posts": [
        {
            "id": 1,
            "created_by": 5,
            "handle": "Nadia",
            "publisher_type": "user",
            "avatar": "https://...",
            "location": "The Valley",
            "media": [
                {
                    "id": 10,
                    "type": "image",
                    "url": "https://...",
                    "thumbnail": null
                }
            ],
            "kind": "photo",
            "caption": "Beautiful sunset",
            "likes_count": 14,
            "comments_count": 3,
            "shares_count": 2,
            "bigups_count": 5,
            "is_liked": false,
            "allow_coin_gifts": true
        }
    ]
}
```

### Toggle Like Vibe
- **Endpoint:** `POST /vibes/{vibe}/like`
- **JSON Response (`200 OK`):**
```json
{
    "is_liked": true,
    "likes_count": 15
}
```

### Get Vibe Comments
- **Endpoint:** `GET /vibes/{vibe}/comments`
- **JSON Response (`200 OK`):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 4,
            "vibe_id": 1,
            "user_id": 8,
            "comment": "So gorgeous!",
            "parent_id": null,
            "user": {
                "id": 8,
                "name": "Marcus",
                "avatar": "https://..."
            },
            "replies": [],
            "is_liked": false,
            "likes_count": 3
        }
    ]
}
```

### Post Comment / Reply
- **Endpoint:** `POST /vibes/{vibe}/comments`
- **Parameters:**
  - `comment` (string, required)
  - `parent_id` (integer, optional - include for nested replies)
- **JSON Response (`201 Created`):**
```json
{
    "id": 6,
    "vibe_id": 1,
    "user_id": 2,
    "comment": "Thanks!",
    "parent_id": 4,
    "user": {
        "id": 2,
        "name": "CurrentUser",
        "avatar": "https://..."
    },
    "likes_count": 0
}
```

### Like / Unlike Comment
- **Endpoint:** `POST /vibe-comments/{comment}/like`
- **JSON Response (`200 OK`):**
```json
{
    "is_liked": true,
    "likes_count": 4
}
```

### Share Vibe
- **Endpoint:** `POST /vibes/{vibe}/share`
- **JSON Response (`200 OK`):**
```json
{
    "shares_count": 3
}
```

### Send Big Up / Gift
- **Endpoint:** `POST /vibes/{vibe}/bigup`
- **Parameters:**
  - `coins` (integer, required)
  - `gift_name` (string, optional)
  - `emoji` (string, optional)
- **JSON Response (`200 OK`):**
```json
{
    "bigups_count": 6,
    "user_coins": 450
}
```

---

## 2. REELS APIS

### Create / Store Reel
- **Endpoint:** `POST /reels/store`
- **Content-Type:** `multipart/form-data`
- **Parameters:**
  - `type` (string, required): `'video'`, `'image'`, or `'gallery'`
  - `file` (file, required): Video/image file (max 50MB)
  - `caption` (string, optional)
  - `location` (string, optional)
- **JSON Response (`201 Created`):**
```json
{
    "success": true,
    "reel": {
        "id": 1,
        "uid": "uuid-string",
        "user_id": 5,
        "type": "video",
        "file_path": "reels/videos/sample.mp4",
        "caption": "My reel caption",
        "status": "active"
    }
}
```

### Get All Reels (Reels Feed)
- **Endpoint:** `GET /reels`
- **JSON Response (`200 OK`):**
```json
[
    {
        "id": 1,
        "uid": "uuid-string",
        "user_id": 5,
        "handle": "nadia",
        "avatar": "https://...",
        "name": "Nadia",
        "type": "video",
        "file_path": "reels/videos/sample.mp4",
        "caption": "Reel caption",
        "likes_count": 12,
        "comments_count": 2,
        "shares_count": 1,
        "is_liked": false,
        "is_saved": false
    }
]
```

### Get Followed Users' Reels
- **Endpoint:** `GET /reels/followed`
- **JSON Response (`200 OK`):** Same array structure as `GET /reels`.

### Get User's Reels
- **Endpoint:** `GET /reels/user/{user}`
- **JSON Response (`200 OK`):**
```json
{
    "user": {
        "id": 5,
        "name": "Nadia",
        "avatar": "https://...",
        "linkup_id": "nadia",
        "city": "The Valley",
        "country": "Anguilla",
        "is_following": true
    },
    "reels": [...]
}
```

### Show Single Reel Detail
- **Endpoint:** `GET /reels/{reel}`
- **JSON Response (`200 OK`):** Single reel object structure.

### Reel Interactions (Like, Save, Share, BigUp, Gift, Comments)
- **Like Reel:** `POST /reels/{reel}/like`
- **Save / Bookmark Reel:** `POST /reels/{reel}/save`
- **Share Reel:** `POST /reels/{reel}/share`
- **Send BigUp:** `POST /reels/{reel}/bigup` (Params: `coins`)
- **Send Gift:** `POST /reels/{reel}/gift` (Params: `gift_id` or `coins`)
- **Get Reel Comments:** `GET /reels/{reel}/comments`
- **Post Reel Comment:** `POST /reels/{reel}/comments` (Params: `comment`)
- **Like Reel Comment:** `POST /reel-comments/{comment}/like`

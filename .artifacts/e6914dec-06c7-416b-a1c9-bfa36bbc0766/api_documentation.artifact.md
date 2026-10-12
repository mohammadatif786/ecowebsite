# LinkUp Vibe, Reel, Chat, Activity & Creator Mobile API Documentation

Base URL: `https://your-domain.com/api/regular-user/`

## Authentication & Headers
All requests require Sanctum bearer token authentication:
- `Authorization: Bearer <sanctum_token>`
- `Accept: application/json`

---

## 1. VIBES ACTIVITY API

### Get User Activity / Notifications (`GET /vibes/activity`)
* **Description:** Retrieves the user's recent activity (likes, comments, mentions, follows) grouped by time (`Today`, `Yesterday`, etc.), matching the Instagram-style Activity panel.
* **Headers:**
  - `Authorization: Bearer <token>`
  - `Accept: application/json`
* **JSON Response (`200 OK`):**
```json
{
    "activity": [
        {
            "id": 14,
            "type": "like",
            "user": {
                "id": 5,
                "name": "Kristian R",
                "handle": "kristian_r",
                "avatar": "https://...",
                "is_following": true
            },
            "message": "kristian_r and 6 others liked your photo",
            "title": "New Like",
            "time_group": "Today",
            "time_ago": "5m",
            "media_thumbnail": "https://...",
            "is_following": true,
            "created_at": "2026-10-06T15:00:00.000000Z"
        }
    ]
}
```

---

## 2. VIBES MANAGEMENT & THANK YOU APIS
* **Create Vibe:** `POST /vibes/store` (`multipart/form-data`)
* **Edit Vibe:** `PUT /vibes/{vibe_id}` (Owner only)
* **Delete Vibe:** `DELETE /vibes/{vibe_id}` (Owner only)
* **Copy Link Vibe:** `GET /vibes/{vibe_id}/copy-link`
* **Repost Vibe:** `POST /vibes/{vibe_id}/repost`
* **Get Gifts Received:** `GET /vibes/gifts-received`
* **Thank Sender:** `POST /vibes/thank-sender` (Params: `user_id`, `message`, `gift_coins_id`)

---

## 3. VIBE CHAT & MESSAGES APIS
* **Get Conversations List:** `GET /vibes/chats/conversations`
* **Get Messages:** `GET /vibes/chats/{recipient_id}/messages`
* **Send Message:** `POST /vibes/chats/{recipient_id}/messages` (Params: `content`)

---

## 4. SUGGESTED CREATORS & FOLLOW APIS
* **Get Suggested Creators:** `GET /vibes/suggested-creators` (Users who have created at least 1 vibe or reel)
* **Toggle Follow / Unfollow:** `POST /creators/{user_id}/toggle-follow`

---

## 5. VIBE INTERACTIONS APIS
* **Like Vibe:** `POST /vibes/{vibe_id}/like`
* **Get Comments:** `GET /vibes/{vibe_id}/comments`
* **Post Comment/Reply:** `POST /vibes/{vibe_id}/comments` (Params: `comment`, `parent_id`)
* **Like Comment:** `POST /vibe-comments/{comment_id}/like`
* **Share Vibe:** `POST /vibes/{vibe_id}/share`
* **Send Big Up:** `POST /vibes/{vibe_id}/bigup` (Params: `coins`)

---

## 6. REELS APIS
* **Create Reel:** `POST /reels/store`
* **List Reels:** `GET /reels`, `GET /reels/followed`, `GET /reels/user/{user_id}`, `GET /reels/{reel_id}`

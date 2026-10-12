# LinkUp Vibe, Reel, Chat, Activity, Earnings & Creator Mobile API Documentation

Base URL: `https://your-domain.com/api/regular-user/`

## Authentication & Headers
All requests require Sanctum bearer token authentication:
- `Authorization: Bearer <sanctum_token>`
- `Accept: application/json`

---

## 1. VIBES EARNINGS APIS

### Get Earnings Summary (`GET /vibes/earnings`)
* **Description:** Retrieves the creator's affiliate sales stats, total commission, pending escrow, available balance, paid out amount, and recent commission list.
* **JSON Response (`200 OK`):**
```json
{
    "success": true,
    "earnings": {
        "sales_driven": 3,
        "total_commission": 150.00,
        "pending": 50.00,
        "available": 100.00,
        "paid": 0.00,
        "recent": [
            {
                "title": "Frontline Costume",
                "amount": 250.00,
                "commission": 12.50,
                "rate": 5.0,
                "status": "pending",
                "source": "vibe"
            }
        ]
    }
}
```

### Release Pending Earnings (`POST /vibes/earnings/release`)
* **Description:** Simulates delivery confirmation and releases pending escrow commissions to available balance.
* **JSON Response (`200 OK`):** `{"success": true, "count": 1, "message": "Successfully released 1 pending commissions."}`

### Transfer Earnings to Wallet (`POST /vibes/earnings/transfer`)
* **Description:** Transfers available earnings to the user's digital wallet (applying a 5% LinkUp platform fee).
* **JSON Response (`200 OK`):** `{"success": true, "amount": 95.00, "linkup_fee": 5.00, "message": "$95.00 transferred to your wallet! (5% LinkUp fee applied: $5.00)"}`

---

## 2. VIBES ACTIVITY & NOTIFICATIONS API
* **Get User Activity:** `GET /vibes/activity`

---

## 3. VIBES MANAGEMENT & THANK YOU APIS
* **Create Vibe:** `POST /vibes/store` (`multipart/form-data`)
* **Edit Vibe:** `PUT /vibes/{vibe_id}` (Owner only)
* **Delete Vibe:** `DELETE /vibes/{vibe_id}` (Owner only)
* **Copy Link Vibe:** `GET /vibes/{vibe_id}/copy-link`
* **Repost Vibe:** `POST /vibes/{vibe_id}/repost`
* **Get Gifts Received:** `GET /vibes/gifts-received`
* **Thank Sender:** `POST /vibes/thank-sender` (Params: `user_id`, `message`, `gift_coins_id`)

---

## 4. VIBE CHAT & MESSAGES APIS
* **Get Conversations List:** `GET /vibes/chats/conversations`
* **Get Messages:** `GET /vibes/chats/{recipient_id}/messages`
* **Send Message:** `POST /vibes/chats/{recipient_id}/messages` (Params: `content`)

---

## 5. SUGGESTED CREATORS & FOLLOW APIS
* **Get Suggested Creators:** `GET /vibes/suggested-creators`
* **Toggle Follow / Unfollow:** `POST /creators/{user_id}/toggle-follow`

---

## 6. VIBE INTERACTIONS APIS
* **Like Vibe:** `POST /vibes/{vibe_id}/like`
* **Get Comments:** `GET /vibes/{vibe_id}/comments`
* **Post Comment/Reply:** `POST /vibes/{vibe_id}/comments` (Params: `comment`, `parent_id`)
* **Like Comment:** `POST /vibe-comments/{comment_id}/like`
* **Share Vibe:** `POST /vibes/{vibe_id}/share`
* **Send Big Up:** `POST /vibes/{vibe_id}/bigup` (Params: `coins`)

---

## 7. REELS APIS
* **Create Reel:** `POST /reels/store`
* **List Reels:** `GET /reels`, `GET /reels/followed`, `GET /reels/user/{user_id}`, `GET /reels/{reel_id}`

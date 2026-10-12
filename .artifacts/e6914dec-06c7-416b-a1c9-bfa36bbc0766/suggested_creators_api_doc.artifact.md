# LinkUp Suggested Creators & Follow APIs Documentation

Base URL: `https://your-domain.com/api/regular-user/`

## Authentication & Headers
All requests require Sanctum bearer token authentication:
- `Authorization: Bearer <sanctum_token>`
- `Accept: application/json`

---

## 1. Get Suggested Creators
* **Endpoint:** `GET /vibes/suggested-creators`
* **Description:** Retrieves a list of suggested creators. Only users who have published at least one vibe post or reel are returned.
* **Headers:**
  - `Authorization: Bearer <token>`
  - `Accept: application/json`

### **JSON Response (`200 OK`):**
```json
{
    "suggested_creators": [
        {
            "user_id": 8,
            "handle": "marcus",
            "name": "Marcus",
            "avatar": "https://your-domain.com/storage/avatars/marcus.jpg",
            "is_following": false
        },
        {
            "user_id": 12,
            "handle": "aaliyah",
            "name": "Aaliyah",
            "avatar": "https://your-domain.com/storage/avatars/aaliyah.jpg",
            "is_following": true
        }
    ]
}
```

---

## 2. Toggle Follow / Unfollow Creator
* **Endpoint:** `POST /creators/{user_id}/toggle-follow`
* **Description:** Toggles following or unfollowing a creator. Automatically sends or cancels follow requests.
* **Headers:**
  - `Authorization: Bearer <token>`
  - `Accept: application/json`
* **URL Parameter:**
  - `{user_id}` (integer, required): User ID of the creator to follow or unfollow.

### **JSON Response when Following (`200 OK`):**
```json
{
    "is_following": true,
    "message": "Creator followed",
    "reels": [
        {
            "id": 1,
            "uid": "uuid-string",
            "user_id": 8,
            "handle": "marcus",
            "avatar": "https://your-domain.com/storage/avatars/marcus.jpg",
            "name": "Marcus",
            "type": "video",
            "file_path": "reels/videos/sample.mp4",
            "thumbnail_path": null,
            "caption": "Carnival highlight 🌴",
            "location": "The Valley",
            "likes_count": 12,
            "comments_count": 3,
            "shares_count": 2,
            "gifts_count": 1,
            "bigups_count": 5,
            "is_liked": false,
            "created_at": "2026-10-06T17:00:00.000000Z"
        }
    ]
}
```

### **JSON Response when Unfollowing (`200 OK`):**
```json
{
    "is_following": false,
    "message": "Creator unfollowed",
    "reels": []
}
```

---

## 3. Code Example (React Native / Axios)

```javascript
// 1. Fetch Suggested Creators
const getSuggestedCreators = async () => {
    try {
        const response = await axios.get('https://your-domain.com/api/regular-user/vibes/suggested-creators', {
            headers: {
                'Authorization': `Bearer ${userToken}`,
                'Accept': 'application/json'
            }
        });
        console.log('Creators:', response.data.suggested_creators);
    } catch (error) {
        console.error('Error fetching creators:', error);
    }
};

// 2. Toggle Follow / Unfollow Creator
const toggleFollowCreator = async (creatorUserId) => {
    try {
        const response = await axios.post(`https://your-domain.com/api/regular-user/creators/${creatorUserId}/toggle-follow`, {}, {
            headers: {
                'Authorization': `Bearer ${userToken}`,
                'Accept': 'application/json'
            }
        });
        console.log(response.data.message); // "Creator followed" or "Creator unfollowed"
        console.log('Is Following:', response.data.is_following);
    } catch (error) {
        console.error('Error toggling follow:', error);
    }
};
```

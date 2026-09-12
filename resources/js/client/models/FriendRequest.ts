export type FriendRequest = {
    uid: string;
    id: number;
    uuid: string | null;
    receiver_id: number;
    user_id: number;
    status: number;
    type: number;
    created_at: string;
    updated_at: string;
    [key: string]: any // ✅ allows useForm to accept it
    avatar: string;

}

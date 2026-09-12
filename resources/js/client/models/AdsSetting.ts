export type AdsSetting = {
    admob_banner_ad_id: string | undefined;
    admob_rewarded_video_id: string;
    admob_interstitial_ad_id?: string;
    admob_native_ad_id: string;
    [key: string]: any // ✅ allows useForm to accept it
};

import { Advertisement } from "./Advertisement"
import { Swipe } from "./Swipe"

export type AdvertisementTable = {
    data: Array<Advertisement>,
    total: number,
    current_page: number,
    per_page: number,
    last_page: number,
    from: number,
    to: number,
    links?: {
        first: string,
        last: string,
        next: string,
        prev: string,
    }
}
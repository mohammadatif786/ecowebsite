import type {Campaign} from './Campaign'
export type CampaignTable = {
data: Array<Campaign>,
links?: {
    first: string,
    last: string,
    next: string,
    prev: string,
},
meta?:{
    current_page: number,
    per_page: number,
    total:number,
    last_page: number,
}
};

export type EventOrganizer = {
    id: 0,
    first_name?: string,
    last_name?: string,
    email: string,
    password?: string,
    phone_number?: string,
    radio?: string,
    passport_photo: string,
    website?: string,
    status: string,
    user: {
        id: number,
        first_name: string,
        last_name: string,
        email: string,
        phone_number?: string,
        address_proof: string
    }
}
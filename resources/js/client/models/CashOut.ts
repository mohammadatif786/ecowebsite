export type CashOut = {
    id: string;
    userId: string;
    amount: number;
    user: {
        id: string;
        name: string;
        email: string;
        firstName: string;
        lastName: string;
    }
    createdAt: string;
    updatedAt: string;
}
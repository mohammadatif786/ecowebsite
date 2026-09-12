export type EmailTemplate = {
    id?: number;
    subject: string;
    template_name: string;
    body: string;
    _method?: string
}
export type SmtpSetting = {
    host: string | undefined;
    port: string;
    encryption?: string;
    username: string;
    password: string;
    [key: string]: any // ✅ allows useForm to accept it
};

declare namespace Models {
export type UserData = {
id: number;
name: string;
is_admin: boolean;
email: string;
};
}
declare namespace Props {
export type AuthProps = {
user: Models.UserData | null;
impersonator: Models.UserData | null;
};
export type FlashProps = {
success?: string | boolean | null;
error?: string | null;
info?: string | null;
warning?: string | null;
};
export type SharedProps = {
auth: Props.AuthProps;
flash: Props.FlashProps;
};
}

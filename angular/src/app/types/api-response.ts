export type ApiResponse<T> = {
    error?: boolean;
    msg?: string;
    success?: boolean;
    errors?: object[];
    data?: T[];
};

export interface DuoStats {
    avgcrawlscore: number;
    avgscore: number;
    crawlscore: number;
    lost: number;
    name: string;
    totalgames: number;
    totalscore: number;
    winlose: number;
    won: number;
}

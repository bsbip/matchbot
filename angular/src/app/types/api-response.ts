export type ApiResponse<T> = {
    error?: boolean;
    msg?: string;
    success?: boolean;
    errors?: any[];
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

export interface PlayerStats {
    id: number;
    won: number;
    lost: number;
    draw: number;
    name: string;
    points: number;
    user_id: string;
    matches: number;
    score: number;
    crawl_score: number;
    score_avg: number;
    crawl_score_avg: number;
}

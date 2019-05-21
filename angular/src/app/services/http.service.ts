import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Injectable({
    providedIn: 'root',
})
export class HttpService {
    public headers: HttpHeaders;

    constructor(private http: HttpClient) {
        this.headers = new HttpHeaders({
            'Content-Type': 'application/json',
            token: environment.token,
        });
    }

    /**
     * Make a GET request.
     *
     * @param url the request URL
     * @returns {Observable<Object>}
     */
    get(url: string): Observable<Object> {
        return this.http.get(environment.apiBaseUrl + '/' + url, {
            headers: this.headers,
        });
    }

    /**
     * Make a POST request.
     *
     * @param url the request URL
     * @param body the request body
     * @returns {Observable<Object>}
     */
    post(url: string, body: any): Observable<Object> {
        body = JSON.stringify(body);

        return this.http.post(environment.apiBaseUrl + '/' + url, body, {
            headers: this.headers,
        });
    }

    /**
     * Make a PUT request.
     *
     * @param url the request URL
     * @param body the request body
     * @returns {Observable<Object>}
     */
    put(url: string, body: any): Observable<Object> {
        body = JSON.stringify(body);

        return this.http.put(environment.apiBaseUrl + '/' + url, body, {
            headers: this.headers,
        });
    }

    /**
     * Make a DELETE request.
     *
     * @param url the request URL
     * @returns {Observable<{}>}
     */
    delete(url: string): Observable<{}> {
        return this.http.delete(environment.apiBaseUrl + '/' + url, {
            headers: this.headers,
        });
    }
}

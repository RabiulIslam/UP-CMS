
import { Injectable } from '@angular/core';
// import { Http, Headers, Response, RequestOptions } from '@angular/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs/Rx';
import 'rxjs/add/operator/map';
import { appConfig } from '../../config';


import { HttpClient, HttpHeaders, HttpErrorResponse, HttpResponse } from '@angular/common/http';


@Injectable()
export class AuthService 
{
    headers: HttpHeaders;
    options: any;
    public auth_key: string;
    public baseUrl: string;

    constructor(private http: HttpClient, private router: Router) 
    {
        // var UrbanpointAdmin = JSON.parse(localStorage.getItem('UrbanpointAdmin'));
        // this.auth_key = UrbanpointAdmin && UrbanpointAdmin.auth_key;
        this.baseUrl = appConfig.base_url;

        // var headers = new Headers();
        // headers.append('Content-Type', 'application/json');
        // headers.append('Authorization', appConfig.default_auth_key);
        // this.options = new RequestOptions({ headers: headers });

        this.headers = new HttpHeaders({ 'Authorization': appConfig.default_auth_key});
        // headers.append('Content-Type', 'application/json');
        // headers.append('Authorization', appConfig.default_auth_key);
        this.options = {headers: this.headers, observe: 'response'};
    }

    public login(formData: any): Promise<any>
    {
        return this.http.post(this.baseUrl + appConfig.base_url_slug + 'signIn', formData, this.options)
        .toPromise().then((response: any) =>
        {
            let result: any = response.body;
            let auth_key = result.data.Authorization;
            let id = result.data.id;
            let name = result.data.name;
            let email = result.data.email;
            let phone = result.data.phone;
            let type = result.data.type;

            localStorage.setItem('UrbanpointAdmin', JSON.stringify(
                {
                    auth_key: auth_key,
                    id: id,
                    name: name,
                    email: email,
                    phone: phone,
                    type: type
                }
            ));
            return result;
        },
        (reason: any) =>
        {
            return reason;
        }).catch(this.handleError);
    }

    public forgotPassword(formData: any): Promise<any>
    {
        return this.http.post(this.baseUrl + appConfig.base_url_slug + 'forgotPassword', formData, this.options)
        .toPromise().then((response: any) =>
        {
            let result: any = response.body;
            return result;
        },
        (reason: any) =>
        {
            return reason;
        }).catch(this.handleError);
    }

    public resetPassword(formData: any): Promise<any>
    {
        return this.http.post(this.baseUrl + appConfig.base_url_slug + 'changePassword', formData, this.options)
        .toPromise().then((response: any) =>
        {
            let result: any = response.body;
            return result;
        },
        (reason: any) =>
        {
            return reason;
        }).catch(this.handleError);
    }

    public getMethod(params: string): Promise<any>
    {
        return this.http.get(this.baseUrl + params, this.options)
        .toPromise().then((response: any) =>
        {
            if (response.status === 401) 
			{
				localStorage.clear();
				this.router.navigate(['auth/login']);
			} 
			else 
			{
				return response.body;
			}
        },
        (reason: any) =>
        {
            return reason;
        }).catch(this.handleError);
    }

    public handleError(error: any): Promise<any>
    {
        // console.log("handleError = " + error);
        return error;
    }

}


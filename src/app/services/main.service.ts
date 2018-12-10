import { Injectable } from '@angular/core';
// import { Http, Headers, Response, RequestOptions } from '@angular/http';
import { HttpClient, HttpHeaders, HttpErrorResponse, HttpResponse } from '@angular/common/http';

import { Router } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import 'rxjs/add/operator/map';
import 'rxjs/add/observable/throw';
import 'rxjs/add/operator/timeoutWith';
import { appConfig } from '../../config';
import { BaseLoaderService } from '../services/base-loader.service';

@Injectable()
export class MainService 
{
	UrbanpointAdmin: any;
	// options: RequestOptions;
	// baseUrl: string;

	headers: HttpHeaders;
    options: any;
    public auth_key: string;
	public baseUrl: string;
	
	// componentSettings: Subject<any> = new Subject();

	// constructor(private http: Http, private router: Router, protected loaderService: BaseLoaderService) 
	constructor(private http: HttpClient, private router: Router, protected loaderService: BaseLoaderService) 
	{
		// this.baseUrl = appConfig.base_url;
		this.UrbanpointAdmin = JSON.parse(localStorage.getItem('UrbanpointAdmin'));

		// var headers = new Headers();
		// headers.append('Content-Type', 'application/json');
		// headers.append('Authorization', this.UrbanpointAdmin.auth_key);
		// this.options = new RequestOptions({ headers: headers });

        this.baseUrl = appConfig.base_url;

		this.headers = new HttpHeaders({ 'Authorization': this.UrbanpointAdmin.auth_key});
		// this.headers.append('Content-Type', 'multipart/form-data');
        // this.headers.append('Accept', 'application/json');
        this.options = {headers: this.headers, observe: 'response'};
	}

	public getList(params: string): Promise<any>
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
			if (reason.error.status === 401) 
			{
				localStorage.clear();
				this.router.navigate(['auth/login']);
				return reason;
			} 
			return reason;

        }).catch(this.handleError);
    }

	onLogout(): Promise<any>
	{
		return this.http.get(this.baseUrl + appConfig.base_url_slug + 'logout', this.options)
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
				if (reason.error.status === 401) 
				{
					localStorage.clear();
					this.router.navigate(['auth/login']);
					return reason;
				} 
				return reason;
	
			}).catch(this.handleError);
	}

	postData( apiSlug: string, formData: any): Promise<any>
	{
		return this.http.post(this.baseUrl + apiSlug, formData, this.options)
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
				if (reason.error.status === 401) 
				{
					localStorage.clear();
					this.router.navigate(['auth/login']);
					return reason;
				} 
				return reason;
	
			}).catch(this.handleError);
	}

	public handleError(error: any): Promise<any>
    {
        return error;
    }

}

import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog } from '@angular/material';


import { MainService, BaseLoaderService, PaginationService } from '../../services';
import { AlertDialog } from '../../lib';
import {ViewCodesComponent} from "./view_codes.dialog";
import { appConfig } from '../../../config';


@Component({
	selector: 'app-access_codes-list',
	templateUrl: './access_codes-list.component.html'
})
export class AccessCodesListComponent implements OnInit 
{
	search: string;
	sub: Subscription;
	index:  any = 1;
	totalPages: number;
	pages: any;
	totalItems: any;
	currentPage:  any = 1;
	merchantsCount: any;
	AccessCodes: any;
	isMultiple: any;
	searchTimer:any;
	perPage: any;
	componentSettings: any = {
		name: null,
		paggination: null,
		search: null
	};
	appName: any;
	AccessCodeHeading: string;

	constructor(protected router: Router,
		protected _route: ActivatedRoute,
		protected mainApiService: MainService,
		protected paginationService: PaginationService, 
		protected loaderService: BaseLoaderService, protected dialog: MatDialog)	
	{
		this.search = '';
		this.AccessCodes = [];
		this.isMultiple = false;
		this.perPage = 20;
		this.appName = 1;
		this.AccessCodeHeading = 'APP 1';
	}

	ngOnInit() 
	{
		this.componentSettings = JSON.parse(localStorage.getItem('componentSettings'))
		if(this.componentSettings)
		{
			if(this.componentSettings.name != null && this.componentSettings.name == 'AccessCodes')
			{
				this.currentPage = this.componentSettings.paggination;
				this.index = this.componentSettings.paggination;
				this.search = this.componentSettings.search;
			}
		}
		// this.sub = this._route.params.subscribe(params => {
		// 	this.appName = params['app'];
		this.gerAccessCodesList(this.currentPage);
		// 	console.log(params);
		// });
	}

	addNew() 
	{
		this.router.navigateByUrl('main/access_codes/add');
	}

	onAppChange(): void
	{
		if(this.appName == '1')
		{
			this.AccessCodeHeading = 'APP 1';
		}
		else if(this.appName == '2')
		{
			this.AccessCodeHeading = 'APP 2';
		}

		this.gerAccessCodesList(1);
	}

	onSearchAccessCode(): void
	{
		clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {

			this.gerAccessCodesList(1);

		}, 700);

	}

	getCode(code): boolean
	{
		console.log(code);
		return true
	}

	onSwitchCodes(isMultiple): void
	{
		this.isMultiple = isMultiple;
		this.gerAccessCodesList(1);
	}

	gerAccessCodesList(index, isLoaderHidden?: boolean): void
	{
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}

		let url = 'getAccessCodes?index=' + index + '&index2=' + this.perPage;

		if(this.search != '')
		{
			// url = 'getAccessCodes?type=' + this.type + '&search=' + this.search;
			url = url + '&search=' + this.search;
		}

		if(this.appName == '1')
		{
			url = url + '&user_app_id=' + 1;
		}
		else if(this.appName == '2')
		{
			url = url + '&user_app_id=' + 2;
		}

		localStorage.setItem('componentSettings', JSON.stringify(
			{
				name: 'AccessCodes',
				paggination: this.index,
				search: this.search
			}
        ));
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				let AccessCodes : any = result.data.accesscodes;
				this.merchantsCount = result.data.accesscodesCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.merchantsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);

				AccessCodes.forEach(element => {
					if(element.status == 1)
					{
						element['slide'] = true;
					}
					else if(element.status == 0)
					{
						element['slide'] = false;
					}
				});
				// console.log('dsdsd')
				this.AccessCodes = AccessCodes;
			}
			else
			{
				this.AccessCodes = [];
				this.merchantsCount = 0;
				this.currentPage = 1;
				this.pages = this.paginationService.setPagination(this.merchantsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
		});
	}

	setPage(pageDate: any) 
	{
		this.currentPage = pageDate.page;
		this.perPage = pageDate.perPage;
		this.index = this.currentPage;
		this.gerAccessCodesList(pageDate.page);
	}

	onEditAccessCodes(accessCode): void 
	{
		localStorage.setItem('AccessCode', JSON.stringify(accessCode));
		this.router.navigateByUrl('main/access_codes/'+ accessCode.id);
	}

	onChangeAccessCode(accessCode): void 
	{
		let status: any;
		if(accessCode.slide)
		{
			status = 1;
		}
		else
		{
			status = 0;
		}
		let merchantData = {
			id: accessCode.id,
			status: status
		};

		let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
		let cm = dialogRef.componentInstance;
		cm.heading = 'Change AccessCode';
		cm.message = 'Are you sure to Update AccessCode';
		cm.submitButtonText = 'Yes';
		cm.cancelButtonText = 'No';
		cm.type = 'ask';
		cm.methodName =  appConfig.base_url_slug + 'updateAccessCode';
		cm.dataToSubmit = merchantData;
		cm.showLoading = true;

		dialogRef.afterClosed().subscribe(result => {
			console.log(result);
			if(result)
			{
				this.gerAccessCodesList(this.currentPage, true);
			}
			else
			{
				accessCode.slide = !accessCode.slide;
			}
		})
	}

	onViewAllAccessCodes(code:any)
	{
		let dialogRef = this.dialog.open(ViewCodesComponent, {autoFocus: false});
		dialogRef.componentInstance.Deal = code;
	}
}

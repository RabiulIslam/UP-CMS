import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog } from '@angular/material';


import { MainService, BaseLoaderService, PaginationService } from '../../services';
import { AlertDialog } from '../../lib';
import { MerchantDetailsComponent } from './merchant-details.component';
import { AppLoaderService } from '../../lib/app-loader/app-loader.service';
import { ExportCSVComponent } from '../../lib/export_csv.component';
import { appConfig } from '../../../config';


@Component({
	selector: 'app-merchants-list',
	templateUrl: './merchants-list.component.html'
})
export class MerchantsListComponent extends ExportCSVComponent implements OnInit
{
	search: string;
	sub: Subscription;
	index: any = 1;
	totalPages: number;
	pages: any;
	totalItems: any;
	currentPage: any = 1;
	merchantsCount: any;
	Merchants: any;
	searchTimer:any;
	sortby: string;
	orderby: string;
	perPage: any;
	// ArrayCSV: any[];
	// loaderMessage: string;
	// start_date: string;
	// end_date: string;
	// ArrayCSVCount: any;
	componentSettings: any = {
		name: null,
		paggination: null,
		search: null
	};


	constructor(protected router: Router,
		protected mainApiService: MainService,
		protected paginationService: PaginationService, 
		protected loaderService: BaseLoaderService, protected appLoaderService: AppLoaderService, protected dialog: MatDialog)	
	{
		super(mainApiService, appLoaderService, dialog);
		this.method = 'getMerchants';
		this.search = '';
		this.Merchants = [];
		this.sortby = '';
		this.orderby = 'ASC';
		this.perPage = 20;
		// this.ArrayCSV = [];
		// this.loaderMessage = "Please wait CSV file is preparing to download.";
		// this.ArrayCSVCount = 0;
	}

	ngOnInit() 
	{
		this.componentSettings = JSON.parse(localStorage.getItem('componentSettings'))
		if(this.componentSettings)
		{
			if(this.componentSettings.name != null && this.componentSettings.name == 'Merchants')
			{
				this.currentPage = this.componentSettings.paggination;
				this.index = this.componentSettings.paggination;
				this.search = this.componentSettings.search;
			}
		}
		this.method = 'getMerchants';
		this.gerMerchantsList(1);
	}

	addNew() {
		this.router.navigateByUrl('main/merchants/add');
	}

	onSearchMerchant(): void
	{
		clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {

			this.gerMerchantsList(1);

		}, 800);


	}

	gerMerchantsList(index, isLoaderHidden?: boolean): void
	{
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}

		let url = 'getMerchants?index=' + index + '&index2=' + this.perPage + '&orderby=' + this.orderby;

		if(this.search != '')
		{
			url = url + '&search=' + this.search;
		}
		if(this.sortby != '')
		{
			url = url + '&sortby=' + this.sortby;
		}
		// if(this.orderby != '')
		// {
		// 	url = url + '&orderby=' + this.orderby;
		// }
		// else
		// {
		// 	this.search = '';
		// }

		localStorage.setItem('componentSettings', JSON.stringify(
			{
				name: 'Merchants',
				paggination: this.index,
				search: this.search
			}
        ));
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.Merchants = result.data.merchants;
				this.merchantsCount = result.data.merchantsCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.merchantsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
			else
			{
				this.Merchants = [];
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
		this.gerMerchantsList(pageDate.page);
	}

	onViewDetails(merchant): void
	{
		let dialogRef = this.dialog.open(MerchantDetailsComponent, {autoFocus: false});
		let compInstance = dialogRef.componentInstance;
		compInstance.Merchant = merchant;
		dialogRef.afterClosed().subscribe(result => {
			if(result != 'cancel')
			{
				this.gerMerchantsList(this.currentPage);
			}
		})
	}

	onEditMerchant(merchant, event): void 
	{
		localStorage.setItem('Merchant', JSON.stringify(merchant));
		this.router.navigateByUrl('main/merchants/' + merchant.id);
		// this.router.navigateByUrl('main/merchants/1');
		event.stopPropagation();
	}

	onAddMultiple(merchant, event): void 
	{
		this.router.navigateByUrl('main/merchants/multiple_outlets/' + merchant.id);
		event.stopPropagation();
	}

	onDeleteMerchant(merchant): void 
	{
		let merchantData = {
			id: merchant.id
		};

		let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
		let cm = dialogRef.componentInstance;
		cm.heading = 'Delete Organization';
		cm.message = 'Are you sure to delete Organization';
		cm.submitButtonText = 'Yes';
		cm.cancelButtonText = 'No';
		cm.type = 'ask';
		cm.methodName = appConfig.base_url_slug + 'deleteMerchant';
		cm.dataToSubmit = merchantData;
		cm.showLoading = true;

		dialogRef.afterClosed().subscribe(result => {
			if(result)
			{
				this.gerMerchantsList(this.currentPage, true);
			}
		})
	}
}

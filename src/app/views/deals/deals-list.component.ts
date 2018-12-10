import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog } from '@angular/material';


import { MainService, BaseLoaderService, PaginationService } from '../../services';
import { AlertDialog, ExportCSVDialog } from '../../lib';
import { DealDetailsComponent } from './deal-details.component';
import { AppLoaderService } from '../../lib/app-loader/app-loader.service';
import { ExportCSVComponent } from '../../lib/export_csv.component';
import { EditSelectedComponent } from './edit-selected.component';
import { appConfig } from '../../../config';


@Component({
	selector: 'app-deals-list',
	templateUrl: './deals-list.component.html'
})
export class DealsListComponent extends ExportCSVComponent implements OnInit 
{
	search: string;
	sub: Subscription;
	index:  any = 1;
	totalPages: number;
	pages: any;
	totalItems: any;
	currentPage: any = 1;
	offersCount: any;
	Deals: any;
	searchTimer:any;
	orderby: string;
	sortby: string;
	perPage: number;
	selectAll: boolean;
	selectedDeals: any;
	componentSettings: any = {
		name: null,
		paggination: null,
		search: null
	};

	constructor(protected router: Router,
		protected _route: ActivatedRoute,
		protected mainApiService: MainService,
		protected paginationService: PaginationService, 
		protected loaderService: BaseLoaderService, protected appLoaderService: AppLoaderService, protected dialog: MatDialog)	
	{
		super(mainApiService, appLoaderService, dialog);
		this.method = 'getOffers';
		this.search = '';
		this.Deals = [];
		this.sortby = '';
		this.orderby = 'ASC';
		this.perPage = 20;
		this.selectedDeals = [];
	}

	ngOnInit() 
	{
		this.componentSettings = JSON.parse(localStorage.getItem('componentSettings'))
		if(this.componentSettings)
		{
			if(this.componentSettings.name != null && this.componentSettings.name == 'Offers')
			{
				this.currentPage = this.componentSettings.paggination;
				this.index = this.componentSettings.paggination;
				this.search = this.componentSettings.search;
			}
		}
		this.method = 'getOffers';
		// this.sub = this._route.params.subscribe(params => {
			// this.type = params['type'];
			// if (this.type) 
			// {
				this.gerDealsList(this.currentPage);
			// }
			// console.log(params);
		// });
	}

	onSelectAll(): void
	{
		if(this.selectAll)
		{
			this.Deals.forEach(element => {
				element.selected = true;
				this.selectedDeals.push(element);
			});
		}
		else
		{
			this.Deals.forEach(element => {
				element.selected = false;
				this.selectedDeals = [];
			});
		}
	}

	onSelectOne(event, deal): void
	{
		this.selectedDeals.push(deal);
		event.stopPropagation();
	}

	addNew() {
		this.router.navigateByUrl('main/deals/add');
	}

	onSearchDeal(): void
	{
		clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {

			this.gerDealsList(1);

		}, 800);

	}

	gerDealsList(index, isLoaderHidden?: boolean): void
	{
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}

		let url = 'getOffers?index=' + index + '&index2=' + this.perPage + '&orderby=' + this.orderby;

		if(this.search != '')
		{
			url = url + '&search=' + this.search;
		}
		if(this.sortby != '')
		{
			url = url + '&sortby=' + this.sortby;
		}

		localStorage.setItem('componentSettings', JSON.stringify(
			{
				name: 'Offers',
				paggination: this.index,
				search: this.search
			}
        ));
		
		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.Deals = result.data.offers;
				this.offersCount = result.data.offersCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.offersCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
				// this.Deals.forEach(element => {
				// 	element.selected = false;
				// });
				this.selectAll = false;
				this.selectedDeals = [];
			}
			else
			{
				this.Deals = [];
				this.offersCount = 0;
				this.currentPage = 1;
				this.pages = this.paginationService.setPagination(this.offersCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
		});
	}

	onViewDetails(deal): void
	{
		let dialogRef = this.dialog.open(DealDetailsComponent, {autoFocus: false});
		let compInstance = dialogRef.componentInstance;
		compInstance.Deal = deal;
		dialogRef.afterClosed().subscribe(result => {
			// if(result == 'reload')
			// {
				this.gerDealsList(this.currentPage);
			// }
		})
	}

	onEditSelectedDeals(): void
	{
		let dialogRef = this.dialog.open(EditSelectedComponent, {autoFocus: false});
		let compInstance = dialogRef.componentInstance;
		compInstance.Deals = this.selectedDeals;
		dialogRef.afterClosed().subscribe(result => {
			if(result)
			{
				this.gerDealsList(this.currentPage);
			}
		})
	}

	setPage(pageDate: any) 
	{
		this.currentPage = pageDate.page;
		this.perPage = pageDate.perPage;
		this.index = this.currentPage;
		this.gerDealsList(pageDate.page);
	}


	onEditDeal(deal, event): void 
	{
		localStorage.setItem('Deal', JSON.stringify(deal));
		this.router.navigateByUrl('main/deals/' + deal.id);
		event.stopPropagation();
	}

	onDeleteDeal(deal): void 
	{
		let dealData = {
			id: deal.id
		};

		let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
		let cm = dialogRef.componentInstance;
		cm.heading = 'Delete Deal';
		cm.message = 'Are you sure to delete Deal';
		cm.submitButtonText = 'Yes';
		cm.cancelButtonText = 'No';
		cm.type = 'ask';
		cm.methodName = appConfig.base_url_slug + 'deleteDeal';
		cm.dataToSubmit = dealData;
		cm.showLoading = true;

		dialogRef.afterClosed().subscribe(result => {
			if(result)
			{
				this.gerDealsList(this.currentPage, true);
			}
		})
	}
}

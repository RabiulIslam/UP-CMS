import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatDialog } from '@angular/material';


import { MainService, BaseLoaderService, PaginationService } from '../../services';
import { AlertDialog } from '../../lib';
import { OutletDetailsComponent } from './outlet-details.component';
import { AppLoaderService } from '../../lib/app-loader/app-loader.service';
import { ExportCSVComponent } from '../../lib/export_csv.component';
import { appConfig } from '../../../config';


@Component({
	selector: 'app-outlets-list',
	templateUrl: './outlets-list.component.html'
})
export class OutletsListComponent  extends ExportCSVComponent implements OnInit 
{
	search: string;
	sub: Subscription;
	index: any = 1;
	totalPages: number;
	pages: any;
	totalItems: any;
	currentPage: any = 1;
	outletsCount: any;
	Outlets: any;
	Categories: any;
	category_id: string;
	searchTimer:any;
	orderby: string;
	sortby: string;
	perPage: number;
	componentSettings: any;

	constructor(protected router: Router,
		protected _route: ActivatedRoute,
		protected mainApiService: MainService,
		protected paginationService: PaginationService, 
		protected loaderService: BaseLoaderService, protected appLoaderService: AppLoaderService, protected dialog: MatDialog)	
	{
		super(mainApiService, appLoaderService, dialog);
		this.method = 'getOutlets';
		this.search = '';
		this.Outlets = [];
		this.category_id = '';
		this.sortby = '';
		this.orderby = 'ASC';
		this.perPage = 20;

		this.componentSettings = {
			name: null,
			paggination: null,
			search: null
		}
	}

	ngOnInit() 
	{
		this.componentSettings = JSON.parse(localStorage.getItem('componentSettings'))
		if(this.componentSettings)
		{
			if(this.componentSettings.name != null && this.componentSettings.name == 'Outlets')
			{
				this.currentPage = this.componentSettings.paggination;
				this.index = this.componentSettings.paggination;
				this.search = this.componentSettings.search;
			}
		}

		this.method = 'getOutlets';
		this.gerOutletsList(this.currentPage);
		this.getCategoriesList();
		
	}

	addNew() {
		this.router.navigateByUrl('main/outlets/add');
	}

	onSearchOutlet(): void
	{
		clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {
			this.gerOutletsList(1);
		}, 800);
	}

	onViewDetails(outlet): void
	{
		let dialogRef = this.dialog.open(OutletDetailsComponent, {autoFocus: false});
		let compInstance = dialogRef.componentInstance;
		compInstance.Outlet = outlet;
		dialogRef.afterClosed().subscribe(result => {
			if(result != 'cancel')
			{
				this.gerOutletsList(this.currentPage);
			}
		})
	}

	getCategoriesList(): void
	{
		this.mainApiService.getList(appConfig.base_url_slug + 'getCategories')
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.Categories = result.data.categories;
			}
			else
			{
				this.Categories = [];
			}
		});
	}

	gerOutletsList(index, isLoaderHidden?: boolean): void
	{
		if(isLoaderHidden)
		{
			this.loaderService.setLoading(false);
		}
		else
		{
			this.loaderService.setLoading(true);
		}

		let url = 'getOutlets?index=' + index + '&index2=' + this.perPage + '&orderby=' + this.orderby;

		if(this.search != '')
		{
			url = url + '&search=' + this.search;
		}
		if(this.sortby != '')
		{
			url = url + '&sortby=' + this.sortby;
		}

		if(this.category_id != '')
		{
			url = url + '&category_id=' + this.category_id;
		}

		localStorage.setItem('componentSettings', JSON.stringify(
			{
				name: 'Outlets',
				paggination: this.index,
				search: this.search
			}
        ));

		this.mainApiService.getList(appConfig.base_url_slug + url)
		.then(result => {
			if (result.status === 200  && result.data) 
			{
				this.Outlets = result.data.outlets;
				this.outletsCount = result.data.outletsCount;
				this.currentPage = index;
				this.pages = this.paginationService.setPagination(this.outletsCount, index, this.perPage);
				this.totalPages = this.pages.totalPages;
				this.loaderService.setLoading(false);
			}
			else
			{
				this.Outlets = [];
				this.outletsCount = 0;
				this.currentPage = 1;
				this.pages = this.paginationService.setPagination(this.outletsCount, index, this.perPage);
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
		this.gerOutletsList(pageDate.page);
	}

	onAddMultiple(item, event): void 
	{
		this.router.navigateByUrl('main/outlets/multiple_deals/' + item.id);
		event.stopPropagation();
	}


	onEditOutlet(outlet, event): void 
	{
		localStorage.setItem('Outlet', JSON.stringify(outlet));
		this.router.navigateByUrl('main/outlets/' + outlet.id);
		event.stopPropagation();
	}

	onDeleteOutlet(outlet): void 
	{
		let merchantData = {
			id: outlet.id
		};

		let dialogRef = this.dialog.open(AlertDialog, {autoFocus: false});
		let cm = dialogRef.componentInstance;
		cm.heading = 'Delete Outlet';
		cm.message = 'Are you sure to delete Outlet';
		cm.submitButtonText = 'Yes';
		cm.cancelButtonText = 'No';
		cm.type = 'ask';
		cm.methodName = appConfig.base_url_slug + 'deleteOutlet';
		cm.dataToSubmit = merchantData;
		cm.showLoading = true;

		dialogRef.afterClosed().subscribe(result => {
			if(result)
			{
				this.gerOutletsList(this.currentPage, true);
			}
		})
	}
}
